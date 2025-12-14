<?php
session_start();
include 'config.php';


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: catalog.php');
    exit;
}

$productID = $_GET['id'];
$product = null;

$query = "SELECT 
             p.*, 
             v.vendorName,
             v.location
           FROM products p
           JOIN vendors v ON p.vendorID = v.vendorID
           WHERE p.productID = ?";

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "i", $productID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 1) {
    $product = mysqli_fetch_assoc($result);
} else {
    $error_message = "Produk tidak ditemukan.";
}

mysqli_close($connection);

function formatRupiah($angka) {
    return 'Rp.' . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Detail Produk: <?php echo htmlspecialchars($product['productName'] ?? 'Tidak Ditemukan'); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    
    <header>
        <div class="header-profile">
            <div class="profile-logo">
                <a href="./profile.php">Furniland</a>
            </div>
            <div class="profile-navigation">
                <div class="profile1">
                    <a href="./catalog.php">Home</a>
                    <a href="./catalog.php">Catalog</a> 
                </div>
                <div class="profile2">
                    <a class="hello" href="./profile.php">Hello, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></a>
                    <a href="./cart.php">Cart</a>
                    <a href="./history.php">History</a>
                    <a class="logout" href="./logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <?php if ($product) : ?>
    <div class="product-detail-container">
        <img src="./assets/<?php echo htmlspecialchars($product['image']); ?>" alt="..." class="detail-img">
        
        <div class="detail">
            <h2><?php echo htmlspecialchars($product['productName']); ?></h2>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <h4><?php echo formatRupiah($product['price']); ?></h4>

            <p class="vendor-info">
            Vendor: <?php echo htmlspecialchars($product['vendorName']); ?>
            <br>
            <br>
            Location: <?php echo htmlspecialchars($product['location']); ?>
            </p>
            
            <form method="POST" action="cart.php">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" class="quantity-container">
                
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productID); ?>">
                <br>
                <br>
                <button type="submit" class="add-to-cart">Add to Cart</button>
            </form>
        </div>
    </div>
    
    <?php else : ?>
        <p style="text-align: center; color: red; padding: 50px;"><?php echo htmlspecialchars($error_message ?? 'Produk tidak valid.'); ?></p>
    <?php endif; ?>

    <footer>
        <div class="footer">
            <p>© 2025 Furniland. All rights reserved.</p>
            <p>Contact us at <a href="mailto:furniland.support@gmail.com">furniland.support@gmail.com</a></p>
        </div>
    </footer>

</body>
</html>