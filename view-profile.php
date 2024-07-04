<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$fullname = $username = $email = $phone_number = $pusat_pengajian= $profile_picture = "";
// Fetch current user details
$sql = "SELECT fullname, username, email, phone_number, pusat_pengajian, profile_picture FROM users WHERE user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $param_user_id);
    $param_user_id = $user_id;

    if ($stmt->execute()) {
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($fullname, $username, $email, $phone_number, $pusat_pengajian, $profile_picture);
            $stmt->fetch();
        }
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Profile</title>
    <link rel="icon" type="image/png" href="image/PMM.png">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; color: #343a40; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: auto; padding: 20px; border-radius: 8px; background-color: #fff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); margin-top: 50px; }
        h1 { font-size: 2em; text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        img { max-width: 200px; }
        .btn-container { text-align: center; margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #28a745; border: none; border-radius: 5px; text-decoration: none; margin: 5px; transition: background-color 0.3s; }
        .btn:hover { background-color: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <h1>View Profile</h1>
        <table>
            <tr>
                <th>Attribute</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Full Name</td>
                <td><?php echo htmlspecialchars($fullname); ?></td>
            </tr>
            <tr>
                <td>Username</td>
                <td><?php echo htmlspecialchars($username); ?></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><?php echo htmlspecialchars($email); ?></td>
            </tr>
            <tr>
                <td>Phone Number</td>
                <td><?php echo htmlspecialchars($phone_number); ?></td>
            </tr>
            <tr>
                <td>School</td>
                <td><?php echo htmlspecialchars($pusat_pengajian); ?></td>
            </tr>
            <tr>
                <td>Profile Picture</td>
                <td><img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture"></td>
            </tr>
        </table>
        <div class="btn-container">
            <a href="javascript:history.back()" class="btn">Previous Page</a>
            <a href="dashboard.php" class="btn">Home Page</a>
        </div>
        <p><a href="update-profile.php">Update Profile</a></p>
    </div>
</body>
</html>
