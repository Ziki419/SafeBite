<?php
require 'db.php';

// Find the user account linked to the email entered on the login form.
function getUserByEmail($pdo, $email) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// Clear failed login attempts after a successful sign in.
function resetLoginAttempts($pdo, $user_id) {
    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
}

// Save the number of failed password attempts for the account.
function saveFailedAttempt($pdo, $user_id, $failed_attempts) {
    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
    $stmt->execute([$failed_attempts, $user_id]);
}

// Lock the account temporarily when the user reaches the failed-login limit.
function lockAccount($pdo, $user_id, $failed_attempts) {
    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?");
    $stmt->execute([$failed_attempts, $user_id]);
}

$error = '';
$email = '';


// Handle the login form and redirect each user role to the correct page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = '';
    $password = '';

    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    }

    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    }


    // Check empty fields
    if ($email == '' || $password == '') {

        $error = tr(
            'Please enter your email and password.',
            'נא להזין אימייל וסיסמה.'
        );

    } else {

        $user = getUserByEmail($pdo, $email);


        // User not found
        if (!$user) {

            $error = tr(
                'Incorrect email or password.',
                'האימייל או הסיסמה שגויים.'
            );

        }


        // Account is locked
        elseif (
            $user['locked_until'] != null &&
            strtotime($user['locked_until']) > time()
        ) {

            $error = tr(
                'Your account is temporarily locked. Please try again later.',
                'החשבון נעול זמנית. נא לנסות שוב מאוחר יותר.'
            );

        }


        // Correct password
        elseif (password_verify($password, $user['password'])) {

            resetLoginAttempts($pdo, $user['id']);


            // Save user information in Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];


            // Admin
            if ($user['role'] == 'admin') {

                header("Location: admin.php");
                exit();

            }


            // Delivery
            elseif ($user['role'] == 'delivery') {

                $_SESSION['delivery_region'] = $user['delivery_region'];

                header("Location: delivery_dashboard.php");
                exit();

            }


            // Normal user
            else {

                header("Location: index.php");
                exit();

            }

        }


        // Wrong password
        else {

            $failed_attempts = $user['failed_attempts'] + 1;


            // Lock after 5 failed attempts
            if ($failed_attempts >= 5) {

                lockAccount(
                    $pdo,
                    $user['id'],
                    $failed_attempts
                );


                $error = tr(
                    'Your account was locked after 5 failed attempts. Try again in 15 minutes.',
                    'החשבון ננעל לאחר 5 ניסיונות שגויים. ניתן לנסות שוב בעוד 15 דקות.'
                );

            }


            // Less than 5 attempts
            else {
                saveFailedAttempt(
                    $pdo,
                    $user['id'],
                    $failed_attempts
                );
                $attempts_left = 5 - $failed_attempts;

                $error = tr(
                    'Incorrect email or password. Attempts remaining: ' . $attempts_left,
                    'האימייל או הסיסמה שגויים. מספר הניסיונות שנותרו: ' . $attempts_left
                );

            }
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
        <?= tr('Login - SafeBite', 'התחברות - SafeBite') ?>
    </title>

    <link rel="stylesheet" href="style.css">

</head>


<body style="min-height: 100vh;">


<?php include 'navbar.php'; ?>


<main class="main-container">
    <div class="login-card" style="min-height: 550px;">


        <div class="login-header">

            <h2>
                SafeBite
            </h2>


            <p>
                <?= tr(
                    'Welcome back! Please log in.',
                    'ברוכים הבאים! נא להתחבר.'
                ) ?>
            </p>

        </div>


        <?php if ($error != ''): ?>

            <div class="alert alert-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="<?= tr('Email Address', 'כתובת אימייל') ?>"
                    value="<?= htmlspecialchars($email) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="<?= tr('Password', 'סיסמה') ?>"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn-login"
            >
                <?= tr('Login', 'התחברות') ?>
            </button>


        </form>


        <div class="login-links">


            <a href="forgotpass.php">

                <?= tr(
                    'Forgot Password?',
                    'שכחת את הסיסמה?'
                ) ?>

            </a>


            <br><br>


            <span>

                <?= tr(
                    "Don't have an account?",
                    'אין לך חשבון?'
                ) ?>

            </span>


            <a href="register.php">

                <?= tr(
                    'Sign Up',
                    'הרשמה'
                ) ?>

            </a>
        </div>
    </div>
    <div class="blank-space" aria-hidden="true"></div>
</main>
</body>
</html>