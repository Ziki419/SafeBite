<?php
require 'db.php';

// Save a new admin reply for the selected support ticket.
function addReply($pdo, $ticket_id, $user_id, $message) {
    $stmt = $pdo->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$ticket_id, $user_id, $message]);
}

function startsWith($text, $start) {
    return substr($text, 0, strlen($start)) == $start;
}

function approveDelivery($pdo, $user_id, $ticket_id, $region) {
    $stmt = $pdo->prepare("UPDATE users SET role = 'delivery', delivery_region = ? WHERE id = ?");
    $stmt->execute([$region, $user_id]);
    $stmt = $pdo->prepare("UPDATE tickets SET status = 'closed' WHERE id = ?");
    $stmt->execute([$ticket_id]);
}

function approveAdmin($pdo, $user_id, $ticket_id) {
    $stmt = $pdo->prepare("UPDATE users SET role = 'admin', delivery_region = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
    $stmt = $pdo->prepare("UPDATE tickets SET status = 'closed' WHERE id = ?");
    $stmt->execute([$ticket_id]);
}

// Only admin users can review and respond to tickets.
if (!isLoggedIn() || !isAdmin()) {
    header("Location: index.php");
    exit();
}

$ticket_id = 0;

if (isset($_GET['id'])) {
    $ticket_id = (int)$_GET['id'];
}

// Load the selected ticket and the user details that belong to it.
$stmt = $pdo->prepare("SELECT tickets.*, users.username, users.email, users.role FROM tickets JOIN users ON users.id = tickets.user_id WHERE tickets.id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Ticket not found.");
}

$is_delivery_request = false;
$is_admin_request = false;
$requested_region = '';
$message = '';

if (startsWith($ticket['subject'], 'Work With Us - Delivery Driver:')) {
    $is_delivery_request = true;
}

if (startsWith($ticket['subject'], 'Work With Us - Admin:')) {
    $is_admin_request = true;
}

$regions = array('north', 'haifa', 'center', 'jerusalem', 'south');

foreach ($regions as $region) {
    if (startsWith($ticket['message'], 'Requested delivery region: ' . $region)) {
        $requested_region = $region;
    }
}

// Check if the admin is submitting a reply or approving a work request.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['submit_reply'])) {
        $reply_message = trim($_POST['reply_message']);

        if ($reply_message != '') {
            addReply($pdo, $ticket_id, $_SESSION['user_id'], $reply_message);
            header("Location: admin.ticketView.php?id=" . $ticket_id);
            exit();
        }
    }

    if (isset($_POST['approve_delivery']) && $is_delivery_request && $ticket['role'] == 'user') {
        if ($requested_region != '') {
            approveDelivery($pdo, $ticket['user_id'], $ticket_id, $requested_region);
            $ticket['role'] = 'delivery';
            $ticket['status'] = 'closed';
            $message = 'Delivery request approved successfully.';
        }
    }

    if (isset($_POST['approve_admin']) && $is_admin_request && $ticket['role'] == 'user') {
        approveAdmin($pdo, $ticket['user_id'], $ticket_id);
        $ticket['role'] = 'admin';
        $ticket['status'] = 'closed';
        $message = 'Admin request approved successfully.';
    }
}

// Get all previous replies so the ticket thread can be displayed in order.
$stmt = $pdo->prepare("SELECT ticket_replies.*, users.username FROM ticket_replies JOIN users ON users.id = ticket_replies.user_id WHERE ticket_replies.ticket_id = ? ORDER BY ticket_replies.created_at ASC");
$stmt->execute([$ticket_id]);
$replies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Details - SafeBite</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<?php include 'navbar.php'; ?>

<div class="admin-container">

    <div class="admin-header">
        <div>
            <h1>Ticket #<?= $ticket['id'] ?></h1>
            <p>View the ticket and respond to the user.</p>
        </div>
    </div>

    <?php if ($message != ''): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="admin-card" style="padding:30px;">

        <div class="grid-2">
            <div>
                <p><strong>User:</strong> <?= htmlspecialchars($ticket['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($ticket['email']) ?></p>
                <p><strong>Subject:</strong> <?= htmlspecialchars($ticket['subject']) ?></p>
            </div>

            <div>
                <p><strong>Status:</strong> <?= htmlspecialchars($ticket['status']) ?></p>
                <p><strong>Created At:</strong> <?= htmlspecialchars($ticket['created_at']) ?></p>
                <p><strong>User Role:</strong> <?= htmlspecialchars($ticket['role']) ?></p>
            </div>
        </div>

        <div class="info-card" style="padding:20px; margin-top:20px;">
            <h3>Original Message</h3>
            <p><?= nl2br(htmlspecialchars($ticket['message'])) ?></p>
        </div>

        <?php if (($is_delivery_request || $is_admin_request) && $ticket['role'] == 'user'): ?>

            <div class="info-card" style="padding:20px; margin-top:20px;">
                <h3>Work With Us Request</h3>

                <?php if ($is_delivery_request): ?>

                    <p>
                        <strong>Requested Role:</strong> Delivery Driver<br>
                        <strong>Requested Region:</strong> <?= htmlspecialchars($requested_region) ?>
                    </p>

                    <form method="POST">
                        <button type="submit" name="approve_delivery" class="btn btn-primary">
                            Approve Delivery Driver
                        </button>
                    </form>

                <?php endif; ?>

                <?php if ($is_admin_request): ?>

                    <p><strong>Requested Role:</strong> Admin</p>

                    <form method="POST">
                        <button type="submit" name="approve_admin" class="btn btn-primary">
                            Approve Admin
                        </button>
                    </form>

                <?php endif; ?>

            </div>

        <?php endif; ?>

        <?php if (count($replies) > 0): ?>

            <div style="margin-top:25px;">
                <h3>Replies</h3>

                <?php foreach ($replies as $reply): ?>

                    <div class="info-card" style="padding:15px; margin-top:10px;">
                        <strong><?= htmlspecialchars($reply['username']) ?></strong>
                        <span class="small-text"><?= htmlspecialchars($reply['created_at']) ?></span>
                        <p><?= nl2br(htmlspecialchars($reply['message'])) ?></p>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <?php if ($ticket['status'] == 'open'): ?>

            <div style="margin-top:25px;">
                <h3>Add a Reply</h3>

                <form method="POST">
                    <textarea name="reply_message" class="form-control" rows="4" placeholder="Type your response here..." required></textarea>
                    <br>
                    <button type="submit" name="submit_reply" class="btn btn-primary">Send Reply</button>
                </form>
            </div>

        <?php else: ?>

            <p style="margin-top:25px;">
                This ticket is closed. Reopen it to add another reply.
            </p>

        <?php endif; ?>

        <div style="margin-top:25px;">
            <a href="admin_tickets.php" class="btn btn-secondary">Back to Tickets</a>

            <?php if ($ticket['status'] == 'open'): ?>

                <form method="POST" action="admin_tickets.php" style="display:inline;">
                    <input type="hidden" name="close_ticket" value="<?= $ticket['id'] ?>">
                    <button type="submit" class="btn btn-secondary">Close Ticket</button>
                </form>

            <?php else: ?>

                <form method="POST" action="admin_tickets.php" style="display:inline;">
                    <input type="hidden" name="open_ticket" value="<?= $ticket['id'] ?>">
                    <button type="submit" class="btn btn-warning">Reopen Ticket</button>
                </form>

            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'chatbot.php'; ?>
</body>
</html>