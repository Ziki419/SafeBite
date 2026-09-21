<?php
require 'db.php';

// Only admins can edit product data.
if (!isLoggedIn() || !isAdmin()) {
    header("Location: index.php");
    exit();
}

// Load the full product record before showing the edit form.
function getProduct($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}

// Save the edited product values back into the database.
function updateProduct($pdo, $product_id, $name, $price, $description, $stock) {
    $stmt = $pdo->prepare(
        "UPDATE products
         SET name = ?, price = ?, description = ?, stock = ?
         WHERE id = ?"
    );
    $stmt->execute([
        $name,
        $price,
        $description,
        $stock,
        $product_id
    ]);
}

// Check that a product ID was sent before editing.
if (!isset($_GET['id'])) {
    header("Location: admin_products.php");
    exit();
}

$product_id = $_GET['id'];

$product_to_edit = getProduct($pdo, $product_id);

if (!$product_to_edit) {
    die("Product not found in the database.");
}


// Update the product when the admin submits the edit form.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {

    $product_id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];

    updateProduct(
        $pdo,
        $product_id,
        $name,
        $price,
        $description,
        $stock
    );
    header("Location: admin_products.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Product - SafeBite Admin</title>

    <link rel="stylesheet" href="style.css">

    <style>

        .edit-product-card {
            padding: 28px;
            box-sizing: border-box;
        }

        .edit-product-card .form-group {
            margin-bottom: 20px;
        }

        .edit-product-card .form-control {
            width: 100%;
            box-sizing: border-box;
        }

        .edit-product-card textarea {
            display: block;
            resize: vertical;
        }

        .edit-product-card .btn-submit {
            width: 100%;
            box-sizing: border-box;
            margin-top: 5px;
        }

    </style>

</head>

<body>

<?php include 'navbar.php'; ?>


<div class="admin-container">

    <div class="admin-header">

        <h2>Edit Product</h2>

        <a
            href="admin_products.php"
            class="btn-back"
        >
            Back to Products
        </a>

    </div>


    <!-- CHANGE - added edit-product-card class -->
    <div class="admin-card edit-product-card">

        <form method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= $product_to_edit['id'] ?>"
            >


            <div class="form-group">

                <label>
                    Name:
                </label>

                <input
                    type="text"
                    name="name"
                    class="form-control"
                    value="<?= htmlspecialchars($product_to_edit['name']) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Price ($):
                </label>

                <input
                    type="number"
                    step="0.01"
                    name="price"
                    class="form-control"
                    value="<?= htmlspecialchars($product_to_edit['price']) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Description:
                </label>

                <textarea
                    name="description"
                    class="form-control"
                    rows="4"
                ><?= htmlspecialchars($product_to_edit['description']) ?></textarea>

            </div>


            <div class="form-group">

                <label>
                    Stock:
                </label>

                <input
                    type="number"
                    name="stock"
                    class="form-control"
                    value="<?= htmlspecialchars($product_to_edit['stock']) ?>"
                    min="0"
                    required
                >

            </div>


            <button
                type="submit"
                name="update_product"
                class="btn-submit"
            >
                Save Changes
            </button>

        </form>
    </div>
</div>
</body>
</html>