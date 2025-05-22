<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is employer, and if necessary parameters are provided
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'employer' || !isset($_GET['app_id']) || !is_numeric($_GET['app_id']) || !isset($_GET['status']) || !isset($_GET['job_id']) || !is_numeric($_GET['job_id'])) {
    $_SESSION['error'] = "Invalid request or insufficient permissions.";
    header("Location: dashboard.php"); // Redirect to employer dashboard
    exit();
}

$employer_id = $_SESSION['user_id'];
$application_id = $_GET['app_id'];
$new_status = $_GET['status'];
$job_id = $_GET['job_id']; // Get job_id to redirect back to the correct applicants page

// Validate the new status
$allowed_statuses = ['Pending', 'Accepted', 'Rejected'];
if (!in_array($new_status, $allowed_statuses)) {
    $_SESSION['error'] = "Invalid application status provided.";
    header("Location: view_applicants.php?job_id=" . $job_id); // Redirect back to applicants page
    exit();
}

// Verify that the application exists and belongs to a job posted by this employer
$stmt_check = $conn->prepare("SELECT APPLICATIONS.id
                              FROM APPLICATIONS
                              JOIN JOBS ON APPLICATIONS.job_id = JOBS.id
                              WHERE APPLICATIONS.id = ? AND JOBS.employer_id = ?");
$stmt_check->bind_param("ii", $application_id, $employer_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    $stmt_check->close();
    $conn->close();
    $_SESSION['error'] = "Application not found or you do not have permission to modify its status.";
    header("Location: dashboard.php"); // Redirect to employer dashboard
    exit();
}
$stmt_check->close();

// Update the application status
$stmt_update = $conn->prepare("UPDATE APPLICATIONS SET status = ? WHERE id = ?");
$stmt_update->bind_param("si", $new_status, $application_id);

if ($stmt_update->execute()) {
    $_SESSION['success'] = "Application status updated to '" . htmlspecialchars($new_status) . "' successfully!";
} else {
    $_SESSION['error'] = "Error updating application status: " . $stmt_update->error;
}

$stmt_update->close();
$conn->close();

// Redirect back to the view applicants page for the specific job
header("Location: view_applicants.php?job_id=" . $job_id);
exit();
?>