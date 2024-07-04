<?php
// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Mentor</title>
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
            max-width: 800px;
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
        .btn-container { text-align: center; margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #28a745; border: none; border-radius: 5px; text-decoration: none; margin: 5px; transition: background-color 0.3s; }
        .btn:hover { background-color: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <h2>List of Mentor</h2>
        <?php
        // Include database connection
        include('config.php');

        // Check if user is logged in and fetch mentees assigned to the mentor
        if (isset($_SESSION['user_id'])) {
            $mentor_id = $_SESSION['user_id'];
            $sql = "SELECT u.user_id, u.fullname, u.username, u.email, u.phone_number, u.profile_picture 
                    FROM users u 
                    JOIN mentor_mentee mm ON u.user_id = mm.mentor_id
                    WHERE mm.mentee_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $mentor_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Mentor Name</th><th>Username</th><th>Email</th><th>Phone Number</th><th>Profile Picture</th></tr>";
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
                    echo "<td><img src='" . htmlspecialchars($row['profile_picture']) . "' alt='Profile Picture'></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No mentees assigned to you.</p>";
            }

            // Close statement and connection
            $stmt->close();
        } else {
            echo "<p>No user logged in.</p>";
        }
        $conn->close();
        ?>
         <div class="btn-container">
    <a href="javascript:history.back()" class="btn">Previous Page</a>
    <a href="dashboard.php" class="btn">Home Page</a>
</div>
    </div>
</body>
</html>
