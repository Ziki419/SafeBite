<?php
require 'db.php';

// Only logged-in users may open order detail pages.
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}
$order_id = 0;
if (isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
}
// Load the selected order so the details page can show the order summary.
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {die("Order not found.");}

// User can only see their own order and admin can see every order.
if (!isAdmin() && $order['user_id'] != $_SESSION['user_id']) {die("Access denied.");}

// Get the items connected to this order so they can be listed in the table.
$stmt = $pdo->prepare("SELECT order_items.*, products.name FROM order_items JOIN products ON products.id = order_items.product_id WHERE order_items.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
$body_class = '';

if (isAdmin()) {
    $body_class = 'admin-theme';
    $is_admin_theme = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details - SafeBite</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="min-height: 100vh;">
<?php include 'navbar.php'; ?>
<div class="page-container">
    <div class="page-header">
        <h1>Order Details #<?= $order['id'] ?></h1>
        <p>Status: <?= htmlspecialchars($order['status']) ?></p>
    </div>
    <div class="admin-card" style="padding:24px; margin-top:24px;">
        <p>
            <strong>Total Price:</strong>
            $<?= number_format($order['total_price'], 2) ?>
        </p>

        <p>
            <strong>Created At:</strong>
            <?= htmlspecialchars($order['created_at']) ?>
        </p>
        <h2>Items</h2>
        <table>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($item['name']) ?>
                    </td>

                    <td>
                        <?= $item['quantity'] ?>
                    </td>

                    <td>
                        $<?= number_format($item['price'], 2) ?>
                    </td>

                    <td>
                        $<?= number_format(
                            $item['price'] * $item['quantity'],
                            2
                        ) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>