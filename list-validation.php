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

// Fetch mentor's assigned mentees
$mentor_id = $_SESSION['user_id'];
$sql = "SELECT u.fullname, u.user_id, p.publication_id, p.journal_name, p.file_path, p.submission_date
        FROM users u
        JOIN mentor_mentee mm ON u.user_id = mm.mentee_id
        JOIN publications p ON u.user_id = p.user_id
        WHERE mm.mentor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $mentor_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Validation</title>
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
            max-width: 1000px;
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

        .comment {
            margin-top: 10px;
        }
        .btn-container { text-align: center; margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #28a745; border: none; border-radius: 5px; text-decoration: none; margin: 5px; transition: background-color 0.3s; }
        .btn:hover { background-color: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Mentor Validation</h2>
        <?php if ($result->num_rows > 0) : ?>
            <table>
                <tr>
                    <th>Full Name</th>
                    <th>User ID</th>
                    <th>Journal Name</th>
                    <th>File</th>
                    <th>Sumbission Date & Time</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['journal_name']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">View File</a></td>
                        <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                        <td><a href="validate-publication.php?publication_id=<?php echo htmlspecialchars($row['publication_id']); ?>">Validate</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else : ?>
            <p>No publication to validate.</p>
        <?php endif; ?>
        <div class="btn-container">
    <a href="javascript:history.back()" class="btn">Previous Page</a>
    <a href="dashboard.php" class="btn">Home Page</a>
</div>
    </div>
</body>
</html>

<?php
// Close statement and connection
$stmt->close();
$conn->close();
?>
