<?php
session_start();

/* Unset all session variables */
$_SESSION = [];

/* Destroy session */
session_destroy();

/* Redirect to login */
header("Location: /public/login_page.php");
exit();
