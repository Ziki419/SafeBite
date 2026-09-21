<?php
require_once 'db.php';

// Read the current session values to display the correct top navigation.
$username = '';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
}

$delivery_region = '';

if (isset($_SESSION['delivery_region'])) {
    $delivery_region = $_SESSION['delivery_region'];
}

$nav_mode = 'work';

if (isset($_SESSION['nav_mode'])) {
    $nav_mode = $_SESSION['nav_mode'];
}

// Change mode when the user presses a mode button in the navbar.
if (isset($_GET['mode'])) {
    if ($_GET['mode'] == 'shop') {
        $_SESSION['nav_mode'] = 'shop';
        $nav_mode = 'shop';
    }
    if ($_GET['mode'] == 'work') {
        $_SESSION['nav_mode'] = 'work';
        $nav_mode = 'work';
    }
}

$home_page = 'index.php';

if (isAdmin()) {
    $home_page = 'admin.php?mode=work';
}

if (isDelivery()) {
    $home_page = 'delivery_dashboard.php?mode=work';
}
?>

<link rel="stylesheet" href="style.css">


<nav class="safebite-nav">

    <a href="<?= $home_page ?>" class="nav-brand">
        SafeBite
    </a>


    <button
        type="button"
        class="hamburger"
        onclick="document.getElementById('navMenu').classList.toggle('active')"
    >
        Menu
    </button>


    <div class="nav-menu" id="navMenu">

        <div class="nav-left">


            <?php if (isLoggedIn() && isAdmin()): ?>


                <?php if ($nav_mode == 'shop'): ?>


                    <a href="index.php" class="nav-pill">
                        <?= tr('Home', 'דף הבית') ?>
                    </a>

                    <a href="cart.php" class="nav-pill">
                        <?= tr('Cart', 'עגלה') ?>
                    </a>

                    <a href="my_orders.php" class="nav-pill">
                        <?= tr('My Orders', 'ההזמנות שלי') ?>
                    </a>

                    <a href="contact.php" class="nav-pill">
                        <?= tr('Contact', 'צור קשר') ?>
                    </a>

                    <a href="admin.php?mode=work" class="nav-pill">
                        <?= tr('Dashboard', 'לוח בקרה') ?>
                    </a>


                <?php else: ?>

                    <a href="admin.php?mode=work" class="nav-pill">
                        <?= tr('Dashboard', 'לוח בקרה') ?>
                    </a>

                    <a href="admin_products.php" class="nav-pill">
                        <?= tr('Products', 'מוצרים') ?>
                    </a>

                    <a href="admin_users.php" class="nav-pill">
                        <?= tr('Users', 'משתמשים') ?>
                    </a>

                    <a href="admin_orders.php" class="nav-pill">
                        <?= tr('Orders', 'הזמנות') ?>
                    </a>

                    <a href="admin_tickets.php" class="nav-pill">
                        <?= tr('Tickets', 'פניות') ?>
                    </a>

                    <a href="index.php?mode=shop" class="nav-pill">
                        <?= tr('Browse Products', 'צפייה במוצרים') ?>
                    </a>

                <?php endif; ?>


            <?php elseif (isLoggedIn() && isDelivery()): ?>


                <?php if ($nav_mode == 'shop'): ?>

                    <a href="index.php" class="nav-pill">
                        <?= tr('Home', 'דף הבית') ?>
                    </a>

                    <a href="cart.php" class="nav-pill">
                        <?= tr('Cart', 'עגלה') ?>
                    </a>

                    <a href="my_orders.php" class="nav-pill">
                    <?= tr('My Orders', 'ההזמנות שלי') ?>
                    </a>

                    <a href="contact.php" class="nav-pill">
                        <?= tr('Contact', 'צור קשר') ?>
                    </a>

                    <a href="delivery_dashboard.php?mode=work" class="nav-pill">
                        <?= tr('Dashboard', 'לוח בקרה') ?>
                    </a>


                <?php else: ?>

                    <a href="delivery_dashboard.php?view=available" class="nav-pill">
                        <?= tr('Available Orders', 'הזמנות זמינות') ?>
                    </a>

                    <a href="delivery_dashboard.php?view=mine" class="nav-pill">
                        <?= tr('My Deliveries', 'המשלוחים שלי') ?>
                    </a>

                    <a href="delivery_dashboard.php?view=credit" class="nav-pill">
                        <?= tr('Credit', 'קרדיט') ?>
                    </a>

                    <a href="index.php?mode=shop" class="nav-pill">
                        <?= tr('Browse Products', 'צפייה במוצרים') ?>
                    </a>

                <?php endif; ?>


            <?php else: ?>

                <a href="index.php" class="nav-pill">
                    <?= tr('Home', 'דף הבית') ?>
                </a>

                <a href="cart.php" class="nav-pill">
                    <?= tr('Cart', 'עגלה') ?>
                </a>

                <a href="contact.php" class="nav-pill">
                    <?= tr('Contact', 'צור קשר') ?>
                </a>

            <?php endif; ?>

        </div>


        <div class="nav-right" style="margin-left: 20px;">


            <a href="?lang=en" class="nav-pill">
                EN
            </a>

            <a href="?lang=he" class="nav-pill">
                עברית
            </a>


            <?php if (isLoggedIn()): ?>

                <?php if ((!isAdmin() && !isDelivery()) || $nav_mode == 'shop'):?>

                    <a href="settings.php" class="nav-pill">
                        <?= tr('Settings', 'הגדרות') ?>
                    </a>

                <?php endif; ?>

                <?php if (isAdmin()): ?>

                    <span class="nav-user">
                        <?= tr('Admin', 'מנהל') ?>,
                        <?= htmlspecialchars($username) ?>
                    </span>

                    <a href="logout.php" class="nav-pill">
                        <?= tr('Logout', 'התנתקות') ?>
                    </a>


                <?php elseif (isDelivery()): ?>

                    <span class="nav-user">

                        <?= tr('Delivery', 'שליח') ?>:
                        <?= htmlspecialchars($username) ?>
                        -
                        <?= htmlspecialchars(regionLabel($delivery_region)) ?>

                    </span>

                    <a href="logout.php" class="nav-pill">
                        <?= tr('Logout', 'התנתקות') ?>
                    </a>


                <?php else: ?>

                    <a href="my_orders.php" class="nav-pill">
                        <?= tr('My Orders', 'ההזמנות שלי') ?>
                    </a>

                    <a href="my_rewards.php" class="nav-pill">
                        <?= tr('My Rewards', 'ההטבות שלי') ?>
                    </a>

                    <a href="my_tickets.php" class="nav-pill">
                        <?= tr('My Tickets', 'הפניות שלי') ?>
                    </a>

                    <span class="nav-user">
                        <?= tr('Hello', 'שלום') ?>,
                        <?= htmlspecialchars($username) ?>
                    </span>

                    <a href="logout.php" class="nav-pill">
                        <?= tr('Logout', 'התנתקות') ?>
                    </a>

                <?php endif; ?>

            <?php else: ?>

                <a href="login.php" class="nav-pill">
                    <?= tr('Login', 'התחברות') ?>
                </a>

                <a href="register.php" class="nav-pill">
                    <?= tr('Sign Up', 'הרשמה') ?>
                </a>

            <?php endif; ?>
        </div>
    </div>
</nav>