<?php

// This stack is used to reverse the cart order while adding or removing items. 
class Stack {
    private $items = array();

    public function push($item) {
        $this->items[] = $item;
    }

    public function pop() {
        return array_pop($this->items);
    }

    public function isEmpty() {
        return empty($this->items);
    }

    public function clear() {
        $this->items = array();
    }

    public function getItems() {
        return $this->items;
    }
}

require 'db.php';

// Save the current cart to the session so it stays available across pages.
function saveCart($cart) {
    $_SESSION['cart_items'] = $cart->getItems();
}

function getProduct($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}

function addProductToCart($cart, $product_id, $stock) {
    $temp = new Stack();
    $found = false;
    $added = false;
    while ($cart->isEmpty() == false) {
        $item = $cart->pop();
        if ($item['product_id'] == $product_id) {
            $found = true;
            if ($item['quantity'] < $stock) {
                $item['quantity']++;
                $added = true;
            }
        }
        $temp->push($item);
    }

    while ($temp->isEmpty() == false) {
        $cart->push($temp->pop());
    }

    if ($found == false) {
        $item = array(
            'product_id' => $product_id,
            'quantity' => 1
        );

        $cart->push($item);
        $added = true;
    }

    return $added;
}

function updateCartQuantity($cart, $product_id, $new_quantity) {
    $temp = new Stack();
    while ($cart->isEmpty() == false) {
        $item = $cart->pop();
        if ($item['product_id'] == $product_id) {
            $item['quantity'] = $new_quantity;
        }
        $temp->push($item);
    }
    while ($temp->isEmpty() == false) {
        $cart->push($temp->pop());
    }
}

function removeProductFromCart($cart, $product_id) {
    $temp = new Stack();

    while ($cart->isEmpty() == false) {
        $item = $cart->pop();

        if ($item['product_id'] != $product_id) {
            $temp->push($item);
        }
    }

    while ($temp->isEmpty() == false) {
        $cart->push($temp->pop());
    }
}

// Get the user's saved allergy IDs so the safety check can compare them with products.
function getUserAllergies($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT allergy_id FROM user_allergies WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll();

    $allergies = array();

    foreach ($rows as $row) {
        $allergies[] = $row['allergy_id'];
    }

    return $allergies;
}

function isProductSafe($pdo, $product_id, $user_allergies) {
    $stmt = $pdo->prepare("SELECT allergy_id FROM product_allergies WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product_allergies = $stmt->fetchAll();

    foreach ($product_allergies as $product_allergy) {
        foreach ($user_allergies as $user_allergy) {
            if ($product_allergy['allergy_id'] == $user_allergy) {
                return false;
            }
        }
    }
    return true;
}

function productInCart($product_id, $cart_items) {
    foreach ($cart_items as $item) {
        if ($item['product_id'] == $product_id) {
            return true;
        }
    }
    return false;
}

// Start with an empty cart if the user has not created one yet.
if (!isset($_SESSION['cart_items'])) {
    $_SESSION['cart_items'] = array();
}

$cart = new Stack();

foreach ($_SESSION['cart_items'] as $item) {
    $cart->push($item);
}

$message = '';
$message_type = 'success';
$cart_changed = false;


// Handle cart actions such as add, remove, clear, or update quantity.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $action = '';
    $product_id = 0;

    if (isset($_POST['action'])) {
        $action = $_POST['action'];
    }

    if (isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
    }


    if ($action == 'clear') {
        $cart->clear();
        $message = tr(
            'The cart was cleared.',
            'העגלה רוקנה.'
        );
        $cart_changed = true;
    }

    if ($action == 'add' && $product_id > 0) {

        $product = getProduct($pdo, $product_id);

        if ($product == false) {

            $message = tr(
                'Product not found.',
                'המוצר לא נמצא.'
            );
            $message_type = 'error';

        } else if ($product['stock'] <= 0) {

            $message = tr(
                'This product is out of stock.',
                'המוצר אזל מהמלאי.'
            );

            $message_type = 'error';

        } else {

            $added = addProductToCart(
                $cart,
                $product_id,
                $product['stock']
            );

            if ($added == true) {

                $message = tr(
                    'Product added to your cart.',
                    'המוצר נוסף לעגלה.'
                );

                $cart_changed = true;

            } else {

                $message = tr(
                    'You cannot add more than the available stock.',
                    'לא ניתן להוסיף יותר מהכמות הקיימת במלאי.'
                );

                $message_type = 'error';
            }
        }
    }


    if ($action == 'update' && $product_id > 0) {

        $product = getProduct($pdo, $product_id);

        $new_quantity = 1;

        if (isset($_POST['quantity'])) {
            $new_quantity = (int)$_POST['quantity'];
        }

        if ($product == false) {

            $message = tr(
                'Product not found.',
                'המוצר לא נמצא.'
            );

            $message_type = 'error';

        } else if ($product['stock'] <= 0) {

            removeProductFromCart(
                $cart,
                $product_id
            );

            $message = tr(
                'This product is out of stock and was removed.',
                'המוצר אזל מהמלאי והוסר מהעגלה.'
            );

            $message_type = 'error';
            $cart_changed = true;

        } else {

            if ($new_quantity < 1) {
                $new_quantity = 1;
            }

            if ($new_quantity > $product['stock']) {

                $new_quantity = $product['stock'];

                $message = tr(
                    'Quantity changed to the available stock.',
                    'הכמות שונתה לכמות הקיימת במלאי.'
                );

                $message_type = 'error';

            } else {

                $message = tr(
                    'Quantity updated successfully.',
                    'הכמות עודכנה בהצלחה.'
                );
            }

            updateCartQuantity($cart,$product_id,$new_quantity);
            $cart_changed = true;
        }
    }


    // Remove product
    if ($action == 'remove' && $product_id > 0) {

        removeProductFromCart($cart,$product_id);
        $message = tr(
            'Product removed from your cart.',
            'המוצר הוסר מהעגלה.'
        );
        $cart_changed = true;
    }

    if ($cart_changed == true) {
        saveCart($cart);
        $_SESSION['coupon'] = null;
    }
}

$cart_items = $cart->getItems();

$display_items = array();
$total_amount = 0;
$has_cart_items = false;

$clean_cart = new Stack();

foreach ($cart_items as $item) {

    $product = getProduct(
        $pdo,
        $item['product_id']
    );

    if ($product != false && $product['stock'] > 0) {

        $quantity = $item['quantity'];

        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($quantity > $product['stock']) {
            $quantity = $product['stock'];
        }

        $clean_item = array(
            'product_id' => $product['id'],
            'quantity' => $quantity
        );

        $clean_cart->push($clean_item);

        $product['cart_quantity'] = $quantity;

        $display_items[] = $product;

        $total_amount =
            $total_amount +
            ($product['price'] * $quantity);

        $has_cart_items = true;
    }
}

$cart = $clean_cart;

saveCart($cart);


// Load the current user's allergy list before recommending safe products.
$user_allergies = array();

if (isLoggedIn()) {

    $user_allergies = getUserAllergies(
        $pdo,
        $_SESSION['user_id']
    );
}


// algo5 Show safe product suggestions that are not already in the cart.
$stmt = $pdo->query("SELECT * FROM products WHERE stock > 0 ORDER BY id DESC LIMIT 20");
$candidates = $stmt->fetchAll();
$more_products = array();
$more_found = 0;
$has_more_products = false;

foreach ($candidates as $candidate) {
    $already_in_cart = productInCart(
        $candidate['id'],
        $cart->getItems()
    );
    $safe = isProductSafe(
        $pdo,
        $candidate['id'],
        $user_allergies
    );
    if ($already_in_cart == false && $safe == true) {
        $more_products[] = $candidate;
        $more_found++;
        $has_more_products = true;
        if ($more_found == 4) {
            break;
        }
    }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= tr('Shopping Cart - SafeBite', 'עגלת קניות - SafeBite') ?>
    </title>

    <link rel="stylesheet" href="style.css">

    <style>
        .cart-container {
            max-width: 1200px;
            margin: 35px auto;
            padding: 0 25px 50px;
        }

        .cart-header {
            background: linear-gradient(135deg, #4caf50, #1f6d3a);
            color: white;
            padding: 28px 32px;
            border-radius: 16px;
            margin-bottom: 20px;
        }

        .cart-header h1 {
            margin: 0;
        }

        .cart-header p {
            margin: 7px 0 0;
        }

        .cart-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            align-items: start;
        }

        .cart-products {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .cart-product {
            background: white;
            border: 1px solid #dfe9e0;
            border-radius: 16px;
            padding: 20px;

            display: grid;
            grid-template-columns: 135px 1fr 120px;
            gap: 22px;
            align-items: center;

            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.05);
        }

        .cart-product-image {
            width: 135px;
            height: 135px;
            background: #f3f8f3;
            border-radius: 12px;

            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .cart-product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .cart-product-info h3 {
            margin: 0 0 7px;
            font-size: 1.15rem;
        }

        .cart-price {
            margin: 0 0 5px;
            color: #333;
        }

        .cart-stock {
            margin: 0 0 15px;
            color: #69736a;
            font-size: 0.9rem;
        }

        .quantity-form {
            display: flex;
            align-items: center;
            gap: 9px;
            flex-wrap: wrap;
        }

        .quantity-form label {
            font-weight: bold;
        }

        .quantity-form input {
            width: 65px;
            padding: 9px;
            border: 1px solid #ccd8cd;
            border-radius: 8px;
            text-align: center;
        }

        .cart-product-right {
            text-align: right;
        }

        .product-total-label {
            display: block;
            color: #777;
            font-size: 0.85rem;
        }

        .product-total {
            display: block;
            color: #1f6d3a;
            font-size: 1.25rem;
            margin: 4px 0 20px;
        }

        .remove-button {
            border: none;
            background: none;
            color: #dc3545;
            padding: 0;
            cursor: pointer;
            font-weight: bold;
        }

        .remove-button:hover {
            text-decoration: underline;
        }

        .cart-summary {
            background: white;
            border: 1px solid #dfe9e0;
            border-radius: 16px;
            padding: 27px;

            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.05);

            position: sticky;
            top: 100px;
        }

        .cart-summary h2 {
            margin: 0 0 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .summary-line {
            border-top: 1px solid #e1e9e2;
            margin: 20px 0;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.25rem;
            margin-bottom: 25px;
        }

        .summary-total strong {
            color: #1f6d3a;
        }

        .checkout-button,
        .continue-button,
        .clear-button {
            display: block;
            width: 100%;
            box-sizing: border-box;

            padding: 13px;
            margin-top: 10px;

            border-radius: 9px;
            text-align: center;
            font-weight: bold;
            text-decoration: none;
        }

        .checkout-button {
            background: #4caf50;
            color: white;
            border: none;
        }

        .continue-button {
            background: #edf7ee;
            border: 1px solid #d2e7d4;
            color: #1f6d3a;
        }

        .clear-button {
            background: white;
            border: 1px solid #efc8cc;
            color: #dc3545;
            cursor: pointer;
        }

        .empty-cart {
            background: white;
            border: 1px solid #dfe9e0;
            border-radius: 16px;
            padding: 55px;
            text-align: center;
        }

        .recommendations {
            margin-top: 45px;
        }

        .recommendation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .recommendation-header h2 {
            margin: 0;
        }

        .recommendation-header p {
            color: #69736a;
            margin: 5px 0 0;
        }

        .recommendation-header a {
            color: #1f6d3a;
            text-decoration: none;
            font-weight: bold;
        }

        .recommendation-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        .recommendation-card {
            background: white;
            border: 1px solid #dfe9e0;
            border-radius: 14px;
            overflow: hidden;
        }

        .recommendation-image {
            height: 145px;
            background: #f3f8f3;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .recommendation-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .recommendation-info {
            padding: 16px;
        }

        .recommendation-info h3 {
            margin: 0 0 8px;
        }

        .recommendation-info strong {
            color: #1f6d3a;
        }

        .recommendation-info p {
            color: #69736a;
            font-size: 0.85rem;
        }

        .recommendation-info button {
            width: 100%;
        }

        @media (max-width: 900px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }

            .cart-summary {
                position: static;
            }

            .recommendation-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 650px) {
            .cart-product {
                grid-template-columns: 90px 1fr;
            }

            .cart-product-image {
                width: 90px;
                height: 90px;
            }

            .cart-product-right {
                grid-column: 1 / -1;
                text-align: left;
            }

            .recommendation-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

</head>

<body style="min-height: 100vh;">

<?php include 'navbar.php'; ?>

<main class="cart-container">

    <div class="cart-header">
        <h1>
            <?= tr('My Shopping Cart', 'עגלת הקניות שלי') ?>
        </h1>

        <p>
            <?= tr(
                'Review your products before checkout.',
                'בדוק את המוצרים לפני התשלום.'
            ) ?>
        </p>
    </div>


    <?php if ($message != ''): ?>

        <div class="alert <?= $message_class ?>">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($has_cart_items == false): ?>

        <div class="empty-cart">

            <h2>
                <?= tr(
                    'Your cart is empty',
                    'העגלה שלך ריקה'
                ) ?>
            </h2>

            <p>
                <?= tr(
                    'Browse our products and add something to your cart.',
                    'עיין במוצרים והוסף מוצרים לעגלה.'
                ) ?>
            </p>

            <a href="index.php" class="btn btn-primary">
                <?= tr('Browse Products', 'עיון במוצרים') ?>
            </a>

        </div>


    <?php else: ?>

        <div class="cart-layout">


            <section class="cart-products">

                <?php foreach ($display_items as $item): ?>

                    <div class="cart-product">


                        <div class="cart-product-image">

                            <?php if ($item['image'] != ''): ?>

                                <img
                                    src="<?= htmlspecialchars($item['image']) ?>"
                                    alt="<?= htmlspecialchars($item['name']) ?>"
                                >

                            <?php else: ?>

                                <span>
                                    <?= tr('No Image', 'אין תמונה') ?>
                                </span>

                            <?php endif; ?>

                        </div>


                        <div class="cart-product-info">

                            <h3>
                                <?= htmlspecialchars($item['name']) ?>
                            </h3>

                            <p class="cart-price">
                                $<?= number_format($item['price'], 2) ?>
                                <?= tr('each', 'ליחידה') ?>
                            </p>

                            <p class="cart-stock">
                                <?= tr('Available Stock:', 'כמות במלאי:') ?>
                                <?= $item['stock'] ?>
                            </p>


                            <form method="POST" class="quantity-form">

                                <input
                                    type="hidden"
                                    name="action"
                                    value="update"
                                >

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= $item['id'] ?>"
                                >

                                <label>
                                    <?= tr('Quantity:', 'כמות:') ?>
                                </label>

                                <input
                                    type="number"
                                    name="quantity"
                                    value="<?= $item['cart_quantity'] ?>"
                                    min="1"
                                    max="<?= $item['stock'] ?>"
                                    required
                                >

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    <?= tr('Update', 'עדכון') ?>
                                </button>

                            </form>

                        </div>


                        <div class="cart-product-right">

                            <span class="product-total-label">
                                <?= tr('Total', 'סה״כ') ?>
                            </span>

                            <strong class="product-total">

                                $<?= number_format(
                                    $item['price'] * $item['cart_quantity'],
                                    2
                                ) ?>

                            </strong>


                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="action"
                                    value="remove"
                                >

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= $item['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="remove-button"
                                >
                                    <?= tr('Remove', 'הסרה') ?>
                                </button>

                            </form>

                        </div>


                    </div>

                <?php endforeach; ?>

            </section>


            <aside class="cart-summary">

                <h2>
                    <?= tr('Order Summary', 'סיכום הזמנה') ?>
                </h2>


                <div class="summary-row">

                    <span>
                        <?= tr('Cart Total', 'סה״כ עגלה') ?>
                    </span>

                    <strong>
                        $<?= number_format($total_amount, 2) ?>
                    </strong>

                </div>


                <div class="summary-line"></div>


                <div class="summary-total">

                    <span>
                        <?= tr('Total', 'סה״כ') ?>
                    </span>

                    <strong>
                        $<?= number_format($total_amount, 2) ?>
                    </strong>

                </div>


                <?php if (isLoggedIn()): ?>

                    <a
                        href="checkout.php"
                        class="checkout-button"
                    >
                        <?= tr(
                            'Proceed to Checkout',
                            'המשך לתשלום'
                        ) ?>
                    </a>

                <?php else: ?>

                    <a
                        href="login.php"
                        class="checkout-button"
                    >
                        <?= tr(
                            'Login to Checkout',
                            'התחבר כדי להמשיך לתשלום'
                        ) ?>
                    </a>

                <?php endif; ?>


                <a
                    href="index.php"
                    class="continue-button"
                >
                    <?= tr(
                        'Continue Shopping',
                        'המשך בקניות'
                    ) ?>
                </a>


                <form method="POST">

                    <input
                        type="hidden"
                        name="action"
                        value="clear"
                    >

                    <button
                        type="submit"
                        class="clear-button"
                    >
                        <?= tr(
                            'Clear Cart',
                            'רוקן עגלה'
                        ) ?>
                    </button>

                </form>

            </aside>


        </div>

    <?php endif; ?>

    <?php if ($has_more_products == true): ?>

        <section class="recommendations">


            <div class="recommendation-header">

                <div>

                    <h2>
                        <?= tr(
                            'You May Also Like',
                            'אולי תאהב גם'
                        ) ?>
                    </h2>

                    <p>
                        <?= tr(
                            'Safe products you can add to your order.',
                            'מוצרים בטוחים שניתן להוסיף להזמנה.'
                        ) ?>
                    </p>

                </div>


                <a href="index.php">

                    <?= tr(
                        'View All Products',
                        'כל המוצרים'
                    ) ?>

                </a>

            </div>


            <div class="recommendation-grid">


                <?php foreach ($more_products as $product): ?>

                    <div class="recommendation-card">


                        <div class="recommendation-image">

                            <?php if ($product['image'] != ''): ?>

                                <img
                                    src="<?= htmlspecialchars($product['image']) ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                >

                            <?php else: ?>

                                <span>
                                    <?= tr('No Image', 'אין תמונה') ?>
                                </span>

                            <?php endif; ?>

                        </div>


                        <div class="recommendation-info">

                            <h3>
                                <?= htmlspecialchars($product['name']) ?>
                            </h3>

                            <strong>
                                $<?= number_format(
                                    $product['price'],
                                    2
                                ) ?>
                            </strong>

                            <p>
                                <?= $product['stock'] ?>
                                <?= tr(
                                    'available',
                                    'במלאי'
                                ) ?>
                            </p>


                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="action"
                                    value="add"
                                >

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= $product['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    <?= tr(
                                        'Add to Cart',
                                        'הוסף לעגלה'
                                    ) ?>
                                </button>

                            </form>

                        </div>


                    </div>

                <?php endforeach; ?>


            </div>

        </section>

    <?php endif; ?>
</main>
<?php include 'chatbot.php'; ?>
</body>
</html>