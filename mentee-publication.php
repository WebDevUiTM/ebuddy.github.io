<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentee Publications</title>
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
            max-width: 1100px;
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
        .btn-container { text-align: center; margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #28a745; border: none; border-radius: 5px; text-decoration: none; margin: 5px; transition: background-color 0.3s; }
        .btn:hover { background-color: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Mentee Publications</h2>
        <?php
        // Include database connection
        include('config.php');
        session_start();

        // Check if mentor_id is set in session
        if (!isset($_SESSION['user_id'])) {
            echo "<p>Error: Mentor ID is not set. Please log in again.</p>";
            echo '<div class="btn-container"><a href="login.php" class="btn">Login Page</a></div>';
            exit();
        }

        // Assuming mentor's ID is stored in the session
        $user_id = $_SESSION['user_id'];

        // Fetch all publications for mentees assigned to the logged-in mentor
        $sql = "SELECT p.*, p.validation_status, p.paper_status, u.fullname AS mentee_name, pl.link
                FROM publications p
                INNER JOIN users u ON p.user_id = u.user_id
                INNER JOIN mentor_mentee mm ON u.user_id = mm.mentee_id
                LEFT JOIN publication_links pl ON p.publication_id = pl.publication_id
                WHERE mm.mentor_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<tr>
            <th>Mentee</th>
            <th>Journal Name</th>
            <th>Category</th>
            <th>Index Type</th>
            <th>Collaboration</th>
            <th>Author</th>
            <th>Submission Date</th>
            <th>File Path</th>
            <th>Status</th>
            <th>Paper Status</th>
            <th>Link</th></tr>";
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['mentee_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['journal_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>" . htmlspecialchars($row['index_type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['collaboration']) . "</td>";
                echo "<td>" . htmlspecialchars($row['author_type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['submission_date']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($row['file_path']) . "' target='_blank'>View Publication</a></td>";
                echo "<td>" . htmlspecialchars($row['validation_status']) . "</td>";
                echo "<td>" . htmlspecialchars($row['paper_status']) . "</td>";
                echo "<td>" . ($row['link'] ? "<a href='" . htmlspecialchars($row['link']) . "' target='_blank'>View Link</a>" : "No Link Available") . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No publications found.</p>";
        }

        // Close connection
        $conn->close();
        ?>
        <div class="btn-container">
            <a href="javascript:history.back()" class="btn">Previous Page</a>
            <a href="dashboard.php" class="btn">Home Page</a>
        </div>
    </div>
</body>
</html>
