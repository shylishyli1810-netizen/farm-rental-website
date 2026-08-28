<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['farmer_id']);
unset($_SESSION['farmer_name']);
unset($_SESSION['farmer_email']);
$_SESSION['flash_success'] = "You have been logged out from Farmer Portal.";
header("Location: ../login.php");
exit();
?>
