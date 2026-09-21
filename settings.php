<?php
require 'db.php';


// Load the current profile information for the logged-in user.
function getUserInfo($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}


// Save the updated username and email back to the database.
function updateUserProfile($pdo, $user_id, $username, $email) {
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->execute([$username, $email, $user_id]);
}


// The user must be logged in before they can edit account settings.
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION['user_id'];

$message = '';
$error = '';


// Update the user's profile when the settings form is submitted.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);


    if ($username == '' || $email == '') {

        $error = tr(
            'Please fill in all fields.',
            'נא למלא את כל השדות.'
        );

    } else {

        updateUserProfile(
            $pdo,
            $user_id,
            $username,
            $email
        );


        $_SESSION['username'] = $username;


        $message = tr(
            'Profile updated successfully.',
            'הפרופיל עודכן בהצלחה.'
        );
    }
}


// Get the current user information for the form values.
$user = getUserInfo($pdo, $user_id);


// Language direction
if (isset($_SESSION['lang']) && $_SESSION['lang'] == 'he') {

    $page_language = 'he';
    $page_direction = 'rtl';

} else {

    $page_language = 'en';
    $page_direction = 'ltr';

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
            'Settings - SafeBite',
            'הגדרות - SafeBite'
        ) ?>
    </title>

    <link rel="stylesheet" href="style.css">

</head>

<body style="min-height: 100vh;">


<?php include 'navbar.php'; ?>


<div
    class="container"
    style="
        max-width: 1200px;
        margin: 35px auto;
        padding: 0 25px;
    "
>


    <h2>
        <?= tr(
            'Account Settings',
            'הגדרות חשבון'
        ) ?>
    </h2>


    <?php if ($message != ''): ?>

        <div class="alert alert-success">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($error != ''): ?>

        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <div
        class="grid-2"
        style="gap: 30px;"
    >


        <!-- Personal Details -->

        <div
            class="card"
            style="
                padding: 40px;
                box-sizing: border-box;
            "
        >

            <h3>
                <?= tr(
                    'Personal Details',
                    'פרטים אישיים'
                ) ?>
            </h3>


            <form method="POST">


                <div class="form-group">

                    <label>
                        <?= tr(
                            'Username',
                            'שם משתמש'
                        ) ?>
                    </label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        value="<?= htmlspecialchars($user['username']) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        <?= tr(
                            'Email Address',
                            'כתובת אימייל'
                        ) ?>
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars($user['email']) ?>"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <?= tr(
                        'Save Changes',
                        'שמור שינויים'
                    ) ?>
                </button>


            </form>

        </div>


        <div>


            <!-- Security -->

            <div
                class="card"
                style="
                    padding: 40px;
                    box-sizing: border-box;
                "
            >

                <h3>
                    <?= tr(
                        'Security',
                        'אבטחה'
                    ) ?>
                </h3>


                <p>
                    <?= tr(
                        'You can change your password using the password reset system.',
                        'ניתן לשנות את הסיסמה דרך מערכת איפוס הסיסמה.'
                    ) ?>
                </p>


                <a
                    href="forgotpass.php"
                    class="btn btn-secondary"
                >
                    <?= tr(
                        'Change Password',
                        'שינוי סיסמה'
                    ) ?>
                </a>

            </div>


            <br>


            <!-- Allergies -->

            <div
                class="card"
                style="
                    padding: 40px;
                    box-sizing: border-box;
                "
            >

                <h3>
                    <?= tr(
                        'Dietary Preferences',
                        'העדפות תזונתיות'
                    ) ?>
                </h3>


                <p>
                    <?= tr(
                        'Manage your allergies so SafeBite can check products for you.',
                        'נהל את האלרגיות שלך כדי ש-SafeBite יוכל לבדוק עבורך מוצרים.'
                    ) ?>
                </p>

                  <!-- here we edit allergies as a user, so we link to edit_allergies.php -->
                <a
               
                    href="edit_allergies.php"
                    class="btn btn-secondary"
                >
                    <?= tr(
                        'Manage Allergies',
                        'ניהול אלרגיות'
                    ) ?>
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>