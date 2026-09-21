<?php
// End the current user session and send the person back to the login page.
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();