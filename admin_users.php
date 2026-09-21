<?php
require 'db.php';

// Check whether the selected role is one of the allowed system roles.
function validRole($role) {
    if ($role == 'user') return true;
    if ($role == 'admin') return true;
    if ($role == 'delivery') return true;
    return false;
}

function validRegion($region) {
    if ($region == 'north') return true;
    if ($region == 'haifa') return true;
    if ($region == 'center') return true;
    if ($region == 'jerusalem') return true;
    if ($region == 'south') return true;
    return false;
}

function emailExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) return true;
    return false;
}

function userHasOrders($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? OR delivery_user_id = ?");
    $stmt->execute([$user_id, $user_id]);
    $order = $stmt->fetch();

    if ($order) return true;
    return false;
}

function userHasTickets($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $ticket = $stmt->fetch();

    if ($ticket) return true;
    return false;
}

function userHasReplies($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM ticket_replies WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $reply = $stmt->fetch();

    if ($reply) return true;
    return false;
}

// Only administrators can manage users and roles.
if (!isLoggedIn() || !isAdmin()) {
    header("Location: index.php");
    exit();
}

$is_admin_theme = true;

$message = '';
$message_type = 'success';

$regions = deliveryRegions();


// Add a new user account when the admin submits the create-user form.
if (isset($_POST['add_user'])) {

    $username = '';
    $email = '';
    $password = '';
    $role = 'user';
    $delivery_region = '';

    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);
    }

    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    }

    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    }

    if (isset($_POST['role'])) {
        $role = $_POST['role'];
    }

    if (isset($_POST['delivery_region'])) {
        $delivery_region = $_POST['delivery_region'];
    }


    if ($username == '' || $email == '' || $password == '') {
        $message = 'All fields are required.';
        $message_type = 'error';
    }

    else if (strlen($password) < 6) {
        $message = 'Password must contain at least 6 characters.';
        $message_type = 'error';
    }

    else if (validRole($role) == false) {
        $message = 'Invalid role.';
        $message_type = 'error';
    }

    else if ($role == 'delivery' && validRegion($delivery_region) == false) {
        $message = 'Choose a work region for the delivery account.';
        $message_type = 'error';
    }

    else if (emailExists($pdo, $email) == true) {
        $message = 'Email already exists.';
        $message_type = 'error';
    }

    else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $saved_region = null;

        if ($role == 'delivery') {
            $saved_region = $delivery_region;
        }

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, delivery_region) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password, $role, $saved_region]);

        if ($role == 'delivery') {
            $message = 'Delivery account added successfully.';
        } else {
            $message = 'User added successfully.';
        }
    }
}


// Change a user's role and delivery region if the admin updates their account.
if (isset($_POST['change_role'])) {

    $user_id = 0;
    $role = 'user';
    $delivery_region = '';

    if (isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
    }

    if (isset($_POST['role'])) {
        $role = $_POST['role'];
    }

    if (isset($_POST['delivery_region'])) {
        $delivery_region = $_POST['delivery_region'];
    }

    if ($user_id == $_SESSION['user_id']) {
        $message = 'You cannot change the role of the account you are currently using.';
        $message_type = 'error';
    }

    else if (validRole($role) == false) {
        $message = 'Invalid role.';
        $message_type = 'error';
    }

    else if ($role == 'delivery' && validRegion($delivery_region) == false) {
        $message = 'Choose a work region for the delivery account.';
        $message_type = 'error';
    }

    else {

        $saved_region = null;

        if ($role == 'delivery') {
            $saved_region = $delivery_region;
        }

        $stmt = $pdo->prepare("UPDATE users SET role = ?, delivery_region = ? WHERE id = ?");
        $stmt->execute([$role, $saved_region, $user_id]);

        $message = 'Role and work region updated successfully.';
    }
}


// Delete a user only after checking that the account is safe to remove.
if (isset($_POST['delete_user'])) {

    $user_id = (int)$_POST['delete_user'];

    if ($user_id == $_SESSION['user_id']) {
        $message = 'You cannot delete your own account.';
        $message_type = 'error';
    }

    else if (userHasOrders($pdo, $user_id) == true) {
        $message = 'This user cannot be deleted because the account has order history.';
        $message_type = 'error';
    }

    else if (userHasTickets($pdo, $user_id) == true) {
        $message = 'This user cannot be deleted because the account has tickets.';
        $message_type = 'error';
    }

    else if (userHasReplies($pdo, $user_id) == true) {
        $message = 'This user cannot be deleted because the account has ticket replies.';
        $message_type = 'error';
    }

    else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        $message = 'User deleted successfully.';
    }
}


// Load all users to show the full admin user list.
$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();


$message_class = 'alert-success';

if ($message_type == 'error') {
    $message_class = 'alert-error';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Users - SafeBite</title>

    <link rel="stylesheet" href="style.css">

    <style>
        .users-page-wrap {
            max-width: 1250px;
            margin: 35px auto;
            padding: 0 20px 40px;
        }

        .users-panel {
            background: white;
            border-radius: 22px;
            padding: 26px;
            box-shadow: 0 14px 30px rgba(0,0,0,0.06);
            border: 1px solid #e4eee5;
        }

        .users-note {
            background: #eef8ef;
            border: 1px solid #cfe8d2;
            border-radius: 12px;
            padding: 13px 15px;
            margin-bottom: 20px;
            color: #245b31;
            line-height: 1.5;
        }

        .users-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .role-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 8px;
            align-items: center;
            margin-bottom: 8px;
        }

        @media (max-width: 800px) {
            .users-page-wrap {
                padding: 0 12px 30px;
            }

            .users-panel {
                padding: 18px;
            }

            .role-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="admin-theme">

<?php include 'navbar.php'; ?>

<div class="users-page-wrap">

    <div class="admin-header">
        <h1>Manage Users</h1>
        <p>Create users, admins and delivery accounts.</p>
    </div>


    <?php if ($message != ''): ?>

        <div class="alert <?= $message_class ?>">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <div class="grid-2">

        <div class="users-panel">

            <h2>Add New User</h2>

            <div class="users-note">
                If the role is delivery, choose a delivery work region.
            </div>

            <form method="POST">

                <input type="hidden" name="add_user" value="1">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" minlength="6" required>
                </div>

                <div class="form-group">

                    <label>Role</label>

                    <select name="role" class="form-control" required>
                        <option value="user">user</option>
                        <option value="admin">admin</option>
                        <option value="delivery">delivery</option>
                    </select>

                </div>

                <div class="form-group">

                    <label>Delivery Work Region</label>

                    <select name="delivery_region" class="form-control">

                        <option value="">Choose region</option>

                        <?php foreach ($regions as $code => $labels): ?>

                            <option value="<?= htmlspecialchars($code) ?>">
                                <?= htmlspecialchars($labels['en']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <span class="small-text">
                        Used only for delivery accounts.
                    </span>

                </div>

                <button type="submit" class="btn btn-primary">
                    Add User
                </button>

            </form>

        </div>


        <div class="users-panel">

            <h2>All Users</h2>

            <div class="users-table-wrap">

                <table>

                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Role / Region</th>
                        <th>Actions</th>
                    </tr>


                    <?php foreach ($users as $user): ?>

                        <tr>

                            <td>
                                <?= $user['id'] ?>
                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars($user['username']) ?>
                                </strong>

                                <br>

                                <span class="small-text">
                                    <?= htmlspecialchars($user['email']) ?>
                                </span>

                            </td>


                            <td>

                                <span class="badge <?= htmlspecialchars($user['role']) ?>">
                                    <?= htmlspecialchars($user['role']) ?>
                                </span>


                                <?php if ($user['role'] == 'delivery'): ?>

                                    <br>

                                    <span class="small-text">
                                        <?= htmlspecialchars(regionLabel($user['delivery_region'])) ?>
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php if ($user['id'] != $_SESSION['user_id']): ?>


                                    <form method="POST" class="role-form">

                                        <input type="hidden" name="change_role" value="1">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">


                                        <select name="role" class="form-control">

                                            <option
                                                value="user"
                                                <?php if ($user['role'] == 'user') echo 'selected'; ?>
                                            >
                                                user
                                            </option>

                                            <option
                                                value="admin"
                                                <?php if ($user['role'] == 'admin') echo 'selected'; ?>
                                            >
                                                admin
                                            </option>

                                            <option
                                                value="delivery"
                                                <?php if ($user['role'] == 'delivery') echo 'selected'; ?>
                                            >
                                                delivery
                                            </option>

                                        </select>


                                        <?php
                                        $current_region = '';

                                        if ($user['delivery_region'] != null) {
                                            $current_region = $user['delivery_region'];
                                        }
                                        ?>


                                        <select name="delivery_region" class="form-control">

                                            <option value="">
                                                No region
                                            </option>

                                            <?php foreach ($regions as $code => $labels): ?>

                                                <option
                                                    value="<?= htmlspecialchars($code) ?>"
                                                    <?php if ($code == $current_region) echo 'selected'; ?>
                                                >
                                                    <?= htmlspecialchars($labels['en']) ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>


                                        <button type="submit" class="btn btn-secondary">
                                            Save
                                        </button>

                                    </form>


                                    <form method="POST" onsubmit="return confirm('Delete this user?');">

                                        <input type="hidden" name="delete_user" value="<?= $user['id'] ?>">

                                        <button type="submit" class="btn btn-danger">
                                            Delete
                                        </button>

                                    </form>


                                <?php else: ?>

                                    <span class="small-text">
                                        Current account
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </table>

            </div>

        </div>

    </div>
</div>
</body>
</html>