<?php
include '../includes/connection.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $productID_to_delete = $_POST['product_id'];
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM transaction_details WHERE productID = ?");
    $check_stmt->bind_param("i", $productID_to_delete);
    $check_stmt->execute();
    $check_stmt->bind_result($transaction_count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($transaction_count > 0) {
        $_SESSION['error'] = "Deletion Failed: Product ID #{$productID_to_delete} is associated with {$transaction_count} transaction detail(s).";
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM products WHERE productID = ?");
        $delete_stmt->bind_param("i", $productID_to_delete);

        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Product ID #{$productID_to_delete} successfully deleted.";
        } else {
            $_SESSION['error'] = "Error deleting product: " . $delete_stmt->error;
        }
        $delete_stmt->close();
    }
    header("Location: manage_products.php");
    exit();
}

$vendors = [];
$vendor_stmt = $conn->prepare("SELECT vendorID, vendorName FROM vendors ORDER BY vendorName ASC");
$vendor_stmt->execute();
$vendor_result = $vendor_stmt->get_result();
while ($row = $vendor_result->fetch_assoc()) {
    $vendors[] = $row;
}
$vendor_stmt->close();
$search = $_GET['search'] ?? '';
$vendorFilter = $_GET['vendor_filter'] ?? '';
$params = [];
$types = '';

$sql = "SELECT p.productID, p.productName, p.price, p.image, v.vendorName 
        FROM products p 
        JOIN vendors v ON p.vendorID = v.vendorID
        WHERE 1=1"; 

if (!empty($search)) {
    $sql .= " AND p.productName LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= 's';
}

if (!empty($vendorFilter) && $vendorFilter !== 'all') {
    $sql .= " AND p.vendorID = ?";
    $params[] = $vendorFilter;
    $types .= 'i';
}

$sql .= " ORDER BY p.productID DESC";

$product_stmt = $conn->prepare($sql);

if (!empty($params)) {
    $product_stmt->bind_param($types, ...$params);
}

$product_stmt->execute();
$products = $product_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$product_stmt->close();

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/manage_products.css">

<div class="manage-container">
    <h1 class="page-title">Manage Products</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="action-bar">
        <a href="add_edit_product.php" class="btn btn-primary">➕ Add New Product</a>
    </div>

    <form method="GET" class="filter-form">
        <div class="search-group">
            <input type="text" name="search" placeholder="Search by Product Name..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        
        <div class="filter-group">
            <label for="vendor_filter">Filter by Vendor:</label>
            <select name="vendor_filter" id="vendor_filter">
                <option value="all">All Vendors</option>
                <?php foreach ($vendors as $vendor): ?>
                    <option value="<?php echo $vendor['vendorID']; ?>" 
                            <?php echo $vendorFilter == $vendor['vendorID'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($vendor['vendorName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Apply</button>
        <?php if (!empty($search) || !empty($vendorFilter)): ?>
            <a href="manage_products.php" class="btn btn-cancel">Clear</a>
        <?php endif; ?>
    </form>

    <div class="table-wrapper">
        <?php if (count($products) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Vendor</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td class="product-image-cell">
                            <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['productName']); ?>" 
                                 class="product-thumbnail"
                                 onerror="this.onerror=null; this.src='../assets/images/default.jpg';" 
                            >
                        </td>
                        <td><?php echo htmlspecialchars($product['productName']); ?></td>
                        <td><?php echo htmlspecialchars($product['vendorName']); ?></td>
                        <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                        <td class="action-cells">
                            <a href="add_edit_product.php?id=<?php echo $product['productID']; ?>" class="btn btn-small btn-edit">Edit</a>
                            
                            <form method="POST" class="delete-form" style="display:inline-block;">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?php echo $product['productID']; ?>">
                                <button type="submit" class="btn btn-small btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No products found matching the criteria. <?php echo (!empty($search) || !empty($vendorFilter)) ? 'Try clearing the search/filter.' : 'Click "Add New Product" to get started.'; ?></p>
        <?php endif; ?>
    </div>
</div>

<?php
echo '<script src="../assets/js/manage_products.js"></script>';
include '../includes/footer.php';
?>