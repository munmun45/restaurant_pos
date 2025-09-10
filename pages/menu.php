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

<!-- Menu Items Grid -->
<div class="row">
    <?php if (count($menu_items) > 0): ?>
        <?php foreach ($menu_items as $item): ?>
        <div class="col-md-4 col-lg-3 mb-4 menu-item" data-category="<?php echo $item['category_id']; ?>">
            <div class="card menu-item-card h-100">
                <?php if (!empty($item['image']) && file_exists('uploads/menu/' . $item['image'])): ?>
                <img src="uploads/menu/<?php echo $item['image']; ?>" class="card-img-top menu-item-image" alt="<?php echo $item['name']; ?>">
                <?php else: ?>
                <div class="card-img-top menu-item-image bg-light d-flex align-items-center justify-content-center">
                    <i class="fas fa-utensils fa-3x text-muted"></i>
                </div>
                <?php endif; ?>
                
                <!-- Food Type Badge -->
                <div class="menu-item-badge">
                    <span class="badge <?php echo $item['food_type'] == 'veg' ? 'veg-badge' : 'non-veg-badge'; ?>">
                        <?php echo ucfirst($item['food_type']); ?>
                    </span>
                    
                    <?php if ($item['age_restriction']): ?>
                    <span class="badge age-restricted-badge ms-1">18+</span>
                    <?php endif; ?>
                </div>
                
                <div class="card-body">
                    <h5 class="card-title"><?php echo $item['name']; ?></h5>
                    <p class="card-text">
                        <?php if (!empty($item['description'])): ?>
                        <?php echo substr($item['description'], 0, 100) . (strlen($item['description']) > 100 ? '...' : ''); ?>
                        <?php else: ?>
                        <span class="text-muted">No description available</span>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="fw-bold text-primary fs-5">$<?php echo number_format($item['price'], 2); ?></span>
                        </div>
                        <div>
                            <!-- Spice Level -->
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
                            <span data-bs-toggle="tooltip" title="Portion Size: <?php echo ucfirst($item['portion_size']); ?>">
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
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center small text-muted mb-2">
                        <span>Prep time: <?php echo $item['prep_time']; ?> mins</span>
                        <span><?php echo ucfirst($item['category_name']); ?></span>
                    </div>
                    
                    <button class="btn btn-primary btn-add-to-order" 
                            data-item-id="<?php echo $item['id']; ?>" 
                            data-item-name="<?php echo $item['name']; ?>" 
                            data-item-price="<?php echo $item['price']; ?>">
                        <i class="fas fa-plus"></i> Add to Order
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
    <div class="col-12">
        <div class="alert alert-info">
            No menu items found in this category.
        </div>
    </div>
    <?php endif; ?>
</div>