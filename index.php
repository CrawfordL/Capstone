<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $rateLimit = 15;
    $rateLimitWindow = 60; 

    if (!isset($_SESSION['request_count'])) {
        $_SESSION['request_count'] = 0;
        $_SESSION['first_request_time'] = time();
    }

    if (time() - $_SESSION['first_request_time'] > $rateLimitWindow) {
        $_SESSION['request_count'] = 0;
        $_SESSION['first_request_time'] = time();
    }

    $_SESSION['request_count'] += 1;

    if ($_SESSION['request_count'] > $rateLimit) {
        http_response_code(429); 
        die("Rate limit exceeded. Please try again later.");
    }

    echo "Debug: User is logged in. Redirecting to webpage.php.<br>";
    header("Location: webpage.php");
    exit();
} else {
    echo "Debug: User is not logged in. Redirecting to login.php.<br>";
    header("Location: login.php");
    exit();
}
?>