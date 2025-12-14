<?php
include './includes/connection.php'; 

$pageTitle = 'Home';
$products = [];
$error_message = '';

if (isset($_SESSION['username'])) {
    $greeting = "Welcome back, " . htmlspecialchars($_SESSION['username']) . "!";
} else {
    $greeting = "Welcome to Furniland!";
}

try {
    $sql = "SELECT 
                p.productID, 
                p.productName, 
                p.price, 
                p.image, 
                v.vendorName
            FROM 
                products p
            JOIN 
                vendors v ON p.vendorID = v.vendorID
            ORDER BY 
                RAND() 
            LIMIT 6";

    $result = $conn->query($sql);

    if ($result) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $error_message = "Error fetching products: " . $conn->error;
    }

} catch (Exception $e) {
    $error_message = "An error occurred: " . $e->getMessage();
}

function formatRupiah($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

include './includes/header.php';
?>

<link rel="stylesheet" href="assets/css/index.css">

<main>
    <div class="hero-section">
        <h1><?php echo htmlspecialchars($greeting); ?></h1>
        <p>It's great to see you. Check out today's recommended furniture pieces.</p>
    </div>

    <h2 class="featured-title">Recommended for You</h2>

    <?php if (!empty($error_message)): ?>
        <div class="error-box"><?php echo htmlspecialchars($error_message); ?></div>
    <?php elseif (empty($products)): ?>
        <p style="text-align: center; color: var(--text-muted);">No products currently available.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['productName']); ?>">
                    <div class="product-info">
                        <a href="product_detail.php?id=<?php echo htmlspecialchars($product['productID']); ?>">
                            <?php echo htmlspecialchars($product['productName']); ?>
                        </a>
                        <span class="product-vendor">by <?php echo htmlspecialchars($product['vendorName']); ?></span>
                        <span class="product-price"><?php echo formatRupiah($product['price']); ?></span>
                        <a href="product_detail.php?id=<?php echo htmlspecialchars($product['productID']); ?>" class="btn-detail">View Detail</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php
include 'includes/footer.php';
?>