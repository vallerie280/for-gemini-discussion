<?php
include '../includes/connection.php';
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit();
}

$loggedInUsername = $_SESSION['username'] ?? 'Member User';
$loggedInRole = $_SESSION['role'] ?? 'member';

$products = [
    ['name' => 'Felix Accent Armchair', 'price' => '1,950,000', 'vendor' => 'Nordico', 'image' => 'Felix Accent Armchair.png', 'description' => 'Mid-century armchair with soft velvet fabric and gold metal legs.'],
    ['name' => 'Kyra Dining Set (4 Chairs)', 'price' => '3,790,000', 'vendor' => 'Rustika', 'image' => 'Kyra Dining Set (4 Chairs).png', 'description' => 'Stylish wood table with cushioned chairs, perfect for modern homes.'],
    ['name' => 'Zenno Floating Wall Shelf', 'price' => '499,000', 'vendor' => 'Rustika', 'image' => 'Zenno Floating Wall Shelf.png', 'description' => 'Wall-mounted shelf made of engineered wood and easy to install.'],
    ['name' => 'Chae\'s Study Table + Drawer', 'price' => '1,675,000', 'vendor' => 'WoodCraft', 'image' => 'Chaes Study Table Drawer.png', 'description' => 'Study desk with twice side drawer unit and smooth oak finish.'],
    ['name' => 'Verra Minimalist Coffee Table', 'price' => '1,150,000', 'vendor' => 'UrbanNest', 'image' => 'Verra Minimalist Coffee Table.png', 'description' => 'Round table with tempered glass top and matte black steel frame.'],
    ['name' => 'Astra Modular Wardrobe', 'price' => '3,850,000', 'vendor' => 'Rustika', 'image' => 'Astra Modular Wardrobe.png', 'description' => 'Customizable wardrobe with sliding doors and built-in LED lights.'],
];

function format_price($price_str) {
    $numeric_price = (float)str_replace(['.', ','], '', $price_str);
    return number_format($numeric_price, 0, ',', '.');
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/member_home.css">

<div class="home-container">
    <div class="welcome-banner">
        <h1>Welcome back, <?php echo htmlspecialchars($loggedInUsername); ?>!</h1>
        <p>It's great to see you. Check out today's recommended furniture pieces.</p>
    </div>

    <section class="featured-section">
        <h2>Recommended for You</h2>
        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-img"
                         onerror="this.onerror=null; this.src='../assets/images/default.jpg';" 
                    >
                    <div class="card-details">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="vendor-name">by <?php echo htmlspecialchars($product['vendor']); ?></p>
                        <p class="price">Rp <?php echo format_price($product['price']); ?></p>
                        <a href="../product_detail.php?id=<?php echo urlencode($product['name']); ?>" class="btn btn-detail">View Detail</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">We currently don't have any product recommendations for you.</p>
            <?php endif; ?>
        </div>
    </section>
    
    <section class="quick-links">
        <h2>Quick Actions</h2>
        <div class="link-grid">
            <a href="catalog.php" class="link-card">
                <h3>Shop All Furniture</h3>
                <p>Browse the full catalog and find your next piece.</p>
            </a>
            <a href="transaction_history.php" class="link-card">
                <h3>View Order History</h3>
                <p>Track your past purchases and reorder items.</p>
            </a>
            <a href="cart.php" class="link-card">
                <h3>Go to Cart</h3>
                <p>Review and finalize the items in your cart.</p>
            </a>
        </div>
    </section>
</div>

<?php
echo '<script src="../assets/js/member_home.js"></script>';
include '../includes/footer.php';
?>