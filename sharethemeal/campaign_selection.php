<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include('db.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];

    if (isset($_POST['existing_campaign']) && !empty($_POST['existing_campaign'])) {
        // Join existing campaign
        $campaign_id = $_POST['existing_campaign'];
        $sql = "INSERT INTO user_campaigns (user_id, campaign_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("ii", $user_id, $campaign_id);
        if (!$stmt->execute()) {
            die('Execute failed: ' . htmlspecialchars($stmt->error));
        }
        $_SESSION['notification'] = "Successfully joined the campaign.";
    } elseif (isset($_POST['campaign_name'])) {
        // Add new campaign
        $campaign_name = htmlspecialchars($_POST['campaign_name']);
        $description = htmlspecialchars($_POST['description']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $total_donation_needs = $_POST['total_donation_needs'];
        $current_donations = $_POST['current_donations'];
        $people_served = $_POST['people_served'];

        // Generate campaign link
        $campaign_name_cleaned = strtolower(str_replace(' ', '', $campaign_name));
        $campaign_url = "www." . $campaign_name_cleaned . ".com";

        $sql = "INSERT INTO campaigns (user_id, campaign_name, description, start_date, end_date, total_donation_needs, current_donations, people_served, campaign_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("issssddis", $user_id, $campaign_name, $description, $start_date, $end_date, $total_donation_needs, $current_donations, $people_served, $campaign_url);
        if (!$stmt->execute()) {
            die('Execute failed: ' . htmlspecialchars($stmt->error));
        }

        $_SESSION['notification'] = "Campaign added successfully. <br> Campaign Link: <a href='http://$campaign_url' target='_blank'>$campaign_url</a>";
    }

    if (isset($_POST['share_campaign'])) {
        $campaign_id = $_POST['campaign_id'];
        $platform = $_POST['platform'];
        
        // Fetch campaign URL
        $sql = "SELECT campaign_url FROM campaigns WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $campaign_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $campaign = $result->fetch_assoc();
        $campaign_url = $campaign['campaign_url'];

        $share_url = '';
        switch ($platform) {
            case 'facebook':
                $share_url = "https://www.facebook.com/sharer/sharer.php?u=http://$campaign_url";
                break;
            case 'twitter':
                $share_url = "https://twitter.com/intent/tweet?url=http://$campaign_url";
                break;
            case 'linkedin':
                $share_url = "https://www.linkedin.com/shareArticle?mini=true&url=http://$campaign_url";
                break;
            case 'instagram':
                $_SESSION['notification'] = "Instagram sharing requires manual intervention. Please copy the link and share it directly.";
                break;
        }

        if ($share_url) {
            $_SESSION['notification'] = "Share your campaign on: <a href='$share_url' target='_blank'>Share Now</a>";
        }
    }
}

// Fetch existing campaigns
$campaigns = [];
$sql = "SELECT * FROM campaigns";
$result = $conn->query($sql);

if ($result === false) {
    die('Query failed: ' . htmlspecialchars($conn->error));
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $campaigns[] = $row;
    }
}

// Fetch user joined campaigns
$user_campaigns = [];
$sql = "SELECT c.* FROM campaigns c
        JOIN user_campaigns uc ON c.id = uc.campaign_id
        WHERE uc.user_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die('Query failed: ' . htmlspecialchars($stmt->error));
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $user_campaigns[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Selection</title>
    <style>
        body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
    }

    .background-image {
        background-image: url('background.png'); /* Replace with your background image */
        background-size: cover;
        background-position: center;
        min-height: 100vh;
        padding: 20px;
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
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        max-width: 900px;
        margin: auto;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h1, h2 {
        color: #5d4037;
    }

    form {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin: 10px 0 5px;
        color: #333;
    }

    input[type="text"], input[type="date"], input[type="number"], textarea, select {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    button {
        background-color: #5d4037; /* Brown color */
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    button:hover {
        background-color: #4e342e; /* Darker brown for hover effect */
    }

    .notice {
        margin-bottom: 20px;
        padding: 10px;
        background-color: #e0f7fa;
        border: 1px solid #b2ebf2;
        border-radius: 4px;
        color: #00796b;
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
        background-color: #5d4037; /* Brown color */
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f8e9;
    }

    a {
        color: #5d4037;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
    </style>
</head>
<body>
    <div class="background-image">
        <nav class="navbar">
            <ul>
                <li><a href="homepage.php">Home</a></li>
                <li><a href="donation_management.php">Donation Management</a></li>
                <li><a href="impact_tracking.php">Impact Tracking</a></li>
                <li><a href="campaign_selection.php">Campaign Selection</a></li>
                <li><a href="profile.php">Profile Management</a></li>
				   <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
<br><br><br><br><br>
        <div class="container">
           <center> <h1>Campaign Selection</h1> </center>
            <div class="notice">
                <?php if (isset($_SESSION['notification'])) {
                    echo $_SESSION['notification'];
                    unset($_SESSION['notification']);
                } ?>
            </div>

            <form action="campaign_selection.php" method="post">
                <center><h2>Add New Campaign</h2></center
                <label for="campaign_name">Campaign Name:</label>
                <input type="text" id="campaign_name" name="campaign_name" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required></textarea>

                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required>

                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" required>

                <label for="total_donation_needs">Total Donation Needs:</label>
                <input type="number" id="total_donation_needs" name="total_donation_needs" required>

                <label for="current_donations">Current Donations:</label>
                <input type="number" id="current_donations" name="current_donations" required>

                <label for="people_served">People Served:</label>
                <input type="number" id="people_served" name="people_served" required>

                <button type="submit">Add Campaign</button>
            </form>

            <form action="campaign_selection.php" method="post">
                <center><h2>Join Existing Campaign</h2></center><br><br>
                <label for="existing_campaign">Select Campaign:</label>
                <select id="existing_campaign" name="existing_campaign">
                    <?php foreach ($campaigns as $campaign) { ?>
                        <option value="<?php echo $campaign['id']; ?>"><?php echo htmlspecialchars($campaign['campaign_name']); ?></option>
                    <?php } ?>
                </select>
                <button type="submit">Join Campaign</button>
            </form>

            <form action="campaign_selection.php" method="post">
                <center><h2>Share Campaign</h2></center><br><br>
                <label for="campaign_id">Select Campaign to Share:</label>
                <select id="campaign_id" name="campaign_id">
                    <?php foreach ($user_campaigns as $campaign) { ?>
                        <option value="<?php echo $campaign['id']; ?>"><?php echo htmlspecialchars($campaign['campaign_name']); ?></option>
                    <?php } ?>
                </select>
                <label for="platform">Select Platform:</label>
                <select id="platform" name="platform">
                    <option value="facebook">Facebook</option>
                    <option value="twitter">Twitter</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="instagram">Instagram</option>
                </select>
                <button type="submit" name="share_campaign">Share Campaign</button>
            </form>

            <center><h2>Joined Campaigns</h2></center><br><br>
            <table>
                <thead>
                    <tr>
                        <th>Campaign Name</th>
                        <th>Description</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Total Donation Needs</th>
                        <th>Current Donations</th>
                        <th>People Served</th>
                        <th>Campaign URL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user_campaigns as $campaign) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($campaign['campaign_name']); ?></td>
                            <td><?php echo htmlspecialchars($campaign['description']); ?></td>
                            <td><?php echo $campaign['start_date']; ?></td>
                            <td><?php echo $campaign['end_date']; ?></td>
                            <td><?php echo $campaign['total_donation_needs']; ?></td>
                            <td><?php echo $campaign['current_donations']; ?></td>
                            <td><?php echo $campaign['people_served']; ?></td>
                            <td><a href="http://<?php echo $campaign['campaign_url']; ?>" target="_blank"><?php echo $campaign['campaign_url']; ?></a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>