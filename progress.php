
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

// Fetch mentee and mentor activity data
$sql = "SELECT 
            p.journal_name, p.category, p.index_type, p.collaboration, p.author_type, p.file_path, p. submission_date, p.paper_status,
            IF(p.validation_status IS NULL, 'Not validated', p.validation_status) AS validation_status, 
            p.correction, p.comment, 
            IF(p.validation_status = 'Validated', p.validation_datetime, '') AS validation_datetime,
            u1.fullname AS mentee_name, u2.fullname AS mentor_name,
            pl.link AS publication_link
        FROM publications p
        LEFT JOIN users u1 ON p.user_id = u1.user_id
        LEFT JOIN mentor_mentee mm ON p.user_id = mm.mentee_id
        LEFT JOIN users u2 ON mm.mentor_id = u2.user_id
        LEFT JOIN publication_links pl ON p.publication_id = pl.publication_id
        ORDER BY p.validation_datetime DESC, p.journal_name ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
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
        <h2>Publication Mentorship Marvel Report</h2>
        <?php if ($result->num_rows > 0) : ?>
            <table>
                <tr>
                    <th>Mentor</th>
                    <th>Mentee</th>
                    <th>Journal Name</th>
                    <th>Category</th>
                    <th>Index Type</th>
                    <th>Collaboration</th>
                    <th>Author</th>
                    <th>File Path</th>
                    <th>Submit Date</th>
                    <th>Status</th>
                    <th>Validate Date</th>
                    <th>Paper Status</th>
                    <th>Link</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['mentor_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['mentee_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['journal_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo htmlspecialchars($row['index_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['collaboration']); ?></td>
                        <td><?php echo htmlspecialchars($row['author_type']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">View File</a></td>
                        <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['validation_status']); ?></td>
                        <td><?php echo htmlspecialchars($row['validation_datetime']); ?></td>
                        <td><?php echo htmlspecialchars($row['paper_status']); ?></td>
                        <td><?php echo $row['publication_link'] ? '<a href="' . htmlspecialchars($row['publication_link']) . '" target="_blank">View Link</a>' : ''; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else : ?>
            <p>No publications found.</p>
        <?php endif; ?>
        <div class="btn-container">
            <a href="javascript:history.back()" class="btn">Previous Page</a>
            <a href="dashboard.php" class="btn">Home Page</a>
            <a href="generate-report.php" class="btn btn-report">Generate Report</a>
        </div>
    </div>
</body>
</html>

<?php
// Close statement and connection
$stmt->close();
$conn->close();
?>
