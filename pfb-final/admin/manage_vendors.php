<?php
include '../includes/connection.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_vendor') {
    $vendorID_to_delete = $_POST['vendor_id'];
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE vendorID = ?");
    $check_stmt->bind_param("i", $vendorID_to_delete);
    $check_stmt->execute();
    $check_stmt->bind_result($product_count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($product_count > 0) {
        $_SESSION['error'] = "Deletion Failed: Vendor ID #{$vendorID_to_delete} is associated with {$product_count} product(s).";
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM vendors WHERE vendorID = ?");
        $delete_stmt->bind_param("i", $vendorID_to_delete);

        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Vendor ID #{$vendorID_to_delete} successfully deleted.";
        } else {
            $_SESSION['error'] = "Error deleting vendor: " . $delete_stmt->error;
        }
        $delete_stmt->close();
    }
    header("Location: manage_vendors.php");
    exit();
}

$vendors = [];
$stmt = $conn->prepare("SELECT vendorID, vendorName, location FROM vendors ORDER BY vendorID DESC");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $vendors[] = $row;
}
$stmt->close();
include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/manage_vendors.css">

<div class="manage-container">
    <h1 class="page-title">Manage Vendors</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="action-bar">
        <a href="add_edit_vendor.php" class="btn btn-primary">➕ Add New Vendor</a>
    </div>

    <div class="table-wrapper">
        <?php if (count($vendors) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vendor Name</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vendors as $vendor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vendor['vendorID']); ?></td>
                        <td><?php echo htmlspecialchars($vendor['vendorName']); ?></td>
                        <td><?php echo htmlspecialchars($vendor['location']); ?></td>
                        <td class="action-cells">
                            <a href="add_edit_vendor.php?id=<?php echo $vendor['vendorID']; ?>" class="btn btn-small btn-edit">Edit</a>
                            
                            <form method="POST" class="delete-form" style="display:inline-block;">
                                <input type="hidden" name="action" value="delete_vendor">
                                <input type="hidden" name="vendor_id" value="<?php echo $vendor['vendorID']; ?>">
                                <button type="submit" class="btn btn-small btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No vendors found. Click 'Add New Vendor' to get started.</p>
        <?php endif; ?>
    </div>
</div>

<?php
echo '<script src="../assets/js/manage_vendors.js"></script>';
include '../includes/footer.php';
?>