<?php
session_start();
include 'config.php'; 

$user_id = $_SESSION['userID'] ?? null;

if (!$user_id) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($connection)) {
    mysqli_begin_transaction($connection);
    
    $grand_total = 0;
    $cart_details = [];

    $query_cart = "SELECT 
                    p.productID, p.price, 
                    c.quantity 
                  FROM cart c
                  JOIN products p ON c.productID = p.productID
                  WHERE c.userID = ?";
    
    $stmt_cart = mysqli_prepare($connection, $query_cart);
    mysqli_stmt_bind_param($stmt_cart, 'i', $user_id);
    mysqli_stmt_execute($stmt_cart);
    $result_cart = mysqli_stmt_get_result($stmt_cart);

    while ($row = mysqli_fetch_assoc($result_cart)) {
        $subtotal = $row['price'] * $row['quantity'];
        $grand_total += $subtotal;
        
        $cart_details[] = [
            'productID' => $row['productID'],
            'quantity' => $row['quantity'],
            'subTotal' => $subtotal
        ];
    }
    
    mysqli_stmt_close($stmt_cart);

    if (!empty($cart_details)) {
        $date = date("Y-m-d");
        $query_trans = "INSERT INTO transactions (userID, totalPrice, transactionDate) VALUES (?, ?, ?)";
        $stmt_trans = mysqli_prepare($connection, $query_trans);
        mysqli_stmt_bind_param($stmt_trans, 'ids', $user_id, $grand_total, $date);
        
        if (!mysqli_stmt_execute($stmt_trans)) {
            mysqli_rollback($connection);
            die("Error inserting transaction: " . mysqli_error($connection));
        }
        
        $transaction_id = mysqli_insert_id($connection);
        mysqli_stmt_close($stmt_trans);

        $query_details = "INSERT INTO transaction_details (transactionID, productID, quantity, subTotal) VALUES (?, ?, ?, ?)";
        $stmt_details = mysqli_prepare($connection, $query_details);

        foreach ($cart_details as $item) {
            $product_id = $item['productID'];
            $quantity = $item['quantity'];
            $subtotal = $item['subTotal'];

            mysqli_stmt_bind_param($stmt_details, 'iiid', $transaction_id, $product_id, $quantity, $subtotal);
            
            if (!mysqli_stmt_execute($stmt_details)) {
                mysqli_rollback($connection);
                die("Error inserting transaction details: " . mysqli_error($connection));
            }
        }
        mysqli_stmt_close($stmt_details);

        $query_clear_cart = "DELETE FROM cart WHERE userID = ?";
        $stmt_clear_cart = mysqli_prepare($connection, $query_clear_cart);
        mysqli_stmt_bind_param($stmt_clear_cart, 'i', $user_id);

        if (!mysqli_stmt_execute($stmt_clear_cart)) {
            mysqli_rollback($connection);
            die("Error clearing cart: " . mysqli_error($connection));
        }
        mysqli_stmt_close($stmt_clear_cart);

        mysqli_commit($connection);
        header('Location: history.php');
        exit;
    } else {
        header('Location: cart.php');
        exit;
    } 
} else {
    header('Location: cart.php'); 
    exit;
}
?>