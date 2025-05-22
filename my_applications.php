<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is seeker
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'seeker') {
    header("Location: ../login.php");
    exit();
}

$seeker_id = $_SESSION['user_id'];

// Fetch applications for the logged-in seeker
$stmt = $conn->prepare("SELECT APPLICATIONS.id AS application_id, APPLICATIONS.application_date, APPLICATIONS.status,
                               JOBS.title AS job_title, JOBS.location AS job_location, USERS.name AS employer_name
                        FROM APPLICATIONS
                        JOIN JOBS ON APPLICATIONS.job_id = JOBS.id
                        JOIN USERS ON JOBS.employer_id = USERS.id
                        WHERE APPLICATIONS.seeker_id = ?
                        ORDER BY APPLICATIONS.application_date DESC"); // Order by most recent application first
$stmt->bind_param("i", $seeker_id);
$stmt->execute();
$result = $stmt->get_result();

$applications = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }
}

$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Job Seeker Panel</title>
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
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main class="container">
        <h2>My Job Applications</h2>

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

        <?php if (count($applications) > 0): ?>
            <div class="application-list">
                <?php foreach ($applications as $app): ?>
                    <div class="application-item">
                        <h3><?php echo htmlspecialchars($app['job_title']); ?></h3>
                        <p><strong>Employer:</strong> <?php echo htmlspecialchars($app['employer_name']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($app['job_location']); ?></p>
                        <p><strong>Applied On:</strong> <?php echo htmlspecialchars($app['application_date']); ?></p>
                        <p><strong>Status:</strong> <span class="status-<?php echo strtolower($app['status']); ?>"><?php echo htmlspecialchars($app['status']); ?></span></p>
                        <!-- Optionally add a link back to the job details page -->
                        <p><a href="view_job.php?id=<?php echo $app['job_id']; ?>" class="button button-small">View Job Details</a></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>You have not submitted any job applications yet.</p>
        <?php endif; ?>

    </main>
    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>