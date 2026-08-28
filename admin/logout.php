<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_email']);
$_SESSION['flash_success'] = "Admin logged out successfully.";
header("Location: login.php");
exit();
?>
