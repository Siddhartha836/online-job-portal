<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is employer, and if job_id is provided
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'employer' || !isset($_GET['job_id']) || !is_numeric($_GET['job_id'])) {
    header("Location: ../login.php"); // Redirect if not logged in, not employer, or no valid job ID
    exit();
}

$employer_id = $_SESSION['user_id'];
$job_id = $_GET['job_id'];

// Verify that the job belongs to the logged-in employer
$stmt_job = $conn->prepare("SELECT id, title FROM JOBS WHERE id = ? AND employer_id = ?");
$stmt_job->bind_param("ii", $job_id, $employer_id);
$stmt_job->execute();
$result_job = $stmt_job->get_result();
$job = $result_job->fetch_assoc();
$stmt_job->close();

// If job not found or doesn't belong to this employer, redirect
if (!$job) {
    $conn->close();
    $_SESSION['error'] = "Job not found or you do not have permission to view applicants for this job.";
    header("Location: dashboard.php");
    exit();
}

$job_title = htmlspecialchars($job['title']);

// Fetch applicants for this job
$stmt_applicants = $conn->prepare("SELECT APPLICATIONS.id AS application_id, APPLICATIONS.cover_letter, APPLICATIONS.resume_path, APPLICATIONS.status, APPLICATIONS.application_date,
                                    USERS.id AS seeker_id, USERS.name AS seeker_name, USERS.email AS seeker_email
                                    FROM APPLICATIONS
                                    JOIN USERS ON APPLICATIONS.seeker_id = USERS.id
                                    WHERE APPLICATIONS.job_id = ?
                                    ORDER BY APPLICATIONS.application_date ASC"); // Order by application date
$stmt_applicants->bind_param("i", $job_id);
$stmt_applicants->execute();
$result_applicants = $stmt_applicants->get_result();

$applicants = [];
if ($result_applicants->num_rows > 0) {
    while($row = $result_applicants->fetch_assoc()) {
        $applicants[] = $row;
    }
}

$stmt_applicants->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicants for <?php echo $job_title; ?> - Employer Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Employer Dashboard</h1>
        <nav>
            <ul>
                <li><span>Welcome, Employer <?php echo htmlspecialchars($_SESSION['name']); ?>!</span></li>
                <li><a href="dashboard.php">My Jobs</a></li>
                <li><a href="add_job.php">Post New Job</a></li>
                <li><a href="../profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main class="container">
        <h2>Applicants for "<?php echo $job_title; ?>"</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (count($applicants) > 0): ?>
            <div class="applicant-list">
                <?php foreach ($applicants as $applicant): ?>
                    <div class="applicant-item">
                        <h3>Applicant: <?php echo htmlspecialchars($applicant['seeker_name']); ?></h3>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($applicant['seeker_email']); ?></p>
                        <p><strong>Application Date:</strong> <?php echo htmlspecialchars($applicant['application_date']); ?></p>
                        <p><strong>Status:</strong> <span class="status-<?php echo strtolower($applicant['status']); ?>"><?php echo htmlspecialchars($applicant['status']); ?></span></p>

                        <h4>Cover Letter:</h4>
                        <p><?php echo nl2br(htmlspecialchars($applicant['cover_letter'])); ?></p>

                        <h4>Resume:</h4>
                        <?php
                        $resume_path = htmlspecialchars($applicant['resume_path']);
                        // Ensure the path is relative for the link
                        $display_path = str_replace('../', '', $resume_path); // Remove the initial '../'
                        ?>
                        <p><a href="../<?php echo $display_path; ?>" target="_blank" class="button button-small">View Resume</a></p>

                        <!-- Link to view seeker profile -->
                        <p><a href="view_seeker_profile.php?seeker_id=<?php echo $applicant['seeker_id']; ?>" class="button button-small">View Profile</a></p>

                        <!-- Actions to change status -->
                        <div class="applicant-actions">
                            <?php if ($applicant['status'] !== 'Accepted'): ?>
                                <a href="process_status_change.php?app_id=<?php echo $applicant['application_id']; ?>&status=Accepted&job_id=<?php echo $job_id; ?>" class="button button-small">Accept</a>
                            <?php endif; ?>
                            <?php if ($applicant['status'] !== 'Rejected'): ?>
                                <a href="process_status_change.php?app_id=<?php echo $applicant['application_id']; ?>&status=Rejected&job_id=<?php echo $job_id; ?>" class="button button-small">Reject</a>
                            <?php endif; ?>
                             <?php if ($applicant['status'] !== 'Pending'): ?>
                                <a href="process_status_change.php?app_id=<?php echo $applicant['application_id']; ?>&status=Pending&job_id=<?php echo $job_id; ?>" class="button button-small">Mark as Pending</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No applicants for this job yet.</p>
        <?php endif; ?>

        <p><a href="dashboard.php">Back to My Job Listings</a></p>

    </main>
    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>