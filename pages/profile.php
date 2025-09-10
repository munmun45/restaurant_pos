<?php
// Profile page

// Get user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = sanitize($_POST['name']);
        $username = sanitize($_POST['username']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate input
        if (empty($name) || empty($username)) {
            $error = "Name and username are required";
        } else {
            // Check if username already exists (except for current user)
            $check_sql = "SELECT id FROM users WHERE username = ? AND id != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("si", $username, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "Username already exists";
            } else {
                // If password change is requested
                if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                    // Verify current password
                    if (!password_verify($current_password, $user['password'])) {
                        $error = "Current password is incorrect";
                    } elseif ($new_password != $confirm_password) {
                        $error = "New passwords do not match";
                    } elseif (strlen($new_password) < 6) {
                        $error = "New password must be at least 6 characters long";
                    } else {
                        // Update user with new password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_sql = "UPDATE users SET name = ?, username = ?, password = ? WHERE id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("sssi", $name, $username, $hashed_password, $user_id);
                        
                        if ($update_stmt->execute()) {
                            $_SESSION['username'] = $username;
                            redirectWithMessage('index.php?page=profile', 'Profile updated successfully');
                        } else {
                            $error = "Error updating profile: " . $conn->error;
                        }
                    }
                } else {
                    // Update user without changing password
                    $update_sql = "UPDATE users SET name = ?, username = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ssi", $name, $username, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $_SESSION['username'] = $username;
                        redirectWithMessage('index.php?page=profile', 'Profile updated successfully');
                    } else {
                        $error = "Error updating profile: " . $conn->error;
                    }
                }
            }
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>My Profile</h1>
    </div>
</div>

<?php if (isset($error)): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                <form method="post" action="" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                        <div class="invalid-feedback">Please enter your name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                        <div class="invalid-feedback">Please enter a username.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <input type="text" class="form-control" id="role" value="<?php echo ucfirst($user['role']); ?>" readonly>
                    </div>
                    
                    <hr>
                    
                    <h5>Change Password</h5>
                    <p class="text-muted small">Leave blank if you don't want to change your password</p>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Account Created:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Need Help?</h5>
                    <p>If you need assistance with your account, please contact the system administrator.</p>
                </div>
            </div>
        </div>
    </div>
</div>