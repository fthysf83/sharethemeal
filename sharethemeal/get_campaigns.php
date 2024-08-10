<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
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

$sql = "SELECT campaign_name, link FROM campaigns WHERE user_id='$user_id' AND active=1";
$result = $conn->query($sql);

$campaigns = [];
while ($row = $result->fetch_assoc()) {
    $campaigns[] = $row;
}

$conn->close();

echo json_encode($campaigns);
?>
