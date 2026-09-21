<?php
require 'db.php';

// Get product details so the checkout page can calculate totals and validate stock.
function getProduct($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}


function getRewardCoupon($pdo, $code, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM user_rewards WHERE coupon_code = ? AND user_id = ? AND is_used = 0");
    $stmt->execute([$code, $user_id]);
    return $stmt->fetch();
}


function getPublicCoupon($pdo, $code) {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ?");
    $stmt->execute([$code]);
    return $stmt->fetch();
}


if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$cart_items = array();
if (isset($_SESSION['cart_items'])) {
    $cart_items = $_SESSION['cart_items'];
}

$display_items = array();
$total_amount = 0;
$has_items = false;

foreach ($cart_items as $item) {
    $product = getProduct($pdo, $item['product_id']);

    if ($product != false && $product['stock'] > 0) {
        $quantity = $item['quantity'];

        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($quantity > $product['stock']) {
            $quantity = $product['stock'];
        }

        $product['cart_quantity'] = $quantity;
        $display_items[] = $product;

        $total_amount = $total_amount + ($product['price'] * $quantity);
        $has_items = true;
    }
}

if ($has_items == false) {
    header("Location: cart.php");
    exit();
}

$coupon_message = '';
$coupon_message_type = 'error';
$discount_amount = 0;

$card_name = '';
$card_number = '';
$expiry_date = '';
$cvv = '';
$delivery_region = '';
$delivery_address = '';
$delivery_date = '';
$delivery_time = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['card_name'])) {
        $card_name = $_POST['card_name'];
    }
    if (isset($_POST['card_number'])) {
        $card_number = $_POST['card_number'];
    }
    if (isset($_POST['expiry_date'])) {
        $expiry_date = $_POST['expiry_date'];
    }
    if (isset($_POST['cvv'])) {
        $cvv = $_POST['cvv'];
    }
    if (isset($_POST['delivery_region'])) {
        $delivery_region = $_POST['delivery_region'];
    }
    if (isset($_POST['delivery_address'])) {
        $delivery_address = $_POST['delivery_address'];
    }
    if (isset($_POST['delivery_date'])) {
        $delivery_date = $_POST['delivery_date'];
    }
    if (isset($_POST['delivery_time'])) {
        $delivery_time = $_POST['delivery_time'];
    }
}


// Coupon logic checks the user's reward codes first and then public coupons.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_coupon'])) {

    $coupon_code = '';

    if (isset($_POST['coupon_code'])) {
        $coupon_code = strtoupper(trim($_POST['coupon_code']));
    }

    if ($coupon_code == '') {
        $coupon_message = tr('Please enter a coupon code.','נא להזין קוד קופון.');
        $_SESSION['coupon'] = null;

    } else if ($total_amount < 100) {

        $coupon_message = tr('Coupons require a minimum cart total of $100.','ניתן להשתמש בקופון רק כאשר סכום העגלה הוא לפחות 100 דולר.');
        $_SESSION['coupon'] = null;
    } else {

        // First check personal reward
        $reward = getRewardCoupon($pdo,$coupon_code,$_SESSION['user_id']);

        $reward_valid = false;
        if ($reward != false) {
            if ($reward['expires_at'] == null) {
                $reward_valid = true;
            } else if ($reward['expires_at'] >= date("Y-m-d")) {
                $reward_valid = true;
            }
        }

        if ($reward_valid == true) {

            $_SESSION['coupon'] = array(
                'code' => $reward['coupon_code'],
                'type' => 'fixed',
                'discount_value' => $reward['discount_amount'],
                'reward_id' => $reward['id']
            );

            $coupon_message = tr('Reward coupon applied successfully.','קופון ההטבה הופעל בהצלחה.');
            $coupon_message_type = 'success';

        } else {

            // If no reward was found check public coupon
            $coupon = getPublicCoupon($pdo,$coupon_code);
            $coupon_valid = false;

            if ($coupon != false) {
                if ($coupon['expiry_date'] == null) {
                    $coupon_valid = true;
                } else if ($coupon['expiry_date'] >= date("Y-m-d")) {
                    $coupon_valid = true;
                }
            }
            if ($coupon_valid == true) {
                $_SESSION['coupon'] = array('code' => $coupon['code'],'type' => 'percent','discount_value' => $coupon['discount_percent']);
                $coupon_message = tr(
                    'Coupon applied successfully.',
                    'הקופון הופעל בהצלחה.'
                );
                $coupon_message_type = 'success';
            } else {
                $_SESSION['coupon'] = null;
                $coupon_message = tr(
                    'Invalid or expired coupon.',
                    'הקופון אינו תקין או שפג תוקפו.'
                );
            }
        }
    }
}

// Calculate the discount value based on the selected coupon type.
if (isset($_SESSION['coupon'])) {
    if ($_SESSION['coupon']['type'] == 'fixed') {
        $discount_amount = $_SESSION['coupon']['discount_value'];

    } else if ($_SESSION['coupon']['type'] == 'percent') {
        $discount_amount = $total_amount * ($_SESSION['coupon']['discount_value'] / 100);
    }
}

if ($discount_amount > $total_amount) {
    $discount_amount = $total_amount;
}

$final_amount = $total_amount - $discount_amount;

$coupon_class = 'alert-error';

if ($coupon_message_type == 'success') {
    $coupon_class = 'alert-success';
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
        <?= tr('Checkout - SafeBite', 'תשלום - SafeBite') ?>
    </title>

    <link rel="stylesheet" href="style.css">

    <style>
        .checkout-page {
            max-width: 1150px;
            margin: 35px auto;
            padding: 0 25px 50px;
        }

        .checkout-header {
            background: linear-gradient(135deg, #4caf50, #1f6d3a);
            color: white;
            padding: 28px 32px;
            border-radius: 16px;
            margin-bottom: 25px;
            transition: 0.3s;
        }

        .checkout-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transform: translateY(-3px);
        }

        .checkout-header h1 {
            margin: 0;
        }

        .checkout-header p {
            margin: 7px 0 0;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 25px;
            align-items: start;
        }

        .checkout-card {
            background: white;
            border: 1px solid #dfe9e0;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.05);
            
        }

        .checkout-card h2 {
            margin-top: 0;
        }

        .checkout-small-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .checkout-summary {
            position: sticky;
            top: 100px;
        }

        .checkout-item {
            padding: 12px 0;
            border-bottom: 1px solid #edf2ed;
        }

        .checkout-item-price {
            color: #667068;
            margin-top: 4px;
        }

        .checkout-total-row {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .checkout-final-total {
            font-size: 1.25rem;
            font-weight: bold;
        }

        .checkout-final-total strong {
            color: #1f6d3a;
        }

        .pay-button {
            width: 100%;
            margin-top: 20px;
            padding: 14px;
        }

        @media (max-width: 850px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .checkout-summary {
                position: static;
            }
        }
    </style>

</head>

<body>

<?php include 'navbar.php'; ?>


<main class="checkout-page">


    <div class="checkout-header">

        <h1>
            <?= tr('Checkout', 'תשלום') ?>
        </h1>

        <p>
            <?= tr(
                'Complete your payment and delivery information.',
                'השלם את פרטי התשלום והמשלוח.'
            ) ?>
        </p>

    </div>


    <!-- CHANGE - one form for payment and coupon + prevent double payment -->
    <form
        method="POST"
        action="checkout.php"
        class="checkout-grid"
        onsubmit="document.getElementById('pay-button').disabled = true;"
    >


        <div class="checkout-card">


                <h2>
                    <?= tr(
                        'Payment Information',
                        'פרטי תשלום'
                    ) ?>
                </h2>


                <div class="form-group">

                    <label>
                        <?= tr(
                            'Card Holder Name',
                            'שם בעל הכרטיס'
                        ) ?>
                    </label>

                    <input
                        type="text"
                        name="card_name"
                        class="form-control"
                        maxlength="60"
                        value="<?= htmlspecialchars($card_name) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        <?= tr(
                            'Card Number',
                            'מספר כרטיס'
                        ) ?>
                    </label>

                    <input
                        type="text"
                        name="card_number"
                        class="form-control"
                        maxlength="16"
                        inputmode="numeric"
                        placeholder="0000000000000000"
                        value="<?= htmlspecialchars($card_number) ?>"
                        required
                    >

                </div>


                <div class="checkout-small-grid">


                    <div class="form-group">

                        <label>
                            <?= tr(
                                'Expiry Date',
                                'תאריך תפוגה'
                            ) ?>
                        </label>

                        <input
                            type="text"
                            name="expiry_date"
                            class="form-control"
                            maxlength="5"
                            placeholder="MM/YY"
                            value="<?= htmlspecialchars($expiry_date) ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            CVV
                        </label>

                        <input
                            type="text"
                            name="cvv"
                            class="form-control"
                            maxlength="3"
                            inputmode="numeric"
                            placeholder="000"
                            value="<?= htmlspecialchars($cvv) ?>"
                            required
                        >

                    </div>


                </div>


                <h2 style="margin-top:30px;">
                    <?= tr(
                        'Delivery Information',
                        'פרטי משלוח'
                    ) ?>
                </h2>


                <div class="form-group">

                    <label>
                        <?= tr(
                            'Delivery Region',
                            'אזור משלוח'
                        ) ?>
                    </label>

                    <select
                        name="delivery_region"
                        class="form-control"
                        required
                    >

                        <option value="">
                            <?= tr(
                                'Choose your region',
                                'בחר אזור'
                            ) ?>
                        </option>

                        <option value="north" <?php if ($delivery_region == 'north') echo 'selected'; ?>>
                            <?= tr('North', 'צפון') ?>
                        </option>

                        <option value="haifa" <?php if ($delivery_region == 'haifa') echo 'selected'; ?>>
                            <?= tr('Haifa', 'חיפה') ?>
                        </option>

                        <option value="center" <?php if ($delivery_region == 'center') echo 'selected'; ?>>
                            <?= tr('Center', 'מרכז') ?>
                        </option>

                        <option value="jerusalem" <?php if ($delivery_region == 'jerusalem') echo 'selected'; ?>>
                            <?= tr('Jerusalem', 'ירושלים') ?>
                        </option>

                        <option value="south" <?php if ($delivery_region == 'south') echo 'selected'; ?>>
                            <?= tr('South', 'דרום') ?>
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        <?= tr(
                            'Delivery Address',
                            'כתובת למשלוח'
                        ) ?>
                    </label>

                    <textarea
                        name="delivery_address"
                        class="form-control"
                        rows="3"
                        required
                    ><?= htmlspecialchars($delivery_address) ?></textarea>

                </div>


                <div class="checkout-small-grid">


                    <div class="form-group">

                        <label>
                            <?= tr(
                                'Delivery Date',
                                'תאריך משלוח'
                            ) ?>
                        </label>

                        <input
                            type="date"
                            name="delivery_date"
                            class="form-control"
                            min="<?= date('Y-m-d') ?>"
                            value="<?= htmlspecialchars($delivery_date) ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            <?= tr(
                                'Delivery Time',
                                'שעת משלוח'
                            ) ?>
                        </label>

                        <input
                            type="time"
                            name="delivery_time"
                            class="form-control"
                            value="<?= htmlspecialchars($delivery_time) ?>"
                            required
                        >

                    </div>


                </div>


                <?php if ($final_amount < 10): ?>

    <div class="alert alert-error">
        <?= tr(
            'The minimum order amount is $10. Please add more products to your cart.',
            'סכום ההזמנה המינימלי הוא 10 דולר. נא להוסיף מוצרים נוספים לעגלה.'
        ) ?>
    </div>

    <button
        type="button"
        class="btn btn-primary pay-button"
        disabled
    >
        <?= tr('Minimum order is $10', 'מינימום הזמנה הוא 10 דולר') ?>
    </button>

    <?php else: ?>
    <button
        type="submit"
        id="pay-button"
        name="pay_order"
        formaction="process_payment.php"
        class="btn btn-primary pay-button"
    >
        <?= tr('Pay', 'שלם') ?>
        $<?= number_format($final_amount, 2) ?>
    </button>
      <?php endif; ?>


        </div>

        <aside class="checkout-card checkout-summary">

            <h2>
                <?= tr(
                    'Order Summary',
                    'סיכום הזמנה'
                ) ?>
            </h2>


            <div class="form-group">

                    <label>
                        <?= tr(
                            'Coupon Code',
                            'קוד קופון'
                        ) ?>
                    </label>

                    <input
                        type="text"
                        name="coupon_code"
                        class="form-control"
                        placeholder="<?= tr(
                            'Enter coupon code',
                            'הזן קוד קופון'
                        ) ?>"
                    >

                </div>


                <button
                    type="submit"
                    name="apply_coupon"
                    formnovalidate
                    class="btn btn-secondary"
                >
                    <?= tr(
                        'Apply Coupon',
                        'הפעל קופון'
                    ) ?>
                </button>

            <?php if ($coupon_message != ''): ?>

                <div
                    class="alert <?= $coupon_class ?>"
                    style="margin-top:15px;"
                >
                    <?= htmlspecialchars($coupon_message) ?>
                </div>

            <?php endif; ?>


            <?php foreach ($display_items as $item): ?>

                <div class="checkout-item">

                    <strong>
                        <?= htmlspecialchars($item['name']) ?>
                    </strong>

                    <div class="checkout-item-price">

                        $<?= number_format($item['price'],2) ?>

                        ×

                        <?= $item['cart_quantity'] ?>

                    </div>

                </div>

            <?php endforeach; ?>


            <div class="checkout-total-row">

                <span>
                    <?= tr(
                        'Subtotal',
                        'סכום ביניים'
                    ) ?>
                </span>

                <strong>
                    $<?= number_format($total_amount,2) ?>
                </strong>

            </div>


            <?php if ($discount_amount > 0): ?>

                <div class="checkout-total-row">

                    <span>
                        <?= tr(
                            'Discount',
                            'הנחה'
                        ) ?>
                    </span>

                    <strong>
                        -$<?= number_format(
                            $discount_amount,
                            2
                        ) ?>
                    </strong>

                </div>

            <?php endif; ?>


            <div class="checkout-total-row checkout-final-total">

                <span>
                    <?= tr(
                        'Total',
                        'סה״כ'
                    ) ?>
                </span>

                <strong>
                    $<?= number_format(
                        $final_amount,
                        2
                    ) ?>
                </strong>

            </div>


            <a
                href="cart.php"
                class="btn btn-secondary"
                style="
                    display:block;
                    text-align:center;
                    margin-top:20px;
                "
            >
                <?= tr(
                    'Back to Cart',
                    'חזרה לעגלה'
                ) ?>
            </a>


        </aside>


    </form>


</main>
<?php include 'chatbot.php'; ?>
</body>
</html>