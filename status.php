<?php
session_start();
include('config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Process paper status update if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['publication_id']) && isset($_POST['paper_status'])) {
        $publication_id = $_POST['publication_id'];
        $paper_status = $_POST['paper_status'];
        $publication_link = isset($_POST['publication_link']) ? $_POST['publication_link'] : null;

        // Update paper status in the database
        $update_sql = "UPDATE publications SET paper_status = ? WHERE publication_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('si', $paper_status, $publication_id);
        $update_stmt->execute();
        $update_stmt->close();

        // If status is Published, insert the publication link
        if ($paper_status == 'Published' && !empty($publication_link)) {
            $link_sql = "INSERT INTO publication_links (publication_id, link) VALUES (?, ?)";
            $link_stmt = $conn->prepare($link_sql);
            $link_stmt->bind_param('is', $publication_id, $publication_link);
            $link_stmt->execute();
            $link_stmt->close();
        }

        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    }
}

// Fetch validation status for the mentee
$mentee_id = $_SESSION['user_id'];
$sql = "SELECT p.publication_id, p.journal_name, p.category, p.index_type, p.collaboration, p.author_type, p.file_path, p.submission_date, 
               IFNULL(p.paper_status, '') AS paper_status,
               IF(p.validation_status IS NULL, 'Not validated', p.validation_status) AS validation_status, 
               p.correction, p.comment, 
               IF(p.validation_status = 'Validated', p.validation_datetime, '') AS validation_datetime,
               (SELECT link FROM publication_links pl WHERE pl.publication_id = p.publication_id LIMIT 1) AS publication_link
        FROM publications p
        WHERE p.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $mentee_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentee Validation Status</title>
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
            max-width: 1700px;
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
        <h2>Mentee Validation Status</h2>
        <?php if ($result->num_rows > 0) : ?>
            <table>
                <tr>
                    <th>Journal Name</th>
                    <th>Category</th>
                    <th>Index Type</th>
                    <th>Collaboration</th>
                    <th>Author</th>
                    <th>File Path</th>
                    <th>Submit Date</th>
                    <th>Status</th>
                    <th>Correction</th>
                    <th>Comment</th>
                    <th>Validate Date</th>
                    <th>Paper Status</th>
                    <th>Update</th>
                    <th>Link</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['journal_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo htmlspecialchars($row['index_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['collaboration']); ?></td>
                        <td><?php echo htmlspecialchars($row['author_type']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">View File</a></td>
                        <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['validation_status']); ?></td>
                        <td><?php echo htmlspecialchars($row['correction']); ?></td>
                        <td><?php echo htmlspecialchars($row['comment']); ?></td>
                        <td><?php echo htmlspecialchars($row['validation_datetime']); ?></td>
                        <td><?php echo htmlspecialchars($row['paper_status']); ?></td>
                        <td>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                                <input type="hidden" name="publication_id" value="<?php echo htmlspecialchars($row['publication_id']); ?>">
                                <select name="paper_status" onchange="toggleLinkInput(this, <?php echo htmlspecialchars($row['publication_id']); ?>)">
                                    <option value="" <?php echo ($row['paper_status'] == '') ? 'selected' : ''; ?>>Select</option>
                                    <option value="Drafted" <?php echo ($row['paper_status'] == 'Drafted') ? 'selected' : ''; ?>>Drafted</option>
                                    <option value="Submitted" <?php echo ($row['paper_status'] == 'Submitted') ? 'selected' : ''; ?>>Submitted</option>
                                    <option value="Accepted" <?php echo ($row['paper_status'] == 'Accepted') ? 'selected' : ''; ?>>Accepted</option>
                                    <option value="Received" <?php echo ($row['paper_status'] == 'Received') ? 'selected' : ''; ?>>Received</option>
                                    <option value="Published" <?php echo ($row['paper_status'] == 'Published') ? 'selected' : ''; ?>>Published</option>
                                </select>
                                <br><br>
                                <div id="link-input-<?php echo htmlspecialchars($row['publication_id']); ?>" style="display: <?php echo ($row['paper_status'] == 'Published') ? 'block' : 'none'; ?>;">
                                    <label for="publication_link">Publication Link:</label>
                                    <input type="url" name="publication_link" value="<?php echo htmlspecialchars($row['publication_link']); ?>">
                                </div>
                                <button type="submit">Update</button>
                            </form>
                        </td>
                        <td><?php echo $row['publication_link'] ? '<a href="' . htmlspecialchars($row['publication_link']) . '" target="_blank">View Link</a>' : ''; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else : ?>
            <p>No validation status available.</p>
        <?php endif; ?>
        <div class="btn-container">
            <a href="javascript:history.back()" class="btn">Previous Page</a>
            <a href="dashboard.php" class="btn">Home Page</a>
        </div>
    </div>
    <script>
        function toggleLinkInput(selectElement, publicationId) {
            var linkInputDiv = document.getElementById('link-input-' + publicationId);
            if (selectElement.value === 'Published') {
                linkInputDiv.style.display = 'block';
            } else {
                linkInputDiv.style.display = 'none';
            }
        }
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
