<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is employer, and if application_id is provided
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'employer' || !isset($_GET['app_id']) || !is_numeric($_GET['app_id'])) {
    $_SESSION['error'] = "Invalid request or insufficient permissions.";
    header("Location: dashboard.php"); // Redirect to employer dashboard
    exit();
}

$employer_id = $_SESSION['user_id'];
$application_id = $_GET['app_id'];

// Fetch application details, including job_id and resume_path, and verify employer ownership
$stmt = $conn->prepare("SELECT APPLICATIONS.resume_path, JOBS.employer_id
                        FROM APPLICATIONS
                        JOIN JOBS ON APPLICATIONS.job_id = JOBS.id
                        WHERE APPLICATIONS.id = ? AND JOBS.employer_id = ?");
$stmt->bind_param("ii", $application_id, $employer_id);
$stmt->execute();
$result = $stmt->get_result();
$application_data = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Check if application exists and belongs to this employer
if (!$application_data || empty($application_data['resume_path'])) {
    $_SESSION['error'] = "Resume not found or you do not have permission to access it.";
    header("Location: dashboard.php"); // Redirect to employer dashboard
    exit();
}

$resume_path = $application_data['resume_path'];
$full_path = realpath(__DIR__ . '/../' . $resume_path); // Get the absolute path

// Ensure the file exists and is within the allowed uploads directory
$upload_dir = realpath(__DIR__ . '/../uploads/resumes/');
if (!file_exists($full_path) || strpos($full_path, $upload_dir) !== 0) {
    $_SESSION['error'] = "Resume file not found or access denied.";
    header("Location: dashboard.php"); // Redirect to employer dashboard
    exit();
}

// Determine file type and name for download
$file_extension = pathinfo($full_path, PATHINFO_EXTENSION);
$mime_type = mime_content_type($full_path); // Get MIME type
$file_name = basename($full_path); // Use the saved filename

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($full_path));

// Clear output buffer
ob_clean();
flush();

// Read the file and output it to the browser
readfile($full_path);

exit; // Stop script execution after file is sent
?>