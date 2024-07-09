<?php
include 'db.php';
include 'load_profanity.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getDatabaseConnection();

    // Sanitize input data
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Load profanity list
    $profanityList = file('profanity.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $profanityList = array_map('trim', $profanityList);

    // Normalization replacements
    $replacements = [
        '0' => 'o',
        '1' => 'i',
        '2' => 'z',
        '3' => 'e',
        '4' => 'a',
        '5' => 's',
        '6' => 'g',
        '7' => 't',
        '8' => 'b',
        '9' => 'p',
        '@' => 'a',
        '$' => 's',
        '!' => 'i',
        '|' => 'i'
    ];

    // Normalize username
    function normalizeUsername($username, $replacements) {
        return preg_replace_callback('/[0123456789@$!|]/', function ($matches) use ($replacements) {
            return $replacements[$matches[0]] ?? $matches[0];
        }, strtolower($username));
    }

    // Check if username contains disallowed characters
    if (!preg_match('/^[a-z0-9_]+$/i', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }

    // Check for profane username
    $normalizedUsername = normalizeUsername($username, $replacements);
    foreach ($profanityList as $profaneWord) {
        $normalizedProfaneWord = normalizeUsername($profaneWord, $replacements);
        $profanePattern = '/' . implode('.*', str_split($normalizedProfaneWord)) . '/i';
        if (preg_match($profanePattern, $normalizedUsername)) {
            $errors[] = "Username contains inappropriate language.";
            break;
        }
    }

    // Check if the username or email already exists
    $stmt = $conn->prepare("SELECT id FROM predictionleague WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Username or Email already in use.";
    }
    $stmt->close();

    // Check password requirements
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password) || preg_match('/[^a-zA-Z\d]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter, one number, and no special characters.";
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    // Validate first and last name
    if (empty($first_name) || empty($last_name)) {
        $errors[] = "First name and last name are required.";
    }

    if (empty($errors)) {
        // Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO predictionleague (username, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $first_name, $last_name, $email, $hashed_password);
        if ($stmt->execute()) {
            echo json_encode(["success" => "Registration successful."]);
        } else {
            echo json_encode(["error" => "Error: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["errors" => $errors]);
    }

    $conn->close();
    exit();
}
?>
