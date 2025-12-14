<?php
session_start();
include 'config.php'; 

$user_id = $_SESSION['userID'] ?? null;
$product_id = $_GET['id'] ?? null;

if ($user_id && $product_id && is_numeric($product_id) && isset($connection)) {
    
    $product_id = (int)$product_id;
    $user_id = (int)$user_id;
    $query = "DELETE FROM cart WHERE userID = ? AND productID = ?";
    $stmt = mysqli_prepare($connection, $query);
    
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $product_id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Product successfully removed from cart.";
    } else {
        $message = "Error removing product: " . mysqli_error($connection);
    }
    mysqli_stmt_close($stmt);

} else {
    $message = "Error: Invalid request or user not logged in.";
}


if (isset($connection)) {
    mysqli_close($connection);
}
header("Location: cart.php");
exit;
?>