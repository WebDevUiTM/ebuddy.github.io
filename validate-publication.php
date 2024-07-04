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

// Check if publication ID is provided
if (!isset($_GET['publication_id'])) {
    header('Location: list-publication.php');
    exit;
}

// Initialize variables
$publication_id = $_GET['publication_id'];
$correction = $comment = "";
$correction_err = $comment_err = "";

// Fetch publication information
$sql = "SELECT u.fullname, u.user_id, p.journal_name, p.file_path, p.validation_status, p.validation_datetime
        FROM publications p
        JOIN users u ON p.user_id = u.user_id
        WHERE p.publication_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i', $publication_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($fullname, $user_id, $journal_name, $file_path, $validation_status, $validation_datetime);
        $stmt->fetch();
    } else {
        // Redirect if publication ID is invalid
        header('Location: list-validation.php');
        exit;
    }
    $stmt->close();
} else {
    // Redirect if SQL query fails
    header('Location: list-validation.php');
    exit;
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate correction
    if (empty(trim($_POST["correction"]))) {
        $correction_err = "Please select a correction status.";
    } else {
        $correction = trim($_POST["correction"]);
    }

    // Validate comment
    if (empty(trim($_POST["comment"]))) {
        $comment_err = "Please enter a comment.";
    } else {
        $comment = trim($_POST["comment"]);
    }

    // Check input errors before inserting into database
    if (empty($correction_err) && empty($comment_err)) {
        // Prepare an update statement
        $sql = "UPDATE publications 
                SET correction = ?, comment = ?, validation_status = ?, validation_datetime = NOW() 
                WHERE publication_id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssi", $param_correction, $param_comment, $param_validation_status, $param_publication_id);

            // Set parameters
            $param_correction = $correction;
            $param_comment = $comment;
            $param_validation_status = 'Validated';
            $param_publication_id = $publication_id;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to validation page
                header("Location: list-validation.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validate Publication</title>
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
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
        }

        form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
        }

        .error {
            color: red;
            font-size: 0.9em;
        }

        input[type="submit"] {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
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
        <h2>Validate Publication</h2>
        <p>Mentee Name: <?php echo htmlspecialchars($fullname); ?></p>
        <p>User ID: <?php echo htmlspecialchars($user_id); ?></p>
        <p>Journal Name: <?php echo htmlspecialchars($journal_name); ?></p>
        <p>File: <a href="<?php echo htmlspecialchars($file_path); ?>" target="_blank">View File</a></p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?publication_id=' . htmlspecialchars($publication_id); ?>" method="post">
            <div class="form-group">
                <label for="correction">Correction:</label>
                <select name="correction" id="correction">
                    <option value="no_correction">No Correction</option>
                    <option value="minor_correction">Minor Correction</option>
                    <option value="major_correction">Major Correction</option>
                    <option value="rejected">Rejected</option>
                </select>
                <span class="error"><?php echo $correction_err; ?></span>
            </div>
            <div class="form-group">
                <label for="comment">Comment:</label>
                <textarea name="comment" id="comment" rows="3" cols="30"></textarea>
                <span class="error"><?php echo $comment_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" value="Submit Validation">
            </div>
            <div class="btn-container">
                <a href="javascript:history.back()" class="btn">Previous Page</a>
                <a href="dashboard.php" class="btn">Home Page</a>
            </div>
        </form>
    </div>
</body>
</html>
