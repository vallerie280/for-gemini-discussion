<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$loggedInUsername = $_SESSION['username'] ?? 'Guest'; 
$loggedInRole = $_SESSION['role'] ?? 'guest';

$home_link = ($loggedInRole == 'admin') ? '../admin/dashboard.php' : (($loggedInRole == 'member') ? 'home.php' : 'index.php');
$profile_link = ($loggedInRole == 'admin') ? '../admin/profile.php' : (($loggedInRole == 'member') ? 'profile.php' : '#');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furniland - <?php echo $pageTitle ?? 'Project'; ?></title>
    <link rel="stylesheet" href="assets/css/global.css">
    </head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="<?php echo $home_link; ?>">Furniland</a>
        </div>
        <div class="nav-links">
            <?php if ($loggedInRole == 'admin'): ?>
                <a href="./admin/dashboard.php" class="nav-link">Dashboard</a>
            <?php elseif ($loggedInRole == 'member'): ?>
                <a href="home.php" class="nav-link">Home</a>
                <a href="catalog.php" class="nav-link">Catalog</a>
                <a href="transaction_history.php" class="nav-link">History</a>
                <a href="cart.php" class="nav-link">Cart</a>
            <?php else: ?>
                 <a href="<?php echo $home_link; ?>" class="nav-link">Home</a>
                <a href="./catalog.php" class="nav-link">Shop</a>
            <?php endif; ?>
        </div>
        <div class="user-controls">
            <?php if ($loggedInRole == 'guest'): ?>
                <a href="./login.php" class="auth-link btn-login">Login</a>
                <a href="./register.php" class="auth-link btn-register">Register</a>
            <?php else: ?>
                <a href="<?php echo $profile_link; ?>" class="user-greeting">Hello, <?php echo htmlspecialchars($loggedInUsername); ?>!</a>
                <a href="./includes/logout.php" class="logout-link">Logout</a>
            <?php endif; ?>
        </div>
    </nav>
    <main></main>
    </body>