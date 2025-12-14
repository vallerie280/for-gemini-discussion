<?php
include '../includes/connection.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php"); 
    exit();
}

$current_admin_id = $_SESSION['userID'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $userID_to_delete = $_POST['user_id'];
    if ($userID_to_delete == $current_admin_id) {
        $_SESSION['error'] = "Deletion Failed: You cannot delete your own active Admin account.";
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE userID = ?");
        $delete_stmt->bind_param("i", $userID_to_delete);

        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "User ID #{$userID_to_delete} successfully deleted.";
        } else {
            $_SESSION['error'] = "Error deleting user: " . $delete_stmt->error;
        }
        $delete_stmt->close();
    }

    header("Location: manage_users.php");
    exit();
}

$users = [];
$stmt = $conn->prepare("SELECT userID, username, email, gender, dob, role FROM users ORDER BY role DESC, userID ASC");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();
include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/manage_users.css">

<div class="manage-container">
    <h1 class="page-title">Manage Users</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="table-wrapper">
        <?php if (count($users) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr class="<?php echo $user['userID'] == $current_admin_id ? 'current-admin-row' : ''; ?>">
                        <td><?php echo htmlspecialchars($user['userID']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['gender']); ?></td>
                        <td><?php echo htmlspecialchars($user['dob']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo strtolower($user['role']); ?>">
                                <?php echo htmlspecialchars($user['role']); ?>
                            </span>
                        </td>
                        <td class="action-cells">
                            <?php if ($user['userID'] == $current_admin_id): ?>
                                <span class="action-denied" title="You cannot delete yourself">Current Admin</span>
                            <?php else: ?>
                                <form method="POST" class="delete-form" style="display:inline-block;">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?php echo $user['userID']; ?>">
                                    <button type="submit" class="btn btn-small btn-delete">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No users found in the database.</p>
        <?php endif; ?>
    </div>
</div>

<?php
echo '<script src="../assets/js/manage_users.js"></script>';
include '../includes/footer.php';
?>