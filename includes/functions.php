<?php
/**
 * Helper functions for the Restaurant POS system
 */

/**
 * Sanitize user input
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Display error message
 * @param string $message Error message to display
 * @return string HTML for error alert
 */
function displayError($message) {
    return '<div class="alert alert-danger alert-dismissible fade show" role="alert">
              ' . $message . '
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Display success message
 * @param string $message Success message to display
 * @return string HTML for success alert
 */
function displaySuccess($message) {
    return '<div class="alert alert-success alert-dismissible fade show" role="alert">
              ' . $message . '
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Upload image file
 * @param array $file $_FILES array element
 * @param string $destination Directory to save the file
 * @return string|bool Filename on success, false on failure
 */
function uploadImage($file, $destination = 'uploads/') {
    // Create directory if it doesn't exist
    if (!file_exists($destination)) {
        mkdir($destination, 0777, true);
    }
    
    $target_file = $destination . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $imageFileType;
    $target_file = $destination . $newFileName;
    
    // Check if image file is an actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return false;
    }
    
    // Check file size (limit to 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return false;
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $newFileName;
    } else {
        return false;
    }
}

/**
 * Get all categories
 * @param mysqli $conn Database connection
 * @return array Array of categories
 */
function getCategories($conn) {
    $categories = [];
    $sql = "SELECT * FROM categories ORDER BY name ASC";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

/**
 * Get category by ID
 * @param mysqli $conn Database connection
 * @param int $id Category ID
 * @return array|bool Category data or false if not found
 */
function getCategoryById($conn, $id) {
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Get menu items by category
 * @param mysqli $conn Database connection
 * @param int $category_id Category ID
 * @return array Array of menu items
 */
function getMenuItemsByCategory($conn, $category_id) {
    $items = [];
    $sql = "SELECT * FROM menu_items WHERE category_id = ? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    return $items;
}

/**
 * Get all menu items
 * @param mysqli $conn Database connection
 * @return array Array of menu items
 */
function getAllMenuItems($conn) {
    $items = [];
    $sql = "SELECT m.*, c.name as category_name 
           FROM menu_items m 
           JOIN categories c ON m.category_id = c.id 
           ORDER BY c.name, m.name ASC";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    return $items;
}

/**
 * Get menu item by ID
 * @param mysqli $conn Database connection
 * @param int $id Menu item ID
 * @return array|bool Menu item data or false if not found
 */
function getMenuItemById($conn, $id) {
    $sql = "SELECT * FROM menu_items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Check if user is admin
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect with message
 * @param string $location URL to redirect to
 * @param string $message Message to display
 * @param string $type Message type (success or error)
 */
function redirectWithMessage($location, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $location");
    exit;
}

/**
 * Display message if set in session
 * @return string HTML for message alert or empty string
 */
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'success';
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        if ($type === 'error') {
            return displayError($message);
        } else {
            return displaySuccess($message);
        }
    }
    
    return '';
}
?>