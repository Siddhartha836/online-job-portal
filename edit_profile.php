<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is seeker
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'seeker') {
    header("Location: ../login.php");
    exit();
}

$seeker_id = $_SESSION['user_id'];

// Fetch existing seeker profile details
// We use LEFT JOIN in case the seeker profile hasn't been created yet
$stmt = $conn->prepare("SELECT SEEKER_PROFILES.skills, SEEKER_PROFILES.education, SEEKER_PROFILES.experience
                        FROM USERS
                        LEFT JOIN SEEKER_PROFILES ON USERS.id = SEEKER_PROFILES.seeker_id
                        WHERE USERS.id = ?");
$stmt->bind_param("i", $seeker_id);
$stmt->execute();
$result = $stmt->get_result();
$seeker_profile_data = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Initialize variables with existing data or empty strings
$skills = $seeker_profile_data['skills'] ?? '';
$education = $seeker_profile_data['education'] ?? '';
$experience = $seeker_profile_data['experience'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Job Seeker Panel</title>
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
        <h2>Edit My Profile</h2>

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

        <div class="form-section">
            <form action="process_edit_profile.php" method="POST">
                <div class="form-group">
                    <label for="skills">Skills:</label>
                    <textarea id="skills" name="skills" rows="6"><?php echo htmlspecialchars($skills); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="education">Education:</label>
                    <textarea id="education" name="education" rows="6"><?php echo htmlspecialchars($education); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="experience">Experience:</label>
                    <textarea id="experience" name="experience" rows="6"><?php echo htmlspecialchars($experience); ?></textarea>
                </div>
                <button type="submit" class="button">Save Profile</button>
                <a href="profile.php" class="button button-secondary">Cancel</a>
            </form>
        </div>

    </main>
    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>