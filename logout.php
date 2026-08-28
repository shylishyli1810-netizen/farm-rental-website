<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();
session_start();
$_SESSION['flash_success'] = "You have been logged out successfully.";
header("Location: login.php");
exit();
?>
