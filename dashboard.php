<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include('db.php');

// Fetch user data from the database
$userId = $_SESSION['user_id'];
$conn = getDatabaseConnection();

$sql = "SELECT username, ticket_balance FROM predictionleague WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $userData = $result->fetch_assoc();
} else {
    // Handle user not found
    die("User not found.");
}

// Fetch round history
$sql = "SELECT date, round, points, position FROM round_history WHERE user_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$roundHistory = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch badges
$sql = "SELECT badge FROM badges WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$badges = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Football Prediction League</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-nav">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Football Prediction League</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($userData['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="#">Account Settings</a></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="container mt-5">
        <!-- User Info Section -->
        <div class="user-info mb-4">
            <h2>Welcome, <?php echo htmlspecialchars($userData['username']); ?></h2>
            <p>Ticket Balance: <?php echo htmlspecialchars($userData['ticket_balance']); ?></p>
        </div>

        <!-- Round History Section -->
        <div class="round-history mb-4">
            <h3>Round History</h3>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Round</th>
                            <th>Points</th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roundHistory as $history): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($history['date']); ?></td>
                                <td><?php echo htmlspecialchars($history['round']); ?></td>
                                <td><?php echo htmlspecialchars($history['points']); ?></td>
                                <td><?php echo htmlspecialchars($history['position']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Achievement Badges Section -->
        <div class="achievement-badges">
            <h3>Achievements</h3>
            <div class="badges d-flex flex-wrap">
                <?php foreach ($badges as $badge): ?>
                    <div class="badge"><?php echo htmlspecialchars($badge['badge']); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <footer class="bg-light text-center py-3">
        <p>Football Prediction League &copy; 2023</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
