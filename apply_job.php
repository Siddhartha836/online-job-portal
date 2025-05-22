<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is seeker
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'seeker') {
    header("Location: ../login.php");
    exit();
}

// Check if job ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirect back to browse jobs if no valid ID is provided
    header("Location: browse_jobs.php");
    exit();
}

$job_id = $_GET['id'];
$seeker_id = $_SESSION['user_id'];

// Fetch job details to display on the application page
$stmt = $conn->prepare("SELECT JOBS.id, JOBS.title, USERS.name AS employer_name
                        FROM JOBS
                        JOIN USERS ON JOBS.employer_id = USERS.id
                        WHERE JOBS.id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

$job = null;
if ($result->num_rows == 1) {
    $job = $result->fetch_assoc();
}

$stmt->close();

// If job is not found, redirect back to browse jobs
if (!$job) {
    $conn->close();
    header("Location: browse_jobs.php");
    exit();
}

// Check if the seeker has already applied for this job
$stmt_check = $conn->prepare("SELECT id FROM APPLICATIONS WHERE job_id = ? AND seeker_id = ?");
$stmt_check->bind_param("ii", $job_id, $seeker_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    // Seeker has already applied
    $stmt_check->close();
    $conn->close();
    $_SESSION['error'] = "You have already applied for this job.";
    header("Location: view_job.php?id=" . $job_id); // Redirect back to job details page
    exit();
}

$stmt_check->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for <?php echo htmlspecialchars($job['title']); ?></title>
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
                <li><a href="../profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main class="container">
        <section class="form-section">
            <h2>Apply for: <?php echo htmlspecialchars($job['title']); ?> at <?php echo htmlspecialchars($job['employer_name']); ?></h2>

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

            <form action="process_application.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job_id); ?>">

                <div class="form-group">
                    <label for="cover_letter">Cover Letter:</label>
                    <textarea id="cover_letter" name="cover_letter" rows="8" required></textarea>
                </div>

                <div class="form-group">
                    <label for="resume">Upload Resume (.pdf, .docx):</label>
                    <input type="file" id="resume" name="resume" accept=".pdf,.docx" required>
                </div>

                <button type="submit" class="button">Submit Application</button>
            </form>
        </section>
    </main>
    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>