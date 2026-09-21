<?php
require 'db.php';

// Check that the password has the required length and includes both letters and numbers.
function validPassword($password) {
    if (strlen($password) < 8) return false;
    $hasLetter = false;
    $hasNumber = false;
    for ($i = 0; $i < strlen($password); $i++) {
        if (ctype_alpha($password[$i])) {
            $hasLetter = true;
        }
        if (ctype_digit($password[$i])) {
            $hasNumber = true;
        }
    }
    if ($hasLetter == true && $hasNumber == true) return true;
    return false;
}

// Check if a user already uses this email address.
function emailExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) return true;
    return false;
}

// Count how many users registered today to enforce the daily limit.
function getTodayRegistrations($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()");
    return $stmt->fetchColumn();
}

// Save the new user in the database with a user role by default.
function registerUser($pdo, $username, $email, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->execute([$username, $email, $hashed_password]);
}


$error = '';
$success = '';


// Validate the signup form and create a new account if everything is correct.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];


    // Check empty fields
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {

        $error = tr("Please fill in all fields.", "נא למלא את כל השדות.");

    } elseif ($password != $confirm_password) {

        $error = tr("Passwords do not match.", "הסיסמאות אינן תואמות.");

    } elseif (validPassword($password) == false) {

        $error = tr(
            "Password must be at least 8 characters and contain letters and numbers.",
            "הסיסמה חייבת להכיל לפחות 8 תווים, כולל אותיות ומספרים."
        );

    } else {
        $todayRegistrations = getTodayRegistrations($pdo);

        // Maximum 500 registrations per day
        if ($todayRegistrations >= 500) {

            $error = tr(
                "Daily registration limit reached. Please try again tomorrow.",
                "הגעת למגבלת ההרשמות היומית. נסה שוב מחר."
            );

        } elseif (emailExists($pdo, $email)) {

            $error = tr(
                "An account with that email already exists.",
                "כבר קיים חשבון עם כתובת האימייל הזאת."
            );

        } else {

            registerUser(
                $pdo,
                $username,
                $email,
                $password
            );

            $success = tr(
                "Registration successful. You can now log in.",
                "ההרשמה הושלמה בהצלחה. כעת ניתן להתחבר."
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
<html lang="<?= $page_language ?>" dir="<?= $page_direction ?>">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= tr('Sign Up - SafeBite', 'הרשמה - SafeBite') ?></title>

    <link rel="stylesheet" href="style.css">

</head>

<body style="min-height: 100vh;">


<?php include 'navbar.php'; ?>


<div class="main-container">

    <div class="register-card" >


        <div class="register-header">

            <h2><?= tr('Join SafeBite', 'הצטרפות ל-SafeBite') ?></h2>

            <p>
                <?= tr(
                    'Create an account to manage your dietary preferences.',
                    'צור חשבון כדי לנהל את ההעדפות התזונתיות שלך.'
                ) ?>
            </p>

        </div>


        <?php if ($error != ''): ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <?php if ($success != ''): ?>

            <div class="alert alert-success">

                <?= htmlspecialchars($success) ?>

                <br>

                <a href="login.php">
                    <?= tr('Login here', 'התחבר כאן') ?>
                </a>

            </div>


        <?php else: ?>


            <form method="POST">


                <div class="form-group">

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        placeholder="<?= tr('Full Name or Username', 'שם מלא או שם משתמש') ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="<?= tr('Email Address', 'כתובת אימייל') ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="<?= tr('Create Password', 'יצירת סיסמה') ?>"
                        required
                        minlength="8"
                    >

                </div>


                <div class="form-group">

                    <input
                        type="password"
                        name="confirm_password"
                        class="form-control"
                        placeholder="<?= tr('Confirm Password', 'אימות סיסמה') ?>"
                        required
                        minlength="8"
                    >

                </div>


                <button
                    type="submit"
                    class="btn-register"
                >
                    <?= tr('Create Account', 'צור חשבון') ?>
                </button>


            </form>


        <?php endif; ?>


        <div class="login-links">

            <span>
                <?= tr('Already have an account?', 'כבר יש לך חשבון?') ?>
            </span>

            <a href="login.php">
                <?= tr('Login here', 'התחבר כאן') ?>
            </a>

        </div>


    </div>

</div>
</body>
</html>