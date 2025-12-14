<?php
include './includes/connection.php';
if ($_SESSION['role'] !== 'admin') {
    $redirect_url = ($_SESSION['role'] === 'member') ? './member/home.php' : './index.php';
    header("Location: " . $redirect_url);
    exit();
}

$admin_username = $_SESSION['username'] ?? 'Admin User';

$pageTitle = 'Dashboard';
include './includes/header.php';
?>

<link rel="stylesheet" href="./assets/css/dashboard.css">

<div class="dashboard-container">
    <h1 class="page-title">Admin Dashboard</h1>

    <div class="card-grid">
        
        <a href="manage_products.php" class="nav-card">
            <h3>Manage Products</h3>
            <p>View, edit, and add furniture products.</p>
        </a>
        
        <a href="manage_vendors.php" class="nav-card">
            <h3>Manage Vendors</h3>
            <p>View and manage furniture vendors.</p>
        </a>
        
        <a href="manage_users.php" class="nav-card">
            <h3>Manage Users</h3>
            <p>View and control user accounts.</p>
        </a>
        
        <a href="view_transactions.php" class="nav-card">
            <h3>View Transactions</h3>
            <p>Monitor purchase history and details.</p>
        </a>
    </div>

    <div class="welcome-banner">
        <h2>Welcome, Admin <?php echo htmlspecialchars($admin_username); ?>!</h2>
        <p>Use the cards above to manage the platform. Keep track of users, inventory, vendors, and transactions efficiently.</p>
    </div>
</div>

<?php
include './includes/footer.php';
?>