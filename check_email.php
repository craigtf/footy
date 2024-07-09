<?php
header('Content-Type: application/json');
include 'db.php';

$conn = getDatabaseConnection();

// Validate and sanitize email input
if (isset($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
    $email = $_GET['email'];

    // Prepare SQL statement
    $sql = "SELECT id FROM predictionleague WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(['error' => 'Failed to prepare SQL statement']);
        $conn->close();
        exit();
    }
    
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    // Check if the email is available
    $response = array('available' => $stmt->num_rows === 0);
    $stmt->close();
} else {
    $response = ['error' => 'Invalid email provided'];
}

$conn->close();
echo json_encode($response);
?>
