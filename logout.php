<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// delete all session variables
$_SESSION = array();

// destroy session
session_destroy();

// lead to the registiration page
header("location: index.php");
exit;
?>