<?php
session_start(); 
include 'config.php'; 

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = $_SESSION['userID'];
$errors = array(); 
$success_message = '';

function fetchUserData($connection, $userID) {
    $query = "SELECT username, email, gender, dob, role FROM users WHERE userID = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $userID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $user;
    }
    return false;
}

$user_data = fetchUserData($connection, $userID);

if (!$user_data) {
    $errors['general'] = "Gagal mengambil data pengguna. Database Error.";
}

$username = $user_data['username'] ?? '';
$email = $user_data['email'] ?? '';
$gender = $user_data['gender'] ?? '';
$dob = $user_data['dob'] ?? '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_profile'])) {
        $new_username = trim($_POST['username']);
        $new_email = trim($_POST['email']);
        $new_gender = $_POST['gender'];
        $new_dob = $_POST['dob'];
        $errors_profile = array(); 

        if (empty($new_username)) {
            $errors_profile['username'] = "Username harus diisi!";
        } elseif (strlen($new_username) < 4 || strlen($new_username) > 20) { 
            $errors_profile['username'] = "Minimal 4 dan maksimal 20 karakter";
        }
        
        if (empty($new_email)) {
            $errors_profile['email'] = "Email harus diisi!";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors_profile['email'] = "Format email tidak valid";
        } elseif (!str_ends_with($new_email, '@gmail.com')) {
            $errors_profile['email'] = "Email harus diakhiri dengan '@gmail.com'";
        }

        if (empty($new_gender)) {
            $errors_profile['gender'] = "Gender harus dipilih!";
        }

        if (empty($new_dob)) {
            $errors_profile['dob'] = 'Date of birth harus diisi!';
        } else {
            $today = new DateTime();
            $birth_date = new DateTime($new_dob);
            if ($birth_date >= $today) {
                $errors_profile['dob'] = "Tanggal lahir tidak valid";
            }
        }
        
        if (empty($errors_profile)) {
            $query_check = "SELECT userID FROM users WHERE (username = ? OR email = ?) AND userID != ?";
            $stmt_check = mysqli_prepare($connection, $query_check);

            if ($stmt_check) {
                mysqli_stmt_bind_param($stmt_check, 'ssi', $new_username, $new_email, $userID);
                mysqli_stmt_execute($stmt_check);
                mysqli_stmt_store_result($stmt_check);

                if (mysqli_stmt_num_rows($stmt_check) > 0){
                    $errors_profile['general'] = "Username atau Email sudah terdaftar oleh pengguna lain.";
                }
                mysqli_stmt_close($stmt_check);
            }
        }

        if (empty($errors_profile)) {
            $query_update = "UPDATE users SET username = ?, email = ?, gender = ?, dob = ? WHERE userID = ?";
            $stmt_update = mysqli_prepare($connection, $query_update);

            if ($stmt_update) {
                mysqli_stmt_bind_param($stmt_update, "ssssi", $new_username, $new_email, $new_gender, $new_dob, $userID);
                
                if (mysqli_stmt_execute($stmt_update)) {
                    $_SESSION['username'] = $new_username; 
                    $success_message = "Profil berhasil diperbarui!";
                    $user_data = fetchUserData($connection, $userID);
                    $username = $user_data['username'];
                    $email = $user_data['email'];
                    $gender = $user_data['gender'];
                    $dob = $user_data['dob'];
                } else {
                    $errors_profile['general'] = "Gagal memperbarui profil. Coba lagi.";
                }
                mysqli_stmt_close($stmt_update);
            } else {
                $errors_profile['general'] = "Database error (Gagal menyiapkan UPDATE).";
            }
        }
        $errors = array_merge($errors, $errors_profile); 
    }

    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirmPassword'];
        $errors_password = array();

        $query_hash = "SELECT password_user FROM users WHERE userID = ?";
        $stmt_hash = mysqli_prepare($connection, $query_hash);

        if ($stmt_hash) {
            mysqli_stmt_bind_param($stmt_hash, 'i', $userID);
            mysqli_stmt_execute($stmt_hash);
            $result_hash = mysqli_stmt_get_result($stmt_hash);
            $hash_data = mysqli_fetch_assoc($result_hash);
            mysqli_stmt_close($stmt_hash);
            $current_hash = $hash_data['password_user'] ?? ''; 

            if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
                $errors_password['general'] = "Semua field password harus diisi.";
            } else {
                if (!password_verify($current_password, $current_hash)) {
                    $errors_password['current_password'] = "Current Password salah.";
                }
                
                if (strlen($new_password) < 8) {
                    $errors_password['new_password'] = "Password baru minimal 8 karakter";
                }
                if ($new_password !== $confirm_new_password) {
                    $errors_password['confirmPassword'] = "Konfirmasi password baru tidak cocok.";
                }
            }
        
            if (empty($errors_password)) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $query_update_pass = "UPDATE users SET password_user = ? WHERE userID = ?";
                $stmt_update_pass = mysqli_prepare($connection, $query_update_pass);

                if ($stmt_update_pass) {
                    mysqli_stmt_bind_param($stmt_update_pass, "si", $hashed_new_password, $userID);

                    if (mysqli_stmt_execute($stmt_update_pass)) {
                        $success_message = "Password berhasil diperbarui.";
                    } else {
                        $errors_password['general'] = "Gagal memperbarui password. Coba lagi.";
                    }
                    mysqli_stmt_close($stmt_update_pass);
                } else {
                    $errors_password['general'] = "Database error (Gagal menyiapkan UPDATE password).";
                }
            }
        } else {
            $errors_password['general'] = "Database error saat mengambil hash.";
        }
        $errors = array_merge($errors, $errors_password);
    }

    if (isset($_POST['delete_account'])) {
        $delete_password = $_POST['delete_password'];
        $errors_delete = array();

        $query_hash = "SELECT password_user FROM users WHERE userID = ?";
        $stmt_hash = mysqli_prepare($connection, $query_hash);
        
        if ($stmt_hash) {
            mysqli_stmt_bind_param($stmt_hash, 'i', $userID);
            mysqli_stmt_execute($stmt_hash);
            $result_hash = mysqli_stmt_get_result($stmt_hash);
            $hash_data = mysqli_fetch_assoc($result_hash);
            mysqli_stmt_close($stmt_hash);
            $current_hash = $hash_data['password_user'] ?? '';
            
            if (empty($delete_password)) {
                $errors_delete['delete_password'] = "Password harus diisi untuk konfirmasi.";
            } elseif (!password_verify($delete_password, $current_hash)) {
                $errors_delete['delete_password'] = "Password konfirmasi salah.";
            }

            if (empty($errors_delete)) {
                $query_delete = "DELETE FROM users WHERE userID = ?";
                $stmt_delete = mysqli_prepare($connection, $query_delete);

                if ($stmt_delete) {
                    mysqli_stmt_bind_param($stmt_delete, "i", $userID);
                    
                    if (mysqli_stmt_execute($stmt_delete)) {
                        $_SESSION = array();
                        if (ini_get("session.use_cookies")) {
                            $params = session_get_cookie_params();
                            setcookie(session_name(), '', time() - 42000,
                                $params["path"], $params["domain"],
                                $params["secure"], $params["httponly"]
                            );
                        }
                        setcookie('remember_user_id', '', time() - 3600, "/");
                        setcookie('remember_username', '', time() - 3600, "/");
                        session_destroy();

                        header('Location: login.php?status=account_deleted');
                        exit;
                    } else {
                        $errors_delete['general'] = "Gagal menghapus akun. Coba lagi.";
                    }
                    mysqli_stmt_close($stmt_delete);
                } else {
                    $errors_delete['general'] = "Database error (Gagal menyiapkan DELETE).";
                }
            }
        } else {
            $errors_delete['general'] = "Database error saat mengambil hash.";
        }
        $errors = array_merge($errors, $errors_delete);
    }
}
mysqli_close($connection);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
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
                    <a href="./profile.php">History</a>
                    <a class="logout" href="./logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="isi-profile">
        <h2>Your Profile</h2>
        <div class="update-profile">
            <form action="./profile.php" method="post">
                <h4>Update Profile Information</h4>
                <div class="username">
                    <br>
                    <label for="username">Username</label>
                    <br>
                    <input type="text" name="username" id="username" placeholder="Enter your username" class="input" value="<?php echo htmlspecialchars($username); ?>">
                    <span id="error-username" style="color: red;" class="error"><?php echo $errors['username'] ?? ''; ?></span>
                </div>
                <div class="email">
                    <br>
                    <label for="email">Email</label>
                    <br>
                    <input type="text" name="email" id="email" placeholder="Enter your email" class="input" value="<?php echo htmlspecialchars($email); ?>">
                    <span id="error-email" style="color: red;"><?php echo $errors['email'] ?? ''; ?></span>
                </div>
                <div class="gender">
                    <br>
                    <label for="gender">Gender</label>
                    <br>
                    <select name="gender" id="gender" class="input">
                        <option value="">--- Gender ---</option>
                        <option value="Male" <?php echo ($gender === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($gender === 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                    <span id="error-gender" style="color: red;"><?php echo $errors['gender'] ?? ''; ?></span>
                </div>
                <div class="dob">
                    <br>
                    <label for="dob">Date Of Birth</label>
                    <br>
                    <input type="date" name="dob" id="dob" class="input" value="<?php echo htmlspecialchars($dob); ?>">
                    <span id="error-dob" style="color: red;" class="error"><?php echo $errors['dob'] ?? ''; ?></span>
                </div>
                <div class="button">
                    <button type="submit" name="save_profile">Save Changes</button>
                </div>
            </form>
        </div>


        <div class="update-password">
            <form action="./profile.php" method="post">
                <h4>Update Password</h4>
                <div class="current-password">
                    <br>
                    <label for="current-password">Current Password</label>
                    <br>
                    <input type="password" name="current_password" id="current-password" placeholder="Input your current password" class="input">
                    <span id="error-current-password" style="color: red;" class="error"><?php echo $errors['current_password'] ?? ''; ?></span>
                </div>
                <div class="new-password">
                    <br>
                    <label for="new-password">New Password</label>
                    <br>
                    <input type="password" name="new_password" id="new-password" placeholder="Create new password" class="input">
                    <span id="error-new-password" style="color: red;" class="error"><?php echo $errors['new_password'] ?? ''; ?></span>
                </div>
                <div class="confirmPassword">
                    <br>
                    <label for="confirmPassword">Confirm New Password</label>
                    <br>
                    <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm your new password" class="input">
                    <span id="error-confirm-password" style="color: red;" class="error"><?php echo $errors['confirmPassword'] ?? ''; ?></span>
                </div>

                <div class="button">
                    <button type="submit" name="update_password">Update Password</button>
                </div>
            </form>
        </div>


        <div class="delete-profile">
            <form action="./profile.php" method="post">
                <h4>Delete Account</h4>
                <p>Once deleted, your account cannot be recovered.</p>
                <div class="delete-password">
                    <label for="delete-password">Confirm Password</label>
                    <br>
                    <input type="password" name="delete_password" id="delete-password" placeholder="Confirm your password" class="input">
                    <span id="error-delete-password" style="color: red;" class="error"><?php echo $errors['delete_password'] ?? ''; ?></span>
                </div>
                <div class="button">
                    <button type="submit" name="delete_account">Delete Account</button>
                </div>
            </form>
        </div>

    </div>

    <footer>
        <div class="footer">
            <p>© 2025 Furniland. All rights reserved.</p>
            <p>Contact us at <a href="./profile.php">furniland.support@gmail.com</a></p>
        </div>
    </footer>

</body>
</html>