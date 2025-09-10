<?php
// Menu display page

// Get all categories
$categories = getCategories($conn);

// Get selected category
$selected_category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Get menu items based on selected category
$menu_items = [];
if ($selected_category == 'all') {
    $menu_items = getAllMenuItems($conn);
} else {
    $menu_items = getMenuItemsByCategory($conn, $selected_category);
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>Menu</h1>
        <p class="lead">Browse our delicious menu items</p>
    </div>
</div>

<!-- Category Filter Pills -->
<div class="row mb-4">
    <div class="col-12">
        <div class="category-pills">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link category-filter <?php echo $selected_category == 'all' ? 'active' : ''; ?>" href="index.php?page=menu" data-category="all">
                        All Categories
                    </a>
                </li>
                
                <?php foreach ($categories as $category): ?>
                <li class="nav-item">
                    <a class="nav-link category-filter <?php echo $selected_category == $category['id'] ? 'active' : ''; ?>" 
                       href="index.php?page=menu&category=<?php echo $category['id']; ?>" 
                       data-category="<?php echo $category['id']; ?>">
                        <?php echo $category['name']; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Menu Items List View -->
<div class="row">
    <?php if (count($menu_items) > 0): ?>
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-hover menu-list-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menu_items as $item): ?>
                    <tr class="menu-item" data-category="<?php echo $item['category_id']; ?>">
                        <!-- Image column -->
                        <td>
                            <?php if (!empty($item['image']) && file_exists('uploads/menu/' . $item['image'])): ?>
                            <img src="uploads/menu/<?php echo $item['image']; ?>" class="menu-list-image" alt="<?php echo $item['name']; ?>">
                            <?php else: ?>
                            <div class="menu-list-image-placeholder">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Item name column -->
                        <td>
                            <div class="d-flex flex-column">
                                <h5 class="mb-1"><?php echo $item['name']; ?></h5>
                                <div>
                                    <span class="badge <?php echo $item['food_type'] == 'veg' ? 'veg-badge' : 'non-veg-badge'; ?>">
                                        <?php echo ucfirst($item['food_type']); ?>
                                    </span>
                                    
                                    <?php if ($item['age_restriction']): ?>
                                    <span class="badge age-restricted-badge ms-1">18+</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Spice Level -->
                                <div class="mt-1">
                                    <span class="spice-level" data-bs-toggle="tooltip" title="Spice Level: <?php echo ucfirst($item['spice_level']); ?>">
                                        <?php 
                                        $spice_icons = [
                                            'mild' => 1,
                                            'medium' => 2,
                                            'hot' => 3,
                                            'extra hot' => 4
                                        ];
                                        $count = $spice_icons[$item['spice_level']] ?? 0;
                                        for ($i = 0; $i < $count; $i++) {
                                            echo '<i class="fas fa-pepper-hot"></i> ';
                                        }
                                        ?>
                                    </span>
                                    
                                    <!-- Portion Size -->
                                    <span class="ms-2" data-bs-toggle="tooltip" title="Portion Size: <?php echo ucfirst($item['portion_size']); ?>">
                                        <?php 
                                        switch ($item['portion_size']) {
                                            case 'small':
                                                echo '<i class="fas fa-cookie-bite"></i>';
                                                break;
                                            case 'regular':
                                                echo '<i class="fas fa-hamburger"></i>';
                                                break;
                                            case 'large':
                                                echo '<i class="fas fa-pizza-slice"></i>';
                                                break;
                                        }
                                        ?>
                                    </span>
                                    
                                    <small class="text-muted ms-2">Prep: <?php echo $item['prep_time']; ?> mins</small>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Description column -->
                        <td>
                            <?php if (!empty($item['description'])): ?>
                            <?php echo substr($item['description'], 0, 100) . (strlen($item['description']) > 100 ? '...' : ''); ?>
                            <?php else: ?>
                            <span class="text-muted">No description available</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Category column -->
                        <td>
                            <?php echo ucfirst($item['category_name']); ?>
                        </td>
                        
                        <!-- Price column -->
                        <td>
                            <?php if (empty($item['variants'])): ?>
                            <span class="fw-bold text-primary">$<?php echo number_format($item['price'], 2); ?></span>
                            <?php else: ?>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#variants-<?php echo $item['id']; ?>" aria-expanded="false">
                                View Options
                            </button>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Actions column -->
                        <td>
                            <?php if (empty($item['variants'])): ?>
                            <div class="d-flex align-items-center">
                                <input type="number" class="form-control form-control-sm me-2 item-quantity" 
                                       min="1" max="10" value="1" style="width: 60px;"
                                       data-item-id="<?php echo $item['id']; ?>">
                                <button class="btn btn-sm btn-primary btn-add-to-order" 
                                        data-item-id="<?php echo $item['id']; ?>" 
                                        data-item-name="<?php echo $item['name']; ?>" 
                                        data-item-price="<?php echo $item['price']; ?>">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <!-- Variants row (collapsible) -->
                    <?php if (!empty($item['variants'])): ?>
                    <tr class="variant-row">
                        <td colspan="6" class="p-0">
                            <div class="collapse" id="variants-<?php echo $item['id']; ?>">
                                <div class="variants-list p-3">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Option</th>
                                                <th>Price</th>
                                                <th>Qty</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($item['variants'] as $variant): ?>
                                            <tr>
                                                <td>
                                                    <div class="variant-details">
                                                        <span class="fw-bold"><?php echo ucfirst($variant['portion_size']); ?></span>
                                                        <?php if ($variant['spice_level'] != 'none'): ?>
                                                            <span class="ms-1 badge bg-danger"><?php echo ucfirst($variant['spice_level']); ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($variant['sweet_level'] != 'none'): ?>
                                                            <span class="ms-1 badge bg-warning text-dark"><?php echo ucfirst($variant['sweet_level']); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted">Prep: <?php echo $variant['prep_time']; ?> mins</small>
                                                </td>
                                                <td class="fw-bold text-primary">
                                                    $<?php echo number_format($variant['price'], 2); ?>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm variant-quantity" 
                                                           min="1" max="10" value="1" style="width: 60px;"
                                                           data-variant-id="<?php echo $variant['id']; ?>">
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary btn-add-variant" 
                                                            data-item-id="<?php echo $item['id']; ?>" 
                                                            data-item-name="<?php echo $item['name']; ?>" 
                                                            data-variant-id="<?php echo $variant['id']; ?>"
                                                            data-variant-price="<?php echo $variant['price']; ?>"
                                                            data-variant-portion="<?php echo $variant['portion_size']; ?>"
                                                            data-variant-spice="<?php echo $variant['spice_level']; ?>">
                                                        <i class="fas fa-plus"></i> Add
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="col-12">
        <div class="alert alert-info">
            No menu items found in this category.
        </div>
    </div>
    <?php endif; ?>
</div>