<?php
// Dashboard page

// Get statistics
$stats = [
    'categories' => 0,
    'menu_items' => 0,
    'orders' => 0
];

// Count categories
$sql = "SELECT COUNT(*) as count FROM categories";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $stats['categories'] = $row['count'];
}

// Count menu items
$sql = "SELECT COUNT(*) as count FROM menu_items";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $stats['menu_items'] = $row['count'];
}

// Count orders
$sql = "SELECT COUNT(*) as count FROM orders";
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $stats['orders'] = $row['count'];
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="mb-4">Dashboard</h1>
        <p class="lead">Welcome to the Restaurant POS System. Here's an overview of your restaurant.</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <i class="fas fa-list"></i>
                <h3><?php echo $stats['categories']; ?></h3>
                <p>Categories</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <i class="fas fa-utensils"></i>
                <h3><?php echo $stats['menu_items']; ?></h3>
                <p>Menu Items</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <i class="fas fa-shopping-cart"></i>
                <h3><?php echo $stats['orders']; ?></h3>
                <p>Orders</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if(isAdmin()): ?>
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=categories&action=add" class="btn btn-primary w-100">
                            <i class="fas fa-plus"></i> Add Category
                        </a>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=menu_items&action=add" class="btn btn-success w-100">
                            <i class="fas fa-plus"></i> Add Menu Item
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=menu" class="btn btn-info w-100 text-white">
                            <i class="fas fa-book-open"></i> View Menu
                        </a>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <a href="index.php?page=orders" class="btn btn-warning w-100 text-dark">
                            <i class="fas fa-shopping-cart"></i> View Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Menu Items -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Menu Items</h5>
                <a href="index.php?page=menu_items" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT m.*, c.name as category_name 
                                   FROM menu_items m 
                                   JOIN categories c ON m.category_id = c.id 
                                   ORDER BY m.id DESC LIMIT 5";
                            $result = $conn->query($sql);
                            
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['name'] . "</td>";
                                    echo "<td>" . $row['category_name'] . "</td>";
                                    echo "<td>$" . number_format($row['price'], 2) . "</td>";
                                    echo "<td>" . ucfirst($row['food_type']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>No menu items found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>