<?php
// This file loads the chat assistant widget shown across the website.
require_once 'db.php';
if (isAdmin() || isDelivery()) {
    if (!isset($_SESSION['nav_mode']) || $_SESSION['nav_mode'] != 'shop') {
        return;
    }
}
?>

<input type="checkbox" id="chat-toggle">

<label for="chat-toggle" class="sb-chat-btn-label">

    <svg class="sb-chat-icon" viewBox="0 0 24 24">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
    </svg>

    <svg class="sb-close-icon" viewBox="0 0 24 24">
        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
    </svg>

</label>


<div class="sb-chat-window">

    <input type="radio" name="sb-chat-view" id="sb-view-home" checked>
    <input type="radio" name="sb-chat-view" id="sb-view-help">
    <input type="radio" name="sb-chat-view" id="sb-view-account">
    <input type="radio" name="sb-chat-view" id="sb-view-orders">


    <div class="sb-chat-header">

        <div class="sb-bot-avatar">SB</div>

        <div>
            <h3><?= tr('SafeBite Assistant', 'העוזר של SafeBite') ?></h3>
            <p><?= tr('Help for first-time users', 'עזרה למשתמשים חדשים') ?></p>
        </div>

    </div>


    <div class="sb-chat-body">


        <div id="sb-section-home" class="sb-view-section">

            <p>
                <?= tr(
                    'Hello! I can help you use SafeBite faster.',
                    'שלום! אני יכול לעזור לך להשתמש ב-SafeBite בקלות.'
                ) ?>
            </p>

            <div class="sb-chat-options">

                <label for="sb-view-help" class="sb-chat-option">
                    <?= tr('First Time Help', 'עזרה למשתמש חדש') ?>
                </label>

                <label for="sb-view-orders" class="sb-chat-option">
                    <?= tr('Orders & Cart', 'הזמנות ועגלה') ?>
                </label>

                <label for="sb-view-account" class="sb-chat-option">
                    <?= tr('Account Options', 'אפשרויות חשבון') ?>
                </label>

                <a href="index.php" class="sb-chat-option">
                    <?= tr('Browse Products', 'עיון במוצרים') ?>
                </a>

            </div>

            <div class="sb-chat-note">
                <?= tr(
                    'SafeBite filters products based on your allergy profile.',
                    'SafeBite מסנן מוצרים לפי האלרגיות שלך.'
                ) ?>
            </div>

        </div>


        <div id="sb-section-help" class="sb-view-section">

            <p>
                <strong>
                    <?= tr('First Time Steps', 'צעדים ראשונים') ?>
                </strong>
            </p>

            <div class="sb-chat-options">


                <?php if (isLoggedIn()): ?>

                    <a href="profile.php" class="sb-chat-option">
                        <?= tr('Add My Allergies', 'הוספת האלרגיות שלי') ?>
                    </a>

                    <a href="index.php" class="sb-chat-option">
                        <?= tr('Start Shopping', 'התחלת קניות') ?>
                    </a>

                    <a href="contact.php" class="sb-chat-option">
                        <?= tr('Contact Support', 'יצירת קשר עם התמיכה') ?>
                    </a>


                <?php else: ?>


                    <a href="register.php" class="sb-chat-option">
                        <?= tr('Create Account', 'יצירת חשבון') ?>
                    </a>

                    <a href="login.php" class="sb-chat-option">
                        <?= tr('Login', 'התחברות') ?>
                    </a>

                    <a href="contact.php" class="sb-chat-option">
                        <?= tr('Support Page', 'עמוד תמיכה') ?>
                    </a>


                <?php endif; ?>


                <label for="sb-view-home" class="sb-chat-option">
                    <?= tr('Back to Menu', 'חזרה לתפריט') ?>
                </label>


            </div>

        </div>


        <div id="sb-section-orders" class="sb-view-section">

            <p>
                <strong>
                    <?= tr('Orders & Cart', 'הזמנות ועגלה') ?>
                </strong>
            </p>

            <div class="sb-chat-options">


                <a href="cart.php" class="sb-chat-option">
                    <?= tr('View Cart', 'צפייה בעגלה') ?>
                </a>


                <?php if (isLoggedIn()): ?>

                    <a href="my_orders.php" class="sb-chat-option">
                        <?= tr('My Orders', 'ההזמנות שלי') ?>
                    </a>

                    <a href="checkout.php" class="sb-chat-option">
                        <?= tr('Go to Checkout', 'מעבר לתשלום') ?>
                    </a>


                <?php else: ?>

                    <a href="login.php" class="sb-chat-option">
                        <?= tr('Login to Order', 'התחבר כדי להזמין') ?>
                    </a>

                <?php endif; ?>


                <label for="sb-view-home" class="sb-chat-option">
                    <?= tr('Back to Menu', 'חזרה לתפריט') ?>
                </label>


            </div>

        </div>


        <div id="sb-section-account" class="sb-view-section">

            <p>
                <strong>
                    <?= tr('Account', 'חשבון') ?>
                </strong>
            </p>

            <div class="sb-chat-options">


                <?php if (isLoggedIn()): ?>


                    <div class="sb-chat-option" style="cursor:default;">
                        <?= tr('User', 'משתמש') ?>:
                        <?= htmlspecialchars($_SESSION['username']) ?>
                    </div>

                    <a href="my_tickets.php" class="sb-chat-option">
                        <?= tr('My Tickets', 'הפניות שלי') ?>
                    </a>

                    <a href="logout.php" class="sb-chat-option" style="color:#dc3545; border-color:#f0b8bf;">
                        <?= tr('Logout', 'התנתקות') ?>
                    </a>


                <?php else: ?>

                    <a href="login.php" class="sb-chat-option">
                        <?= tr('Login', 'התחברות') ?>
                    </a>

                    <a href="register.php" class="sb-chat-option">
                        <?= tr('Create Account', 'יצירת חשבון') ?>
                    </a>


                <?php endif; ?>

                <label for="sb-view-home" class="sb-chat-option">
                    <?= tr('Back to Menu', 'חזרה לתפריט') ?>
                </label>


            </div>
        </div>
    </div>
</div>