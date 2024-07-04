<?php
session_start();
include('config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$journal_name = $category = $index_type = $collaboration = $author_type = "";
$journal_name_err = $category_err = $index_type_err = $collaboration_err = $file_path_err = $author_type_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate journal name
    if (empty(trim($_POST["journal_name"]))) {
        $journal_name_err = "Please enter the journal name.";
    } else {
        $journal_name = trim($_POST["journal_name"]);
    }

    // Validate category
    if (empty(trim($_POST["category"]))) {
        $category_err = "Please select a category.";
    } else {
        $category = trim($_POST["category"]);
    }

    // Validate index type
    if (empty(trim($_POST["index_type"]))) {
        $index_type_err = "Please select an index type.";
    } else {
        $index_type = trim($_POST["index_type"]);
    }

    // Validate collaboration
    if (empty(trim($_POST["collaboration"]))) {
        $collaboration_err = "Please select a collaboration type.";
    } else {
        $collaboration = trim($_POST["collaboration"]);
    }

    // Validate author type
    if (empty(trim($_POST["author_type"]))) {
        $author_type_err = "Please select an author type.";
    } else {
        $author_type = trim($_POST["author_type"]);
    }

    // Check if file is uploaded
    if (!empty($_FILES["file"]["name"])) {
        $file_name = basename($_FILES["file"]["name"]);
        $target_dir = "uploads/";
        $target_file = $target_dir . $file_name;

        // Check file type
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($file_type != "pdf") {
            $file_path_err = "Only PDF files are allowed.";
        }

        // Check file size
        if ($_FILES["file"]["size"] > 100000000) {
            $file_path_err = "File size exceeds maximum limit (5MB).";
        }

        // Check if file path error is not set
        if (empty($file_path_err)) {
            // Upload file
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                $file_path = $target_file;
            } else {
                $file_path_err = "Failed to upload file.";
            }
        }
    } else {
        $file_path_err = "Please select a file.";
    }

    // Check input errors before inserting into database
    if (empty($journal_name_err) && empty($category_err) && empty($index_type_err) && empty($collaboration_err) && empty($author_type_err) && empty($file_path_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO publications (user_id, journal_name, category, index_type, collaboration, author_type, file_path) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issssss", $param_user_id, $param_journal_name, $param_category, $param_index_type, $param_collaboration, $param_author_type, $param_file_path);

            // Set parameters
            $param_user_id = $_SESSION['user_id'];
            $param_journal_name = $journal_name;
            $param_category = $category;
            $param_index_type = $index_type;
            $param_collaboration = $collaboration;
            $param_author_type = $author_type;
            $param_file_path = $file_path;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to publication list page
                header("Location: view-publication.php");
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
    <title>Add Publication</title>
    <link rel="icon" type="image/png" href="image/PMM.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-top: 0;
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
        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
        input[type="submit"],
        input[type="reset"] {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover,
        input[type="reset"]:hover {
            background-color: #0056b3;
        }
        .btn-container { text-align: center; margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #28a745; border: none; border-radius: 5px; text-decoration: none; margin: 5px; transition: background-color 0.3s; }
        .btn:hover { background-color: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add Publication</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="journal_name">Journal Name:</label>
                <input type="text" name="journal_name" id="journal_name" value="<?php echo $journal_name; ?>">
                <span class="error"><?php echo $journal_name_err; ?></span>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <select name="category" id="category">
                    <option value="">Please select</option>
                    <option value="Journal"<?php if ($category === "Journal") echo " selected"; ?>>Journal</option>
                    <option value="Proceeding"<?php if ($category === "Proceeding") echo " selected"; ?>>Proceeding</option>
                    <option value="Research Book"<?php if ($category === "Research Book") echo " selected"; ?>>Research Book</option>
                    <option value="Chapter & Research Book"<?php if ($category === "Chapter & Research Book") echo " selected"; ?>>Chapter & Research Book</option>
                </select>
                <span class="error"><?php echo $category_err; ?></span>
            </div>
            <div class="form-group">
                <label for="index_type">Index Type:</label>
                <select name="index_type" id="index_type">
                    <option value="">Please select</option>
                    <option value="ERA"<?php if ($index_type === "ERA") echo " selected"; ?>>ERA</option>
                    <option value="Scopus"<?php if ($index_type === "Scopus") echo " selected"; ?>>Scopus</option>
                    <option value="WoS"<?php if ($index_type === "WoS") echo " selected"; ?>>WoS</option>
                    <option value="MyCite"<?php if ($index_type === "MyCite") echo " selected"; ?>>MyCite</option>
                </select>
                <span class="error"><?php echo $index_type_err; ?></span>
            </div>
            <div class="form-group">
                <label for="collaboration">Collaboration:</label>
                <select name="collaboration" id="collaboration">
                    <option value="">Please select</option>
                    <option value="Industry"<?php if ($collaboration === "Industry") echo " selected"; ?>>Industry</option>
                    <option value="National"<?php if ($collaboration === "National") echo " selected"; ?>>National</option>
                    <option value="International"<?php if ($collaboration === "International") echo " selected"; ?>>International</option>
                </select>
                <span class="error"><?php echo $collaboration_err; ?></span>
            </div>
            <div class="form-group">
                <label for="author_type">Author Type:</label>
                <select name="author_type" id="author_type">
                    <option value="">Please select</option>
                    <option value="Corresponding Author"<?php if ($author_type === "Corresponding Author") echo " selected"; ?>>Corresponding Author</option>
                    <option value="Sole Author from UiTM"<?php if ($author_type === "Sole Author from UiTM") echo " selected"; ?>>Sole Author from UiTM</option>
                    <option value="First Author from UiTM"<?php if ($author_type === "First Author from UiTM") echo " selected"; ?>>First Author from UiTM</option>
                </select>
                <span class="error"><?php echo $author_type_err; ?></span>
            </div>
            <div class="form-group">
                <label for="file">Upload Document:</label>
                <input type="file" name="file" id="file">
                <span class="error"><?php echo $file_path_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" value="Submit">
                <input type="reset" value="Reset">
            </div>
            <div class="btn-container">
                <a href="javascript:history.back()" class="btn">Previous Page</a>
                <a href="dashboard.php" class="btn">Home Page</a>
            </div>
        </form>
    </div>
</body>
</html>
