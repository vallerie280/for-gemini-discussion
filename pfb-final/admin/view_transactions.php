<?php
include '../includes/connection.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$transactions = [];
$sql = "SELECT t.transactionID, u.username, t.totalPrice, t.transactionDate 
        FROM transactions t 
        JOIN users u ON t.userID = u.userID
        ORDER BY t.transactionDate DESC, t.transactionID DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/view_transactions.css">

<div class="manage-container">
    <h1 class="page-title">View All Transactions</h1>
    
    <div class="table-wrapper">
        <?php if (count($transactions) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>User (Username)</th>
                        <th>Total Price</th>
                        <th>Transaction Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['transactionID']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                        <td>Rp <?php echo number_format($transaction['totalPrice'], 0, ',', '.'); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($transaction['transactionDate'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No completed transactions found yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php
echo '<script src="../assets/js/view_transactions.js"></script>';
include '../includes/footer.php';
?>