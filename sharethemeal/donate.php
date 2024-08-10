<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sharethemeal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $amount = $_POST['amount'];
    $payment_details = $_POST['payment_details'];

    $sql = "INSERT INTO donations (user_id, amount, payment_details) VALUES ('$user_id', '$amount', '$payment_details')";

    if ($conn->query($sql) === TRUE) {
        echo "Donation successful";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
