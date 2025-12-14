<?php
include '../includes/connection.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$vendorID = $_GET['id'] ?? null;
$isEditing = $vendorID !== null;
$pageTitle = $isEditing ? 'Edit Vendor' : 'Add New Vendor';

$vendorName = '';
$location = '';
$errors = [];

if ($isEditing) {
    $stmt = $conn->prepare("SELECT vendorName, location FROM vendors WHERE vendorID = ?");
    $stmt->bind_param("i", $vendorID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $vendorData = $result->fetch_assoc();
        $vendorName = $vendorData['vendorName'];
        $location = $vendorData['location'];
    } else {
        $_SESSION['error'] = "Vendor ID #{$vendorID} not found.";
        header("Location: manage_vendors.php");
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vendorName = trim($_POST['vendor_name']);
    $location = trim($_POST['location']);

    if (empty($vendorName)) {
        $errors['vendor_name'] = 'Vendor Name is required.';
    } elseif (strlen($vendorName) > 20) {
        $errors['vendor_name'] = 'Vendor Name must be 20 characters or less.';
    }

    if (empty($location)) {
        $errors['location'] = 'Location is required.';
    } elseif (strlen($location) > 100) {
        $errors['location'] = 'Location must be 100 characters or less.';
    }

    if (empty($errors)) {
        if ($isEditing) {
            $stmt = $conn->prepare("UPDATE vendors SET vendorName = ?, location = ? WHERE vendorID = ?");
            $stmt->bind_param("ssi", $vendorName, $location, $vendorID);
            $successMessage = "Vendor '{$vendorName}' updated successfully!";
        } else {
            $stmt = $conn->prepare("INSERT INTO vendors (vendorName, location) VALUES (?, ?)");
            $stmt->bind_param("ss", $vendorName, $location);
            $successMessage = "New vendor '{$vendorName}' added successfully!";
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = $successMessage;
            header("Location: manage_vendors.php"); 
            exit();
        } else {
            $errors['db_error'] = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/add_edit_vendor.css">

<div class="form-container">
    <h1 class="page-title"><?php echo $pageTitle; ?></h1>

    <?php if (isset($errors['db_error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($errors['db_error']); ?></div>
    <?php endif; ?>

    <form method="POST" class="vendor-form">
        <?php if ($isEditing): ?>
            <input type="hidden" name="vendor_id" value="<?php echo htmlspecialchars($vendorID); ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="vendor_name">Vendor Name:</label>
            <input 
                type="text" 
                id="vendor_name" 
                name="vendor_name" 
                value="<?php echo htmlspecialchars($vendorName); ?>" 
                maxlength="20"
                required>
            <?php if (isset($errors['vendor_name'])): ?>
                <span class="error-message"><?php echo htmlspecialchars($errors['vendor_name']); ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="location">Location:</label>
            <input 
                type="text" 
                id="location" 
                name="location" 
                value="<?php echo htmlspecialchars($location); ?>" 
                maxlength="100"
                required>
            <?php if (isset($errors['location'])): ?>
                <span class="error-message"><?php echo htmlspecialchars($errors['location']); ?></span>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo $isEditing ? 'Save Changes' : 'Add Vendor'; ?></button>
            <a href="manage_vendors.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
echo '<script src="../assets/js/add_edit_vendor.js"></script>';
include '../includes/footer.php';
?>