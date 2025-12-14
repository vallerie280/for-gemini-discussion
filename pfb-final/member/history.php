<?php
session_start();
include 'config.php';

$user_id = $_SESSION['userID'] ?? null;
$username = $_SESSION['username'] ?? 'Guest';

if (!$user_id) {
    header('Location: login.php');
    exit;
}

function formatRupiah($angka) {
    return 'Rp.' . number_format($angka, 0, ',', '.');
}

$transaction_history = [];

if (isset($connection)) {
    
    $query_trans = "SELECT transactionID, totalPrice, transactionDate FROM transactions WHERE userID = ? ORDER BY transactionDate DESC, transactionID DESC";
    $stmt_trans = mysqli_prepare($connection, $query_trans);
    mysqli_stmt_bind_param($stmt_trans, 'i', $user_id);
    mysqli_stmt_execute($stmt_trans);
    $result_trans = mysqli_stmt_get_result($stmt_trans);

    while ($trans_row = mysqli_fetch_assoc($result_trans)) {
        $transaction_id = $trans_row['transactionID'];
        
        $transaction_history[$transaction_id] = [
            'totalPrice' => formatRupiah($trans_row['totalPrice']),
            'date' => date('d M Y', strtotime($trans_row['transactionDate'])),
            'details' => []
        ];

        $query_details = "SELECT 
                            td.quantity, td.subTotal, 
                            p.productName 
                          FROM transaction_details td
                          JOIN products p ON td.productID = p.productID
                          WHERE td.transactionID = ?";
        
        $stmt_details = mysqli_prepare($connection, $query_details);
        mysqli_stmt_bind_param($stmt_details, 'i', $transaction_id);
        mysqli_stmt_execute($stmt_details);
        $result_details = mysqli_stmt_get_result($stmt_details);

        while ($detail_row = mysqli_fetch_assoc($result_details)) {
            $transaction_history[$transaction_id]['details'][] = [
                'productName' => $detail_row['productName'],
                'quantity' => $detail_row['quantity'],
                'subTotal' => formatRupiah($detail_row['subTotal'])
            ];
        }
        mysqli_stmt_close($stmt_details);
    }
    mysqli_stmt_close($stmt_trans);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Transaction History</title>
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
                    <a href="./history.php">History</a> <a class="logout" href="./logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="history-main-content">
        <div class="history-container">
            <h1 style="text-align: center; margin-bottom: 40px;">Transaction History</h1>
            
            <?php if (empty($transaction_history)) : ?>
                <i><p style="text-align: center; font-size: 1.2em;">You haven't made any transactions yet.</p><i>
            <?php else : ?>
                
                <?php foreach ($transaction_history as $id => $transaction) : ?>
                    <div class="transaction-block">
                        <div class="transaction-header">
                            <div>
                                <div class="transaction-id">Transaction ID: <?php echo $id; ?></div>
                                <div class="transaction-date">Date: <?php echo $transaction['date']; ?></div>
                            </div>
                            <div class="transaction-total">Total: <?php echo $transaction['totalPrice']; ?></div>
                        </div>

                        <table class="details-table">
                            <thead>
                                <tr>
                                    <th style="color: white;">Product</th>
                                    <th style="color: white;">Qty</th>
                                    <th style="color: white;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transaction['details'] as $detail) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($detail['productName']); ?></td>
                                        <td><?php echo htmlspecialchars($detail['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($detail['subTotal']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>

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
if (isset($connection)) {
    mysqli_close($connection);
}
?>
</body>
</html>