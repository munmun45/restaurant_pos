<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurant_pos";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create table
$sql = "CREATE TABLE IF NOT EXISTS menu_item_variants (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    menu_item_id INT(11) NOT NULL,
    spice_level VARCHAR(20) DEFAULT 'none',
    sweet_level VARCHAR(20) DEFAULT 'none',
    portion_size VARCHAR(20) DEFAULT 'small',
    prep_time INT(11) DEFAULT 15,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
)";

// Execute query
if ($conn->query($sql) === TRUE) {
    echo "Table menu_item_variants created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>