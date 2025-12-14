<?php
session_start();
include 'config.php';

// Pastikan koneksi tersedia
if (!isset($conn) || !$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['username'] ?? 'User';

$vendors = [];
$query_vendors = "SELECT vendorID, vendorName FROM vendors ORDER BY vendorName ASC";
$result_vendors = mysqli_query($conn, $query_vendors);
if ($result_vendors) {
    while ($row = mysqli_fetch_assoc($result_vendors)) {
        $vendors[] = $row;
    }
}

$search_term = $_GET['search'] ?? '';
$filter_vendor_id = $_GET['vendor'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'name_asc'; 
$where_clauses = [];
$bind_params = '';
$bind_values = [];

$query_products = "SELECT 
                    p.productID, p.productName, p.description, p.price, p.image, 
                    v.vendorName 
                   FROM products p
                   JOIN vendors v ON p.vendorID = v.vendorID";

if (!empty($search_term)) {
    $search_safe = '%' . $search_term . '%'; 
    $where_clauses[] = "(p.productName LIKE ? OR p.description LIKE ?)";
    $bind_params .= 'ss';
    $bind_values[] = $search_safe;
    $bind_values[] = $search_safe;
}

if (!empty($filter_vendor_id) && is_numeric($filter_vendor_id)) {
    $where_clauses[] = "p.vendorID = ?";
    $bind_params .= 'i';
    $bind_values[] = (int)$filter_vendor_id;
}

if (!empty($where_clauses)) {
    $query_products .= " WHERE " . implode(" AND ", $where_clauses);
}

$order_by_clause = "p.productID ASC"; 

switch ($sort_by) {
    case 'name_asc':
        $order_by_clause = "p.productName ASC";
        break;
    case 'name_desc':
        $order_by_clause = "p.productName DESC";
        break;
    case 'price_asc':
        $order_by_clause = "p.price ASC";
        break;
    case 'price_desc':
        $order_by_clause = "p.price DESC";
        break;
    default:
        $order_by_clause = "p.productID ASC";
        break;
}

$query_products .= " ORDER BY " . $order_by_clause;

$products = [];
$error_message = '';
$result = false;

if (empty($bind_values)) {
    // Eksekusi query tanpa parameter
    $result = mysqli_query($connection, $query_products);
} else {
    // Eksekusi menggunakan Prepared Statement (dengan search/filter)
    $stmt = mysqli_prepare($connection, $query_products);
    if ($stmt) {
        // PERHATIAN: Pastikan ini adalah mysqli_stmt_bind_param (tanpa dobel 'bind')
        mysqli_stmt_bind_param($stmt, $bind_params, ...$bind_values);
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    } else {
         $error_message = "Prepared Statement Error: " . mysqli_error($connection);
    }
}

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    } else {
        $error_message = "Tidak ada produk yang tersedia dengan kriteria pencarian/filter tersebut.";
    }
} else if (empty($error_message)) {
    $error_message = "Database Error: " . mysqli_error($connection);
}


// Fungsi untuk format Rupiah
function formatRupiah($angka) {
    return 'Rp.' . number_format($angka, 0, ',', '.');
}

// Tutup koneksi di akhir skrip
mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog Page</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-profile">
            <div class="profile-logo">
                <a href="./catalog.php">Furniland</a> 
            </div>
            <div class="profile-navigation">
                <div class="profile1">
                    <a href="./catalog.php">Home</a>
                    <a href="./catalog.php">Catalog</a> 
                </div>
                <div class="profile2">
                    <a class="hello" href="./profile.php">Hello, <?php echo htmlspecialchars($username); ?></a>
                    <a href="./cart.php">Cart</a>
                    <a href="./history.php">History</a>
                    <a class="logout" href="./logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="body-content">
        <div class="header-body">
            <h2>Product Catalog</h2>
            <p>Browse, filter, and sort furniture</p>
        </div>

        <div class="filter-container">
            <form action="catalog.php" method="GET" class="filter-form">
                <div class="filter1">
                    <select name="vendor" class="vendor-select">
                        <option value="">All Vendors</option>
                        <?php foreach ($vendors as $vendor) : ?>
                            <option value="<?php echo htmlspecialchars($vendor['vendorID']); ?>"
                                <?php echo ((string)$filter_vendor_id === (string)$vendor['vendorID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($vendor['vendorName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="sort_by" class="sort-select">
                        <option value="name_asc" <?php echo ($sort_by === 'name_asc') ? 'selected' : ''; ?>>Sort by name (A-Z)</option>
                        <option value="name_desc" <?php echo ($sort_by === 'name_desc') ? 'selected' : ''; ?>>Sort by name (Z-A)</option>
                        <option value="price_asc" <?php echo ($sort_by === 'price_asc') ? 'selected' : ''; ?>>Sort by price (Low)</option>
                        <option value="price_desc" <?php echo ($sort_by === 'price_desc') ? 'selected' : ''; ?>>Sort by price (High)</option>
                        <option value="id_asc" <?php echo ($sort_by === 'id_asc') ? 'selected' : ''; ?>>Sort: Default (Newest)</option>
                    </select>

                    <button type="submit" class="apply-btn">Apply</button>
                    
                    <a href="catalog.php" class="reset-btn">Reset</a>   
                </div>

                <div class="filter2">
                    <input type="text" name="search" placeholder="Search products" 
                        value="<?php echo htmlspecialchars($search_term); ?>" class="search-input">
                
                </div>
            
            </form>
        </div>
        <div class="products-container">
            
            <?php 
            if (!empty($error_message)) : 
            ?>
                <p style="text-align: center; color: red; margin-top: 50px;"><?php echo htmlspecialchars($error_message); ?></p>
            <?php 
            elseif (!empty($products)) : 
                foreach ($products as $product) :
            ?>

            <div class="product-item">
                <img src="./assets/<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['productName']); ?>" 
                     class="img-product">
                
                <h3><?php echo htmlspecialchars($product['productName']); ?></h3>
                
                <p class="vendor-name">Vendor: <?php echo htmlspecialchars($product['vendorName']); ?></p> 
                
                <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>
                <h4><?php echo formatRupiah($product['price']); ?></h4>
                
                <div class="view-detail">
                    <a href="./product_detail.php?id=<?php echo $product['productID']; ?>">
                        <button>View Detail</button>
                    </a>
                </div>
            </div>
            <?php 
                endforeach; 
            endif;
            ?>
        </div>
    </div>
    <footer>
        <div class="footer">
            <p>© 2025 Furniland. All rights reserved.</p>
            <p>Contact us at <a href="./catalog.php">furniland.support@gmail.com</a></p>
        </div>
    </footer>
    
</body>
</html>