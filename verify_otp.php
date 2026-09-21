<?php
require 'db.php';
$message = '';

// This page can only run after the user requested a password reset email.
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgotpass.php");
    exit();
}

// Check the code entered by the user against the saved reset OTP.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = trim($_POST['otp']);
    $email = $_SESSION['reset_email'];
    $stmt = $pdo->prepare("SELECT reset_otp, otp_expiry FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $current_time = date("Y-m-d H:i:s");
        if ($user['reset_otp'] == $entered_otp) {

            if ($current_time <= $user['otp_expiry']) {
                $_SESSION['allow_password_reset'] = true;
                header("Location: reset_password.php");
                exit();
            } else {
                $message = "This code has expired. Please request a new one.";
            }
        } else {
            $message = "Invalid verification code. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - SafeBite</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="main-container">
    <div class="login-card">

        <div class="login-header">
            <h2>SafeBite</h2>
            <p>
                We sent a 6-digit code to
                <b><?= htmlspecialchars($_SESSION['reset_email']) ?></b>.
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
                    type="text"
                    name="otp"
                    class="form-control"
                    placeholder="Enter 6-digit OTP"
                    maxlength="6"
                    required
                >
            </div>
            <button type="submit" class="btn-login">
                Verify Code
            </button>
        </form>
        <div class="login-links">
            <a href="forgotpass.php">Resend Code</a>
        </div>
    </div>
</div>
</body>
</html>