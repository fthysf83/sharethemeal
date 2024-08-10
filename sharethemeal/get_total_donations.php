<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['total_donations' => 0]);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sharethemeal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT SUM(amount) as total_donations FROM donations WHERE user_id='$user_id' AND YEAR(donation_date) = YEAR(CURDATE())";
$result = $conn->query($sql);

$total_donations = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_donations = $row['total_donations'];
}

$conn->close();

echo json_encode(['total_donations' => $total_donations]);
?>
