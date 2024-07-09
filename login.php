<?php
session_start();

// Include the database configuration file
include 'db.php';

// Get the database connection
$conn = getDatabaseConnection();

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT id, username, password FROM predictionleague WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashed_password);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $response['success'] = true;
        } else {
            $response['error'] = 'Email and password do not match';
        }
    } else {
        $response['error'] = 'No user found with this email';
    }
    
    $stmt->close();
}

$conn->close();

echo json_encode($response);
?>
