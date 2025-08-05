<?php
session_start();

// Set the correct username and password that people need to log in
// In a real website, these would be stored securely in a database
$valid_username = "admin";
$valid_password = "admin";

// Check if someone just submitted the login form
// POST means they clicked the login button and sent their username/password
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the username and password that the user typed in
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Check if what they typed matches our valid login credentials
    if ($username === $valid_username && $password === $valid_password) {
        // If the login is correct, remember that this user is logged in
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $username;
        
        // Show debug information (helpful for testing, but should be removed in production)
        echo "Debug: Login successful. Session data: ";
        print_r($_SESSION);  // This shows all the session information
        
        // Send the user to the main webpage since they logged in successfully
        header("Location: webpage.php");
        // Stop running this script since we're redirecting
        exit();
    } else {
        // If the username or password is wrong, save an error message
        $_SESSION["error"] = "Invalid username or password";
        
        // Show debug information (helpful for testing)
        echo "Debug: Login failed. Redirecting to login.php.<br>";
        
        // Send them back to the login page to try again
        header("Location: login.php");
        // Stop running this script since we're redirecting
        exit();
    }
}
?>