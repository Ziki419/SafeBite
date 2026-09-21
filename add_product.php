<?php
require 'db.php';

// Add the product first, then save any checked allergen links for that product.
function addProductWithAllergens($pdo,$name,$description,$price,$image,$allergens) {
    $stmt = $pdo->prepare(
        "INSERT INTO products
        (name, description, price, image)
        VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$name,$description,$price,$image]);
    $new_product_id = $pdo->lastInsertId();
    if (!empty($allergens)) {
        $stmt_allergy = $pdo->prepare(
            "INSERT INTO product_allergies
            (product_id, allergy_id)
            VALUES (?, ?)"
        );
        foreach ($allergens as $allergy_id) {
            $stmt_allergy->execute([$new_product_id,$allergy_id]);
        }
    }
}

// Only admins can open this page and add items to the catalog.
if (!isLoggedIn() || !isAdmin()) {
    header("Location: index.php");
    exit();
}

$success_message = '';
$error_message = '';

// Read the product form data and save the new product into the database.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $image = trim($_POST['image']);
    $allergens = array();
    if (isset($_POST['allergens'])) {
        $allergens = $_POST['allergens'];
    }
    if (empty($name) || empty($price)) {
        $error_message =
            "Product name and price are required.";
    } else {
        addProductWithAllergens($pdo,$name,$description,$price,$image,$allergens);
        $success_message =
            "Successfully added '" .$name ."' to the catalog!";
        $_POST = array();
    }
}

// Load all allergy options so the admin can choose which allergens the product contains.
$stmt_allergies = $pdo->query(
    "SELECT *
    FROM allergies
    ORDER BY name ASC"
);
$allergies = $stmt_allergies->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Product - SafeBite Admin</title>

    <link rel="stylesheet" href="style.css">
</head>

<body class="admin-theme">

    <?php include 'navbar.php'; ?>

    <div class="admin-container">

        <div class="admin-header">

            <h2>Add New Product</h2>

            <a href="admin.php" class="btn-back">
                Back to Dashboard
            </a>

        </div>

        <div class="admin-card"  style="padding: 30px; box-sizing: border-box;">

            <?php if ($success_message != ''): ?>

                <div class="alert alert-success">

                    <?= htmlspecialchars($success_message) ?>

                </div>

            <?php endif; ?>

            <?php if ($error_message != ''): ?>

                <div class="alert alert-error">

                    <?= htmlspecialchars($error_message) ?>

                </div>

            <?php endif; ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="add_product"
                    value="1"
                >

                <div class="form-group">

                    <label>Product Name</label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="e.g., Whole Wheat Bread"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>Price ($)</label>

                    <input
                        type="number"
                        step="0.01"
                        name="price"
                        class="form-control"
                        placeholder="e.g., 5.99"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>Image URL</label>

                    <input
                        type="text"
                        name="image"
                        class="form-control"
                        placeholder="e.g., assets/bread.jpg or https://link-to-image.com/img.jpg"
                    >

                </div>

                <div class="form-group">

                    <label>Product Description</label>

                    <textarea
                        name="description"
                        class="form-control"
                        placeholder="Write a short description of the product..."
                    ></textarea>

                </div>

                <div class="form-group">

                    <label>
                        Contains Allergens
                        (Check all that apply):
                    </label>

                    <div class="allergy-grid">

                        <?php if (empty($allergies)): ?>

                            <span
                                style="
                                    color: #dc3545;
                                    font-weight: bold;
                                "
                            >
                                No allergies in database!
                                Add them in phpMyAdmin first.
                            </span>

                        <?php else: ?>

                            <?php foreach ($allergies as $allergy): ?>

                                <label class="allergy-item">

                                    <input
                                        type="checkbox"
                                        name="allergens[]"
                                        value="<?= $allergy['id'] ?>"
                                    >

                                    <?= htmlspecialchars(
                                        $allergy['name']
                                    ) ?>

                                </label>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </div>

                <button
                    type="submit"
                    class="btn-submit"
                >
                    Insert into Database
                </button>
                
            </form>
        </div>
    </div>
</body>
</html>