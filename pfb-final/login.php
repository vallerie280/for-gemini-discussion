<?php
session_start();
include './includes/connection.php';

if (isset($_SESSION['userID'])) {
    header('Location: catalog.php');
    exit;
}

if (isset($_COOKIE['remember_user_id'])) {
    $cookie_user_id = $_COOKIE['remember_user_id'];
    $query = "SELECT userID, username, role FROM users WHERE userID = ? ";
    $stmt = mysqli_prepare($conn, $query);

    if($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $cookie_user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $username = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($username) {
            $_SESSION['userID'] = $username['userID'];
            $_SESSION['username'] = $username['username'];
            $_SESSION['role'] = $username['role'];
            header('Location: catalog.php');
            exit;
        }
    }
}

$email = $password = '';
$errors = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember-me']);

    if (empty($email)) {
        $errors['email'] = "Email harus diisi!";
    }
    if (empty($password)) {
        $errors['password'] = "Password harus diisi!";
    }

    if (empty($errors)) {
        $query = "SELECT userID, username, password_user, role FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);

        if($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $username = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);


            if ($username && password_verify($password, $username['password_user'])) {
                $_SESSION['userID'] = $username['userID'];
                $_SESSION['username'] = $username['username'];
                $_SESSION['role'] = $username['role'];

                if ($remember_me) {
                    $expiry = time() + (7 * 24 * 60 * 60); 
                    setcookie('remember_user_id', $username['userID'], $expiry, "/");
                    setcookie('remember_username', $username['username'], $expiry, "/");
                } else {
                    setcookie('remember_user_id', '', time() - 3600, "/");
                    setcookie('remember_username', '', time() - 3600, "/");
                }

                header('Location: catalog.php');
                exit;
            } else {
                $errors['general'] = "Email atau Password salah.";
            }
        } else {
            $errors['general'] = "Database Error.";
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
    <title>Login Page</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>

    <body>
        <div class="container-form">

            <div class="container">
                <h3>Login</h3>
                <div class="form">
                    <form action="./login.php" method="post">
                        <div class="email">
                            <br>
                            <label for="email">Email</label>
                            <br>
                            <input type="text" name="email" id="email" placeholder="Enter your email" class="input" value="<?php echo htmlspecialchars($email); ?>">
                            <span id="error-email" style="color: red;" class="error"><?php echo $errors['email'] ?? ''; ?></span>
                        </div>
                        <div class="password">
                            <br>
                            <label for="password">Password</label>
                            <br>
                            <input type="password" name="password" id="password" placeholder="Enter password" class="input">
                            <span id="error-password" style="color: red;" class="error"><?php echo $errors['password'] ?? ''; ?></span>
                        </div>
                        <div class="remember-me">
                            <input type="checkbox" name="remember-me" id="remember-me">
                            <label for="remember-me">Remember me</label>
                        </div>
                        <div class="button">
                            <br>
                            <button type="submit">Login</button>
                        </div>
                        <div class="link-login">
                            <p>Don't have an account? <a href="./register.php">Register here</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>

    <footer>
        <div class="footer">
            <p>© 2025 Furniland. All rights reserved.</p>
            <p>Contact us at <a href="./login.php">furniland.support@gmail.com</a></p>
        </div>
        </footer>
</body>
</html>


