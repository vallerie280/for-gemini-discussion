<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "furniland_db"; 


$conn_server = mysqli_connect($host, $user, $password);

if (!$conn_server) {
    die("ERROR TAHAP 1 (Koneksi Server): Pastikan XAMPP/MySQL berjalan. " . mysqli_connect_error());
}

echo "<h2>Setup Database Furniland</h2>";

$sql_drop = "DROP DATABASE IF EXISTS $dbname";
mysqli_query($conn_server, $sql_drop);
echo "Database $dbname berhasil dihapus (jika ada).<br>";

$sql_create = "CREATE DATABASE $dbname";
if (mysqli_query($conn_server, $sql_create)) {
    echo "Database **$dbname** berhasil dibuat!<br>";
} else {
    die("ERROR TAHAP 1 (Create DB): Gagal membuat database. " . mysqli_error($conn_server));
}

mysqli_close($conn_server);
echo "<hr>";

$connection = mysqli_connect($host, $user, $password, $dbname);

if (!$connection) {
    die("ERROR TAHAP 2 (Koneksi DB): Koneksi ke database $dbname gagal. " . mysqli_connect_error());
}

echo "<h3>Membuat Tabel dan Mengisi Data...</h3>";

$create_queries = [
    "CREATE TABLE IF NOT EXISTS users ( userID INT(10) AUTO_INCREMENT PRIMARY KEY, username VARCHAR(20) UNIQUE, email VARCHAR(20) UNIQUE, password_user VARCHAR(255), gender VARCHAR(6), dob DATE, role VARCHAR(6) );",

    "CREATE TABLE IF NOT EXISTS vendors ( vendorID INT(10) AUTO_INCREMENT PRIMARY KEY, vendorName VARCHAR(20) NOT NULL, location VARCHAR(100) NOT NULL );",
    
    "CREATE TABLE IF NOT EXISTS transactions ( transactionID INT(10) AUTO_INCREMENT PRIMARY KEY, userID INT(10), totalPrice FLOAT(10) NOT NULL, transactionDate DATE NOT NULL, FOREIGN KEY (userID) REFERENCES users(userID) ON DELETE CASCADE );",
    
    "CREATE TABLE IF NOT EXISTS products ( productID INT(10) AUTO_INCREMENT PRIMARY KEY, productName VARCHAR(30) NOT NULL, description VARCHAR(255) NOT NULL, price FLOAT(10) NOT NULL, image VARCHAR(100) NOT NULL, vendorID INT(10), FOREIGN KEY (vendorID) REFERENCES vendors (vendorID) ON DELETE CASCADE );",
    
    "CREATE TABLE IF NOT EXISTS cart ( cartID INT(10) AUTO_INCREMENT PRIMARY KEY, userID INT(10), productID INT(10), quantity INT(5) NOT NULL, FOREIGN KEY (userID) REFERENCES users (userID) ON DELETE CASCADE, FOREIGN KEY (productID) REFERENCES products(productID) ON DELETE CASCADE );",
    
    "CREATE TABLE IF NOT EXISTS transaction_details ( detailID INT(10) AUTO_INCREMENT PRIMARY KEY, transactionID INT(10), productID INT(10), quantity INT(5) NOT NULL, subTotal INT(10) NOT NULL, FOREIGN KEY (transactionID) REFERENCES transactions (transactionID) ON DELETE CASCADE, FOREIGN KEY (productID) REFERENCES products (productID) ON DELETE CASCADE );",

    "INSERT INTO vendors (vendorName, location) VALUES ('Boke Furniture', 'Jakarta'), ('Fabelio', 'Semarang'), ('GGS', 'Yogyakarta'), ('Kayuku', 'Tangerang'), ('Indonesia furniture', 'Jakarta');",
    
    "INSERT INTO products (productName, description, price, image, vendorID) VALUES ('Faxalen', 'Mirror cabinet with built-in lights, oak effect, 60x15x95 cm', 4995000, 'faxalen.jpeg', 1), ('Pand', 'Table multi-fungsional', 1079000, 'pand.jpeg', 2), ('Gultarp', 'Seat, antrasit/remmarn antrasit', 145000, 'gultarp.jpeg', 3), ('Vihals', 'Table, white 125x74 cm', 749000, 'vihals.jpeg', 4), ('Soderhamn', '3-seat sofa, tonerud grey', 9995000, 'soderhamn.jpeg', 5);"
];

foreach ($create_queries as $index => $query) {
    if (mysqli_query($connection, $query)) {
        echo "Tabel/Data " . ($index + 1) . " berhasil dibuat/di-insert.<br>";
    } else {
        echo "<span style='color: red;'>ERROR pada Query " . ($index + 1) . ": " . mysqli_error($connection) . "</span><br>";
    }
}

mysqli_close($connection);
echo "<hr>Setup database dan tabel selesai. File **config.php** sekarang dapat digunakan.";
?>