<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Initialize variables
$fullname = $username = $email = $phone_number = $profile_picture = $pusat_pengajian = "";
$fullname_err = $username_err = $email_err = $phone_number_err = $profile_picture_err = $pusat_pengajian_err = "";

// Fetch current user details
$sql = "SELECT fullname, username, email, phone_number, profile_picture, pusat_pengajian FROM users WHERE user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $param_user_id);
    $param_user_id = $user_id;

    if ($stmt->execute()) {
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($fullname, $username, $email, $phone_number, $profile_picture, $pusat_pengajian);
            $stmt->fetch();
        }
    }
    $stmt->close();
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate fullname
    if (empty(trim($_POST["fullname"]))) {
        $fullname_err = "Please enter your fullname.";
    } else {
        $fullname = trim($_POST["fullname"]);
    }

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate phone number
    if (empty(trim($_POST["phone_number"]))) {
        $phone_number_err = "Please enter your phone number.";
    } else {
        $phone_number = trim($_POST["phone_number"]);
    }

    // Validate profile picture upload
    if ($_FILES["profile_picture"]["error"] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file size
        if ($_FILES["profile_picture"]["size"] > 500000) {
            $profile_picture_err = "Sorry, your file is too large.";
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $profile_picture_err = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }

        // Check if $profile_picture_err is set to an empty string
        if (empty($profile_picture_err)) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = $target_file;
            } else {
                $profile_picture_err = "Sorry, there was an error uploading your file.";
            }
        }
    } elseif ($_FILES["profile_picture"]["error"] == UPLOAD_ERR_NO_FILE) {
        $profile_picture = $profile_picture; // No file was uploaded, keep the existing one
    } else {
        $profile_picture_err = "Error uploading file.";
    }

    // Validate pusat pengajian
    if (empty(trim($_POST["pusat_pengajian"]))) {
        $pusat_pengajian_err = "Please select your Pusat Pengajian.";
    } else {
        $pusat_pengajian = trim($_POST["pusat_pengajian"]);
    }

    // Check input errors before updating the database
    if (empty($fullname_err) && empty($username_err) && empty($email_err) && empty($phone_number_err) && empty($profile_picture_err) && empty($pusat_pengajian_err)) {
        // Prepare an update statement
        $sql = "UPDATE users SET fullname = ?, username = ?, email = ?, phone_number = ?, profile_picture = ?, pusat_pengajian = ? WHERE user_id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssi", $param_fullname, $param_username, $param_email, $param_phone_number, $param_profile_picture, $param_pusat_pengajian, $param_user_id);

            // Set parameters
            $param_fullname = $fullname;
            $param_username = $username;
            $param_email = $email;
            $param_phone_number = $phone_number;
            $param_profile_picture = $profile_picture;
            $param_pusat_pengajian = $pusat_pengajian;
            $param_user_id = $user_id;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Update successful, update session variables
                $_SESSION['fullname'] = $fullname;
                $_SESSION['username'] = $username;
                $_SESSION['profile_picture'] = $profile_picture;
                $_SESSION['pusat_pengajian'] = $pusat_pengajian;
                header("Location: dashboard.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <link rel="icon" type="image/png" href="image/PMM.png">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; color: #343a40; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: auto; padding: 20px; border-radius: 8px; background-color: #fff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); margin-top: 50px; }
        h1 { font-size: 2em; text-align: center; margin-bottom: 20px; }
        form { display: flex; flex-direction: column; }
        label { margin-bottom: 5px; }
        input[type="text"], input[type="email"], input[type="tel"], input[type="file"], button, select { padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .error { color: #ff0000; }
        .btn-container { text-align: center; margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #28a745; border: none; border-radius: 5px; text-decoration: none; margin: 5px; transition: background-color 0.3s; }
        .btn:hover { background-color: #218838; }

    </style>
</head>
<body>
    <div class="container">
        <h1>Update Profile</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <label for="fullname">Full Name</label>
            <input type="text" name="fullname" id="fullname" value="<?php echo $fullname; ?>">
            <span class="error"><?php echo $fullname_err; ?></span>
            
            <label for="username">Username</label>
            <input type="text" name="username" value="<?php echo $username; ?>">
            <span class="error"><?php echo $username_err; ?></span>
            
            <label for="email">Email</label>
            <input type="email" name="email" value="<?php echo $email; ?>">
            <span class="error"><?php echo $email_err; ?></span>
            
            <label for="phone_number">Phone Number</label>
            <input type="tel" name="phone_number" value="<?php echo $phone_number; ?>">
            <span class="error"><?php echo $phone_number_err; ?></span>
            
            <label for="pusat_pengajian">School</label>
            <select name="pusat_pengajian">
                <option value="" <?php echo empty($pusat_pengajian) ? 'selected' : ''; ?>>Select School</option>
                <option value="School of Computing Science" <?php echo $pusat_pengajian == 'School of Computing Science' ? 'selected' : ''; ?>>School of Computing Science</option>
                <option value="School of Mathematic Science" <?php echo $pusat_pengajian == 'School of Mathematic Science' ? 'selected' : ''; ?>>School of Mathematic Science</option>
                <option value="School of Information Science" <?php echo $pusat_pengajian == 'School of Information Science' ? 'selected' : ''; ?>>School of Information Science</option>
            </select>
            <span class="error"><?php echo $pusat_pengajian_err; ?></span>
            
            <label for="profile_picture">Profile Picture</label>
            <input type="file" name="profile_picture">
            <span class="error"><?php echo $profile_picture_err; ?></span>
            <div class="btn-container">
            <button type="submit">Update Profile</button>
      
            </div>
            <div class="btn-container">
                <a href="javascript:history.back()" class="btn">Previous Page</a>
                <a href="dashboard.php" class="btn">Home Page</a>
            </div>
        </form>
    </div>
</body>
</html>
