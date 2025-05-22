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
    header("Location: browse_jobs.php"); // Redirect back to browse page
    exit();
}

// Get job_id and cover_letter from POST data
$job_id = $_POST['job_id'] ?? null;
$cover_letter = $_POST['cover_letter'] ?? '';

// Validate job_id
if (!isset($job_id) || !is_numeric($job_id)) {
    $_SESSION['error'] = "Invalid job ID provided.";
    header("Location: browse_jobs.php");
    exit();
}

// Validate cover letter
if (empty($cover_letter)) {
    $_SESSION['error'] = "Cover letter cannot be empty.";
    header("Location: view_job.php?id=" . $job_id); // Redirect back to job view page
    exit();
}

// --- Handle Resume Upload ---
$upload_dir = '../uploads/resumes/'; // Directory to save resumes (relative to this script)
$uploaded_file = $_FILES['resume'] ?? null;
$resume_path = null; // To store the path in the database

// Create upload directory if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Create directory with permissions
}

if ($uploaded_file && $uploaded_file['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']; // PDF and DOCX
    $max_file_size = 2 * 1024 * 1024; // 2MB

    // Validate file type
    if (!in_array($uploaded_file['type'], $allowed_types)) {
        $_SESSION['error'] = "Invalid file type. Only PDF and DOCX are allowed.";
        header("Location: view_job.php?id=" . $job_id);
        exit();
    }

    // Validate file size
    if ($uploaded_file['size'] > $max_file_size) {
        $_SESSION['error'] = "File size exceeds the maximum limit of 2MB.";
        header("Location: view_job.php?id=" . $job_id);
        exit();
    }

    // Generate a unique filename
    $file_extension = pathinfo($uploaded_file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('resume_', true) . '.' . $file_extension;
    $destination_path = $upload_dir . $new_filename;

    // Move the uploaded file
    if (move_uploaded_file($uploaded_file['tmp_name'], $destination_path)) {
        $resume_path = $destination_path; // Store the path for the database
    } else {
        $_SESSION['error'] = "Error uploading your resume. Please try again.";
        header("Location: view_job.php?id=" . $job_id);
        exit();
    }
} else {
    // Handle upload errors or no file uploaded (though the form requires it)
     if ($uploaded_file['error'] !== UPLOAD_ERR_NO_FILE) {
         $_SESSION['error'] = "An upload error occurred: Code " . $uploaded_file['error'];
     } else {
         $_SESSION['error'] = "Resume file is required.";
     }
    header("Location: view_job.php?id=" . $job_id);
    exit();
}

// --- Save Application to Database ---

// Check if the seeker has already applied for this job (double check)
$stmt_check = $conn->prepare("SELECT id FROM APPLICATIONS WHERE seeker_id = ? AND job_id = ?");
$stmt_check->bind_param("ii", $seeker_id, $job_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $stmt_check->close();
    $conn->close();
    // Optionally delete the uploaded resume if application already exists
    if (file_exists($resume_path)) {
        unlink($resume_path);
    }
    $_SESSION['error'] = "You have already applied for this job.";
    header("Location: view_job.php?id=" . $job_id);
    exit();
}
$stmt_check->close();


// Insert application into the database
$stmt_insert = $conn->prepare("INSERT INTO APPLICATIONS (job_id, seeker_id, cover_letter, resume_path, status, application_date) VALUES (?, ?, ?, ?, 'Pending', NOW())");
$stmt_insert->bind_param("iiss", $job_id, $seeker_id, $cover_letter, $resume_path);

if ($stmt_insert->execute()) {
    $_SESSION['success'] = "Your application has been submitted successfully!";
    header("Location: my_applications.php"); // Redirect to My Applications page
} else {
    // If database insert fails, delete the uploaded resume
    if (file_exists($resume_path)) {
        unlink($resume_path);
    }
    $_SESSION['error'] = "Error submitting your application: " . $stmt_insert->error;
    header("Location: view_job.php?id=" . $job_id); // Redirect back to job view page
}

$stmt_insert->close();
$conn->close();
?>