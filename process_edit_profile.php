<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is seeker
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'seeker') {
    header("Location: ../login.php");
    exit();
}

$seeker_id = $_SESSION['user_id'];

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: profile.php"); // Redirect back to profile page
    exit();
}

// Get profile data from POST
$skills = $_POST['skills'] ?? '';
$education = $_POST['education'] ?? '';
$experience = $_POST['experience'] ?? '';

// Check if a profile already exists for this seeker
$stmt_check = $conn->prepare("SELECT id FROM SEEKER_PROFILES WHERE seeker_id = ?");
$stmt_check->bind_param("i", $seeker_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Profile exists, update it
    $stmt_update = $conn->prepare("UPDATE SEEKER_PROFILES SET skills = ?, education = ?, experience = ? WHERE seeker_id = ?");
    $stmt_update->bind_param("sssi", $skills, $education, $experience, $seeker_id);

    if ($stmt_update->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating profile: " . $stmt_update->error;
    }
    $stmt_update->close();

} else {
    // Profile does not exist, insert a new one
    $stmt_insert = $conn->prepare("INSERT INTO SEEKER_PROFILES (seeker_id, skills, education, experience) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("isss", $seeker_id, $skills, $education, $experience);

    if ($stmt_insert->execute()) {
        $_SESSION['success'] = "Profile created successfully!";
    } else {
        $_SESSION['error'] = "Error creating profile: " . $stmt_insert->error;
    }
    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();

// Redirect back to the profile page
header("Location: profile.php");
exit();
?>