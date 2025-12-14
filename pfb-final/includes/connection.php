<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "furniland_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function fetch_vendors($conn) {
    $vendors = [];
    $sql = "SELECT vendorID, vendorName FROM vendors ORDER BY vendorName ASC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $vendors[] = $row;
        }
    }
    return $vendors;
}
?>