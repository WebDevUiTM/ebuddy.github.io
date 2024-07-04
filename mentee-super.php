<?php
// Start the session
session_start();

// Include database connection
include('config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user role
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Only allow access for super admin
if ($role !== 'super_admin') {
    header('Location: dashboard.php');
    exit;
}

// Initialize search term
$search_term = '';
if (isset($_POST['search'])) {
    $search_term = $_POST['search_term'];
}

// Define SQL query to fetch mentees and their mentors
$sql = "SELECT u.user_id, u.fullname, u.username, u.email, u.phone_number, u.profile_picture, m.fullname AS mentor_name
        FROM users u
        LEFT JOIN mentor_mentee ms ON u.user_id = ms.mentee_id
        LEFT JOIN users m ON ms.mentor_id = m.user_id
        WHERE u.role = 'mentee' AND u.fullname LIKE ?
        ORDER BY u.fullname ASC";

// Execute SQL query
$stmt = $conn->prepare($sql);
$search_term_param = '%' . $search_term . '%';
$stmt->bind_param('s', $search_term_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Mentees</title>
    <link rel="icon" type="image/png" href="image/PMM.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        h2 {
            text-align: center;
        }

        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-container input[type="text"] {
            padding: 10px;
            font-size: 16px;
            width: 300px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .search-container input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        img {
            max-width: 100px;
        }

        .btn-container { 
            text-align: center; 
            margin-top: 20px; 
        }

        .btn { 
            display: inline-block; 
            padding: 10px 20px; 
            font-size: 16px; 
            color: #fff; 
            background-color: #28a745; 
            border: none; 
            border-radius: 5px; 
            text-decoration: none; 
            margin: 5px; 
            transition: background-color 0.3s; 
        }

        .btn:hover { 
            background-color: #218838; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>List of Mentees</h2>
        <div class="search-container">
            <form method="POST" action="">
                <input type="text" name="search_term" placeholder="Search by Name" value="<?php echo htmlspecialchars($search_term); ?>">
                <input type="submit" name="search" value="Search">
            </form>
        </div>
        <?php
        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>Full Name</th><th>Username</th><th>Email</th><th>Phone Number</th><th>Profile Picture</th><th>Mentor Name</th></tr>";
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
                echo "<td><img src='" . htmlspecialchars($row['profile_picture']) . "' alt='Profile Picture'></td>";
                echo "<td>" . htmlspecialchars($row['mentor_name']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No mentees found.</p>";
        }
        // Close statement and connection
        $stmt->close();
        $conn->close();
        ?>
        <div class="btn-container">
            <a href="javascript:history.back()" class="btn">Previous Page</a>
            <a href="dashboard.php" class="btn">Home Page</a>
        </div>
    </div>
</body>
</html>
