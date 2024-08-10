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
$campaign_id = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : 0;

// Fetch campaigns for the user to select from
$campaigns_sql = "SELECT id, campaign_name FROM campaigns WHERE id IN (SELECT campaign_id FROM user_campaigns WHERE user_id = $user_id)";
$campaigns_result = $conn->query($campaigns_sql);

// Fetch impact tracking records based on the selected campaign
$impact_tracking_sql = $campaign_id > 0 ? "SELECT * FROM impact_tracking WHERE user_id='$user_id' AND campaign_id='$campaign_id'" : "SELECT * FROM impact_tracking WHERE user_id='$user_id'";
$impact_tracking_result = $conn->query($impact_tracking_sql);

// Fetch campaign details for progress display
$campaign_details = [];
if ($campaign_id > 0) {
    $campaign_details_sql = "SELECT * FROM campaigns WHERE id='$campaign_id'";
    $campaign_details_result = $conn->query($campaign_details_sql);
    if ($campaign_details_result->num_rows > 0) {
        $campaign_details = $campaign_details_result->fetch_assoc();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact Tracking</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
.background-image {
        background-image: url('background.png'); /* Replace with your background image */
        background-size: cover;
        background-position: center;
        min-height: 100vh;
        padding: 20px;
    }
        .navbar {
            background-color: #6d4c41;
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
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
        }

        .navbar ul li a:hover {
            background-color: #4e342e;
            border-radius: 4px;
        }

        .container {
            width: 80%;
            margin: 80px auto 20px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #6d4c41;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            width: 100%;
            max-width: 300px;
        }

        button {
            padding: 10px 20px;
            background-color: #6d4c41;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #4e342e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #6d4c41;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .campaign-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .campaign-details h2 {
            margin-top: 0;
            color: #6d4c41;
        }

        .impact-description {
            margin-top: 20px;
            padding: 15px;
            background-color: #e8e8e8;
            border-radius: 8px;
        }

        .impact-description h2 {
            color: #6d4c41;
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
        <h1>Impact Tracking</h1>

        <form method="GET" action="impact_tracking.php">
            <label for="campaign">Select Campaign</label>
            <select id="campaign" name="campaign_id">
                <option value="">-- All Campaigns --</option>
                <?php while ($campaign = $campaigns_result->fetch_assoc()): ?>
                    <option value="<?php echo $campaign['id']; ?>" <?php echo $campaign_id == $campaign['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($campaign['campaign_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Filter</button>
        </form>

        <?php if ($campaign_id > 0 && !empty($campaign_details)): ?>
            <div class="campaign-details">
                <h2>Campaign Details</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($campaign_details['campaign_name']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($campaign_details['description']); ?></p>
                <p><strong>Start Date:</strong> <?php echo htmlspecialchars($campaign_details['start_date']); ?></p>
                <p><strong>End Date:</strong> <?php echo htmlspecialchars($campaign_details['end_date']); ?></p>
                <p><strong>Total Donation Needs:</strong> <?php echo htmlspecialchars($campaign_details['total_donation_needs']); ?></p>
                <p><strong>Current Donations:</strong> <?php echo htmlspecialchars($campaign_details['current_donations']); ?></p>
                <p><strong>People Served:</strong> <?php echo htmlspecialchars($campaign_details['people_served']); ?></p>
            </div>
        <?php endif; ?>


</body>
</html>
