<?php
session_start();
require_once 'db.php'; // Include the database connection file

// Check if user is logged in and if the request method is POST
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php"); // Redirect to login if not logged in or not a POST request
    exit();
}

$user_id = $_SESSION['user_id'];
$new_name = $_POST['name'];
$new_email = $_POST['email'];

// Basic validation and sanitization
$new_name = htmlspecialchars($new_name);
$new_email = filter_var($new_email, FILTER_SANITIZE_EMAIL);

// Validate email format
if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format.";
    header("Location: profile.php");
    exit();
}

// Check if the new email already exists for *another* user
$stmt = $conn->prepare("SELECT id FROM USERS WHERE email = ? AND id != ?");
$stmt->bind_param("si", $new_email, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['error'] = "Email already exists for another user.";
    $stmt->close();
    $conn->close();
    header("Location: profile.php");
    exit();
}
$stmt->close();

// Update user information in the database
$stmt = $conn->prepare("UPDATE USERS SET name = ?, email = ? WHERE id = ?");
$stmt->bind_param("ssi", $new_name, $new_email, $user_id);

if ($stmt->execute()) {
    // Update session variable if name was changed
    $_SESSION['name'] = $new_name;
    $_SESSION['success'] = "Profile updated successfully!";
} else {
    $_SESSION['error'] = "Error updating profile: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirect back to the profile page
header("Location: profile.php");
exit();
?>