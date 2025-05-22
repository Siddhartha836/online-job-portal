<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is employer
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'employer') {
    header("Location: ../login.php");
    exit();
}

// We don't need to fetch data for this page, just display the form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post New Job - Employer Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Employer Dashboard</h1>
        <nav>
            <ul>
                <li><span>Welcome, Employer <?php echo htmlspecialchars($_SESSION['name']); ?>!</span></li>
                <li><a href="dashboard.php">My Jobs</a></li>
                <li><a href="add_job.php">Post New Job</a></li> <!-- Link to this page -->
                <li><a href="../profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main class="container">
        <section class="form-section">
            <h2>Post a New Job</h2>

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

            <form action="process_add_job.php" method="POST">
                <div class="form-group">
                    <label for="title">Job Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Job Description:</label>
                    <textarea id="description" name="description" rows="6" required></textarea>
                </div>
                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" required>
                </div>
                <div class="form-group">
                    <label for="salary">Salary (e.g., 50000.00):</label>
                    <input type="number" id="salary" name="salary" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="deadline">Application Deadline:</label>
                    <input type="date" id="deadline" name="deadline" required>
                </div>

                <button type="submit" class="button">Post Job</button>
            </form>
        </section>
    </main>
    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>