<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Publications</title>
    <link rel="icon" type="image/png" href="image/PMM.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-top: 0;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        td a {
            text-decoration: none;
            color: #007bff;
        }
        td a:hover {
            text-decoration: underline;
        }
        p {
            text-align: center;
            font-style: italic;
        }
        .btn-container { text-align: center; margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #28a745; border: none; border-radius: 5px; text-decoration: none; margin: 5px; transition: background-color 0.3s; }
        .btn:hover { background-color: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Publications</h2>
        <?php
        session_start();
        include('config.php');

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $user_id = $_SESSION['user_id'];

        // Fetch publications of the logged-in user
        $sql = "SELECT * FROM publications WHERE user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                $result = $stmt->get_result();

                // Check if the user has any publications
                if ($result->num_rows > 0) {
                    echo "<table>";
                    echo "<tr><th>Journal Name</th><th>Category</th><th>Index Type</th><th>Collaboration</th><th>Author</th><th>File Path</th><th>Submission Date</tr>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['journal_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['index_type']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['collaboration']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['author_type']) . "</td>";
                        echo "<td><a href='" . htmlspecialchars($row['file_path']) . "' target='_blank'>View Publication</a></td>";
                        echo "<td>" . htmlspecialchars($row['submission_date']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No publications found.</p>";
                }
            } else {
                echo "<p>Error fetching publications.</p>";
            }

            // Close statement
            $stmt->close();
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
