<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    echo "Debug: User is logged in. Redirecting to webpage.php.<br>";
    header("Location: webpage.php");
    exit();
} else {
    echo "Debug: User is not logged in. Redirecting to login.php.<br>";
    header("Location: login.php");
    exit();
}
?>