<?php
require 'db.php';

// Check that the order filter selected by the admin is one of the allowed values.
function validOrderFilter($filter) {
    if ($filter == 'current') return true;
    if ($filter == 'delivered') return true;
    if ($filter == 'cancelled') return true;
    if ($filter == 'all') return true;
    return false;
}

function getOrders($pdo, $filter, $region) {
    if ($filter == 'current' && $region == 'all') {
        $stmt = $pdo->prepare("SELECT o.*, customer.username, customer.email, driver.username AS driver_username FROM orders o JOIN users customer ON customer.id = o.user_id LEFT JOIN users driver ON driver.id = o.delivery_user_id WHERE o.status = 'pending' OR o.status = 'delivering' ORDER BY o.id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    if ($filter == 'current' && $region != 'all') {
        $stmt = $pdo->prepare("SELECT o.*, customer.username, customer.email, driver.username AS driver_username FROM orders o JOIN users customer ON customer.id = o.user_id LEFT JOIN users driver ON driver.id = o.delivery_user_id WHERE (o.status = 'pending' OR o.status = 'delivering') AND o.delivery_region = ? ORDER BY o.id DESC");
        $stmt->execute([$region]);
        return $stmt->fetchAll();
    }

    if ($filter == 'delivered' && $region == 'all') {
        $stmt = $pdo->prepare("SELECT o.*, customer.username, customer.email, driver.username AS driver_username FROM orders o JOIN users customer ON customer.id = o.user_id LEFT JOIN users driver ON driver.id = o.delivery_user_id WHERE o.status = 'delivered' ORDER BY o.id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    if ($filter == 'delivered' && $region != 'all') {
        $stmt = $pdo->prepare("SELECT o.*, customer.username, customer.email, driver.username AS driver_username FROM orders o JOIN users customer ON customer.id = o.user_id LEFT JOIN users driver ON driver.id = o.delivery_user_id WHERE o.status = 'delivered' AND o.delivery_region = ? ORDER BY o.id DESC");
        $stmt->execute([$region]);
        return $stmt->fetchAll();
    }

    if ($filter == 'cancelled' && $region == 'all') {
        $stmt = $pdo->prepare("SELECT o.*, customer.username, customer.email, driver.username AS driver_username FROM orders o JOIN users customer ON customer.id = o.user_id LEFT JOIN users driver ON driver.id = o.delivery_user_id WHERE o.status = 'cancelled' ORDER BY o.id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    if ($filter == 'cancelled' && $region != 'all') {
        $stmt = $pdo->prepare("SELECT o.*, customer.username, customer.email, driver.username AS driver_username FROM orders o JOIN users customer ON customer.id = o.user_id LEFT JOIN users driver ON driver.id = o.delivery_user_id WHERE o.status = 'cancelled' AND o.delivery_region = ? ORDER BY o.id DESC");
        $stmt->execute([$region]);
        return $stmt->fetchAll();
    }

    if ($filter == 'all' && $region != 'all') {
        $stmt = $pdo->prepare("SELECT o.*, customer.username, customer.email, driver.username AS driver_username FROM orders o JOIN users customer ON customer.id = o.user_id LEFT JOIN users driver ON driver.id = o.delivery_user_id WHERE o.delivery_region = ? ORDER BY o.id DESC");
        $stmt->execute([$region]);
        return $stmt->fetchAll();
    }

    $stmt = $pdo->prepare("SELECT o.*, customer.username, customer.email, driver.username AS driver_username FROM orders o JOIN users customer ON customer.id = o.user_id LEFT JOIN users driver ON driver.id = o.delivery_user_id ORDER BY o.id DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}


// Reset a delivery in progress so the order goes back to the pending list.
function resetDelivery($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if (!$order) return false;
    if ($order['status'] != 'delivering') {
        return false;
    }
    $stmt = $pdo->prepare("UPDATE orders SET delivery_user_id = NULL, status = 'pending', delivery_rate = 0, delivery_earning = 0, picked_at = NULL, delivered_at = NULL WHERE id = ?");
    $stmt->execute([$order_id]);
    return true;
}

function adminOrderStatusLabel($status) {
    if ($status == 'pending') return tr('Pending', 'ממתינה');
    if ($status == 'delivering') return tr('Delivering', 'במשלוח');
    if ($status == 'delivered') return tr('Delivered', 'נמסרה');
    if ($status == 'cancelled') return tr('Cancelled', 'בוטלה');
    return $status;
}

function formatOrderDate($date) {
    if ($date == null || $date == '') {
        return '';
    }
    $year = substr($date, 0, 4);
    $month = substr($date, 5, 2);
    $day = substr($date, 8, 2);
    $time = substr($date, 11, 5);
    return $day . '/' . $month . '/' . $year . ' ' . $time;
}


// Only admins can use the order management page.
if (!isLoggedIn() || !isAdmin()) {
    header("Location: index.php");
    exit();
}

$is_admin_theme = true;

$message = '';
$message_type = 'success';

$filter = 'current';

if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
}

if (isset($_POST['filter'])) {
    $filter = $_POST['filter'];
}

if (validOrderFilter($filter) == false) {
    $filter = 'current';
}

$region_filter = 'all';

if (isset($_GET['region'])) {
    $region_filter = $_GET['region'];
}

if (isset($_POST['region'])) {
    $region_filter = $_POST['region'];
}

if ($region_filter != 'all') {
    if (validDeliveryRegion($region_filter) == false) {
        $region_filter = 'all';
    }
}

// If the admin resets a delivery, update the order back to a wait-for-driver state.
if (isset($_POST['reset_delivery'])) {
    $order_id = 0;
    if (isset($_POST['order_id'])) {
        $order_id = (int)$_POST['order_id'];
    }

    if (resetDelivery($pdo, $order_id) == true) {
        $message = tr(
            'Order reset and returned to the available delivery list.',
            'ההזמנה אופסה והוחזרה לרשימת המשלוחים הזמינים.'
        );
    } else {
        $message = tr(
            'This order could not be reset.',
            'לא ניתן לאפס את ההזמנה.'
        );
        $message_type = 'error';
    }
}

$orders = getOrders($pdo, $filter, $region_filter);

$regions = deliveryRegions();


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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= tr('Admin Orders - SafeBite', 'ניהול הזמנות - SafeBite') ?>
    </title>

    <link rel="stylesheet" href="style.css">

    <style>

        .filters-row {
            display: flex;
            gap: 12px;
            align-items: end;
            flex-wrap: wrap;
            margin: 20px 0;
        }

        .filters-row .form-group {
            margin: 0;
            min-width: 180px;
        }

        .orders-table-wrap {
            overflow-x: auto;
        }

        .order-status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .order-status-badge.pending {
            background: #fff4d6;
            color: #8b650d;
        }

        .order-status-badge.delivering {
            background: #e8f2ff;
            color: #2461a5;
        }

        .order-status-badge.delivered {
            background: #e9f8ec;
            color: #2a6837;
        }

        .order-status-badge.cancelled {
            background: #fdebea;
            color: #a43631;
        }

        .cancelled-date {
            display: block;
            color: #8b605d;
            font-size: 0.78rem;
            margin-top: 5px;
        }

    </style>

</head>

<body class="admin-theme">

<?php include 'navbar.php'; ?>


<div class="admin-page-wrap">

    <div class="admin-header">

        <h1>
            <?= tr('Manage Orders', 'ניהול הזמנות') ?>
        </h1>

        <p>
            <?= tr(
                'Review orders, delivery regions, assigned drivers and delivery status.',
                'צפו בהזמנות, אזורי משלוח, שליחים משויכים ומצב המשלוח.'
            ) ?>
        </p>

    </div>


    <?php if ($message != ''): ?>

        <div class="alert <?= $message_class ?>">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <form method="GET" class="filters-row">


        <div class="form-group">

            <label for="filter">
                <?= tr('Order View', 'תצוגת הזמנות') ?>
            </label>

            <select name="filter" id="filter" class="form-control">

                <option
                    value="current"
                    <?php if ($filter == 'current') echo 'selected'; ?>
                >
                    <?= tr('Current Orders', 'הזמנות נוכחיות') ?>
                </option>

                <option
                    value="delivered"
                    <?php if ($filter == 'delivered') echo 'selected'; ?>
                >
                    <?= tr('Delivered Orders', 'הזמנות שנמסרו') ?>
                </option>

                <option
                    value="cancelled"
                    <?php if ($filter == 'cancelled') echo 'selected'; ?>
                >
                    <?= tr('Cancelled Orders', 'הזמנות שבוטלו') ?>
                </option>

                <option
                    value="all"
                    <?php if ($filter == 'all') echo 'selected'; ?>
                >
                    <?= tr('All Orders', 'כל ההזמנות') ?>
                </option>

            </select>

        </div>


        <div class="form-group">

            <label for="region">
                <?= tr('Region', 'אזור') ?>
            </label>

            <select name="region" id="region" class="form-control">

                <option
                    value="all"
                    <?php if ($region_filter == 'all') echo 'selected'; ?>
                >
                    <?= tr('All Regions', 'כל האזורים') ?>
                </option>


                <?php foreach ($regions as $code => $labels): ?>

                    <option
                        value="<?= htmlspecialchars($code) ?>"
                        <?php if ($region_filter == $code) echo 'selected'; ?>
                    >
                        <?= htmlspecialchars(tr($labels['en'], $labels['he'])) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <button type="submit" class="btn btn-primary">
            <?= tr('Filter', 'סינון') ?>
        </button>

    </form>


    <div class="admin-panel orders-table-wrap">

        <table>

            <tr>
                <th><?= tr('Order', 'הזמנה') ?></th>
                <th><?= tr('Customer', 'לקוח') ?></th>
                <th><?= tr('Total', 'סה״כ') ?></th>
                <th><?= tr('Region', 'אזור') ?></th>
                <th><?= tr('Driver', 'שליח') ?></th>
                <th><?= tr('Status', 'מצב') ?></th>
                <th><?= tr('Credit', 'קרדיט') ?></th>
                <th><?= tr('Actions', 'פעולות') ?></th>
            </tr>


            <?php if (empty($orders)): ?>

                <tr>
                    <td colspan="8" style="text-align:center; padding:30px;">
                        <?= tr(
                            'No orders found for this filter.',
                            'לא נמצאו הזמנות עבור הסינון שנבחר.'
                        ) ?>
                    </td>
                </tr>

            <?php endif; ?>


            <?php foreach ($orders as $order): ?>

                <?php

                $status = $order['status'];

                $driver_name = tr('Not assigned', 'לא משויך');

                if ($order['driver_username'] != null) {
                    $driver_name = $order['driver_username'];
                }


                $order_region = null;

                if ($order['delivery_region'] != null) {
                    $order_region = $order['delivery_region'];
                }


                $delivery_earning = 0;

                if ($order['delivery_earning'] != null) {
                    $delivery_earning = $order['delivery_earning'];
                }

                ?>


                <tr>


                    <td>
                        #<?= $order['id'] ?>
                    </td>


                    <td>

                        <strong>
                            <?= htmlspecialchars($order['username']) ?>
                        </strong>

                        <br>

                        <span class="small-text">
                            <?= htmlspecialchars($order['email']) ?>
                        </span>

                    </td>


                    <td>
                        $<?= number_format($order['total_price'], 2) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars(regionLabel($order_region)) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars($driver_name) ?>
                    </td>


                    <td>

                        <span class="order-status-badge <?= htmlspecialchars($status) ?>">
                            <?= htmlspecialchars(adminOrderStatusLabel($status)) ?>
                        </span>


                        <?php if ($status == 'cancelled' && $order['cancelled_at'] != null): ?>

                            <span class="cancelled-date">
                                <?= htmlspecialchars(formatOrderDate($order['cancelled_at'])) ?>
                            </span>

                        <?php endif; ?>

                    </td>


                    <td>
                        $<?= number_format($delivery_earning, 2) ?>
                    </td>


                    <td>


                        <a
                            href="order_details.php?id=<?= $order['id'] ?>"
                            class="btn btn-primary"
                        >
                            <?= tr('View', 'צפייה') ?>
                        </a>


                        <?php if ($status == 'delivering'): ?>

                            <form
                                method="POST"
                                style="display:inline;"
                                onsubmit="return confirm('Reset this delivery assignment?');"
                            >

                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?= $order['id'] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="filter"
                                    value="<?= htmlspecialchars($filter) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="region"
                                    value="<?= htmlspecialchars($region_filter) ?>"
                                >

                                <button
                                    type="submit"
                                    name="reset_delivery"
                                    class="btn btn-secondary"
                                >
                                    <?= tr('Reset', 'איפוס') ?>
                                </button>

                            </form>

                        <?php endif; ?>


                    </td>


                </tr>

            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>