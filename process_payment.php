<?php
require 'db.php';
require 'mailer.php';

// Get product data needed to validate stock and calculate the order total.
function getProduct($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}

// algo1 Check that the card number is a valid 16-digit format.
function validCardNumber($card_number) {
    if (strlen($card_number) != 16) return false;
    for ($i = 0; $i < 16; $i++) {
        if ($card_number[$i] < '0' || $card_number[$i] > '9') return false;
    }
    return true;
}

// Validate the card expiry date so expired cards are blocked.
function validExpiryDate($expiry_date) {
    if (strlen($expiry_date) != 5) return false;
    if ($expiry_date[2] != '/') return false;
    if ($expiry_date[0] < '0' || $expiry_date[0] > '9') return false;
    if ($expiry_date[1] < '0' || $expiry_date[1] > '9') return false;
    if ($expiry_date[3] < '0' || $expiry_date[3] > '9') return false;
    if ($expiry_date[4] < '0' || $expiry_date[4] > '9') return false;

    $month = (int)substr($expiry_date, 0, 2);
    $year = (int)('20' . substr($expiry_date, 3, 2));

    if ($month < 1 || $month > 12) return false;

    $current_month = (int)date("m");
    $current_year = (int)date("Y");

    if ($year < $current_year) return false;

    if ($year == $current_year && $month < $current_month) {
        return false;
    }
    return true;
}

// Check that the security code is exactly 3 digits.
function validCVV($cvv) {
    if (strlen($cvv) != 3) return false;
    for ($i = 0; $i < 3; $i++) {
        if ($cvv[$i] < '0' || $cvv[$i] > '9') return false;
    }
    return true;
}

// Save a reward coupon for the customer after they reach a reward milestone.
function createReward($pdo, $user_id, $code, $amount, $expires_at) {
    $stmt = $pdo->prepare("INSERT INTO user_rewards (user_id, coupon_code, discount_amount, is_used, expires_at) VALUES (?, ?, ?, 0, ?)");
    $stmt->execute([$user_id, $code, $amount, $expires_at]);
}

// Only logged-in users can complete a purchase.
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// This page only works when the checkout form is submitted via POST.
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: checkout.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$order_success = false;
$new_order_id = 0;

$card_name = '';
$card_number = '';
$expiry_date = '';
$cvv = '';
$delivery_region = '';
$delivery_address = '';
$delivery_date = '';
$delivery_time = '';

if (isset($_POST['card_name'])) {
    $card_name = trim($_POST['card_name']);
}

if (isset($_POST['card_number'])) {
    $card_number = trim($_POST['card_number']);
}

if (isset($_POST['expiry_date'])) {
    $expiry_date = trim($_POST['expiry_date']);
}

if (isset($_POST['cvv'])) {
    $cvv = trim($_POST['cvv']);
}

if (isset($_POST['delivery_region'])) {
    $delivery_region = $_POST['delivery_region'];
}

if (isset($_POST['delivery_address'])) {
    $delivery_address = trim($_POST['delivery_address']);
}

if (isset($_POST['delivery_date'])) {
    $delivery_date = $_POST['delivery_date'];
}

if (isset($_POST['delivery_time'])) {
    $delivery_time = $_POST['delivery_time'];
}

if (
    $card_name == '' ||
    $card_number == '' ||
    $expiry_date == '' ||
    $cvv == '' ||
    $delivery_region == '' ||
    $delivery_address == '' ||
    $delivery_date == '' ||
    $delivery_time == ''
) {
    $error_message = tr(
        'All fields are required.',
        'כל השדות הם שדות חובה.'
    );
}
else if (strlen($card_name) < 2) {
    $error_message = tr(
        'Please enter a valid cardholder name.',
        'נא להזין שם תקין של בעל הכרטיס.'
    );
}
else if (validCardNumber($card_number) == false) {
    $error_message = tr(
        'Card number must contain exactly 16 digits.',
        'מספר הכרטיס חייב להכיל בדיוק 16 ספרות.'
    );
}
else if (validExpiryDate($expiry_date) == false) {
    $error_message = tr(
        'Expiry date must be valid and not expired.',
        'תאריך התוקף חייב להיות תקין ולא פג.'
    );
}
else if (validCVV($cvv) == false) {
    $error_message = tr(
        'CVV must contain exactly 3 digits.',
        'קוד CVV חייב להכיל בדיוק 3 ספרות.'
    );
}
else if (strlen($delivery_address) < 5) {
    $error_message = tr(
        'Please enter a valid delivery address.',
        'נא להזין כתובת משלוח תקינה.'
    );
}
else if (
    $delivery_region != 'north' &&
    $delivery_region != 'haifa' &&
    $delivery_region != 'center' &&
    $delivery_region != 'jerusalem' &&
    $delivery_region != 'south'
) {
    $error_message = tr(
        'Please choose a valid delivery region.',
        'נא לבחור אזור משלוח תקין.'
    );
}
else if ($delivery_date < date("Y-m-d")) {
    $error_message = tr(
        'Delivery date cannot be in the past.',
        'תאריך המשלוח אינו יכול להיות בעבר.'
    );
}
else if (
    $delivery_date == date("Y-m-d") &&
    $delivery_time < date("H:i")
) {
    $error_message = tr(
        'Delivery time cannot be in the past.',
        'שעת המשלוח אינה יכולה להיות בעבר.'
    );
}
else if (
    !isset($_SESSION['cart_items']) ||
    empty($_SESSION['cart_items'])
) {
    $error_message = tr(
        'Your cart is empty.',
        'העגלה ריקה.'
    );
}

if ($error_message == '') {

    $cart_items = $_SESSION['cart_items'];
    $products = array();
    $total_amount = 0;

    foreach ($cart_items as $item) {
        $product = getProduct($pdo,$item['product_id']);
        if ($product == false) {
            $error_message = tr(
                'A product was not found.',
                'אחד המוצרים לא נמצא.'
            );
            break;
        }
        $quantity = $item['quantity'];
        if ($quantity < 1) {
            $error_message = tr(
                'Invalid product quantity.',
                'כמות המוצר אינה תקינה.'
            );
            break;
        }
        if ($quantity > $product['stock']) {
            $error_message = tr(
                'Not enough stock for ' . $product['name'] . '.',
                'אין מספיק מלאי עבור ' . $product['name'] . '.'
            );
            break;
        }

        $product['cart_quantity'] = $quantity;
        $products[] = $product;

        $total_amount = $total_amount + ($product['price'] * $quantity);
    }
}


if ($error_message == '') {

    $discount_amount = 0;

    if (isset($_SESSION['coupon'])) {

        if ($_SESSION['coupon']['type'] == 'fixed') {
            $discount_amount =
                $_SESSION['coupon']['discount_value'];
        }

        if ($_SESSION['coupon']['type'] == 'percent') {
            $discount_amount =
                $total_amount *
                ($_SESSION['coupon']['discount_value'] / 100);
        }
    }

    if ($discount_amount > $total_amount) {
        $discount_amount = $total_amount;
    }

    $final_amount =
        $total_amount -
        $discount_amount;


    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, status, delivery_address, delivery_region, delivery_date, delivery_time) VALUES (?, ?, 'pending', ?, ?, ?, ?)");

    $stmt->execute([
        $user_id,
        $final_amount,
        $delivery_address,
        $delivery_region,
        $delivery_date,
        $delivery_time
    ]);

    $new_order_id =
        $pdo->lastInsertId();


    foreach ($products as $product) {

        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

        $stmt->execute([
            $new_order_id,
            $product['id'],
            $product['cart_quantity'],
            $product['price']
        ]);


        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        $stmt->execute([
            $product['cart_quantity'],
            $product['id']
        ]);
    }


    if (isset($_SESSION['coupon']) && isset($_SESSION['coupon']['reward_id'])) {

        $stmt = $pdo->prepare("UPDATE user_rewards SET is_used = 1 WHERE id = ?");

        $stmt->execute([
            $_SESSION['coupon']['reward_id']
        ]);
    }

    $stmt = $pdo->prepare("SELECT username, email, total_spent, total_orders FROM users WHERE id = ?");

    $stmt->execute([$user_id]);

    $customer =
        $stmt->fetch();

    $old_spent = $customer['total_spent'];

    $old_orders = $customer['total_orders'];

    $new_spent = $old_spent + $final_amount;

    $new_orders = $old_orders + 1;


    $stmt = $pdo->prepare("UPDATE users SET total_spent = ?, total_orders = ? WHERE id = ?");

    $stmt->execute([
        $new_spent,
        $new_orders,
        $user_id
    ]);


    // ALGORITHM 2 - $750 REWARD
    // If the user has reached a multiple of $750 in spending, give them a $50 reward.
    $old_level = (int)($old_spent / 750);

    $new_level = (int)($new_spent / 750);

    if ($new_level > $old_level) {

        $reward_code = "REWARD50-" .rand(100000, 999999);
        $expires_at = null;

        createReward(
            $pdo,
            $user_id,
            $reward_code,
            50,
            $expires_at
        );

         $reward_message = tr(
        'Congratulations! You earned a $50 reward! Checkout My Reward section',
        'מזל טוב! זכית בהטבה של 50 דולר! ניתן לראות אותה באזור ההטבות שלי'
         );
    }

    // ALGORITHM 3 - 40 ORDERS REWARD 
    // If the user has reached a multiple of 40 orders, give them a reward based on their average order value.
    if ($new_orders % 40 == 0) {

        $average_order = $new_spent / $new_orders;

        $reward_code = "AVG40-" .rand(100000, 999999);
        $expires_at = null;

        createReward(
            $pdo,
            $user_id,
            $reward_code,
            $average_order,
            $expires_at
        );

        $reward_message = tr(
        'Congratulations! You earned a loyalty reward! Checkout My Reward section',
        'מזל טוב! זכית בהטבת נאמנות! ניתן לראות אותה באזור ההטבות שלי'
        );
    }


    $subject = "SafeBite Order #" .$new_order_id;

    $email_message = "SafeBite Receipt\n\n";

    $email_message .= "Order Number: #" .$new_order_id ."\n\n";


    foreach ($products as $product) {
        $product_total = $product['price'] * $product['cart_quantity'];
        $email_message .=
            $product['name'] .
            " x " .
            $product['cart_quantity'] .
            " - $" .
            number_format($product_total,2) ."\n";
    }


    $email_message .= "\nTotal: $" .number_format($final_amount,2) ."\n";


    $email_message .= "Delivery Address: " .$delivery_address ."\n";


    $email_message .= "Delivery Date: " .$delivery_date ."\n\n";

    $email_message .= "Thank you for shopping with SafeBite.";

    sendSafeBiteEmail($customer['email'],$subject,$email_message);
    $_SESSION['cart_items'] = array();
    $_SESSION['coupon'] = null;
    $order_success = true;
}

$page_language = 'en';
$page_direction = 'ltr';
if (isset($_SESSION['lang']) && $_SESSION['lang'] == 'he') {
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
            'Payment - SafeBite',
            'תשלום - SafeBite'
        ) ?>
    </title>

    <link
        rel="stylesheet"
        href="style.css"
    >

</head>


<body>


<?php include 'navbar.php'; ?>


<div class="main-container">

    <div class="login-card">


        <?php if ($order_success == true): ?>


            <div class="login-header">

                <h2>
                    <?= tr(
                        'Payment Successful',
                        'התשלום הצליח'
                    ) ?>
                </h2>

                <p>
                    <?= tr(
                        'Your order was created successfully.',
                        'ההזמנה נוצרה בהצלחה.'
                    ) ?>
                </p>
            </div>
            <?php if (!empty($reward_message)): ?>
            <div class="alert alert-success">
                 <?= htmlspecialchars($reward_message) ?>
            </div>
            <?php endif; ?>

            <p style="text-align:center;">

                <strong>
                    <?= tr(
                        'Order Number:',
                        'מספר הזמנה:'
                    ) ?>
                </strong>

                #<?= $new_order_id ?>

            </p>


            <div
                style="
                    text-align:center;
                    margin-top:25px;
                "
            >

                <a
                    href="order_details.php?id=<?= $new_order_id ?>"
                    class="btn btn-primary"
                >
                    <?= tr(
                        'View Order',
                        'צפייה בהזמנה'
                    ) ?>
                </a>


                <a
                    href="my_orders.php"
                    class="btn btn-secondary"
                >
                    <?= tr(
                        'My Orders',
                        'ההזמנות שלי'
                    ) ?>
                </a>

            </div>


        <?php else: ?>


            <div class="login-header">

                <h2>
                    <?= tr(
                        'Payment Failed',
                        'התשלום נכשל'
                    ) ?>
                </h2>

            </div>


            <div class="alert alert-error">

                <?= htmlspecialchars($error_message)?>

            </div>


            <div style="text-align:center;">

                <a
                    href="checkout.php"
                    class="btn btn-primary"
                >
                    <?= tr(
                        'Back to Checkout',
                        'חזרה לתשלום'
                    ) ?>
                </a>

            </div>


        <?php endif; ?>


    </div>

</div>
</body>
</html>