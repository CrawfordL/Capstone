<?php
session_start();


$valid_username = "admin";
$valid_password = "admin";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $username;
        echo "Debug: Login successful. Session data: ";
        print_r($_SESSION);
        header("Location: webpage.php");
        exit();
    } else {
        $_SESSION["error"] = "Invalid username or password";
        echo "Debug: Login failed. Redirecting to login.php.<br>";
        header("Location: login.php");
        exit();
    }
}
