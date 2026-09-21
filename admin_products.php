<?php
require 'db.php';

// Remove a product from the catalog when the admin chooses to delete it.
function deleteProduct($pdo, $product_id) {
    $stmt = $pdo->prepare(
        "DELETE FROM products WHERE id = ?"
    );
    $stmt->execute([$product_id]);
}

// Only administrators can access the product management page.
if (!isLoggedIn() || !isAdmin()) {
    header("Location: index.php");
    exit();
}
$is_admin_theme = true;

$message = '';
$message_type = '';
// Check if the admin submitted a delete request for a product.
if (
    $_SERVER['REQUEST_METHOD'] == 'POST'
    && isset($_POST['delete_product'])
) {
    $product_id = $_POST['delete_product'];

    deleteProduct($pdo, $product_id);

    $message = 'Product deleted successfully.';
    $message_type = 'success';
}

// Load the list of products so the admin can review and manage them.
$stmt = $pdo->query(
    "SELECT *
     FROM products
     ORDER BY id DESC"
);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Products - SafeBite</title>

    <link rel="stylesheet" href="style.css">
</head>

<body class="admin-theme">

<?php include 'navbar.php'; ?>

<div class="admin-page-wrap">

    <div class="admin-header">

        <h1>Manage Products</h1>

        <p>
            Review products, remove items,
            or go to add new products.
        </p>

    </div>


    <?php if ($message != ''): ?>

        <?php if ($message_type == 'success'): ?>

            <div class="alert alert-success">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php else: ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>

    <?php endif; ?>


    <div class="admin-panel">

        <div class="flex-between">

            <h2>Products List</h2>

            <a
                href="add_product.php"
                class="btn btn-primary"
            >
                Add Product
            </a>

        </div>


        <table>

            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Description</th>
                <th>Action</th>
            </tr>


            <?php foreach ($products as $product): ?>

                <tr>

                    <td>
                        <?= $product['id'] ?>
                    </td>

                    <td>
                        <strong>
                            <?= htmlspecialchars($product['name']) ?>
                        </strong>
                    </td>

                    <td>
                        $<?= number_format($product['price'], 2) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($product['description']) ?>
                    </td>

                    <td>

                        <a
                            href="edit_product.php?id=<?= $product['id'] ?>"
                            class="btn btn-warning"
                        >
                            Edit
                        </a>

                        <form
                            method="POST"
                            onsubmit="return confirm('Delete this product?');"
                        >

                            <input
                                type="hidden"
                                name="delete_product"
                                value="<?= $product['id'] ?>"
                            >

                            <button
                                type="submit"
                                class="btn btn-danger"
                            >
                                Delete
                            </button>

                        </form>

                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>

</div>
</body>
</html>