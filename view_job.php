<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is seeker
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'seeker') {
    header("Location: ../login.php");
    exit();
}

$seeker_id = $_SESSION['user_id'];

// Check if job ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid job ID.";
    header("Location: browse_jobs.php"); // Redirect back to browse page
    exit();
}

$job_id = $_GET['id'];

// Fetch job details
$stmt_job = $conn->prepare("SELECT JOBS.id, JOBS.title, JOBS.description, JOBS.location, JOBS.salary, JOBS.deadline, USERS.name AS employer_name
                            FROM JOBS
                            JOIN USERS ON JOBS.employer_id = USERS.id
                            WHERE JOBS.id = ?");
$stmt_job->bind_param("i", $job_id);
$stmt_job->execute();
$result_job = $stmt_job->get_result();
$job = $result_job->fetch_assoc();
$stmt_job->close();

// If job not found
if (!$job) {
    $conn->close();
    $_SESSION['error'] = "Job not found.";
    header("Location: browse_jobs.php"); // Redirect back to browse page
    exit();
}

// Check if the seeker has already applied for this job
$stmt_check_application = $conn->prepare("SELECT id FROM APPLICATIONS WHERE seeker_id = ? AND job_id = ?");
$stmt_check_application->bind_param("ii", $seeker_id, $job_id);
$stmt_check_application->execute();
$result_check_application = $stmt_check_application->get_result();
$has_applied = $result_check_application->num_rows > 0;
$stmt_check_application->close();

$conn->close(); // Close connection after fetching all necessary data
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> - Job Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Job Seeker Dashboard</h1>
        <nav>
            <ul>
                <li><span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span></li>
                <li><a href="../index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="browse_jobs.php">Browse Jobs</a></li>
                <li><a href="my_applications.php">My Applications</a></li>
                <li><a href="../profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main class="container">
        <h2><?php echo htmlspecialchars($job['title']); ?></h2>

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

        <div class="job-details">
            <p><strong>Company:</strong> <?php echo htmlspecialchars($job['employer_name']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
            <p><strong>Salary:</strong> $<?php echo htmlspecialchars(number_format($job['salary'], 2)); ?></p>
            <p><strong>Deadline:</strong> <?php echo htmlspecialchars($job['deadline']); ?></p>

            <h3>Job Description:</h3>
            <div class="job-description">
                <?php echo nl2br(htmlspecialchars($job['description'])); ?>
            </div>
        </div>

        <?php if ($has_applied): ?>
            <div class="alert info">
                You have already applied for this job. View your applications <a href="my_applications.php">here</a>.
            </div>
        <?php else: ?>
            <div class="apply-form form-section">
                <h3>Apply for this Job</h3>
                <form action="process_application.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                    <div class="form-group">
                        <label for="cover_letter">Cover Letter:</label>
                        <textarea id="cover_letter" name="cover_letter" rows="6" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="resume">Upload Resume (PDF or DOCX, max 2MB):</label>
                        <input type="file" id="resume" name="resume" accept=".pdf,.docx" required>
                    </div>
                    <button type="submit" class="button">Submit Application</button>
                </form>
            </div>
        <?php endif; ?>

        <p><a href="browse_jobs.php">Back to Browse Jobs</a></p>

    </main>
    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>