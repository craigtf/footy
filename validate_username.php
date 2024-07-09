<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "predictionleague"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $sql = "SELECT id FROM predictionleague WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    $response = array('available' => $stmt->num_rows === 0);
    $stmt->close();
    $conn->close();

    echo json_encode($response);
} else {
    echo json_encode(['error' => 'No username provided']);
}
?>
