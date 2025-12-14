<?php
include '../includes/connection.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Assume fetch_vendors is defined in connection.php
$vendors = fetch_vendors($conn); 
$pageTitle = 'Add New Product';
$form_action = 'Add Product';
$is_editing = false;
$product = [
    'productName' => '', 
    'description' => '', 
    'price' => '', 
    'vendorID' => '', 
    'image' => ''
];
$error_message = '';
$success_message = '';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productID = $_GET['id'];
    $is_editing = true;
    $pageTitle = 'Edit Product';
    $form_action = 'Update Product';
    
    $stmt = $conn->prepare("SELECT productName, description, price, image, vendorID FROM products WHERE productID = ?");
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
    } else {
        $_SESSION['error_message'] = "Product not found.";
        header("Location: manage_products.php");
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['productName']);
    $description = trim($_POST['description']);
    $price = (float)str_replace(['Rp', '.', ','], '', $_POST['price']);
    $vendorID = (int)$_POST['vendor'];
    
    $product['productName'] = $name;
    $product['description'] = $description;
    $product['price'] = $price;
    $product['vendorID'] = $vendorID;

    if (strlen($name) < 3 || strlen($name) > 30) {
        $error_message = "Product Name must be between 3 and 30 characters.";
    } elseif (empty($description)) {
        $error_message = "Description must be filled.";
    } elseif ($price <= 0) {
        $error_message = "Price must be a numeric value greater than 0.";
    } elseif ($vendorID <= 0) {
        $error_message = "A vendor must be selected.";
    }

    if (empty($error_message)) {
        $image_path = $product['image'];
        $update_image = false;
        
        if (!empty($_FILES['productImage']['name']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK) {
            $image_info = getimagesize($_FILES['productImage']['tmp_name']);
            $mime_type = $image_info['mime'];
            
            if ($mime_type === 'image/jpeg' || $mime_type === 'image/png') {
                $update_image = true;
                $image_path = time() . '_' . basename($_FILES['productImage']['name']);
                $target_dir = "../assets/images/";
                $target_file = $target_dir . $image_path;

                if (!move_uploaded_file($_FILES["productImage"]["tmp_name"], $target_file)) {
                    $error_message = "Error uploading image file.";
                }
            } else {
                $error_message = "Product Image must be a valid .jpg or .png format.";
            }
        }

        if (empty($error_message)) {
            if ($is_editing) {
                if ($update_image) {
                    $sql = "UPDATE products SET productName = ?, description = ?, price = ?, image = ?, vendorID = ? WHERE productID = ?";
                    $types = "ssdsii"; 
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, $name, $description, $price, $image_path, $vendorID, $productID);
                } else {
                    $sql = "UPDATE products SET productName = ?, description = ?, price = ?, vendorID = ? WHERE productID = ?";
                    $types = "sdsii"; 
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, $name, $description, $price, $vendorID, $productID);
                }
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Product updated successfully!";
                    header("Location: manage_products.php");
                    exit();
                } else {
                    $error_message = "Error updating product: " . $stmt->error;
                }
                $stmt->close();
                
            } else {
                if (!$update_image) {
                    $error_message = "Product image must be uploaded.";
                } else {
                    $sql = "INSERT INTO products (productName, description, price, image, vendorID) VALUES (?, ?, ?, ?, ?)";
                    $types = "ssdsi";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, $name, $description, $price, $image_path, $vendorID);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "New product added successfully!";
                        header("Location: manage_products.php");
                        exit();
                    } else {
                        $error_message = "Error adding new product: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/add_edit_product.css">

<div class="form-container">
    <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

    <?php if (!empty($error_message)): ?>
        <div class="error-box"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <form action="add_edit_product.php<?php echo $is_editing ? '?id=' . htmlspecialchars($productID) : ''; ?>" method="POST" enctype="multipart/form-data">
        
        <label for="productName">Product Name</label>
        <input type="text" id="productName" name="productName" value="<?php echo htmlspecialchars($product['productName']); ?>" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>

        <label for="price">Price</label>
        <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>

        <label for="vendor">Vendor</label>
        <select id="vendor" name="vendor" required>
            <option value="">-- Select Vendor --</option>
            <?php foreach ($vendors as $vendor): ?>
                <option value="<?php echo htmlspecialchars($vendor['vendorID']); ?>" 
                        <?php echo ($product['vendorID'] == $vendor['vendorID']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($vendor['vendorName']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="productImage">Product Image (<?php echo $is_editing ? 'Leave blank to keep current' : 'Required'; ?>)</label>
        <input type="file" id="productImage" name="productImage" accept="image/png, image/jpeg">
        
        <?php if ($is_editing && !empty($product['image'])): ?>
            <p class="current-image">Current Image: <?php echo htmlspecialchars($product['image']); ?></p>
        <?php endif; ?>

        <button type="submit"><?php echo htmlspecialchars($form_action); ?></button>
    </form>
</div>

<?php
include '../includes/footer.php';
?>