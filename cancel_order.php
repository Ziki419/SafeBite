<?php
require 'db.php';

function countRewardsReached($value, $requirement) {
    $count = 0;
    $next_reward = $requirement;
    while ($next_reward <= $value) {
        $count++;
        $next_reward = $next_reward + $requirement;
    }
    return $count;
}

function restoreStock($pdo, $items) {
    foreach ($items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);
    }
}

function removeCancelledOrderRewards($pdo, $user_id, $reward_name, $amount) {
    for ($i = 0; $i < $amount; $i++) {
        $stmt = $pdo->prepare("SELECT id FROM user_rewards WHERE user_id = ? AND is_used = 0 AND coupon_code LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$user_id, $reward_name . '%']);
        $reward = $stmt->fetch();
        if ($reward) {
            $stmt = $pdo->prepare("DELETE FROM user_rewards WHERE id = ?");
            $stmt->execute([$reward['id']]);
        }
    }
}

// Only logged-in users can cancel their own order.
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$order_id = 0;

if (isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
}

if ($order_id == 0) {
    header("Location: my_orders.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found.");
}

if ($order['status'] != 'pending') {
    die("This order can no longer be cancelled.");
}

if ($order['delivery_user_id'] != null) {
    die("This order was already accepted by a delivery driver.");
}

$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

if (empty($items)) {
    die("No products found in this order.");
}

$stmt = $pdo->prepare("SELECT total_spent, total_orders FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

// Calculate the customer's new spending and order totals after the cancellation.
$old_spent = $user['total_spent'];
$old_orders = $user['total_orders'];

$new_spent = $old_spent - $order['total_price'];
$new_orders = $old_orders - 1;

if ($new_spent < 0) {$new_spent = 0;}

if ($new_orders < 0) {$new_orders = 0;}

$old_spending_rewards = countRewardsReached($old_spent, 750);
$new_spending_rewards = countRewardsReached($new_spent, 750);

$spending_rewards_to_remove = $old_spending_rewards - $new_spending_rewards;

$old_order_rewards = countRewardsReached($old_orders, 40);
$new_order_rewards = countRewardsReached($new_orders, 40);

$order_rewards_to_remove = $old_order_rewards - $new_order_rewards;

// Return canceled items back to stock before updating the order status.
restoreStock($pdo, $items);

$stmt = $pdo->prepare("UPDATE users SET total_spent = ?, total_orders = ? WHERE id = ?");
$stmt->execute([$new_spent, $new_orders, $user_id]);

if ($spending_rewards_to_remove > 0) {
    removeCancelledOrderRewards(
        $pdo,
        $user_id,
        'REWARD50-',
        $spending_rewards_to_remove
    );
}

if ($order_rewards_to_remove > 0) {
    removeCancelledOrderRewards(
        $pdo,
        $user_id,
        'AVG40-',
        $order_rewards_to_remove
    );
}

// Mark the order as cancelled and clear all delivery details linked to it.
$stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', cancelled_at = NOW(), delivery_user_id = NULL, delivery_rate = 0, delivery_earning = 0, picked_at = NULL, delivered_at = NULL WHERE id = ?");
$stmt->execute([$order_id]);
header("Location: my_orders.php");
exit();
?>