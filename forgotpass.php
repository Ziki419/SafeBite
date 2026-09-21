<?php
require 'db.php';
require 'mailer.php';

// Find the user by email before creating a password reset code.
function getUserByEmail($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// Store the OTP and expiration time so the user can verify the password reset.
function saveResetCode($pdo, $user_id, $otp, $expiry) {
    $stmt = $pdo->prepare("UPDATE users SET reset_otp = ?, otp_expiry = ? WHERE id = ?");
    $stmt->execute([$otp, $expiry, $user_id]);
}

$message = '';

// The user submitted the email form to request a password reset code.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = '';

    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    }

    if ($email == '') {

        $message = tr(
            'Please enter your email address.',
            'נא להזין כתובת אימייל.'
        );

    } else {

        $user = getUserByEmail($pdo, $email);


        // If the email matches a user account, create and send the OTP.
        if ($user) {
            // Create a random 6-digit OTP for account verification.
            $otp = random_int(100000, 999999);

            // Make the reset code expire after 15 minutes.
            $expiry = date(
                "Y-m-d H:i:s",
                time() + 900
            );

            // Save the reset code in the database for later validation.
            saveResetCode(
                $pdo,
                $user['id'],
                $otp,
                $expiry
            );

            // Email information
            $subject = "SafeBite - Password Reset Code";

            $email_body =
                "Your password reset code is: "
                . $otp
                . "\n\nThis code will expire in 15 minutes.";

            if (sendSafeBiteEmail($email, $subject, $email_body)) {
                $_SESSION['reset_email'] = $email;
                header("Location: verify_otp.php");
                exit();

            } else {
                $message = tr(
                    'Failed to send the email. Please try again.',
                    'שליחת האימייל נכשלה. נא לנסות שוב.'
                );
            }
        }

        // User not found
        else {
            $message = tr(
                "We couldn't find an account with that email.",
                'לא נמצא חשבון עם כתובת האימייל הזאת.'
            );

        }
    }
}

$page_language = 'en';
$page_direction = 'ltr';
if (isset($_SESSION['lang']) &&$_SESSION['lang'] == 'he') {
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
            'Reset Password - SafeBite',
            'איפוס סיסמה - SafeBite'
        ) ?>
    </title>

    <link rel="stylesheet" href="style.css">

</head>

<body style="min-height: 100vh;">


<?php include 'navbar.php'; ?>


<main class="main-container">

<div class="login-card" style="min-height: 500px; max-width: 500px; box-sizing: border-box;">

        <div class="login-header">

            <h2>
                SafeBite
            </h2>

            <p>
                <?= tr(
                    'Enter your email to receive a reset code.',
                    'הזן את האימייל שלך כדי לקבל קוד לאיפוס הסיסמה.'
                ) ?>
            </p>

        </div>


        <?php if ($message != ''): ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="<?= tr(
                        'Email Address',
                        'כתובת אימייל'
                    ) ?>"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn-login"
            >
                <?= tr(
                    'Send Code',
                    'שלח קוד'
                ) ?>
            </button>


        </form>


        <div class="login-links">

            <span>
                <?= tr(
                    'Remembered your password?',
                    'נזכרת בסיסמה?'
                ) ?>
            </span>

            <a href="login.php">
                <?= tr(
                    'Login here',
                    'התחבר כאן'
                ) ?>
            </a>


        </div>
    </div>
</main>
</body>
</html>