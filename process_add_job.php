<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is employer, and if the request method is POST
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'employer' || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../login.php"); // Redirect to login if not logged in or not a POST request
    exit();
}

$employer_id = $_SESSION['user_id'];

// Get job details from the form
$title = $_POST['title'];
$description = $_POST['description'];
$location = $_POST['location'];
$salary = $_POST['salary'];
$deadline = $_POST['deadline'];

// Basic validation and sanitization
$title = htmlspecialchars($title);
$description = htmlspecialchars($description);
$location = htmlspecialchars($location);
$salary = filter_var($salary, FILTER_VALIDATE_FLOAT); // Validate as float
$deadline = htmlspecialchars($deadline);

// Validate required fields and salary/deadline format
if (empty($title) || empty($description) || empty($location) || $salary === false || empty($deadline)) {
    $_SESSION['error'] = "Please fill in all required fields correctly.";
    header("Location: add_job.php");
    exit();
}

// Validate deadline date format (basic check)
if (!strtotime($deadline)) {
     $_SESSION['error'] = "Invalid deadline date format.";
     header("Location: add_job.php");
     exit();
}


// Insert job into the database
$stmt = $conn->prepare("INSERT INTO JOBS (employer_id, title, description, location, salary, deadline) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssds", $employer_id, $title, $description, $location, $salary, $deadline); // 'i' for int, 's' for string, 'd' for double

if ($stmt->execute()) {
    $_SESSION['success'] = "Job posted successfully!";
} else {
    $_SESSION['error'] = "Error posting job: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirect back to the employer dashboard
header("Location: dashboard.php");
exit();
?>