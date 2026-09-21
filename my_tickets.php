<?php
require 'db.php';

// Load all tickets created by the logged-in user.
function getUserTickets($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Convert the raw ticket status into a simple readable label.
function getTicketStatus($status) {
    if ($status == 'open') return tr('Open', 'פתוחה');
    if ($status == 'closed') return tr('Closed', 'סגורה');
    return $status;
}
// Only logged-in users can view the tickets they sent.
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$tickets = getUserTickets($pdo, $_SESSION['user_id']);

$page_language = 'en';
$page_direction = 'ltr';
if (isset($_SESSION['lang']) &&$_SESSION['lang'] == 'he') {
    $page_language = 'he';
    $page_direction = 'rtl';
}
?>

<!DOCTYPE html>
<html lang="<?= $page_language ?>" dir="<?= $page_direction ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= tr('My Tickets - SafeBite', 'הפניות שלי - SafeBite') ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="min-height: 100vh;">
<?php include 'navbar.php'; ?>
<div class="page-container" style="max-width:1200px; margin:35px auto; padding:0 25px;">
    <div class="page-header">
        <h1><?= tr('My Tickets', 'הפניות שלי') ?></h1>
        <p><?= tr(
            'Review your support and Work With Us requests.',
            'צפה בפניות התמיכה ובבקשות העבודה שלך.'
        ) ?></p>
    </div>
    <div class="admin-card" style="padding:30px; margin-top:25px;">
        <?php if (count($tickets) == 0): ?>
            <div class="empty-state">
                <p><?= tr('You have not sent any tickets yet.', 'עדיין לא שלחת פניות.') ?></p>

                <a href="contact.php" class="btn btn-primary">
                    <?= tr('Send a Ticket', 'שלח פנייה') ?>
                </a>
            </div>
        <?php else: ?>
            <table>
                <tr>
                    <th><?= tr('ID', 'מספר') ?></th>
                    <th><?= tr('Subject', 'נושא') ?></th>
                    <th><?= tr('Status', 'סטטוס') ?></th>
                    <th><?= tr('Date', 'תאריך') ?></th>
                    <th><?= tr('Message', 'הודעה') ?></th>
                </tr>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>#<?= $ticket['id'] ?></td>

                        <td>
                            <?= htmlspecialchars($ticket['subject']) ?>
                        </td>

                        <td>
                            <span class="badge">
                                <?= htmlspecialchars(getTicketStatus($ticket['status'])) ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($ticket['created_at']) ?>
                        </td>

                        <td>
                            <?= nl2br(htmlspecialchars($ticket['message'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <br>
            <a href="contact.php" class="btn btn-primary">
                <?= tr('Send New Ticket', 'שלח פנייה חדשה') ?>
            </a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>