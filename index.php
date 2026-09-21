<?php
require 'db.php';

// algo1 Get the logged-in user's allergy IDs to compare against product allergens.
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

// algo2  Find up to two other products from the same category that are safe for the user.
function getSafeAlternatives($pdo, $product, $user_allergies) {
    $stmt = $pdo->prepare(
        "SELECT * FROM products
         WHERE type = ?
         AND id != ?
         AND stock > 0"
    );
    $stmt->execute([
        $product['type'],
        $product['id']
    ]);
    $products = $stmt->fetchAll();
    $safe_products = array();
    $found = 0;
    foreach ($products as $alternative) {
        if (isProductSafe($pdo, $alternative['id'], $user_allergies)) {
            $safe_products[] = $alternative;
            $found++;
            if ($found == 2) {
                break;
            }
        }
    }
    return $safe_products;
}


// Load the current user's allergies so unsafe products can be marked.
$user_allergies = array();

if (isLoggedIn()) {
    $user_allergies = getUserAllergies($pdo, $_SESSION['user_id']);
}


// Read search and filter values from the browser to find product matches.
$search = '';

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}


$type = '';

if (isset($_GET['type'])) {
    $type = $_GET['type'];
}


$diet = '';

if (isset($_GET['diet'])) {
    $diet = $_GET['diet'];
}


// Build the product query based on the selected diet and product type.
if ($type != '' && $diet != '') {

    $stmt = $pdo->prepare(
        "SELECT * FROM products
         WHERE name LIKE ?
         AND type = ?
         AND dietary_tags LIKE ?
         ORDER BY id DESC"
    );

    $stmt->execute([
        "%" . $search . "%",
        $type,
        "%" . $diet . "%"
    ]);

} else if ($type != '') {

    $stmt = $pdo->prepare(
        "SELECT * FROM products
         WHERE name LIKE ?
         AND type = ?
         ORDER BY id DESC"
    );

    $stmt->execute(["%" . $search . "%",$type]);

} else if ($diet != '') {

    $stmt = $pdo->prepare(
        "SELECT * FROM products
         WHERE name LIKE ?
         AND dietary_tags LIKE ?
         ORDER BY id DESC"
    );

    $stmt->execute([
        "%" . $search . "%",
        "%" . $diet . "%"
    ]);

} else {
    $stmt = $pdo->prepare(
        "SELECT * FROM products
         WHERE name LIKE ?
         ORDER BY id DESC"
    );

    $stmt->execute([
        "%" . $search . "%"
    ]);
}
$products = $stmt->fetchAll();

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
        <?= tr('SafeBite - Home', 'SafeBite - דף הבית') ?>
    </title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<?php include 'navbar.php'; ?>


<div class="hero">

    <h1>
        <?= tr(
            'Welcome to SafeBite',
            'ברוכים הבאים ל-SafeBite'
        ) ?>
    </h1>

    <p>
        <?= tr(
            'The safest way to shop for your dietary needs.',
            'הדרך הבטוחה ביותר לקנות בהתאם לצרכים התזונתיים שלך.'
        ) ?>
    </p>

</div>


<div class="container">


    <?php if (isLoggedIn()): ?>

        <div class="alert-box">

            <?= tr(
                'Safety Engine Active: Products are checked against your allergies.',
                'מנוע הבטיחות פעיל: המוצרים נבדקים לפי האלרגיות שלך.'
            ) ?>

        </div>

    <?php else: ?>

        <div class="alert-box">

            <a href="login.php">
                <?= tr('Login', 'התחברות') ?>
            </a>

            <?= tr(
                ' to check products against your allergies.',
                ' כדי לבדוק מוצרים לפי האלרגיות שלך.'
            ) ?>

        </div>

    <?php endif; ?>


    <form
        method="GET"
        action="index.php"
        class="filter-bar"
    >


        <div class="search-row">

            <input
                type="text"
                name="search"
                placeholder="<?= tr(
                    'Search for products...',
                    'חיפוש מוצרים...'
                ) ?>"
                value="<?= htmlspecialchars($search) ?>"
            >

            <button
                type="submit"
                class="btn-filter"
            >
                <?= tr('Search', 'חיפוש') ?>
            </button>

        </div>


        <div class="lifestyle-filters">

            <label>
                <?= tr('Diet:', 'תזונה:') ?>
            </label>


            <select
                name="diet"
                class="form-control"
            >

                <option value="">
                    <?= tr('All Diets', 'כל סוגי התזונה') ?>
                </option>


                <option
                    value="Vegan"
                    <?php if ($diet == 'Vegan') echo 'selected'; ?>
                >
                    <?= tr('Vegan', 'טבעוני') ?>
                </option>


                <option
                    value="Vegetarian"
                    <?php if ($diet == 'Vegetarian') echo 'selected'; ?>
                >
                    <?= tr('Vegetarian', 'צמחוני') ?>
                </option>


                <option
                    value="Sugar-Free"
                    <?php if ($diet == 'Sugar-Free') echo 'selected'; ?>
                >
                    <?= tr('Sugar-Free', 'ללא סוכר') ?>
                </option>


                <option
                    value="Halal"
                    <?php if ($diet == 'Halal') echo 'selected'; ?>
                >
                    <?= tr('Halal', 'חלאל') ?>
                </option>


                <option
                    value="Kosher"
                    <?php if ($diet == 'Kosher') echo 'selected'; ?>
                >
                    <?= tr('Kosher', 'כשר') ?>
                </option>

            </select>


            <label>
                <?= tr('Type:', 'סוג:') ?>
            </label>


            <select
                name="type"
                class="form-control"
            >

                <option value="">
                    <?= tr('All Types', 'כל הסוגים') ?>
                </option>


                <option
                    value="dairy"
                    <?php if ($type == 'dairy') echo 'selected'; ?>
                >
                    <?= tr('Dairy', 'מוצרי חלב') ?>
                </option>


                <option
                    value="meat"
                    <?php if ($type == 'meat') echo 'selected'; ?>
                >
                    <?= tr('Meat', 'בשר') ?>
                </option>


                <option
                    value="wheat"
                    <?php if ($type == 'wheat') echo 'selected'; ?>
                >
                    <?= tr('Wheat', 'חיטה') ?>
                </option>


                <option
                    value="fruit&vege"
                    <?php if ($type == 'fruit&vege') echo 'selected'; ?>
                >
                    <?= tr('Fruit & Vege', 'פירות וירקות') ?>
                </option>


                <option
                    value="snacks"
                    <?php if ($type == 'snacks') echo 'selected'; ?>
                >
                    <?= tr('Snacks', 'חטיפים') ?>
                </option>


                <option
                    value="sauce"
                    <?php if ($type == 'sauce') echo 'selected'; ?>
                >
                    <?= tr('Sauce', 'רטבים') ?>
                </option>

            </select>


            <button
                type="submit"
                class="btn-filter"
            >
                <?= tr('Filter', 'סינון') ?>
            </button>

        </div>

    </form>


    <div class="product-grid">

        <?php

        // Keep track of whether any products matched the current filters.
        $found_product = false;

        ?>


        <?php foreach ($products as $product): ?>

            <?php

            // Mark that at least one product was found and then check whether it is safe for the user.
            $found_product = true;

            $is_safe = isProductSafe(
                $pdo,
                $product['id'],
                $user_allergies
            );
            ?>


            <div
                class="product-card <?php
                if ($is_safe == false) echo 'unsafe';
                ?>"
            >


                <?php if ($is_safe == false): ?>

                    <div class="unsafe-badge">
                        <?= tr(
                            'UNSAFE FOR YOU',
                            'לא בטוח עבורך'
                        ) ?>
                    </div>

                <?php endif; ?>


                <div class="product-image">

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


                <div class="product-info">

                    <div class="product-title">
                        <?= htmlspecialchars($product['name']) ?>
                    </div>

                    <div class="product-desc">
                        <?= htmlspecialchars($product['description']) ?>
                    </div>

                    <div class="product-price">
                        $<?= number_format($product['price'], 2) ?>
                    </div>

                </div>


                <div class="card-footer">


                    <?php if ($is_safe == true): ?>


                        <?php if ($product['stock'] > 0): ?>

                            <form
                                action="cart.php"
                                method="POST"
                            >

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
                                    class="btn-add"
                                >
                                    <?= tr(
                                        'Add to Cart',
                                        'הוסף לעגלה'
                                    ) ?>
                                </button>

                            </form>

                        <?php else: ?>

                            <button
                                disabled
                                class="btn-add"
                            >
                                <?= tr(
                                    'Out of Stock',
                                    'אזל מהמלאי'
                                ) ?>
                            </button>

                        <?php endif; ?>


                    <?php else: ?>


                        <button
                            disabled
                            class="btn-add"
                        >
                            <?= tr(
                                'Blocked by Allergy',
                                'חסום בגלל אלרגיה'
                            ) ?>
                        </button>


                        <?php

                        $safe_alternatives = getSafeAlternatives(
                            $pdo,
                            $product,
                            $user_allergies
                        );

                        $found_alternative = false;

                        ?>


                        <div class="safe-swap-box">

                            <span class="safe-swap-title">
                                <?= tr(
                                    'Safe Alternatives:',
                                    'חלופות בטוחות:'
                                ) ?>
                            </span>


                            <?php foreach ($safe_alternatives as $alternative): ?>

                                <?php
                                $found_alternative = true;
                                ?>

                                <div class="safe-swap-item">

                                    <span>
                                        <?= htmlspecialchars(
                                            $alternative['name']
                                        ) ?>
                                    </span>


                                    <form
                                        action="cart.php"
                                        method="POST"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="add"
                                        >

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?= $alternative['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn-add"
                                        >
                                            <?= tr('Add', 'הוסף') ?>
                                        </button>

                                    </form>

                                </div>

                            <?php endforeach; ?>


                            <?php if ($found_alternative == false): ?>

                                <p>
                                    <?= tr(
                                        'No safe alternatives found.',
                                        'לא נמצאו חלופות בטוחות.'
                                    ) ?>
                                </p>

                            <?php endif; ?>


                        </div>


                    <?php endif; ?>


                </div>

            </div>


        <?php endforeach; ?>


        <?php if ($found_product == false): ?>

            <p>
                <?= tr(
                    'No products found.',
                    'לא נמצאו מוצרים.'
                ) ?>
            </p>

        <?php endif; ?>


    </div>

</div>


<?php include 'chatbot.php'; ?>

</body>
</html>