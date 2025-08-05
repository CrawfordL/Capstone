
<?php 
// Set the name of the database file we want to connect to
$databaseFile = "chat_history.db"; 

// Start a "try" block - this means "attempt to do the following, but be ready to handle errors"
try {     
    // Create a new database connection to our SQLite database file
    $pdo = new PDO("sqlite:$databaseFile");     
    
    // Tell the database connection to throw errors if something goes wrong (instead of failing silently)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);     
    
    // If we get here, everything worked! Print a success message
    echo "Connected to database successfully!"; 
    
// If anything in the "try" block fails, this "catch" block will run instead
} catch (PDOException $e) {     
    // Stop the program and show an error message with details about what went wrong
    die("Database connection failed: " . $e->getMessage()); 
} 
?>