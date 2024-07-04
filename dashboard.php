<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : '';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Fetch user details
$sql = "SELECT username, fullname, profile_picture FROM users WHERE user_id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['username'];
    $fullname = $user['fullname'];
    $profile_picture = $user['profile_picture'] ?: "image/default.jpg"; // Ensure default path is correct
} else {
    $fullname = "Unknown User";
    $profile_picture = "image/default.jpg"; // Default profile picture if none is uploaded
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="icon" type="image/png" href="image/PMM.png">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; color: #343a40; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: auto; padding: 20px; border-radius: 8px; background-color: #fff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); margin-top: 50px; }
        .sidebar { width: 200px; position: fixed; top: 0; left: 0; height: 100%; background-color: #87B49F; color: #fff; padding-top: 20px; }
        .sidebar a { display: block; color: #fff; padding: 10px; text-decoration: none; }
        .sidebar a:hover { background-color: #007bff; }
        .profile img { width: 100px; border-radius: 50%; margin: 0 auto; display: block; }
        .profile p { text-align: center; }
        .content { margin-left: 220px; padding: 20px; }
        .submenu { display: none; }
        .submenu.show { display: block; }
        .menu-item.active + .submenu { display: block; }
        h1 { font-size: 2em; text-align: left; margin-bottom: 20px; }
        header { background-color: #28a745; color: #fff; padding: 10px 0; text-align: center; font-size: 24px; font-weight: bold; }
        .icon-container { display: flex; justify-content: left; gap: 30px; margin-top: 20px; }
        .icon-container a { text-align: center; color: #343a40; text-decoration: none; }
        .icon-container img { width: 80px; height: 80px; }
        .icon-container p { margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #dee2e6; text-align: left; }
        th { background-color: #007bff; color: #fff; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        tr:hover { background-color: #cce5ff; }
        
    </style>
</head>
<body>
<header>Publication Mentorship Marvel</header>
    <div class="sidebar">
        <div class="profile">
            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
            <p><?php echo htmlspecialchars($username); ?></p>
        </div>
        <a href="javascript:void(0);" class="menu-item">Profile</a>
        <div class="submenu">
            <a href="view-profile.php">View Profile</a>
            <a href="update-profile.php">Update Profile</a>
        </div>
        <?php if ($role == 'mentor'): ?>
            <a href="javascript:void(0);" class="menu-item">Mentee</a>
            <div class="submenu">
                <a href="list-mentee.php">Mentee's List</a>
                <a href="mentee-publication.php">Mentee's Publications</a> 
            </div>
            <a href="javascript:void(0);" class="menu-item">Validation</a>
            <div class="submenu">
                <a href="list-validation.php">Validation List</a>
            </div>
             <?php elseif ($role == 'super_admin'): ?>
                <a href="javascript:void(0);" class="menu-item">Mentor-Mentee</a>
            <div class="submenu">
                <a href="mentor-super.php">List of Mentor</a>
                <a href="mentee-super.php">List of Mentee</a>
            </div>
            <a href="javascript:void(0);" class="menu-item">Report</a>
            <div class="submenu">
                <a href="progress.php">Progress</a>
            </div>
        <?php else: ?>
            <a href="list-mentor.php">Mentor</a>
            <a href="javascript:void(0);" class="menu-item">Publication</a>
            <div class="submenu">
                <a href="add-publication.php">Add Publication</a>
                <a href="view-publication.php">View My Publications</a>
            </div>
            <a href="javascript:void(0);" class="menu-item">Validation</a>
            <div class="submenu">
                <a href="status.php">Status</a>
            </div>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </div>
    <div class="content">
        <h1>Welcome, <?php echo htmlspecialchars($fullname); ?>!</h1>
        <p>This is the <?php echo htmlspecialchars($role); ?>'s home page.</p>
        <?php if ($role == 'mentor'): ?>
            <div class="icon-container">
                <a href="view-profile.php">
                    <img src="image/edit-profile-icon.jpg" alt="View Profile Icon">
                    <p>View Profile</p>
                </a>
                <a href="list-mentee.php">
                    <img src="image/list-mentee-icon.png" alt="Mentee's List Icon">
                    <p>Mentee's List</p>
                </a>
                <a href="list-validation.php">
                    <img src="image/validate.png" alt="Validation Icon">
                    <p>Validation List</p>
                </a>
            </div>
            <?php elseif ($role == 'super_admin'): ?>
    
        <?php else: ?>
            <div class="icon-container">
                <a href="view-profile.php">
                    <img src="image/edit-profile-icon.jpg" alt="View Profile Icon">
                    <p>View Profile</p>
                </a>
                <a href="view-publication.php">
                    <img src="image/add-pub-icon.jpg" alt="View Publications Icon">
                    <p>View Publications</p>
                </a>
                <a href="status.php">
                    <img src="image/valid-pub-icon.jpg" alt="Status Icon">
                    <p>View Status</p>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    item.classList.toggle('active');
                    var submenu = item.nextElementSibling;
                    if (submenu) {
                        submenu.classList.toggle('show');
                    }
                });
            });
        });
    </script>
</body>
</html>
