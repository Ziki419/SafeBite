<?php
require 'db.php';

// Decide whether a reward is available, used, or expired.
function getRewardStatus($reward, $today) {
    if ($reward['is_used'] == 1) {
        return 'used';
    }
    if ($reward['expires_at'] != null && $reward['expires_at'] < $today) {
        return 'expired';
    }
    return 'available';
}

function getRewardStatusText($status) {
    if ($status == 'used') {
        return tr('Used', 'נוצל');
    }
    if ($status == 'expired') {
        return tr('Expired', 'תוקף הסתיים');
    }
    return tr('Available', 'זמין');
}

// Only logged-in users can open their rewards page.
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION['user_id'];

// Load all rewards connected to the current account.
$stmt = $pdo->prepare("SELECT * FROM user_rewards WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$rewards = $stmt->fetchAll();


$today = date("Y-m-d");

$available_count = 0;
$used_count = 0;
$expired_count = 0;


foreach ($rewards as $reward) {
    $status = getRewardStatus($reward,$today);
    if ($status == 'available') {
        $available_count++;
    }
    if ($status == 'used') {
        $used_count++;
    }
    if ($status == 'expired') {
        $expired_count++;
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
        <?= tr(
            'My Rewards - SafeBite',
            'ההטבות שלי - SafeBite'
        ) ?>
    </title>

    <link rel="stylesheet" href="style.css">

    <style>

        .rewards-page {
            max-width: 1180px;
            margin: 38px auto 60px;
            padding: 0 24px;
        }

        .rewards-header {
            background: linear-gradient(135deg, #4caf50, #1f6d3a);
            color: white;
            border-radius: 18px;
            padding: 30px;
        }

        .rewards-header h1 {
            margin: 0 0 8px;
        }

        .rewards-header p {
            margin: 0;
        }

        .reward-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 24px;
        }

        .summary-card {
            background: white;
            border: 1px solid #dce9de;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 18px rgba(0,0,0,0.05);
        }

        .summary-card strong {
            display: block;
            font-size: 2rem;
            color: #1f6d3a;
        }

        .rewards-note {
            margin-top: 24px;
            padding: 16px;
            background: #f0f8f1;
            border: 1px solid #cfe5d2;
            border-radius: 12px;
        }

        .rewards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 25px;
        }

        .reward-card {
            background: white;
            border: 1px solid #dce9de;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.05);
            transition: 0.3s;
        }

        .reward-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            transform: translateY(-3px);
        }

        .reward-value {
            font-size: 1.7rem;
            font-weight: bold;
            color: #1f6d3a;
        }

        .reward-status {
            display: inline-block;
            margin-top: 10px;
            padding: 6px 10px;
            border-radius: 15px;
            font-weight: bold;
        }

        .reward-status.available {
            background: #e9f8ec;
            color: #2a6837;
        }

        .reward-status.used {
            background: #eeeeee;
            color: #666666;
        }

        .reward-status.expired {
            background: #fdebea;
            color: #a43631;
        }

        .reward-code {
            margin-top: 18px;
            padding: 12px;
            background: #f8fbf8;
            border: 1px dashed #a7cbaa;
            border-radius: 10px;
            font-weight: bold;
        }

        .reward-info {
            margin-top: 15px;
            line-height: 1.8;
        }

        .empty-rewards {
            margin-top: 25px;
            background: white;
            border: 1px solid #dce9de;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
        }

        @media (max-width: 800px) {

            .reward-summary,
            .rewards-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body style="min-height: 100vh;">

<?php include 'navbar.php'; ?>


<main class="rewards-page">


    <div class="rewards-header">

        <h1>
            <?= tr(
                'My Rewards',
                'ההטבות שלי'
            ) ?>
        </h1>

        <p>
            <?= tr(
                'Your SafeBite reward coupons.',
                'קופוני ההטבה שלך ב-SafeBite.'
            ) ?>
        </p>

    </div>


    <div class="reward-summary">


        <div class="summary-card">

            <strong>
                <?= $available_count ?>
            </strong>

            <?= tr(
                'Available',
                'זמינות'
            ) ?>

        </div>


        <div class="summary-card">

            <strong>
                <?= $used_count ?>
            </strong>

            <?= tr(
                'Used',
                'נוצלו'
            ) ?>

        </div>


        <div class="summary-card">

            <strong>
                <?= $expired_count ?>
            </strong>

            <?= tr(
                'Expired',
                'פג תוקף'
            ) ?>

        </div>


    </div>


    <div class="rewards-note">

        <?= tr(
            'Enter an available reward code in the coupon field during checkout. The cart must be at least $100.',
            'הזן קוד הטבה זמין בשדה הקופון בתשלום. סכום העגלה חייב להיות לפחות 100 דולר.'
        ) ?>

    </div>


    <?php if (empty($rewards)): ?>


        <div class="empty-rewards">

            <h2>
                <?= tr(
                    'No rewards yet',
                    'עדיין אין הטבות'
                ) ?>
            </h2>

            <p>
                <?= tr(
                    'Continue shopping to earn rewards.',
                    'המשך לקנות כדי לקבל הטבות.'
                ) ?>
            </p>

            <a
                href="index.php"
                class="btn btn-primary"
            >
                <?= tr(
                    'Browse Products',
                    'עיון במוצרים'
                ) ?>
            </a>

        </div>


    <?php else: ?>


        <div class="rewards-grid">


            <?php foreach ($rewards as $reward): ?>


                <?php

                $status =
                    getRewardStatus(
                        $reward,
                        $today
                    );

                ?>


                <div class="reward-card">


                    <div class="reward-value">

                        $<?= number_format(
                            $reward['discount_amount'],
                            2
                        ) ?>

                    </div>


                    <div
                        class="reward-status <?= $status ?>"
                    >

                        <?= getRewardStatusText(
                            $status
                        ) ?>

                    </div>


                    <div class="reward-code">

                        <?= tr(
                            'Coupon Code:',
                            'קוד קופון:'
                        ) ?>

                        <br>

                        <?= htmlspecialchars(
                            $reward['coupon_code']
                        ) ?>

                    </div>


                    <div class="reward-info">


                        <div>

                            <strong>
                                <?= tr(
                                    'Created:',
                                    'נוצר:'
                                ) ?>
                            </strong>

                            <?= substr(
                                $reward['created_at'],
                                0,
                                10
                            ) ?>

                        </div>


                        <div>

                            <strong>
                                <?= tr(
                                    'Expires:',
                                    'בתוקף עד:'
                                ) ?>
                            </strong>


                            <?php if ($reward['expires_at'] == null): ?>

                                <?= tr(
                                    'No expiration',
                                    'ללא תפוגה'
                                ) ?>

                            <?php else: ?>

                                <?= htmlspecialchars(
                                    $reward['expires_at']
                                ) ?>

                            <?php endif; ?>


                        </div>


                    </div>


                    <?php if ($status == 'available'): ?>

                        <div style="margin-top:18px;">

                            <a
                                href="index.php"
                                class="btn btn-primary"
                            >
                                <?= tr(
                                    'Shop Now',
                                    'קנייה עכשיו'
                                ) ?>
                            </a>

                        </div>

                    <?php endif; ?>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</main>
<?php include 'chatbot.php'; ?>
</body>
</html>