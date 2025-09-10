<?php
// Menu Items management page

// Check if user is admin
if (!isAdmin()) {
    redirectWithMessage('index.php', 'You do not have permission to access this page', 'error');
}

// Get action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add menu item
    if (isset($_POST['add_menu_item'])) {
        $category_id = $_POST['category_id'];
        $name = sanitize($_POST['name']);
        $food_type = $_POST['food_type'];
        $age_restriction = isset($_POST['age_restriction']) ? 1 : 0;
        $description = sanitize($_POST['description']);
        
        // Get the first variant for the main item
        $price = isset($_POST['variants'][0]['price']) ? $_POST['variants'][0]['price'] : 0;
        $spice_level = isset($_POST['variants'][0]['spice_level']) ? $_POST['variants'][0]['spice_level'] : 'none';
        $sweet_level = isset($_POST['variants'][0]['sweet_level']) ? $_POST['variants'][0]['sweet_level'] : 'none';
        $portion_size = isset($_POST['variants'][0]['portion_size']) ? $_POST['variants'][0]['portion_size'] : 'small';
        $prep_time = isset($_POST['variants'][0]['prep_time']) ? $_POST['variants'][0]['prep_time'] : 15;
        
        // Validate input
        if (empty($name) || empty($price) || empty($category_id)) {
            $error = "Name, price and category are required";
        } else {
            // Handle image upload
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = uploadImage($_FILES['image'], 'uploads/menu/');
                if (!$image) {
                    $error = "Error uploading image. Please check file type and size.";
                }
            }
            
            if (!isset($error)) {
                $sql = "INSERT INTO menu_items (category_id, name, price, food_type, age_restriction, spice_level, sweet_level, portion_size, prep_time, image, description) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isdssssssss", $category_id, $name, $price, $food_type, $age_restriction, $spice_level, $sweet_level, $portion_size, $prep_time, $image, $description);
                
                if ($stmt->execute()) {
                    $menu_item_id = $conn->insert_id;
                    
                    // Save additional variants if any
                    if (isset($_POST['variants']) && count($_POST['variants']) > 1) {
                        saveMenuItemVariants($conn, $menu_item_id, $_POST['variants']);
                    }
                    
                    redirectWithMessage('index.php?page=menu_items', 'Menu item added successfully');
                } else {
                    $error = "Error adding menu item: " . $conn->error;
                }
            }
        }
    }
    
    // Edit menu item
    if (isset($_POST['edit_menu_item'])) {
        $id = $_POST['id'];
        $category_id = $_POST['category_id'];
        $name = sanitize($_POST['name']);
        $food_type = $_POST['food_type'];
        $age_restriction = isset($_POST['age_restriction']) ? 1 : 0;
        $description = sanitize($_POST['description']);
        
        // Get the first variant for the main item
        $price = isset($_POST['variants'][0]['price']) ? $_POST['variants'][0]['price'] : 0;
        $spice_level = isset($_POST['variants'][0]['spice_level']) ? $_POST['variants'][0]['spice_level'] : 'none';
        $sweet_level = isset($_POST['variants'][0]['sweet_level']) ? $_POST['variants'][0]['sweet_level'] : 'none';
        $portion_size = isset($_POST['variants'][0]['portion_size']) ? $_POST['variants'][0]['portion_size'] : 'small';
        $prep_time = isset($_POST['variants'][0]['prep_time']) ? $_POST['variants'][0]['prep_time'] : 15;
        
        // Validate input
        if (empty($name) || empty($price) || empty($category_id)) {
            $error = "Name, price and category are required";
        } else {
            // Get current menu item
            $current_item = getMenuItemById($conn, $id);
            $image = $current_item['image'];
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $new_image = uploadImage($_FILES['image'], 'uploads/menu/');
                if ($new_image) {
                    // Delete old image if exists
                    if (!empty($image) && file_exists('uploads/menu/' . $image)) {
                        unlink('uploads/menu/' . $image);
                    }
                    $image = $new_image;
                } else {
                    $error = "Error uploading image. Please check file type and size.";
                }
            }
            
            if (!isset($error)) {
                $sql = "UPDATE menu_items SET 
                        category_id = ?, 
                        name = ?, 
                        price = ?, 
                        food_type = ?, 
                        age_restriction = ?, 
                        spice_level = ?, 
                        sweet_level = ?, 
                        portion_size = ?, 
                        prep_time = ?, 
                        image = ?, 
                        description = ? 
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isdssssssssi", $category_id, $name, $price, $food_type, $age_restriction, $spice_level, $sweet_level, $portion_size, $prep_time, $image, $description, $id);
                
                if ($stmt->execute()) {
                    // Save additional variants if any
                    if (isset($_POST['variants']) && count($_POST['variants']) > 1) {
                        // First delete existing variants
                        deleteMenuItemVariants($conn, $id);
                        // Then save new variants
                        saveMenuItemVariants($conn, $id, $_POST['variants']);
                    }
                    
                    redirectWithMessage('index.php?page=menu_items', 'Menu item updated successfully');
                } else {
                    $error = "Error updating menu item: " . $conn->error;
                }
            }
        }
    }
    
    // Delete menu item
    if (isset($_POST['delete_menu_item'])) {
        $id = $_POST['id'];
        
        // Get current menu item for image deletion
        $current_item = getMenuItemById($conn, $id);
        
        $sql = "DELETE FROM menu_items WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Delete image if exists
            if (!empty($current_item['image']) && file_exists('uploads/menu/' . $current_item['image'])) {
                unlink('uploads/menu/' . $current_item['image']);
            }
            redirectWithMessage('index.php?page=menu_items', 'Menu item deleted successfully');
        } else {
            $error = "Error deleting menu item: " . $conn->error;
        }
    }
}

// Get menu item for editing
$menu_item = null;
$menu_item_variants = [];
if ($action == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $menu_item = getMenuItemById($conn, $id);
    
    if (!$menu_item) {
        redirectWithMessage('index.php?page=menu_items', 'Menu item not found', 'error');
    }
    
    // Get variants for this menu item
    $menu_item_variants = getMenuItemVariants($conn, $id);
}

// Get all menu items for listing
$menu_items = [];
if ($action == 'list') {
    $menu_items = getAllMenuItems($conn);
}

// Get all categories for dropdown
$categories = getCategories($conn);

/**
 * Save menu item variants to the database
 * @param mysqli $conn Database connection
 * @param int $menu_item_id Menu item ID
 * @param array $variants Array of variants
 */
function saveMenuItemVariants($conn, $menu_item_id, $variants) {
    // Skip the first variant as it's already saved in the main menu_items table
    for ($i = 1; $i < count($variants); $i++) {
        $variant = $variants[$i];
        
        // Skip if price is not set or is empty
        if (!isset($variant['price']) || empty($variant['price'])) {
            continue;
        }
        
        $spice_level = isset($variant['spice_level']) ? $variant['spice_level'] : 'none';
        $sweet_level = isset($variant['sweet_level']) ? $variant['sweet_level'] : 'none';
        $portion_size = isset($variant['portion_size']) ? $variant['portion_size'] : 'small';
        $prep_time = isset($variant['prep_time']) ? $variant['prep_time'] : 15;
        $price = $variant['price'];
        
        $sql = "INSERT INTO menu_item_variants (menu_item_id, spice_level, sweet_level, portion_size, prep_time, price) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssd", $menu_item_id, $spice_level, $sweet_level, $portion_size, $prep_time, $price);
        $stmt->execute();
    }
}

/**
 * Delete all variants for a menu item
 * @param mysqli $conn Database connection
 * @param int $menu_item_id Menu item ID
 */
function deleteMenuItemVariants($conn, $menu_item_id) {
    $sql = "DELETE FROM menu_item_variants WHERE menu_item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $menu_item_id);
    $stmt->execute();
}

/**
 * Get all variants for a menu item
 * @param mysqli $conn Database connection
 * @param int $menu_item_id Menu item ID
 * @return array Array of variants
 */
function getMenuItemVariants($conn, $menu_item_id) {
    $variants = [];
    
    $sql = "SELECT * FROM menu_item_variants WHERE menu_item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $menu_item_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $variants[] = $row;
    }
    
    return $variants;
}
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><?php echo $action == 'list' ? 'Menu Items' : ($action == 'add' ? 'Add Menu Item' : 'Edit Menu Item'); ?></h1>
            
            <?php if ($action == 'list'): ?>
            <a href="index.php?page=menu_items&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Menu Item
            </a>
            <?php else: ?>
            <a href="index.php?page=menu_items" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Menu Items
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (isset($error)): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($action == 'add' || $action == 'edit'): ?>
<!-- Add/Edit Menu Item Form -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $menu_item['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="name" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $action == 'edit' ? $menu_item['name'] : ''; ?>" required>
                            <div class="invalid-feedback">Please enter an item name.</div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($action == 'edit' && $menu_item['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="food_type" class="form-label">Food Type</label>
                            <select class="form-select" id="food_type" name="food_type" required>
                                <option value="veg" <?php echo ($action == 'edit' && $menu_item['food_type'] == 'veg') ? 'selected' : ''; ?>>Vegetarian</option>
                                <option value="eggetarian" <?php echo ($action == 'edit' && $menu_item['food_type'] == 'eggetarian') ? 'selected' : ''; ?>>Eggetarian</option>
                                <option value="non-veg" <?php echo ($action == 'edit' && $menu_item['food_type'] == 'non-veg') ? 'selected' : ''; ?>>Non-Vegetarian</option>
                            </select>
                        </div>
                    </div>
                    
                    
                    
                    <div id="attributes-container">
                        <div class="row mb-3 attribute-row">
                            <div class="col-md-2">
                                <label for="spice_level" class="form-label">Spice Level</label>
                                <select class="form-select" id="spice_level" name="variants[0][spice_level]">
                                    <option value="none" <?php echo ($action == 'edit' && $menu_item['spice_level'] == 'none') ? 'selected' : ''; ?>>None</option>
                                    <option value="mild" <?php echo ($action == 'edit' && $menu_item['spice_level'] == 'mild') ? 'selected' : ''; ?>>Mild</option>
                                    <option value="medium" <?php echo ($action == 'edit' && $menu_item['spice_level'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                                    <option value="hot" <?php echo ($action == 'edit' && $menu_item['spice_level'] == 'hot') ? 'selected' : ''; ?>>Hot</option>
                                    <option value="extra hot" <?php echo ($action == 'edit' && $menu_item['spice_level'] == 'extra hot') ? 'selected' : ''; ?>>Extra Hot</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="sweet_level" class="form-label">Sweet Level</label>
                                <select class="form-select" id="sweet_level" name="variants[0][sweet_level]">
                                    <option value="none" <?php echo ($action == 'edit' && $menu_item['sweet_level'] == 'none') ? 'selected' : ''; ?>>None</option>
                                    <option value="mild" <?php echo ($action == 'edit' && $menu_item['sweet_level'] == 'mild') ? 'selected' : ''; ?>>Mild</option>
                                    <option value="medium" <?php echo ($action == 'edit' && $menu_item['sweet_level'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                                    <option value="sweet" <?php echo ($action == 'edit' && $menu_item['sweet_level'] == 'sweet') ? 'selected' : ''; ?>>Sweet</option>
                                    <option value="extra sweet" <?php echo ($action == 'edit' && $menu_item['sweet_level'] == 'extra sweet') ? 'selected' : ''; ?>>Extra Sweet</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="portion_size" class="form-label">Portion Size</label>
                                <select class="form-select" id="portion_size" name="variants[0][portion_size]">
                                    <option value="small" <?php echo ($action == 'edit' && $menu_item['portion_size'] == 'small') ? 'selected' : ''; ?>>Small</option>
                                    <option value="regular" <?php echo ($action == 'edit' && $menu_item['portion_size'] == 'regular') ? 'selected' : ''; ?>>Regular</option>
                                    <option value="large" <?php echo ($action == 'edit' && $menu_item['portion_size'] == 'large') ? 'selected' : ''; ?>>Large</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="prep_time" class="form-label">Preparation Time</label>
                                <input type="number" class="form-control" id="prep_time" name="variants[0][prep_time]" min="1" value="<?php echo $action == 'edit' ? $menu_item['prep_time'] : '15'; ?>">
                            </div>

                            <div class="col-md-2">
                                <label for="price" class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price" name="variants[0][price]" step="0.01" min="0" value="<?php echo $action == 'edit' ? $menu_item['price'] : ''; ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid price.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" id="addAttributeBtn" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Section
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $action == 'edit' ? $menu_item['description'] : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Recommended size: 500x500 pixels. Max file size: 5MB.</div>
                        
                        <?php if ($action == 'edit' && !empty($menu_item['image'])): ?>
                        <div class="image-preview mt-2">
                            <img src="uploads/menu/<?php echo $menu_item['image']; ?>" alt="<?php echo $menu_item['name']; ?>" id="imagePreview">
                        </div>
                        <?php else: ?>
                        <div class="image-preview mt-2">
                            <img src="" alt="Image Preview" id="imagePreview" style="display: none;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="<?php echo $action == 'add' ? 'add_menu_item' : 'edit_menu_item'; ?>" class="btn btn-primary">
                            <?php echo $action == 'add' ? 'Add Menu Item' : 'Update Menu Item'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add attribute section functionality
        const addAttributeBtn = document.getElementById('addAttributeBtn');
        const attributesContainer = document.getElementById('attributes-container');
        
        if (addAttributeBtn && attributesContainer) {
            let sectionCounter = 0; // Start from 0 for the first variant
            
            // Add remove button to the first row if it doesn't have one
            const firstRow = attributesContainer.querySelector('.attribute-row');
            if (firstRow && !firstRow.querySelector('.remove-section')) {
                const removeButtonCol = document.createElement('div');
                removeButtonCol.className = 'col-md-1 d-flex align-items-end mb-2';
                removeButtonCol.innerHTML = `
                    <button type="button" class="btn btn-outline-danger btn-sm remove-section">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                firstRow.appendChild(removeButtonCol);
                
                // Add event listener to the remove button
                const removeButton = removeButtonCol.querySelector('.remove-section');
                removeButton.addEventListener('click', function() {
                    if (attributesContainer.querySelectorAll('.attribute-row').length > 1) {
                        this.closest('.attribute-row').remove();
                    }
                });
            }
            
            // Function to add a new variant row
            function addVariantRow(variant = {}) {
                sectionCounter++;
                
                // Default values or use provided variant data
                const spiceLevel = variant.spice_level || 'none';
                const sweetLevel = variant.sweet_level || 'none';
                const portionSize = variant.portion_size || 'small';
                const prepTime = variant.prep_time || 15;
                const price = variant.price || '';
                
                const newRow = document.createElement('div');
                newRow.className = 'row mb-3 attribute-row';
                newRow.innerHTML = `
                    <div class="col-md-2">
                        <label for="spice_level_${sectionCounter}" class="form-label">Spice Level</label>
                        <select class="form-select" id="spice_level_${sectionCounter}" name="variants[${sectionCounter}][spice_level]">
                            <option value="none" ${spiceLevel === 'none' ? 'selected' : ''}>None</option>
                            <option value="mild" ${spiceLevel === 'mild' ? 'selected' : ''}>Mild</option>
                            <option value="medium" ${spiceLevel === 'medium' ? 'selected' : ''}>Medium</option>
                            <option value="hot" ${spiceLevel === 'hot' ? 'selected' : ''}>Hot</option>
                            <option value="extra hot" ${spiceLevel === 'extra hot' ? 'selected' : ''}>Extra Hot</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="sweet_level_${sectionCounter}" class="form-label">Sweet Level</label>
                        <select class="form-select" id="sweet_level_${sectionCounter}" name="variants[${sectionCounter}][sweet_level]">
                            <option value="none" ${sweetLevel === 'none' ? 'selected' : ''}>None</option>
                            <option value="mild" ${sweetLevel === 'mild' ? 'selected' : ''}>Mild</option>
                            <option value="medium" ${sweetLevel === 'medium' ? 'selected' : ''}>Medium</option>
                            <option value="sweet" ${sweetLevel === 'sweet' ? 'selected' : ''}>Sweet</option>
                            <option value="extra sweet" ${sweetLevel === 'extra sweet' ? 'selected' : ''}>Extra Sweet</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="portion_size_${sectionCounter}" class="form-label">Portion Size</label>
                        <select class="form-select" id="portion_size_${sectionCounter}" name="variants[${sectionCounter}][portion_size]">
                            <option value="small" ${portionSize === 'small' ? 'selected' : ''}>Small</option>
                            <option value="regular" ${portionSize === 'regular' ? 'selected' : ''}>Regular</option>
                            <option value="large" ${portionSize === 'large' ? 'selected' : ''}>Large</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="prep_time_${sectionCounter}" class="form-label">Preparation Time</label>
                        <input type="number" class="form-control" id="prep_time_${sectionCounter}" name="variants[${sectionCounter}][prep_time]" min="1" value="${prepTime}">
                    </div>

                    <div class="col-md-2">
                        <label for="price_${sectionCounter}" class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="price_${sectionCounter}" name="variants[${sectionCounter}][price]" step="0.01" min="0" value="${price}">
                        </div>
                    </div>
                    
                    <div class="col-md-1 d-flex align-items-end mb-2">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-section">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                attributesContainer.appendChild(newRow);
                
                // Add event listener to the remove button
                const removeButtons = document.querySelectorAll('.remove-section');
                removeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        if (attributesContainer.querySelectorAll('.attribute-row').length > 1) {
                            this.closest('.attribute-row').remove();
                            // Reindex the variants after removal
                            reindexVariants();
                        }
                    });
                });
            }
            
            // Add event listener to the add button
            addAttributeBtn.addEventListener('click', function() {
                addVariantRow();
            });
            
            // Function to reindex variants after removal
            function reindexVariants() {
                const rows = attributesContainer.querySelectorAll('.attribute-row');
                rows.forEach((row, index) => {
                    // Update all input and select names in this row
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name && name.includes('variants[')) {
                            const newName = name.replace(/variants\[\d+\]/, `variants[${index}]`);
                            input.setAttribute('name', newName);
                        }
                    });
                });
            }
            
            // Load existing variants if editing
            <?php if ($action == 'edit' && !empty($menu_item_variants)): ?>
            // Load variants from PHP
            const variants = <?php echo json_encode($menu_item_variants); ?>;
            
            // Add each variant as a row
            variants.forEach(variant => {
                addVariantRow(variant);
            });
            <?php endif; ?>
        }
    });
</script>

<?php else: ?>
<!-- Menu Items List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (count($menu_items) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Type</th>
                                <th>Spice Level</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menu_items as $item): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($item['image']) && file_exists('uploads/menu/' . $item['image'])): ?>
                                    <img src="uploads/menu/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" width="50" height="50" style="object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-light text-center" style="width: 50px; height: 50px; line-height: 50px;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $item['name']; ?></td>
                                <td><?php echo $item['category_name']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo $item['food_type'] == 'veg' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo ucfirst($item['food_type']); ?>
                                    </span>
                                    <?php if ($item['age_restriction']): ?>
                                    <span class="badge bg-secondary ms-1">18+</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $spice_icons = [
                                        'mild' => 1,
                                        'medium' => 2,
                                        'hot' => 3,
                                        'extra hot' => 4
                                    ];
                                    $count = $spice_icons[$item['spice_level']] ?? 0;
                                    for ($i = 0; $i < $count; $i++) {
                                        echo '<i class="fas fa-pepper-hot text-danger"></i> ';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="index.php?page=menu_items&action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <form method="post" action="" class="d-inline">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" name="delete_menu_item" class="btn btn-sm btn-danger btn-delete">
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
                    No menu items found. <a href="index.php?page=menu_items&action=add">Add a menu item</a> to get started.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>