<?php
session_start();
include 'config.php'; 

$user_id = $_SESSION['userID'] ?? null;
$user_name = $_SESSION['username'] ?? 'Guest';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity']) && $user_id) {
    
    $product_id = (int)$_POST['product_id'];
    $quantity_added = (int)$_POST['quantity'];

    if ($product_id > 0 && $quantity_added > 0 && isset($connection)) {
        $check_query = "SELECT quantity FROM cart WHERE userID = ? AND productID = ?";
        $stmt_check = mysqli_prepare($connection, $check_query);
        mysqli_stmt_bind_param($stmt_check, 'ii', $user_id, $product_id);
        mysqli_stmt_execute($stmt_check);
        $result = mysqli_stmt_get_result($stmt_check);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            $new_quantity = $row['quantity'] + $quantity_added;
            $update_query = "UPDATE cart SET quantity = ? WHERE userID = ? AND productID = ?";
            $stmt_update = mysqli_prepare($connection, $update_query);
            mysqli_stmt_bind_param($stmt_update, 'iii', $new_quantity, $user_id, $product_id);
            mysqli_stmt_execute($stmt_update);
        } else {
            $insert_query = "INSERT INTO cart (userID, productID, quantity) VALUES (?, ?, ?)";
            $stmt_insert = mysqli_prepare($connection, $insert_query);
            mysqli_stmt_bind_param($stmt_insert, 'iii', $user_id, $product_id, $quantity_added);
            mysqli_stmt_execute($stmt_insert);
        }
        header('Location: cart.php'); 
        exit;
    }
}

function formatRupiah($angka) {
    return 'Rp.' . number_format($angka, 0, ',', '.');
}

$cart_data = []; 

if ($user_id && isset($conn)) {
    $query = "SELECT 
                p.productID, p.productName, p.price, 
                c.quantity 
              FROM cart c
              JOIN products p ON c.productID = p.productID
              WHERE c.userID = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $qty = $row['quantity'];
        $price = $row['price'];
        
        $cart_data[] = [
            'id' => $row['productID'],
            'name' => $row['productName'],
            'price' => $price,
            'qty' => $qty,
            'subtotal' => $price * $qty
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Cart Page</title>
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

    <main class="cart-main-content">
        <div class="cart-container">
            <h1 class="cart-title">Your Cart</h1>
            <?php if (empty($cart_data)) : ?>
                <p class="empty-message"><i>Your cart is empty.</i></p>
            <?php else : ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        foreach ($cart_data as $item) : 
                            $grand_total += $item['subtotal'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['qty']); ?></td>
                            <td><?php echo formatRupiah($item['subtotal']); ?></td>
                            <td>
                                <a href="remove_cart.php?id=<?php echo htmlspecialchars($item['id']); ?>" class="remove-btn">Remove</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-summary">
                    <div class="grand-total">
                        <strong>Grand Total: <?php echo formatRupiah($grand_total); ?></strong>
                    </div>
                    <a href="checkout.php" class="checkout-btn">Checkout</a>
                </div>

            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="footer-cart">
            <p>© 2025 Furniland. All rights reserved.</p>
            <p>Contact us at <a href="./catalog.php">furniland.support@gmail.com</a></p>
        </div>
    </footer>


<?php 
if (isset($conn)) {
    mysqli_close($conn);
}
?>

</body>
</html>