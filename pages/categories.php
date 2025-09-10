<?php
// Categories management page

// Check if user is admin
if (!isAdmin()) {
    redirectWithMessage('index.php', 'You do not have permission to access this page', 'error');
}

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add category
    if (isset($_POST['add_category'])) {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        
        if (empty($name)) {
            $error = "Category name is required";
        } else {
            $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $name, $description);
            
            if ($stmt->execute()) {
                redirectWithMessage('index.php?page=categories', 'Category added successfully');
            } else {
                $error = "Error adding category: " . $conn->error;
            }
        }
    }
    
    // Edit category
    if (isset($_POST['edit_category'])) {
        $id = $_POST['id'];
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        
        if (empty($name)) {
            $error = "Category name is required";
        } else {
            $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $name, $description, $id);
            
            if ($stmt->execute()) {
                redirectWithMessage('index.php?page=categories', 'Category updated successfully');
            } else {
                $error = "Error updating category: " . $conn->error;
            }
        }
    }
    
    // Delete category
    if (isset($_POST['delete_category'])) {
        $id = $_POST['id'];
        
        // Check if category has menu items
        $check_sql = "SELECT COUNT(*) as count FROM menu_items WHERE category_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_row = $check_result->fetch_assoc();
        
        if ($check_row['count'] > 0) {
            $error = "Cannot delete category. It has menu items associated with it.";
        } else {
            $sql = "DELETE FROM categories WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                redirectWithMessage('index.php?page=categories', 'Category deleted successfully');
            } else {
                $error = "Error deleting category: " . $conn->error;
            }
        }
    }
}

// Get category for editing
$category = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $category = getCategoryById($conn, $id);
    
    if (!$category) {
        redirectWithMessage('index.php?page=categories', 'Category not found', 'error');
    }
}

// Get all categories for listing
$categories = [];
if ($action == 'list') {
    $categories = getCategories($conn);
}
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><?php echo $action == 'list' ? 'Categories' : ($action == 'add' ? 'Add Category' : 'Edit Category'); ?></h1>
            
            <?php if ($action == 'list'): ?>
            <a href="index.php?page=categories&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Category
            </a>
            <?php else: ?>
            <a href="index.php?page=categories" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Categories
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (isset($error)): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($action == 'add' || $action == 'edit'): ?>
<!-- Add/Edit Category Form -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="post" action="" class="needs-validation" novalidate>
                    <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $action == 'edit' ? $category['name'] : ''; ?>" required>
                        <div class="invalid-feedback">Please enter a category name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $action == 'edit' ? $category['description'] : ''; ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="<?php echo $action == 'add' ? 'add_category' : 'edit_category'; ?>" class="btn btn-primary">
                            <?php echo $action == 'add' ? 'Add Category' : 'Update Category'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Categories List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (count($categories) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?php echo $cat['id']; ?></td>
                                <td><?php echo $cat['name']; ?></td>
                                <td><?php echo $cat['description'] ? substr($cat['description'], 0, 50) . (strlen($cat['description']) > 50 ? '...' : '') : 'No description'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($cat['created_at'])); ?></td>
                                <td>
                                    <a href="index.php?page=categories&action=edit&id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <form method="post" action="" class="d-inline">
                                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                        <button type="submit" name="delete_category" class="btn btn-sm btn-danger btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    No categories found. <a href="index.php?page=categories&action=add">Add a category</a> to get started.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>