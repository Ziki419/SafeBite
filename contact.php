<?php
require 'db.php';

// Save a new support or work request into the tickets table.
function createTicket($pdo, $user_id, $subject, $message) {
    $stmt = $pdo->prepare("INSERT INTO tickets (user_id, subject, message, status) VALUES (?, ?, ?, 'open')");
    $stmt->execute([$user_id, $subject, $message]);
}

$success = '';
$error = '';

// Process the contact form and build the right subject for each ticket type.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isLoggedIn()) {
        $error = tr('Please login first before sending a ticket.', 'יש להתחבר לפני שליחת פנייה.');
    } else {
        $ticket_type = '';
        $subject = '';
        $ticket_message = '';
        $delivery_region = '';

        if (isset($_POST['ticket_type'])) {
            $ticket_type = $_POST['ticket_type'];
        }

        if (isset($_POST['subject'])) {
            $subject = trim($_POST['subject']);
        }

        if (isset($_POST['message'])) {
            $ticket_message = trim($_POST['message']);
        }

        if (isset($_POST['delivery_region'])) {
            $delivery_region = $_POST['delivery_region'];
        }
        if ($subject == '' || $ticket_message == '') {

            $error = tr(
                'Subject and message are required.',
                'יש למלא נושא והודעה.'
            );

        }
        elseif (
            $ticket_type != 'support' &&
            $ticket_type != 'delivery' &&
            $ticket_type != 'admin'
        ) {

            $error = tr(
                'Please choose a valid ticket type.',
                'נא לבחור סוג פנייה תקין.'
            );

        }

        // Admin and delivery users do not need to apply for another role
        elseif (($ticket_type == 'delivery' || $ticket_type == 'admin') && (isAdmin() || isDelivery())) {
            $error = tr(
                'Work With Us requests are only available for regular users.',
                'בקשות הצטרפות לצוות זמינות רק למשתמשים רגילים.'
            );
        }
        // Delivery request must include a region
        elseif ($ticket_type == 'delivery' && $delivery_region == '') {
            $error = tr(
                'Please choose your delivery region.',
                'נא לבחור אזור משלוחים.'
            );
        } else {  
            if ($ticket_type == 'support') {
                $subject = 'Support: ' . $subject;
            }
            elseif ($ticket_type == 'delivery') {
                $subject = 'Work With Us - Delivery Driver: ' . $subject;
                $ticket_message =
                    "Requested delivery region: "
                    . $delivery_region
                    . "\n\n"
                    . $ticket_message;
            }
            elseif ($ticket_type == 'admin') {
                $subject = 'Work With Us - Admin: ' . $subject;
            }
            createTicket($pdo,$_SESSION['user_id'],$subject,$ticket_message);
            $success = tr(
                'Your ticket was sent successfully.',
                'הפנייה נשלחה בהצלחה.'
            );
        }
    }
}

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
        <?= tr(
            'Contact Us - SafeBite',
            'צור קשר - SafeBite'
        ) ?>
    </title>

    <link rel="stylesheet" href="style.css">

</head>

<body>


<?php include 'navbar.php'; ?>


<div class="contact-page">


    <div class="contact-shape-1"></div>
    <div class="contact-shape-2"></div>
    <div class="contact-shape-3"></div>


    <div class="contact-wrap">


        <div class="contact-hero">

            <h1>
                <?= tr(
                    'Contact SafeBite',
                    'צור קשר עם SafeBite'
                ) ?>
            </h1>

            <p>
                <?= tr(
                    'Need help or want to join the SafeBite team? Send us a ticket and an admin will review it.',
                    'צריך עזרה או רוצה להצטרף לצוות SafeBite? שלח פנייה ומנהל יבדוק אותה.'
                ) ?>
            </p>

        </div>


        <div class="contact-grid">


            <!-- Ticket Form -->

            <div class="contact-card">


                <div class="contact-mini-badge">
                    <?= tr(
                        'Ticket Form',
                        'טופס פנייה'
                    ) ?>
                </div>


                <h2>
                    <?= tr(
                        'Send us a message',
                        'שלח לנו הודעה'
                    ) ?>
                </h2>


                <?php if ($success != ''): ?>

                    <div class="contact-alert contact-alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>

                <?php endif; ?>


                <?php if ($error != ''): ?>

                    <div class="contact-alert contact-alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>

                <?php endif; ?>


                <?php if (isLoggedIn()): ?>


                    <form method="POST">


                        <!-- Ticket Type -->

                        <div class="form-group">

                            <label>
                                <?= tr(
                                    'Ticket Type',
                                    'סוג פנייה'
                                ) ?>
                            </label>


                            <select
                                name="ticket_type"
                                class="form-control"
                                required
                            >

                                <option value="support">
                                    <?= tr(
                                        'Support',
                                        'תמיכה'
                                    ) ?>
                                </option>


                                <?php if (!isAdmin() && !isDelivery()): ?>

                                    <option value="delivery">
                                        <?= tr(
                                            'Work With Us - Delivery Driver',
                                            'עבודה איתנו - שליח'
                                        ) ?>
                                    </option>

                                    <option value="admin">
                                        <?= tr(
                                            'Work With Us - Admin',
                                            'עבודה איתנו - מנהל'
                                        ) ?>
                                    </option>

                                <?php endif; ?>

                            </select>

                        </div>
                        <div class="form-group">

                            <label>
                                <?= tr(
                                    'Subject',
                                    'נושא'
                                ) ?>
                            </label>

                            <input
                                type="text"
                                name="subject"
                                class="form-control"
                                placeholder="<?= tr(
                                    'Write a short subject',
                                    'כתוב נושא קצר'
                                ) ?>"
                                required
                            >

                        </div>
                        <?php if (!isAdmin() && !isDelivery()): ?>

                            <div class="form-group">

                                <label>
                                    <?= tr(
                                        'Delivery Region',
                                        'אזור משלוחים'
                                    ) ?>
                                </label>


                                <select
                                    name="delivery_region"
                                    class="form-control"
                                >

                                    <option value="">
                                        <?= tr(
                                            'Only choose this if applying as a delivery driver',
                                            'בחר רק אם אתה מגיש בקשה להיות שליח'
                                        ) ?>
                                    </option>

                                    <option value="north">
                                        <?= tr(
                                            'North',
                                            'צפון'
                                        ) ?>
                                    </option>

                                    <option value="haifa">
                                        <?= tr(
                                            'Haifa',
                                            'חיפה'
                                        ) ?>
                                    </option>

                                    <option value="center">
                                        <?= tr(
                                            'Center',
                                            'מרכז'
                                        ) ?>
                                    </option>

                                    <option value="jerusalem">
                                        <?= tr(
                                            'Jerusalem',
                                            'ירושלים'
                                        ) ?>
                                    </option>

                                    <option value="south">
                                        <?= tr(
                                            'South',
                                            'דרום'
                                        ) ?>
                                    </option>

                                </select>

                            </div>

                        <?php endif; ?>


                        <!-- Message -->

                        <div class="form-group">

                            <label>
                                <?= tr(
                                    'Message',
                                    'הודעה'
                                ) ?>
                            </label>

                            <textarea
                                name="message"
                                class="form-control"
                                placeholder="<?= tr(
                                    'Write your message here...',
                                    'כתוב את ההודעה שלך כאן...'
                                ) ?>"
                                required
                            ></textarea>

                        </div>


                        <button
                            type="submit"
                            class="contact-btn"
                        >
                            <?= tr(
                                'Send Ticket',
                                'שלח פנייה'
                            ) ?>
                        </button>


                    </form>


                <?php else: ?>


                    <div class="contact-alert contact-alert-error">

                        <?= tr(
                            'Please login first to send a support ticket.',
                            'יש להתחבר כדי לשלוח פנייה.'
                        ) ?>

                    </div>


                    <a
                        href="login.php"
                        class="btn btn-primary"
                    >
                        <?= tr(
                            'Login',
                            'התחברות'
                        ) ?>
                    </a>


                <?php endif; ?>


            </div>
            <div class="contact-side-card">


                <div class="contact-mini-badge">
                    <?= tr(
                        'How can we help?',
                        'איך אפשר לעזור?'
                    ) ?>
                </div>


                <h3>
                    <?= tr(
                        "We're here to help",
                        'אנחנו כאן כדי לעזור'
                    ) ?>
                </h3>


                <div class="contact-info-list">


                    <div class="contact-info-item">

                        <strong>
                            <?= tr(
                                'Allergy Support',
                                'עזרה בנושא אלרגיות'
                            ) ?>
                        </strong>

                        <?= tr(
                            'Ask about allergy filtering or your allergy profile.',
                            'שאל על סינון אלרגיות או על פרופיל האלרגיות שלך.'
                        ) ?>

                    </div>


                    <div class="contact-info-item">

                        <strong>
                            <?= tr(
                                'Order Questions',
                                'שאלות על הזמנות'
                            ) ?>
                        </strong>

                        <?= tr(
                            'Ask about orders, delivery or payment problems.',
                            'שאל על הזמנות, משלוחים או בעיות תשלום.'
                        ) ?>

                    </div>


                    <div class="contact-info-item">

                        <strong>
                            <?= tr(
                                'Work With Us',
                                'עבודה איתנו'
                            ) ?>
                        </strong>

                        <?= tr(
                            'Regular users can request to become a delivery driver or an admin.',
                            'משתמש רגיל יכול לשלוח בקשה להיות שליח או מנהל.'
                        ) ?>

                    </div>


                </div>

            </div>


        </div>

    </div>

</div>
<?php include 'chatbot.php'; ?>
</body>
</html>