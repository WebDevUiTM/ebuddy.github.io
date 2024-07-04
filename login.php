<?php
session_start();
include('config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password, role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($hashed_password, $role);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid password.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid user ID.']);
    }

    $stmt->close();
    $conn->close();
}
?>
