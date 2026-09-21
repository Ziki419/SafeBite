<?php
require 'db.php';

// Replace the user's saved allergies with the new choices from the form.
function saveUserAllergies($pdo, $user_id, $selected_allergies){
    $stmt = $pdo->prepare(
        "DELETE FROM user_allergies
         WHERE user_id = ?"
    );
    $stmt->execute([$user_id]);
    $stmt = $pdo->prepare(
        "INSERT INTO user_allergies
         (user_id, allergy_id)
         VALUES (?, ?)"
    );
    foreach ($selected_allergies as $allergy_id) {
        $stmt->execute([$user_id,$allergy_id]);
    }
}

// Load all allergy options so the user can select their preferences.
function getAllAllergies($pdo){
    $stmt = $pdo->query("SELECT id, name FROM allergies");
    return $stmt->fetchAll();
}

// Get the current user's saved allergy IDs from the database.
function getUserAllergies($pdo, $user_id){
    $stmt = $pdo->prepare(
        "SELECT allergy_id
         FROM user_allergies
         WHERE user_id = ?"
    );
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll();
    $user_allergies = array();

    foreach ($rows as $row) {
        $user_allergies[] = $row['allergy_id'];
    }
    return $user_allergies;
}

// Check whether a checkbox should be marked as selected for the current user.
function allergyIsSelected($allergy_id, $user_allergies){
    foreach ($user_allergies as $saved_allergy_id) {
        if ($allergy_id == $saved_allergy_id) {
            return true;
        }
    }
    return false;
}

// The user must be logged in before changing dietary preferences.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Save allergy choices
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_allergies = array();
    if (isset($_POST['allergies'])) {
        $selected_allergies = $_POST['allergies'];
    }
    saveUserAllergies($pdo,$user_id,$selected_allergies);
    $message = "Dietary preferences updated successfully!";
    $message_type = "success";
}
$all_allergies = getAllAllergies($pdo);
$current_user_allergies = getUserAllergies($pdo,$user_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Allergies - SafeBite</title>

    <link rel="stylesheet" href="style.css">

</head>

<body style="min-height: 100vh;">

<?php include 'navbar.php'; ?>

<div
    class="container"
    style="max-width: 600px; margin-top: 5vh;"
>

    <h2
        style="
            color: var(--green-dark);
            margin-bottom: 8px;
        "
    >
        Dietary Preferences
    </h2>


    <p
        style="
            color: var(--muted);
            margin-bottom: 24px;
        "
    >
        Select your allergies.
        The SafeBite engine will highlight
        products that are dangerous for you.
    </p>


    <?php if ($message != ''): ?>

        <?php if ($message_type == 'success'): ?>

            <div class="alert alert-success">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php else: ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>

    <?php endif; ?>


    <div
        class="card"
        style="padding: 25px;"
    >

        <form method="POST">


            <div
                class="lifestyle-filters"
                style="margin-bottom: 25px;"
            >

                <?php foreach ($all_allergies as $allergy): ?>

                    <?php

                    $is_checked = '';

                    if (
                        allergyIsSelected(
                            $allergy['id'],
                            $current_user_allergies
                        )
                    ) {
                        $is_checked = 'checked';
                    }

                    ?>

                    <label class="diet-pill">

                        <input
                            type="checkbox"
                            name="allergies[]"
                            value="<?= $allergy['id'] ?>"
                            <?= $is_checked ?>
                        >

                        <span class="pill-label">

                            <?= htmlspecialchars(
                                $allergy['name']
                            ) ?>

                        </span>

                    </label>

                <?php endforeach; ?>

            </div>


            <div
                style="
                    display: flex;
                    gap: 15px;
                "
            >

                <button
                    type="submit"
                    class="btn btn-primary"
                    style="flex: 1;"
                >
                    Save Preferences
                </button>


                <a
                    href="settings.php"
                    class="btn btn-secondary"
                    style="
                        flex: 1;
                        text-align: center;
                        line-height: 22px;
                    "
                >
                    Back to Settings
                </a>
            </div>
        </form>
    </div>
</div>
</body>
</html>