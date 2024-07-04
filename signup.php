<?php
// Include database connection
include('config.php'); // Ensure this file contains the connection code

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];

    // Validate user ID
    $stmt = $conn->prepare("SELECT fullname, role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // User ID is valid
        $stmt->bind_result($fullname, $role);
        $stmt->fetch();

        // Hash the password before storing
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Update the user's password
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user_id);

        if ($update_stmt->execute()) {
            $message = "Password set successfully for " . htmlspecialchars($fullname) . " (" . htmlspecialchars($role) . ").";
            $message_type = "success";
        } else {
            $message = "Error updating password: " . $conn->error;
            $message_type = "error";
        }

        $update_stmt->close();
    } else {
        // User ID is invalid
        $message = "Invalid user ID.";
        $message_type = "error";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup</title>
    <link rel="icon" type="image/png" href="image/PMM.png">
  <style>
         body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 1px 30px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
            margin-top: 190px;
            margin-left: 95px;
        }
        .container-w1 { 
            width: 60%; 
            height: 100%;
            background-image: url("image/background3.png");
            background-position: top;
            background-repeat: no-repeat;
            background-size: 840px 642px;
            }
        .container-w2{
            width: 40%;
            height: 100%;
            background-color: white;
        }
        .container-1 {
            background-color: black;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 1px 30px rgba(0, 0, 0, 0.1);
            width: 600px;
            text-align: center;
            margin-top: 0px;
            margin-left: 85px;
            background-color: rgba(255, 255, 255, 0.3);
        }
        h1{ 
            font-family: "Times New Roman", Times, serif;
            font-size: 40px;
            font-weight: bold;
            text-align: center;
            color: black;
        }
        h2 {
            color: black;
        }
        .error { color: red; }
        .form-group { margin-bottom: 15px; }
        label { display: block; text-align:left; }
        a { display: block; text-align: center; margin-top: 10px; }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

    </style>
</head>
<body>
<div class="container-w1">

</div>
<div class="container-w2">
<div class="container">
        <h2>Sign Up</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="signup.php">
            <label for="user_id">Staff ID:</label>
            <input type="text" id="user_id" name="user_id" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" value="Sign Up">
        </form>
        <a href="index.html">Login</a>
    </div>    
</div>
</body>
</html>