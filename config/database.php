<?php
// Database connection details
$servername = "localhost"; // Your database host
$username = "root";        // Your database username
$password = "";            // Your database password
$dbname = "restaurant_pos"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    // Uncomment this line if you want to confirm the connection for testing purposes
    // echo "Connected successfully";
}

// Set charset to utf8
$conn->set_charset("utf8");

// Function to get PDO connection for prepared statements
function getPDOConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "restaurant_pos";
    
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>
