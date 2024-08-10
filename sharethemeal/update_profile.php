<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
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
$address = $_POST['address'];
$profile_picture = $_FILES['profile_picture']['name'];
$new_password = $_POST['new_password'];

$update_sql = "UPDATE users SET address=?";

if ($profile_picture) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($profile_picture);
    move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
    $update_sql .= ", profile_picture=?";
}

if ($new_password) {
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $update_sql .= ", password=?";
}

$update_sql .= " WHERE id=?";

$stmt = $conn->prepare($update_sql);

if ($profile_picture && $new_password) {
    $stmt->bind_param("sssi", $address, $profile_picture, $hashed_password, $user_id);
} elseif ($profile_picture) {
    $stmt->bind_param("ssi", $address, $profile_picture, $user_id);
} elseif ($new_password) {
    $stmt->bind_param("ssi", $address, $hashed_password, $user_id);
} else {
    $stmt->bind_param("si", $address, $user_id);
}

$stmt->execute();
$stmt->close();
$conn->close();

header('Location: profile.php?status=success');
exit();
?>
