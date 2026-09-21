<?php

// Start the user session so login, language, and cart data can be stored.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang'])) {
    if ($_GET['lang'] == 'he') {
        $_SESSION['lang'] = 'he';
    } else {
        $_SESSION['lang'] = 'en';
    }
}
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}


// Return the correct text in the selected language.
function tr($english, $hebrew) {
    if ($_SESSION['lang'] == 'he') {
        return $hebrew;
    }
    return $english;
}


// Connect to the MySQL database used by the SafeBite project.
$host = 'localhost';
$db = 'safebite_db';
$user = 'root';
$pass = '1234';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$pdo = new PDO($dsn, $user, $pass);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


// Check whether the current user is logged in.
function isLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        return true;
    }
    return false;
}

function currentRole() {
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'];
    }
    return '';
}


function isAdmin() {
    if (currentRole() == 'admin') {
        return true;
    }
    return false;
}

function isDelivery() {
    if (currentRole() == 'delivery') {
        return true;
    }
    return false;
}

function isCustomer() {
    if (currentRole() == 'user') {
        return true;
    }
    return false;
}

// Return the valid delivery regions used in the app.
function deliveryRegions() {
    return array(
        'north' => array('en' => 'North','he' => 'צפון'),
        'haifa' => array('en' => 'Haifa','he' => 'חיפה'),
        'center' => array('en' => 'Center','he' => 'מרכז'),
        'jerusalem' => array('en' => 'Jerusalem','he' => 'ירושלים'),
        'south' => array('en' => 'South','he' => 'דרום')
    );
}

// Check whether a delivery region is one of the accepted values.
function validDeliveryRegion($region) {
    if ($region == 'north') return true;
    if ($region == 'haifa') return true;
    if ($region == 'center') return true;
    if ($region == 'jerusalem') return true;
    if ($region == 'south') return true;
    return false;
}

// Returns the region name in english or hebrew
function regionLabel($region) {
    if ($region == 'north') {
        return tr('North', 'צפון');
    }
    if ($region == 'haifa') {
        return tr('Haifa', 'חיפה');
    }
    if ($region == 'center') {
        return tr('Center', 'מרכז');
    }
    if ($region == 'jerusalem') {
        return tr('Jerusalem', 'ירושלים');
    }
    if ($region == 'south') {
        return tr('South', 'דרום');
    }
    return tr('Not assigned', 'לא הוגדר');
}
?>