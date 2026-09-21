<?php 
require 'db.php'; 

function getMonthlySales($pdo, $selectedMonth) {

    // If the admin selected a month, show only that month
    if ($selectedMonth != '') {

        $stmt = $pdo->prepare(
            "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') AS sale_month,
                COUNT(*) AS orders_count,
                SUM(total_price) AS total_sales
             FROM orders
             WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY sale_month DESC"
        );

        $stmt->execute([$selectedMonth]);

    } else {
        // If no month was selected, show all months
        $stmt = $pdo->query(
            "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') AS sale_month,
                COUNT(*) AS orders_count,
                SUM(total_price) AS total_sales
             FROM orders
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY sale_month DESC"
        );
    }
    return $stmt->fetchAll();
}


if (!isLoggedIn() || !isAdmin()) { 
    header("Location: index.php"); 
    exit(); 
}


$is_admin_theme = true;


// Count the information shown in the dashboard
$totalProducts = $pdo->query(
    "SELECT COUNT(*) FROM products"
)->fetchColumn();

$totalUsers = $pdo->query(
    "SELECT COUNT(*) FROM users"
)->fetchColumn();

$totalOrders = $pdo->query(
    "SELECT COUNT(*) FROM orders"
)->fetchColumn();

$totalTickets = $pdo->query(
    "SELECT COUNT(*) FROM tickets"
)->fetchColumn();


// Get the selected month from the filter
$selectedMonth = '';

if (isset($_GET['month'])) {
    $selectedMonth = $_GET['month'];
}


// Get monthly sales report
$monthlySales = getMonthlySales($pdo, $selectedMonth);


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

    <title>
        <?= tr(
            'Admin Dashboard - SafeBite',
            'לוח ניהול - SafeBite'
        ) ?>
    </title>

    <link rel="stylesheet" href="style.css">
</head>

<body class="admin-theme">

<?php include 'navbar.php'; ?>


<div class="admin-dashboard-wrap">

    <div class="admin-hero">

        <div class="admin-hero-content">

            <h1>
                <?= tr(
                    'SafeBite Admin Dashboard',
                    'לוח הניהול של SafeBite'
                ) ?>
            </h1>

            <p>
                <?= tr(
                    'Manage products, users, orders, and support tickets from one place.',
                    'נהל מוצרים, משתמשים, הזמנות ופניות תמיכה ממקום אחד.'
                ) ?>
            </p>

        </div>

    </div>


    <div class="admin-top-stats">

        <div class="admin-stat-card">

            <div class="admin-stat-label">
                <?= tr('Products', 'מוצרים') ?>
            </div>

            <div class="admin-stat-value">
                <?= $totalProducts ?>
            </div>

            <div class="admin-stat-note">
                <?= tr(
                    'Products in catalog',
                    'מוצרים בקטלוג'
                ) ?>
            </div>

        </div>


        <div class="admin-stat-card">

            <div class="admin-stat-label">
                <?= tr('Users', 'משתמשים') ?>
            </div>

            <div class="admin-stat-value">
                <?= $totalUsers ?>
            </div>

            <div class="admin-stat-note">
                <?= tr(
                    'Registered accounts',
                    'חשבונות רשומים'
                ) ?>
            </div>

        </div>


        <div class="admin-stat-card">

            <div class="admin-stat-label">
                <?= tr('Orders', 'הזמנות') ?>
            </div>

            <div class="admin-stat-value">
                <?= $totalOrders ?>
            </div>

            <div class="admin-stat-note">
                <?= tr(
                    'Total customer orders',
                    'סך הזמנות הלקוחות'
                ) ?>
            </div>

        </div>


        <div class="admin-stat-card">

            <div class="admin-stat-label">
                <?= tr('Tickets', 'פניות') ?>
            </div>

            <div class="admin-stat-value">
                <?= $totalTickets ?>
            </div>

            <div class="admin-stat-note">
                <?= tr(
                    'Support requests',
                    'פניות תמיכה'
                ) ?>
            </div>

        </div>

    </div>


    <h2 class="admin-section-title">
        <?= tr(
            'Management Panels',
            'לוחות ניהול'
        ) ?>
    </h2>


    <div class="admin-menu-grid">

        <a
            href="admin_products.php"
            class="admin-menu-card"
        >

            <h3>
                <?= tr(
                    'Manage Products',
                    'ניהול מוצרים'
                ) ?>
            </h3>

            <p>
                <?= tr(
                    'Add new products, review the current catalog, and remove items when needed.',
                    'הוסף מוצרים חדשים, הצג את הקטלוג הנוכחי והסר מוצרים בעת הצורך.'
                ) ?>
            </p>

            <span class="admin-menu-meta">
                <?= $totalProducts ?>
                <?= tr('products', 'מוצרים') ?>
            </span>

        </a>


        <a
            href="admin_users.php"
            class="admin-menu-card"
        >

            <h3>
                <?= tr(
                    'Manage Users',
                    'ניהול משתמשים'
                ) ?>
            </h3>

            <p>
                <?= tr(
                    'Add users, delete accounts, and change roles between user, admin, and delivery.',
                    'הוסף משתמשים, מחק חשבונות ושנה תפקידים בין משתמש, מנהל ושליח.'
                ) ?>
            </p>

            <span class="admin-menu-meta">
                <?= $totalUsers ?>
                <?= tr('users', 'משתמשים') ?>
            </span>

        </a>


        <a
            href="admin_orders.php"
            class="admin-menu-card"
        >

            <h3>
                <?= tr(
                    'Manage Orders',
                    'ניהול הזמנות'
                ) ?>
            </h3>

            <p>
                <?= tr(
                    'Review orders, follow status, and control delivery progress for customers.',
                    'צפה בהזמנות, עקוב אחר הסטטוס ונהל את התקדמות המשלוח ללקוחות.'
                ) ?>
            </p>

            <span class="admin-menu-meta">
                <?= $totalOrders ?>
                <?= tr('orders', 'הזמנות') ?>
            </span>

        </a>


        <a
            href="admin_tickets.php"
            class="admin-menu-card"
        >

            <h3>
                <?= tr(
                    'Manage Tickets',
                    'ניהול פניות'
                ) ?>
            </h3>

            <p>
                <?= tr(
                    'Open support tickets, close or reopen them, and check customer messages.',
                    'פתח פניות תמיכה, סגור או פתח אותן מחדש ובדוק הודעות של לקוחות.'
                ) ?>
            </p>

            <span class="admin-menu-meta">
                <?= $totalTickets ?>
                <?= tr('tickets', 'פניות') ?>
            </span>

        </a>


        <a
            href="add_product.php"
            class="admin-menu-card"
        >

            <h3>
                <?= tr(
                    'Add Product',
                    'הוספת מוצר'
                ) ?>
            </h3>

            <p>
                <?= tr(
                    'Go directly to the add product form and connect allergens to products.',
                    'עבור ישירות לטופס הוספת מוצר וחבר אלרגנים למוצרים.'
                ) ?>
            </p>

            <span class="admin-menu-meta">
                <?= tr(
                    'Quick action',
                    'פעולה מהירה'
                ) ?>
            </span>

        </a>

    </div>

</div>


<div
    class="admin-dashboard-wrap"
    id="monthly-report"
>

    <h2 class="admin-section-title">
        <?= tr(
            'Monthly Sales Report',
            'דוח מכירות חודשי'
        ) ?>
    </h2>


    <div class="filter-container">

        <form
            method="GET"
            action="admin.php#monthly-report"
            class="filter-form monthly-filter-form"
        >

            <label for="month">
                <?= tr('Filter by Month','סינון לפי חודש') ?>
            </label>

           <select id="month" name="month" required>

    <option value="">
        <?= tr('Choose a month', 'בחר חודש') ?>
    </option>

    <option value="2026-01" <?php if ($selectedMonth == '2026-01') echo 'selected'; ?>>
        <?= tr('January', 'ינואר') ?>
    </option>

    <option value="2026-02" <?php if ($selectedMonth == '2026-02') echo 'selected'; ?>>
        <?= tr('February', 'פברואר') ?>
    </option>

    <option value="2026-03" <?php if ($selectedMonth == '2026-03') echo 'selected'; ?>>
        <?= tr('March', 'מרץ') ?>
    </option>

    <option value="2026-04" <?php if ($selectedMonth == '2026-04') echo 'selected'; ?>>
        <?= tr('April', 'אפריל') ?>
    </option>

    <option value="2026-05" <?php if ($selectedMonth == '2026-05') echo 'selected'; ?>>
        <?= tr('May', 'מאי') ?>
    </option>

    <option value="2026-06" <?php if ($selectedMonth == '2026-06') echo 'selected'; ?>>
        <?= tr('June', 'יוני') ?>
    </option>

    <option value="2026-07" <?php if ($selectedMonth == '2026-07') echo 'selected'; ?>>
        <?= tr('July', 'יולי') ?>
    </option>

    <option value="2026-08" <?php if ($selectedMonth == '2026-08') echo 'selected'; ?>>
        <?= tr('August', 'אוגוסט') ?>
    </option>

    <option value="2026-09" <?php if ($selectedMonth == '2026-09') echo 'selected'; ?>>
        <?= tr('September', 'ספטמבר') ?>
    </option>

    <option value="2026-10" <?php if ($selectedMonth == '2026-10') echo 'selected'; ?>>
        <?= tr('October', 'אוקטובר') ?>
    </option>

    <option value="2026-11" <?php if ($selectedMonth == '2026-11') echo 'selected'; ?>>
        <?= tr('November', 'נובמבר') ?>
    </option>

    <option value="2026-12" <?php if ($selectedMonth == '2026-12') echo 'selected'; ?>>
        <?= tr('December', 'דצמבר') ?>
    </option>

</select>

            <button
                type="submit"
                class="btn btn-primary"
            >
                <?= tr('Filter', 'סנן') ?>
            </button>

            <a
                href="admin.php#monthly-report"
                class="btn btn-secondary"
            >
                <?= tr('Show All', 'הצג הכול') ?>
            </a>

        </form>

    </div>


    <div class="admin-stat-card">

        <table>

            <tr>

                <th>
                    <?= tr('Month', 'חודש') ?>
                </th>

                <th>
                    <?= tr('Orders', 'הזמנות') ?>
                </th>

                <th>
                    <?= tr('Total Sales', 'סך המכירות') ?>
                </th>

            </tr>


            <?php if (count($monthlySales) > 0): ?>

                <?php foreach ($monthlySales as $row): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($row['sale_month']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['orders_count']) ?>
                        </td>

                        <td>
                            $<?= number_format($row['total_sales'], 2) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="3">

                        <?= tr(
                            'No sales were found for the selected month.',
                            'לא נמצאו מכירות בחודש שנבחר.'
                        ) ?>

                    </td>

                </tr>

            <?php endif; ?>

        </table>

    </div>

</div>

</body>
</html>