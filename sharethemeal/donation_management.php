<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sharethemeal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the user ID based on the username
$username = $_SESSION['username'];
$user_sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($user_sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$user_id = $user['id'];
$stmt->close();

// Fetch campaigns for selection
$campaigns_sql = "SELECT id, campaign_name FROM campaigns";
$campaigns_result = $conn->query($campaigns_sql);

if ($campaigns_result === false) {
    die('Query failed: ' . htmlspecialchars($conn->error));
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $bank = $_POST['bank'];
    $campaign_id = $_POST['campaign_id'];

    // Simple validation
    if (empty($amount) || empty($payment_method) || empty($bank) || empty($campaign_id)) {
        $error = "Please fill in all fields.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Invalid amount.";
    } else {
        // Insert donation into database
        $stmt = $conn->prepare("INSERT INTO donations (user_id, campaign_id, amount, payment_method, bank, donation_date) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("iidss", $user_id, $campaign_id, $amount, $payment_method, $bank);

        if ($stmt->execute()) {
            // Update current donations in campaigns table
            $update_stmt = $conn->prepare("UPDATE campaigns SET current_donations = current_donations + ? WHERE id = ?");
            if ($update_stmt === false) {
                die("Error preparing update statement: " . $conn->error);
            }
            $update_stmt->bind_param("di", $amount, $campaign_id);
            $update_stmt->execute();
            $update_stmt->close();

            $success = "Donation successful!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Retrieve donation history
$donations_sql = "SELECT d.*, c.campaign_name FROM donations d JOIN campaigns c ON d.campaign_id = c.id WHERE d.user_id = ?";
$stmt = $conn->prepare($donations_sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$donations_result = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Management</title>
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
                <center><h1>Donation Management</h1></center>
                
                <!-- Display any error or success messages -->
                <?php if (isset($error)): ?>
                    <div class="message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form action="donation_management.php" method="POST">
                    <label for="amount">Donation Amount (RM)</label>
                    <input type="text" id="amount" name="amount" required>
                    
                    <label for="payment_method">Payment Method</label>
                    <input type="text" id="payment_method" name="payment_method" required>
                    
                    <label for="bank">Bank</label>
                    <input type="text" id="bank" name="bank" required>
                    
                    <label for="campaign_id">Select Campaign</label>
                    <select id="campaign_id" name="campaign_id" required>
                        <option value="">Select a campaign</option>
                        <?php while ($campaign = $campaigns_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($campaign['id']); ?>">
                                <?php echo htmlspecialchars($campaign['campaign_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    
                    <button type="submit">Donate</button>
                </form>
                
                <div class="donation-history">
                    <center><h2>Your Donation History</h2></center>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Campaign</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Bank</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $donations_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['campaign_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['amount']); ?></td>
                                    <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                    <td><?php echo htmlspecialchars($row['bank']); ?></td>
                                    <td><?php echo htmlspecialchars($row['donation_date']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
