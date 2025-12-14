<?php
session_start(); 
include './includes/connection.php';

if (isset($_SESSION['userID'])) {
    header('Location: catalog.php');
    exit;
}

$username = $email = $password = $confirmPassword = $dob = $gender = '';
$errors = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword']; 
    $dob = $_POST['dob'];
    $gender = $_POST['gender'] ?? ''; 

    if (empty($username)) {
        $errors['username'] = "Username harus diisi!";
    } elseif (strlen($username) < 4) { 
        $errors['username'] = "Minimal 4 karakter";
    } elseif (strlen($username) > 20) { 
        $errors['username'] = "Maksimal 20 karakter";
    }
    
    if (empty($email)) {
        $errors['email'] = "Email harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format email tidak valid";
    } elseif (!str_ends_with($email, '@gmail.com')) {
        $errors['email'] = "Email harus diakhiri dengan '@gmail.com'";
    }

    if (empty($password)) {
        $errors['password'] = "Password harus diisi!";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password minimal 8 karakter";
    }

    if (empty($confirmPassword)) {
        $errors['confirmPassword'] = "Confirm password harus diisi!"; 
    } elseif ($password !== $confirmPassword) {
        $errors['confirmPassword'] = "Tidak match dengan password";
    }

    if (empty($gender)) {
        $errors['gender'] = "Gender harus dipilih!";
    } elseif ($gender !== 'Male' && $gender !== 'Female') {
        $errors['gender'] = "Pilihan gender tidak valid";
    }

    if (empty($dob)) {
        $errors['dob'] = 'Date of birth harus diisi!';
    } else {
        $today = new DateTime();
        $birth_date = new DateTime($dob);

        if ($birth_date >= $today) {
            $errors['dob'] = "Tanggal lahir tidak valid (harus di masa lalu)";
        }
    }


    if (empty($errors)) {
        $query_check = "SELECT userID FROM users WHERE username = ? OR email = ?";
        $stmt_check = mysqli_prepare($connection, $query_check);

        if ($stmt_check) {
            mysqli_stmt_bind_param($stmt_check, 'ss', $username, $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) > 0){
                $errors['general'] = "Username atau Email sudah terdaftar";
            }
            mysqli_stmt_close($stmt_check);
        } else {
            $errors['general'] = "Database error (Cek Koneksi)";
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'member';

        $query_insert = "INSERT INTO users (username, email, password_user, gender, dob, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($connection, $query_insert);
        
        if ($stmt_insert) {
            mysqli_stmt_bind_param($stmt_insert, "ssssss", $username, $email, $hashed_password, $gender, $dob, $role);

            if (mysqli_stmt_execute($stmt_insert)) {
                header('Location: login.php?status=registration_success');
                exit;
            } else {
                $errors['general'] = "Registrasi gagal. Coba lagi: " . mysqli_error($connection);
            }
            mysqli_stmt_close($stmt_insert);
        } else {
            $errors['general'] = "Database error (Gagal menyiapkan INSERT).";
        }
    }
}
mysqli_close($conn);
include './includes/header.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>

    <body>
        <div class="container-form">

            <div class="container">
                <h3>Register</h3>
                <?php if (isset($errors['general'])): ?>
                    <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($errors['general']); ?></p>
                <?php endif; ?>

                <div class="form">
                    <form action="./register.php" method="post"> 
                        <div class="username">
                            <br>
                            <label for="username">Username</label>
                            <br>
                            <input type="text" name="username" id="username" placeholder="Enter your username" class="input" value="<?php echo htmlspecialchars($username); ?>">
                            <span style="color: red;" class="error"><?php echo $errors['username'] ?? ''; ?></span>
                        </div>
                        <div class="email">
                            <br>
                            <label for="email">Email</label>
                            <br>
                            <input type="text" name="email" id="email" placeholder="Enter your email" class="input" value="<?php echo htmlspecialchars($email); ?>">
                            <span style="color: red;" class="error"><?php echo $errors['email'] ?? ''; ?></span>
                        </div>
                        <div class="password">
                            <br>
                            <label for="password">Password</label>
                            <br>
                            <input type="password" name="password" id="password" placeholder="Create password" class="input"> 
                            <span style="color: red;" class="error"><?php echo $errors['password'] ?? ''; ?></span>
                        </div>
                        <div class="confirmPassword">
                            <br>
                            <label for="confirmPassword">Re-enter Password</label>
                            <br>
                            <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm your password" class="input"> 
                            <span style="color: red;" class="error"><?php echo $errors['confirmPassword'] ?? ''; ?></span>
                        </div>
                        <div class="gender">
                            <br>
                            <label for="gender">Gender</label>
                            <div class="genderOption">
                                <input type="radio" name="gender" id="male" value="Male" <?php echo ($gender === 'Male') ? 'checked' : ''; ?>>
                                <label for="male">Male</label>
                                <input type="radio" name="gender" id="female" value="Female" <?php echo ($gender === 'Female') ? 'checked' : ''; ?>>
                                <label for="female">Female</label>
                                <span style="color: red;" class="error"><?php echo $errors['gender'] ?? ''; ?></span>
                            </div>
                        </div>
                        <div class="dob">
                            <br>
                            <label for="dob">Date Of Birth</label>
                            <br>
                            <input type="date" name="dob" id="dob" class="input" value="<?php echo htmlspecialchars($dob); ?>">
                            <span style="color: red;" class="error"><?php echo $errors['dob'] ?? ''; ?></span>
                        </div>
                        <div class="button">
                            <br>
                            <button type="submit">Register</button>
                        </div>
                        <div class="link-login">
                            <p>Already have an account? <a href="./login.php">Login here</a></p>
                        </div>
                    </form>
                </div>
            </div>
        
        </div>
    </body>

    <footer>
        <div class="footer">
            <p>© 2025 Furniland. All rights reserved.</p>
            <p>Contact us at <a href="./register.php">furniland.support@gmail.com</a></p>
        </div>
        </footer>
    
</body>
</html>