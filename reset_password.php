<?php
require 'db.php';

// Validate the new password against the app's strength rules.
function validPassword($password) {
    if (strlen($password) < 8) return false;
    $hasLetter = false;
    $hasNumber = false;
    for ($i = 0; $i < strlen($password); $i++) {
        $char = $password[$i];
        if (($char >= 'a' && $char <= 'z') || ($char >= 'A' && $char <= 'Z')) {
            $hasLetter = true;
        }
        if ($char >= '0' && $char <= '9') {
            $hasNumber = true;
        }
    }
    if ($hasLetter == true && $hasNumber == true) {
        return true;
    }
    return false;
}

$message = '';

// Only allow the password reset page if the OTP step already approved the user.
if (!isset($_SESSION['allow_password_reset']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgotpass.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];

    // Make sure both entered passwords match before updating the account.
    if ($new_password != $confirm_password) {
        $message = "Passwords do not match.";
    } else if (validPassword($new_password) == false) {
        $message = "Password must be at least 8 characters and contain a letter and a number.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_otp = NULL, otp_expiry = NULL WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);

        $_SESSION['reset_email'] = null;
        $_SESSION['allow_password_reset'] = null;

        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SafeBite</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="main-container">
    <div class="login-card">
        <div class="login-header">
            <h2>SafeBite</h2>
            <p>Create a new secure password.</p>
        </div>
        <?php if ($message != ''): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form method="POST">

            <div class="form-group">
                <input type="password" name="new_password" class="form-control"
                       placeholder="New Password" minlength="8" required>
            </div>

            <div class="form-group">
                <input type="password" name="confirm_password" class="form-control"
                       placeholder="Confirm New Password" minlength="8" required>
            </div>

            <button type="submit" class="btn-login">Update Password</button>

        </form>
    </div>
</div>
</body>
</html>