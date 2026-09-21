<?php
require 'db.php';

// Convert the raw order status into a friendly label for the user.
function getOrderStatus($status) {
    if ($status == 'pending') {
        return tr('Pending', 'ממתינה');
    }
    if ($status == 'delivering') {
        return tr('Out for Delivery', 'בדרך למשלוח');
    }
    if ($status == 'delivered') {
        return tr('Delivered', 'נמסרה');
    }
    if ($status == 'cancelled') {
        return tr('Cancelled', 'בוטלה');
    }
    return $status;
}

// Add the active CSS class to the selected orders filter tab.
function activeFilter($filter, $name) {
    if ($filter == $name) {return ' active';}
    return '';
}

// Only logged-in users can view their own order list.
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Read the active order filter from the URL and keep it valid.
$filter = 'all';

if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
}
// Make sure filter is valid
if ($filter != 'all' && $filter != 'current' && $filter != 'delivered' && $filter != 'cancelled') {$filter = 'all';}
if ($filter == 'current') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND (status = 'pending' OR status = 'delivering') ORDER BY id DESC");
} else if ($filter == 'delivered') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND status = 'delivered' ORDER BY id DESC");
} else if ($filter == 'cancelled') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND status = 'cancelled' ORDER BY id DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
}

$stmt->execute([$user_id]);

$orders = $stmt->fetchAll();

// Check whether the user has any orders in the selected view.
$has_orders = false;
foreach ($orders as $order) {
    $has_orders = true;
    break;
}

$page_language = 'en';
$page_direction = 'ltr';
if (isset($_SESSION['lang']) && $_SESSION['lang'] == 'he'){
    $page_language = 'he';
    $page_direction = 'rtl';
}
?>

<!DOCTYPE html>
<html
    lang="<?= $page_language ?>"
    dir="<?= $page_direction ?>"
>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= tr(
            'My Orders - SafeBite',
            'ההזמנות שלי - SafeBite'
        ) ?>
    </title>

    <link rel="stylesheet" href="style.css">

    <style>

        .orders-page {
            max-width: 1180px;
            margin: 38px auto 60px;
            padding: 0 24px;
        }

        .orders-header {
            background: linear-gradient(135deg, #4caf50, #1f6d3a);
            color: white;
            border-radius: 18px;
            padding: 30px;
            margin-bottom: 24px;
        }

        .orders-header h1 {
            margin: 0 0 8px;
        }

        .orders-header p {
            margin: 0;
        }

        .orders-filter-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .orders-filter-link {
            padding: 10px 16px;
            border-radius: 20px;
            border: 1px solid #cfe0d1;
            background: white;
            color: #445148;
            font-weight: bold;
            text-decoration: none;
        }

        .orders-filter-link:hover {
            background: #4caf50;
            color: white;
        }

        .orders-filter-link.active {
            background: #4caf50;
            color: white;
        }

        .orders-list {
            display: grid;
            gap: 20px;
        }

        .order-card {
            background: white;
            border: 1px solid #dce9de;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.05);
            transition: 0.3s;
        }

        .order-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            transform: translateY(-3px);
        }

        .order-top {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #edf2ed;
        }

        .order-number {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .order-date {
            margin-top: 5px;
            color: #6c766e;
        }

        .order-status {
            padding: 7px 12px;
            border-radius: 20px;
            font-weight: bold;
        }

        .order-status.pending {
            background: #fff4d6;
            color: #8b650d;
        }

        .order-status.delivering {
            background: #e8f2ff;
            color: #2461a5;
        }

        .order-status.delivered {
            background: #e9f8ec;
            color: #2a6837;
        }

        .order-status.cancelled {
            background: #fdebea;
            color: #a43631;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .order-info-box {
            background: #f8fbf8;
            padding: 14px;
            border-radius: 10px;
        }

        .order-info-box span {
            display: block;
            color: #6a756d;
            margin-bottom: 5px;
        }

        .order-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .cancel-button {
            border: 1px solid #d89c99;
            background: #fff5f4;
            color: #a5332f;
            padding: 11px 16px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }

        .empty-orders {
            background: white;
            border: 1px solid #dce9de;
            border-radius: 16px;
            padding: 45px;
            text-align: center;
        }

        @media (max-width: 800px) {

            .order-info {
                grid-template-columns: 1fr 1fr;
            }

        }

        @media (max-width: 500px) {

            .order-info {
                grid-template-columns: 1fr;
            }

            .order-top {
                flex-direction: column;
            }

        }

    </style>

</head>

<body>

<?php include 'navbar.php'; ?>


<main class="orders-page">


    <div class="orders-header">

        <h1>
            <?= tr(
                'My Orders',
                'ההזמנות שלי'
            ) ?>
        </h1>

        <p>
            <?= tr(
                'View and manage your SafeBite orders.',
                'צפה ונהל את ההזמנות שלך.'
            ) ?>
        </p>

    </div>


    <div class="orders-filter-row">


        <a
            href="my_orders.php?filter=all"
            class="orders-filter-link<?= activeFilter($filter, 'all') ?>"
        >
            <?= tr('All', 'הכול') ?>
        </a>


        <a
            href="my_orders.php?filter=current"
            class="orders-filter-link<?= activeFilter($filter, 'current') ?>"
        >
            <?= tr('Current', 'נוכחיות') ?>
        </a>


        <a
            href="my_orders.php?filter=delivered"
            class="orders-filter-link<?= activeFilter($filter, 'delivered') ?>"
        >
            <?= tr('Delivered', 'נמסרו') ?>
        </a>


        <a
            href="my_orders.php?filter=cancelled"
            class="orders-filter-link<?= activeFilter($filter, 'cancelled') ?>"
        >
            <?= tr('Cancelled', 'בוטלו') ?>
        </a>


    </div>


    <?php if ($has_orders == false): ?>


        <div class="empty-orders">

            <h2>
                <?= tr(
                    'No orders found',
                    'לא נמצאו הזמנות'
                ) ?>
            </h2>

            <p>
                <?= tr(
                    'There are no orders in this category.',
                    'אין הזמנות בקטגוריה זו.'
                ) ?>
            </p>

            <a
                href="index.php"
                class="btn btn-primary"
            >
                <?= tr(
                    'Browse Products',
                    'עיון במוצרים'
                ) ?>
            </a>

        </div>


    <?php else: ?>


        <div class="orders-list">


            <?php foreach ($orders as $order): ?>


                <?php

                // Check whether this order can still be cancelled by the customer.
                $status =
                    $order['status'];


                $can_cancel = false;


                if (
                    $status == 'pending' &&
                    $order['delivery_user_id'] == null
                ) {
                    $can_cancel = true;
                }

                ?>


                <div class="order-card">


                    <div class="order-top">


                        <div>

                            <div class="order-number">

                                <?= tr(
                                    'Order',
                                    'הזמנה'
                                ) ?>

                                #<?= $order['id'] ?>

                            </div>


                            <div class="order-date">

                                <?= substr(
                                    $order['created_at'],
                                    0,
                                    16
                                ) ?>

                            </div>

                        </div>


                        <div
                            class="order-status <?= htmlspecialchars($status) ?>"
                        >
                            <?= htmlspecialchars(
                                getOrderStatus($status)
                            ) ?>
                        </div>


                    </div>


                    <div class="order-info">


                        <div class="order-info-box">

                            <span>
                                <?= tr(
                                    'Total',
                                    'סה״כ'
                                ) ?>
                            </span>

                            <strong>
                                $<?= number_format(
                                    $order['total_price'],
                                    2
                                ) ?>
                            </strong>

                        </div>


                        <div class="order-info-box">

                            <span>
                                <?= tr(
                                    'Delivery Region',
                                    'אזור משלוח'
                                ) ?>
                            </span>

                            <strong>
                                <?= htmlspecialchars(regionLabel($order['delivery_region'])) ?>
                            </strong>

                        </div>


                        <div class="order-info-box">

                            <span>
                                <?= tr(
                                    'Delivery Date',
                                    'תאריך משלוח'
                                ) ?>
                            </span>

                            <strong>

                                <?php if ($order['delivery_date'] != null): ?>

                                    <?= htmlspecialchars(
                                        $order['delivery_date']
                                    ) ?>

                                <?php else: ?>

                                    -

                                <?php endif; ?>

                            </strong>

                        </div>


                        <div class="order-info-box">

                            <span>
                                <?= tr(
                                    'Delivery Time',
                                    'שעת משלוח'
                                ) ?>
                            </span>

                            <strong>

                                <?php if ($order['delivery_time'] != null): ?>

                                    <?= substr(
                                        $order['delivery_time'],
                                        0,
                                        5
                                    ) ?>

                                <?php else: ?>

                                    -

                                <?php endif; ?>

                            </strong>

                        </div>


                    </div>

                    <div class="order-actions">

                        <a
                            href="order_details.php?id=<?= $order['id'] ?>"
                            class="btn btn-primary"
                        >
                            <?= tr(
                                'View Details',
                                'צפייה בפרטים'
                            ) ?>
                        </a>


                        <?php if ($can_cancel == true): ?>


                            <form
                                method="POST"
                                action="cancel_order.php"
                            >

                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?= $order['id'] ?>"
                                >


                                <button
                                    type="submit"
                                    class="cancel-button"
                                    onclick="return confirm('Cancel this order?');"
                                >
                                    <?= tr(
                                        'Cancel Order',
                                        'ביטול הזמנה'
                                    ) ?>
                                </button>


                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php include 'chatbot.php'; ?>
</body>
</html>