<?php
require 'db.php';

// Update the ticket status when an admin closes or reopens a request.
function changeTicketStatus($pdo, $ticket_id, $status) {
    $stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
    $stmt->execute([$status, $ticket_id]);
}

function deleteTicket($pdo, $ticket_id) {
    $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
}

// Only the admin can open the tickets page and manage support requests.
if (!isLoggedIn() || !isAdmin()) {
    header("Location: index.php");
    exit();
}

$message = '';

// Handle admin actions such as closing, reopening, or deleting a ticket.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['close_ticket'])) {
        changeTicketStatus($pdo, $_POST['close_ticket'], 'closed');
        $message = 'Ticket closed successfully.';
    }

    if (isset($_POST['open_ticket'])) {
        changeTicketStatus($pdo, $_POST['open_ticket'], 'open');
        $message = 'Ticket reopened successfully.';
    }

    if (isset($_POST['delete_ticket'])) {
        deleteTicket($pdo, $_POST['delete_ticket']);
        $message = 'Ticket deleted successfully.';
    }
}

// Load all tickets with the user information so the admin can review each request.
$tickets = $pdo->query("SELECT tickets.*, users.username, users.email FROM tickets JOIN users ON users.id = tickets.user_id ORDER BY tickets.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tickets - SafeBite</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-theme">
<?php include 'navbar.php'; ?>

<div class="admin-container">

    <div class="admin-header">
        <div>
            <h1>Manage Tickets</h1>
            <p>Review support and Work With Us requests.</p>
        </div>
    </div>

    <?php if ($message != ''): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="admin-card" style="padding: 30px; box-sizing: border-box;">

        <?php if (count($tickets) == 0): ?>

            <div class="empty-state">No tickets found yet.</div>

        <?php else: ?>

            <table>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>View</th>
                    <th>Actions</th>
                </tr>

                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>#<?= $ticket['id'] ?></td>

                        <td>
                            <strong><?= htmlspecialchars($ticket['username']) ?></strong><br>
                            <span class="small-text"><?= htmlspecialchars($ticket['email']) ?></span>
                        </td>

                        <td><?= htmlspecialchars($ticket['subject']) ?></td>

                        <td>
                            <span class="badge">
                                <?= htmlspecialchars($ticket['status']) ?>
                            </span>
                        </td>

                        <td><?= htmlspecialchars($ticket['created_at']) ?></td>

                        <td>
                            <a href="admin.ticketView.php?id=<?= $ticket['id'] ?>" class="btn btn-primary">
                                Open
                            </a>
                        </td>

                        <td>
                            <?php if ($ticket['status'] == 'open'): ?>
                                <form method="POST">
                                    <input type="hidden" name="close_ticket" value="<?= $ticket['id'] ?>">
                                    <button type="submit" class="btn btn-secondary">Close</button>
                                </form>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="open_ticket" value="<?= $ticket['id'] ?>">
                                    <button type="submit" class="btn btn-warning">Reopen</button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" onsubmit="return confirm('Delete this ticket?');">
                                <input type="hidden" name="delete_ticket" value="<?= $ticket['id'] ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

        <?php endif; ?>
    </div>
</div>
</body>
</html>