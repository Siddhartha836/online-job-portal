<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is seeker
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'seeker') {
    header("Location: ../login.php");
    exit();

}

$seeker_id = $_SESSION['user_id'];
$seeker_name = $_SESSION['name'];

// Fetch the count of applications for the logged-in seeker
$stmt = $conn->prepare("SELECT COUNT(*) AS application_count FROM APPLICATIONS WHERE seeker_id = ?");
$stmt->bind_param("i", $seeker_id);
$stmt->execute();
$result = $stmt->get_result();
$application_data = $result->fetch_assoc();
$application_count = $application_data['application_count'];
$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Job Seeker Dashboard</h1>
        <nav>
            <ul>
                <li><span>Welcome, <?php echo htmlspecialchars($seeker_name); ?>!</span></li>
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
        <h2>Welcome to Your Dashboard, <?php echo htmlspecialchars($seeker_name); ?>!</h2>

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

        <div class="dashboard-summary">
            <div class="summary-item">
                <h3>Applications Submitted</h3>
                <p><?php echo $application_count; ?></p>
                <p><a href="my_applications.php">View My Applications</a></p>
            </div>
            <!-- Add more summary items here later, e.g., Saved Jobs -->
        </div>

        <div class="quick-links">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="browse_jobs.php">Browse Available Jobs</a></li>
                <li><a href="profile.php">Edit Your Profile</a></li>
            </ul>
        </div>

    </main>
    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>