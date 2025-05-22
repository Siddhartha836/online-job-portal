<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is employer, and if seeker_id is provided
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'employer' || !isset($_GET['seeker_id']) || !is_numeric($_GET['seeker_id'])) {
    header("Location: ../login.php"); // Redirect if not logged in, not employer, or no valid seeker ID
    exit();
}

$employer_id = $_SESSION['user_id']; // Employer ID (not used for fetching seeker profile, but good for context)
$seeker_id = $_GET['seeker_id'];

// Fetch seeker's basic user details and profile details
// Use LEFT JOIN in case the seeker profile hasn't been created yet
$stmt = $conn->prepare("SELECT USERS.name, USERS.email,
                               SEEKER_PROFILES.skills, SEEKER_PROFILES.education, SEEKER_PROFILES.experience
                        FROM USERS
                        LEFT JOIN SEEKER_PROFILES ON USERS.id = SEEKER_PROFILES.seeker_id
                        WHERE USERS.id = ?");
$stmt->bind_param("i", $seeker_id);
$stmt->execute();
$result = $stmt->get_result();
$seeker_data = $result->fetch_assoc();
$stmt->close();
$conn->close();

// If seeker data not found
if (!$seeker_data) {
    $_SESSION['error'] = "Seeker profile not found.";
    header("Location: dashboard.php"); // Redirect back to employer dashboard
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($seeker_data['name']); ?>'s Profile - Employer Panel</title>
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
        <h2><?php echo htmlspecialchars($seeker_data['name']); ?>'s Profile</h2>

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

        <div class="profile-details">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($seeker_data['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($seeker_data['email']); ?></p>

            <h3>Additional Profile Information:</h3>
            <?php if (!empty($seeker_data['skills']) || !empty($seeker_data['education']) || !empty($seeker_data['experience'])): ?>
                <p><strong>Skills:</strong> <?php echo nl2br(htmlspecialchars($seeker_data['skills'])); ?></p>
                <p><strong>Education:</strong> <?php echo nl2br(htmlspecialchars($seeker_data['education'])); ?></p>
                <p><strong>Experience:</strong> <?php echo nl2br(htmlspecialchars($seeker_data['experience'])); ?></p>
            <?php else: ?>
                <p>No additional profile information added by this seeker yet.</p>
            <?php endif; ?>

            <p><a href="javascript:history.back()" class="button button-secondary">Back to Applicants</a></p>
        </div>

    </main>
    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>