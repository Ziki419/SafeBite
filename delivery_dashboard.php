<?php
require 'db.php';

// algo1 Calculate the delivery percentage based on the order region and driver's region.
function calculateDeliveryRate($order_region, $driver_region) {
    if ($order_region == $driver_region) return 20;
    return 25;
}
// algo2 Calculate the delivery earnings based on the order total and delivery rate.
function calculateDeliveryEarning($total, $rate) {
    $earning = $total * ($rate / 100);
    return $earning;
}
// Get the delivery driver information from the database.
function getDriver($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT id, username, delivery_region FROM users WHERE id = ? AND role = 'delivery'");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}
// Check if the given region code is valid.
function validDashboardView($view) {
    if ($view == 'available') return true;
    if ($view == 'active') return true;
    if ($view == 'credit') return true;
    return false;
}
// Check if the given region code is valid.
function validDashboardScope($scope) {
    if ($scope == 'all') return true;
    if ($scope == 'my_region') return true;
    if ($scope == 'outside') return true;
    if (validDeliveryRegion($scope)) return true;
    return false;
}

function getAvailableOrders($pdo, $driver_id, $driver_region, $scope) {
    if ($scope == 'my_region') {
        $stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.status = 'pending' AND o.delivery_user_id IS NULL AND o.user_id != ? AND o.delivery_region = ? ORDER BY o.delivery_date ASC, o.delivery_time ASC, o.id DESC");
        $stmt->execute([$driver_id, $driver_region]);
        return $stmt->fetchAll();
    }

    if ($scope == 'outside') {
        $stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.status = 'pending' AND o.delivery_user_id IS NULL AND o.user_id != ? AND o.delivery_region != ? ORDER BY o.delivery_date ASC, o.delivery_time ASC, o.id DESC");
        $stmt->execute([$driver_id, $driver_region]);
        return $stmt->fetchAll();
    }

    if (validDeliveryRegion($scope)) {
        $stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.status = 'pending' AND o.delivery_user_id IS NULL AND o.user_id != ? AND o.delivery_region = ? ORDER BY o.delivery_date ASC, o.delivery_time ASC, o.id DESC");
        $stmt->execute([$driver_id, $scope]);
        return $stmt->fetchAll();
    }

    $stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.status = 'pending' AND o.delivery_user_id IS NULL AND o.user_id != ? ORDER BY o.delivery_date ASC, o.delivery_time ASC, o.id DESC");
    $stmt->execute([$driver_id]);
    return $stmt->fetchAll();
}

function getActiveOrders($pdo, $driver_id) {
    $stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.delivery_user_id = ? AND o.status = 'delivering' ORDER BY o.delivery_date ASC, o.delivery_time ASC, o.id DESC");
    $stmt->execute([$driver_id]);
    return $stmt->fetchAll();
}

function getDeliveredOrders($pdo, $driver_id) {
    $stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.delivery_user_id = ? AND o.status = 'delivered' ORDER BY o.id DESC");
    $stmt->execute([$driver_id]);
    return $stmt->fetchAll();
}

function getOrderItems($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT order_items.*, products.name FROM order_items JOIN products ON products.id = order_items.product_id WHERE order_items.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}


// Assign an available order to a delivery driver when they accept it.
function acceptOrder($pdo, $order_id, $driver_id, $driver_region) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if (!$order) {return 0;}
    if ($order['status'] != 'pending') return 0;
    if ($order['delivery_user_id'] != null) return 0;

    // Driver cannot deliver their own order
    if ($order['user_id'] == $driver_id) return 0;

    $rate = calculateDeliveryRate($order['delivery_region'],$driver_region);
    $stmt = $pdo->prepare("UPDATE orders SET delivery_user_id = ?, delivery_rate = ?, delivery_earning = 0, status = 'delivering', picked_at = NOW(), delivered_at = NULL WHERE id = ? AND status = 'pending' AND delivery_user_id IS NULL");
    $stmt->execute([$driver_id, $rate, $order_id]);
    $stmt = $pdo->prepare("SELECT delivery_user_id, status FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $check = $stmt->fetch();
    if (!$check) {return 0;}
    if ($check['delivery_user_id'] != $driver_id) return 0;
    if ($check['status'] != 'delivering') return 0;
    return $rate;
}

// Mark a delivery as complete and calculate the driver's earnings.
function markOrderDelivered($pdo, $order_id, $driver_id) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND delivery_user_id = ? AND status = 'delivering'");
    $stmt->execute([$order_id, $driver_id]);
    $order = $stmt->fetch();
    if (!$order) {return -1;}
    $total = $order['total_price'];
    $rate = $order['delivery_rate'];
    $earning = calculateDeliveryEarning($total,$rate);
    $stmt = $pdo->prepare("UPDATE orders SET status = 'delivered', delivery_earning = ?, delivered_at = NOW() WHERE id = ? AND delivery_user_id = ? AND status = 'delivering'");
    $stmt->execute([$earning, $order_id, $driver_id]);
    return $earning;
}

function getDriverStats($pdo, $driver_id) {
    $stmt = $pdo->prepare("SELECT status, delivery_earning, delivered_at FROM orders WHERE delivery_user_id = ?");
    $stmt->execute([$driver_id]);
    $orders = $stmt->fetchAll();

    $active_orders = 0;
    $delivered_orders = 0;
    $total_credit = 0;
    $month_credit = 0;

    $current_month = date("Y-m");

    foreach ($orders as $order) {
        if ($order['status'] == 'delivering') {$active_orders++;}
        if ($order['status'] == 'delivered') {
            $delivered_orders++;
            $earning = 0;
            if ($order['delivery_earning'] != null) {
                $earning = $order['delivery_earning'];
            }
            $total_credit = $total_credit + $earning;
            if ($order['delivered_at'] != null) {
                $delivery_month = substr($order['delivered_at'],0,7);
                if ($delivery_month == $current_month) {
                    $month_credit = $month_credit + $earning;
                }
            }
        }
    }
    return array('active_orders' => $active_orders,'delivered_orders' => $delivered_orders,'total_credit' => $total_credit,'month_credit' => $month_credit);
}

function deliveryStatus($status) {
    if ($status == 'pending') return tr('Pending', 'ממתינה');
    if ($status == 'delivering') return tr('Delivering', 'במשלוח');
    if ($status == 'delivered') return tr('Delivered', 'נמסרה');
    if ($status == 'cancelled') return tr('Cancelled', 'בוטלה');
    return $status;
}

function formatDeliveryDate($date) {
    if ($date == null || $date == '') return '-';
    $year = substr($date, 0, 4);
    $month = substr($date, 5, 2);
    $day = substr($date, 8, 2);
    $time = substr($date, 11, 5);
    return $day . '/' . $month . '/' . $year . ' ' . $time;
}

// Only logged-in delivery users can access this dashboard.
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!isDelivery()) {
    header("Location: index.php");
    exit();
}

$driver_id = $_SESSION['user_id'];

$driver = getDriver($pdo, $driver_id);

if (!$driver) {
    header("Location: index.php");
    exit();
}

$driver_region = $driver['delivery_region'];

$regions = deliveryRegions();

$message = '';
$message_type = 'success';

$view = 'available';

if (isset($_GET['view'])) {
    $view = $_GET['view'];
}

if (validDashboardView($view) == false) {
    $view = 'available';
}

$scope = 'all';

if (isset($_GET['scope'])) {
    $scope = $_GET['scope'];
}

if (validDashboardScope($scope) == false) {
    $scope = 'all';
}


// Driver chooses an available order and starts delivering it.
if (isset($_POST['pick_order'])) {

    $order_id = 0;

    if (isset($_POST['order_id'])) {
        $order_id = (int)$_POST['order_id'];
    }


    if ($driver_region == null || validDeliveryRegion($driver_region) == false) {

        $message = tr('Your delivery account does not have a work region.','לחשבון השליח שלך לא הוגדר אזור עבודה.');
        $message_type = 'error';

    } else {

        $rate = acceptOrder($pdo,$order_id,$driver_id,$driver_region);

        if ($rate == 0) {
            $message = tr('This order is no longer available.','ההזמנה כבר אינה זמינה.');

            $message_type = 'error';

        } else {
            $message = tr(
                'Order accepted successfully. Your delivery rate is ' . $rate . '%.',
                'ההזמנה התקבלה בהצלחה. אחוז הרווח שלך הוא ' . $rate . '%.'
            );

            $view = 'active';
        }
    }
}

if (isset($_POST['mark_delivered'])) {

    $order_id = 0;

    if (isset($_POST['order_id'])) {
        $order_id = (int)$_POST['order_id'];
    }


    $earning = markOrderDelivered($pdo,$order_id,$driver_id);

    if ($earning < 0) {

        $message = tr(
            'This delivery could not be completed.',
            'לא ניתן להשלים את המשלוח.'
        );

        $message_type = 'error';

        $view = 'active';

    } else {

        $message = tr(
            'Order delivered successfully. Your credit is $' . number_format($earning, 2) . '.',
            'ההזמנה נמסרה בהצלחה. הקרדיט שלך הוא $' . number_format($earning, 2) . '.'
        );

        $view = 'credit';
    }
}

$stats = getDriverStats($pdo,$driver_id);

// GET ORDERS
if ($view == 'available') {
    $orders = getAvailableOrders($pdo,$driver_id,$driver_region,$scope);
}

elseif ($view == 'active') {
    $orders = getActiveOrders($pdo,$driver_id);
}

else {
    $orders = getDeliveredOrders($pdo,$driver_id);
}

$message_class = 'alert-success';

if ($message_type == 'error') {
    $message_class = 'alert-error';
}

$page_language = 'en';
$page_direction = 'ltr';
if (isset($_SESSION['lang']) && $_SESSION['lang'] == 'he') {
    $page_language = 'he';
    $page_direction = 'rtl';
}
?>

<!DOCTYPE html>

<html lang="<?= $page_language ?>" dir="<?= $page_direction ?>">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= tr(
            'Delivery Dashboard - SafeBite',
            'לוח שליח - SafeBite'
        ) ?>
    </title>

    <link rel="stylesheet" href="style.css">

    <style>

        .delivery-wrap {
            max-width: 1250px;
            margin: 35px auto;
            padding: 0 20px 45px;
        }

        .delivery-header {
            background: linear-gradient(
                135deg,
                var(--green),
                var(--green-dark)
            );
            color: white;
            border-radius: 22px;
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: 0 14px 30px rgba(31,106,49,0.18);
        }

        .delivery-header h1 {
            margin: 0 0 8px;
            color: white;
        }

        .delivery-header p {
            margin: 0;
            color: rgba(255,255,255,0.92);
        }

        .delivery-stats {
            display: grid;
            grid-template-columns: repeat(4,1fr);
            gap: 16px;
            margin-bottom: 22px;
        }

        .delivery-stat {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .delivery-stat strong {
            display: block;
            font-size: 1.8rem;
            color: var(--green-dark);
            margin-bottom: 5px;
        }

        .delivery-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .delivery-tab {
            display: inline-block;
            padding: 10px 17px;
            border-radius: 999px;
            background: white;
            border: 1px solid #cfe2d1;
            color: var(--green-dark);
            font-weight: 700;
            text-decoration: none;
        }

        .delivery-tab.active {
            background: var(--green);
            border-color: var(--green);
            color: white;
        }

        .region-filter {
            display: flex;
            gap: 12px;
            align-items: end;
            flex-wrap: wrap;
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 22px;
            box-shadow: var(--shadow);
        }

        .region-filter .form-group {
            margin: 0;
            min-width: 250px;
            flex: 1;
        }

        .delivery-orders {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 20px;
        }

        .delivery-order-card,
        .credit-panel,
        .delivery-empty {
            background: white;
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 22px;
            box-shadow: var(--shadow);
        }

        .delivery-order-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .delivery-order-top h2 {
            margin: 0;
            color: var(--green-dark);
        }

        .region-rate-badge {
            display: inline-block;
            margin-top: 8px;
            padding: 7px 11px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.88rem;
        }

        .region-rate-badge.home {
            background: #e7f8ea;
            color: #1f6a31;
        }

        .region-rate-badge.outside {
            background: #fff3d8;
            color: #9a5b00;
        }

        .delivery-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 18px;
        }

        .delivery-info-item {
            background: #f7fbf7;
            border-radius: 11px;
            padding: 11px 13px;
            overflow-wrap: anywhere;
        }

        .delivery-info-item.full {
            grid-column: 1 / -1;
        }

        .delivery-info-label {
            display: block;
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .delivery-items {
            margin: 10px 0 18px;
            padding-inline-start: 22px;
        }

        .earning-box {
            background: var(--green-soft);
            border: 1px solid #cfe8d2;
            border-radius: 12px;
            padding: 13px 15px;
            color: var(--green-dark);
            font-weight: 800;
            margin-bottom: 16px;
        }

        .delivery-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .delivery-empty,
        .credit-panel {
            grid-column: 1 / -1;
        }

        .delivery-empty {
            text-align: center;
            color: var(--muted);
            padding: 40px 20px;
        }

        .credit-table-wrap {
            overflow-x: auto;
        }

        @media (max-width: 900px) {

            .delivery-stats {
                grid-template-columns: repeat(2,1fr);
            }

            .delivery-orders {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {

            .delivery-wrap {
                padding: 0 12px 30px;
            }

            .delivery-stats {
                grid-template-columns: 1fr;
            }

            .delivery-info {
                grid-template-columns: 1fr;
            }

            .delivery-info-item.full {
                grid-column: auto;
            }
        }

    </style>

</head>
<body>

<?php include 'navbar.php'; ?>

<main class="delivery-wrap">


    <section class="delivery-header">

        <h1>
            <?= tr(
                'Delivery Dashboard',
                'לוח שליח'
            ) ?>
        </h1>

        <p>

            <?= tr(
                'Work region',
                'אזור עבודה'
            ) ?>:

            <strong>
                <?= htmlspecialchars(regionLabel($driver_region)) ?>
            </strong>

            <br>

            <?= tr(
                'Your region pays 20%. Outside your region pays 25%.',
                'האזור שלך משלם 20%. מחוץ לאזור שלך משלם 25%.'
            ) ?>

        </p>

    </section>


    <?php if ($message != ''): ?>

        <div class="alert <?= $message_class ?>">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($driver_region == null || validDeliveryRegion($driver_region) == false): ?>

        <div class="alert alert-error">

            <?= tr(
                'No work region is assigned to your account. Ask the administrator to assign one.',
                'לא הוגדר אזור עבודה לחשבונך. יש לפנות למנהל.'
            ) ?>

        </div>

    <?php endif; ?>


    <section class="delivery-stats">

        <div class="delivery-stat">

            <strong>
                <?= $stats['active_orders'] ?>
            </strong>

            <?= tr(
                'Active Orders',
                'הזמנות פעילות'
            ) ?>

        </div>


        <div class="delivery-stat">

            <strong>
                <?= $stats['delivered_orders'] ?>
            </strong>

            <?= tr(
                'Delivered Orders',
                'הזמנות שנמסרו'
            ) ?>

        </div>


        <div class="delivery-stat">

            <strong>
                $<?= number_format($stats['total_credit'], 2) ?>
            </strong>

            <?= tr(
                'Total Credit',
                'קרדיט כולל'
            ) ?>

        </div>


        <div class="delivery-stat">

            <strong>
                $<?= number_format($stats['month_credit'], 2) ?>
            </strong>

            <?= tr(
                'This Month',
                'החודש'
            ) ?>

        </div>

    </section>


    <nav class="delivery-tabs">


        <a
            href="delivery_dashboard.php?view=available"
            class="delivery-tab <?php if ($view == 'available') echo 'active'; ?>"
        >
            <?= tr(
                'Available Orders',
                'הזמנות זמינות'
            ) ?>
        </a>


        <a
            href="delivery_dashboard.php?view=active"
            class="delivery-tab <?php if ($view == 'active') echo 'active'; ?>"
        >
            <?= tr(
                'My Active Deliveries',
                'המשלוחים הפעילים שלי'
            ) ?>
        </a>


        <a
            href="delivery_dashboard.php?view=credit"
            class="delivery-tab <?php if ($view == 'credit') echo 'active'; ?>"
        >
            <?= tr(
                'Credit History',
                'היסטוריית קרדיט'
            ) ?>
        </a>


    </nav>


    <?php if ($view == 'available'): ?>


        <form method="GET" class="region-filter">

            <input
                type="hidden"
                name="view"
                value="available"
            >


            <div class="form-group">

                <label>
                    <?= tr(
                        'Choose orders by region',
                        'בחר הזמנות לפי אזור'
                    ) ?>
                </label>


                <select
                    name="scope"
                    class="form-control"
                >


                    <option
                        value="all"
                        <?php if ($scope == 'all') echo 'selected'; ?>
                    >
                        <?= tr(
                            'All Regions',
                            'כל האזורים'
                        ) ?>
                    </option>


                    <option
                        value="my_region"
                        <?php if ($scope == 'my_region') echo 'selected'; ?>
                    >
                        <?= tr(
                            'My Region - 20%',
                            'האזור שלי - 20%'
                        ) ?>
                    </option>


                    <option
                        value="outside"
                        <?php if ($scope == 'outside') echo 'selected'; ?>
                    >
                        <?= tr(
                            'Outside My Region - 25%',
                            'מחוץ לאזור שלי - 25%'
                        ) ?>
                    </option>


                    <?php foreach ($regions as $code => $labels): ?>

                        <option
                            value="<?= htmlspecialchars($code) ?>"
                            <?php if ($scope == $code) echo 'selected'; ?>
                        >
                            <?= htmlspecialchars(
                                tr(
                                    $labels['en'],
                                    $labels['he']
                                )
                            ) ?>
                        </option>

                    <?php endforeach; ?>


                </select>

            </div>


            <button
                type="submit"
                class="btn btn-primary"
            >
                <?= tr(
                    'Apply Filter',
                    'הפעל סינון'
                ) ?>
            </button>


        </form>


    <?php endif; ?>


    <section class="delivery-orders">


        <?php if (empty($orders)): ?>

            <div class="delivery-empty">

                <?php if ($view == 'available'): ?>

                    <?= tr(
                        'There are no available orders.',
                        'אין הזמנות זמינות.'
                    ) ?>

                <?php elseif ($view == 'active'): ?>

                    <?= tr(
                        'You do not have an active delivery.',
                        'אין לך משלוח פעיל.'
                    ) ?>

                <?php else: ?>

                    <?= tr(
                        'You have not completed any deliveries yet.',
                        'עדיין לא השלמת משלוחים.'
                    ) ?>

                <?php endif; ?>

            </div>

        <?php endif; ?>


        <?php if ($view == 'credit' && !empty($orders)): ?>


            <div class="credit-panel">

                <h2>
                    <?= tr(
                        'Credit History',
                        'היסטוריית קרדיט'
                    ) ?>
                </h2>


                <div class="credit-table-wrap">

                    <table>

                        <tr>

                            <th><?= tr('Order', 'הזמנה') ?></th>

                            <th><?= tr('Region', 'אזור') ?></th>

                            <th><?= tr('Order Total', 'סכום הזמנה') ?></th>

                            <th><?= tr('Rate', 'אחוז') ?></th>

                            <th><?= tr('Credit Earned', 'קרדיט שהתקבל') ?></th>

                            <th><?= tr('Delivered At', 'נמסרה בתאריך') ?></th>

                        </tr>


                        <?php foreach ($orders as $order): ?>

                            <tr>

                                <td>
                                    #<?= $order['id'] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        regionLabel(
                                            $order['delivery_region']
                                        )
                                    ) ?>
                                </td>

                                <td>
                                    $<?= number_format(
                                        $order['total_price'],
                                        2
                                    ) ?>
                                </td>

                                <td>
                                    <?= number_format($order['delivery_rate'],0)?>%
                                </td>

                                <td>

                                    <strong>
                                        $<?= number_format(
                                            $order['delivery_earning'],
                                            2
                                        ) ?>
                                    </strong>

                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        formatDeliveryDate(
                                            $order['delivered_at']
                                        )
                                    ) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>


                    </table>

                </div>

            </div>


        <?php endif; ?>


        <?php if ($view != 'credit'): ?>


            <?php foreach ($orders as $order): ?>


                <?php

                $total = $order['total_price'];

                $rate = $order['delivery_rate'];


                if ($view == 'available') {

                    $rate = calculateDeliveryRate(
                        $order['delivery_region'],
                        $driver_region
                    );
                }


                $expected_earning = calculateDeliveryEarning(
                    $total,
                    $rate
                );


                $home_rate = false;

                if ($rate == 20) {
                    $home_rate = true;
                }


                $address = '-';

                if ($order['delivery_address'] != null && $order['delivery_address'] != '') {
                    $address = $order['delivery_address'];
                }


                $delivery_date = '-';

                if ($order['delivery_date'] != null) {
                    $delivery_date = $order['delivery_date'];
                }


                $delivery_time = '-';

                if ($order['delivery_time'] != null) {
                    $delivery_time = $order['delivery_time'];
                }


                $items = getOrderItems(
                    $pdo,
                    $order['id']
                );

                ?>


                <article class="delivery-order-card">


                    <div class="delivery-order-top">


                        <div>

                            <h2>
                                <?= tr(
                                    'Order',
                                    'הזמנה'
                                ) ?>

                                #<?= $order['id'] ?>
                            </h2>


                            <?php if ($home_rate == true): ?>

                                <span class="region-rate-badge home">

                                    <?= tr(
                                        'Your Region - 20%',
                                        'האזור שלך - 20%'
                                    ) ?>

                                </span>

                            <?php else: ?>

                                <span class="region-rate-badge outside">

                                    <?= tr(
                                        'Outside Region - 25%',
                                        'מחוץ לאזור - 25%'
                                    ) ?>

                                </span>

                            <?php endif; ?>


                        </div>


                        <span class="badge <?= htmlspecialchars($order['status']) ?>">

                            <?= htmlspecialchars(
                                deliveryStatus(
                                    $order['status']
                                )
                            ) ?>

                        </span>


                    </div>


                    <div class="delivery-info">


                        <div class="delivery-info-item">

                            <span class="delivery-info-label">
                                <?= tr(
                                    'Customer',
                                    'לקוח'
                                ) ?>
                            </span>

                            <?= htmlspecialchars(
                                $order['username']
                            ) ?>

                        </div>


                        <div class="delivery-info-item">

                            <span class="delivery-info-label">
                                <?= tr(
                                    'Email',
                                    'אימייל'
                                ) ?>
                            </span>

                            <?= htmlspecialchars(
                                $order['email']
                            ) ?>

                        </div>


                        <div class="delivery-info-item">

                            <span class="delivery-info-label">
                                <?= tr(
                                    'Order Region',
                                    'אזור ההזמנה'
                                ) ?>
                            </span>

                            <?= htmlspecialchars(
                                regionLabel(
                                    $order['delivery_region']
                                )
                            ) ?>

                        </div>


                        <div class="delivery-info-item">

                            <span class="delivery-info-label">
                                <?= tr(
                                    'Order Total',
                                    'סכום הזמנה'
                                ) ?>
                            </span>

                            $<?= number_format(
                                $total,
                                2
                            ) ?>

                        </div>


                        <div class="delivery-info-item full">

    <span class="delivery-info-label">
        <?= tr(
            'Delivery Address',
            'כתובת למשלוח'
        ) ?>
    </span>

    <?= htmlspecialchars($address) ?>

    <?php if ($address != '-'): ?>

        <br><br>

        <a
            href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($address . ', ' . regionLabel($order['delivery_region']) . ', Israel') ?>"
            target="_blank"
        >
            <?= tr(
                'Open in Google Maps',
                'פתח ב-Google Maps'
            ) ?>
        </a>

    <?php endif; ?>

</div>


                        <div class="delivery-info-item">

                            <span class="delivery-info-label">
                                <?= tr(
                                    'Delivery Date',
                                    'תאריך משלוח'
                                ) ?>
                            </span>

                            <?= htmlspecialchars($delivery_date) ?>

                        </div>


                        <div class="delivery-info-item">

                            <span class="delivery-info-label">
                                <?= tr(
                                    'Delivery Time',
                                    'שעת משלוח'
                                ) ?>
                            </span>

                            <?= htmlspecialchars($delivery_time) ?>

                        </div>


                    </div>


                    <h3>
                        <?= tr(
                            'Products',
                            'מוצרים'
                        ) ?>
                    </h3>


                    <ul class="delivery-items">


                        <?php if (empty($items)): ?>

                            <li>
                                <?= tr(
                                    'No product details are available.',
                                    'פרטי המוצרים אינם זמינים.'
                                ) ?>
                            </li>

                        <?php else: ?>


                            <?php foreach ($items as $item): ?>

                                <li>

                                    <?= htmlspecialchars(
                                        $item['name']
                                    ) ?>

                                    -

                                    <?= $item['quantity'] ?>

                                    ×

                                    $<?= number_format(
                                        $item['price'],
                                        2
                                    ) ?>

                                </li>

                            <?php endforeach; ?>


                        <?php endif; ?>


                    </ul>


                    <div class="earning-box">

                        <?= tr(
                            'Your credit after delivery',
                            'הקרדיט שלך לאחר המסירה'
                        ) ?>:

                        $<?= number_format(
                            $expected_earning,
                            2
                        ) ?>

                        (<?= $rate ?>%)

                    </div>


                    <div class="delivery-actions">


                        <?php if ($view == 'available'): ?>


                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?= $order['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    name="pick_order"
                                    class="btn btn-primary"
                                >
                                    <?= tr(
                                        'Accept Order',
                                        'קבל הזמנה'
                                    ) ?>
                                </button>

                            </form>


                        <?php elseif ($view == 'active'): ?>


                            <form
                                method="POST"
                                onsubmit="return confirm('Confirm that this order was delivered?');"
                            >

                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?= $order['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    name="mark_delivered"
                                    class="btn btn-primary"
                                >
                                    <?= tr(
                                        'Mark as Delivered',
                                        'סמן כנמסרה'
                                    ) ?>
                                </button>

                            </form>


                        <?php endif; ?>


                    </div>


                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>