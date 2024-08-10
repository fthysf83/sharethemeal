<?php
session_start(); // Start the session to access session variables

// Check if the user is logged in; redirect to login if not
if (!isset($_SESSION['username'])) {
    header("Location: index.html"); // Redirect to login page if not logged in
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sharethemeal";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch active campaigns
$campaigns = [];
$sql = "SELECT campaign_name, campaign_url FROM campaigns WHERE active = 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $campaigns[] = $row;
    }
}

// Fetch total donations for the logged-in user
$total_donations = 0;
$sql = "SELECT SUM(amount) as total_donations FROM donations WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $total_donations = $row['total_donations'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <style>
        /* Import Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        
        body {
            font-family: 'Roboto', sans-serif; /* Apply the imported font */
            background-color: #f5f5f5; /* Light gray background color */
            margin: 0;
            padding: 0;
        }

        .background-image {
            background-image: url('background.png'); /* Replace with the path to your image */
            background-size: cover; /* Ensure the image covers the entire element */
            background-repeat: no-repeat; /* Prevent the image from repeating */
            height: 250vh; /* Full viewport height */
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
            font-family: 'Roboto', sans-serif; /* Apply the imported font */
        }

        .navbar ul li a:hover {
            background-color: #4e342e; /* Darker brown on hover */
            border-radius: 4px;
        }

        .container {
            width: 80%;
            margin: 80px auto 20px; /* Adjust top margin for fixed navbar */
            background-color: rgba(255, 255, 255, 0.9); /* Slightly transparent background for the form */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            padding: 20px;
        }

        .dashboard {
            display: flex;
            flex-direction: column;
        }

        .section {
            margin-bottom: 20px;
        }

        h1 {
            text-align: center;
            color: #6d4c41;
            font-family: 'Roboto', sans-serif; /* Apply the imported font */
        }

        h2 {
            color: #6d4c41;
            font-family: 'Roboto', sans-serif; /* Apply the imported font */
        }

        #campaigns-list, #donations-total {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #ffffff;
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
            <div class="form-container">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
				<center><img src="homepage.png" alt="Description of ShareTheMeal"></center>
                
                
                <div class="dashboard">
                    <div class="section">
                        <center><h2>Active Campaigns</h2>
                        <div id="campaigns-list">
                           <h2> <?php if (!empty($campaigns)): ?>
                                <?php foreach ($campaigns as $campaign): ?>
                                    <div><?php echo htmlspecialchars($campaign['campaign_name']); ?> - <a href="<?php echo htmlspecialchars($campaign['campaign_url']); ?>" target="_blank"><?php echo htmlspecialchars($campaign['campaign_url']); ?></a></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div>No active campaigns</div></center> </h2>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="section">
                        <center><h2>Total Donations This Year</h2>
                        <div id="donations-total">
                            <h2><?php echo 'RM' . number_format($total_donations, 2); ?></h2>
                       </div></center><br><br>
					   <center><img src="homepage4.png" alt="Description of ShareTheMeal"></center><br><br>
					   <center><img src="homepage3.png" alt="Description of ShareTheMeal"></center>
                    </div>
                </div> 
            </div>
        </div>
    </div>
</body>
</html>
