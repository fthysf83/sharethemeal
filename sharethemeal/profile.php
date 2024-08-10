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
$sql = "SELECT username, email, full_name, nric, address, profile_picture FROM users WHERE id='$user_id'";
$result = $conn->query($sql);

$user = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
 .background-image {
            background-image: url('background.png'); /* Replace with the path to your image */
            background-size: cover; /* Ensure the image covers the entire element */
            background-repeat: no-repeat; /* Prevent the image from repeating */
            height: 180vh; /* Full viewport height */
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background-color: #6d4c41; /* Brown background for the navbar */
            padding: 10px;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .navbar ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: space-around;
        }

        .navbar ul li {
            display: inline;
        }

        .navbar ul li a {
            color: #ffffff; /* White text */
            text-decoration: none;
            padding: 10px 20px;
            display: block;
        }

        .navbar ul li a:hover {
            background-color: #4e342e; /* Darker brown on hover */
            border-radius: 4px;
        }

        .container {
            margin: 80px auto 20px; /* Adjust top margin for fixed navbar */
            width: 80%;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #6d4c41;
            text-align: center;
        }

        .profile-picture {
            display: block;
            max-width: 200px;
            margin: 0 auto;
            border-radius: 50%;
            border: 2px solid #6d4c41;
        }

        form {
            margin: 20px 0;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        input[type="file"] {
            margin-bottom: 10px;
        }

        button {
            background-color: #6d4c41; /* Brown background */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #4e342e; /* Darker brown on hover */
        }

        .info {
            margin-bottom: 20px;
        }

        .notice {
            background-color: #dff0d8; /* Light green background */
            color: #3c763d; /* Dark green text */
            padding: 15px;
            border: 1px solid #d6e9c6;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
   <div class="background-image">
   
    <nav class="navbar">
        <ul>
		<li><a href="homepage.php">Homepage</a></li>
            <li><a href="donation_management.php">Donation Management</a></li>
            <li><a href="impact_tracking.php">Impact Tracking</a></li>
            <li><a href="campaign_selection.php">Campaign Selection</a></li>
            <li><a href="profile.php">Profile Management</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Profile Management</h1>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="notice">
                Profile updated successfully!
            </div>
        <?php endif; ?>
        <div class="info">
            <img src="<?php echo $user['profile_picture'] ? 'uploads/' . $user['profile_picture'] : 'default.png'; ?>" alt="Profile Picture" class="profile-picture">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
            <p><strong>NRIC:</strong> <?php echo htmlspecialchars($user['nric']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
        </div>
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>

            <label for="profile_picture">Profile Picture</label>
            <input type="file" id="profile_picture" name="profile_picture">

            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password">

            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>
