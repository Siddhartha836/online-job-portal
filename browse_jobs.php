<?php
session_start();
require_once '../db.php'; // Include the database connection file

// Check if user is logged in and is seeker
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'seeker') {
    header("Location: ../login.php");
    exit();
}

$seeker_id = $_SESSION['user_id'];

// --- Handle Search ---
$search_keyword = $_GET['keyword'] ?? '';
$search_location = $_GET['location'] ?? '';

// Build the base SQL query
$sql = "SELECT JOBS.id, JOBS.title, JOBS.description, JOBS.location, JOBS.salary, JOBS.deadline, USERS.name AS employer_name
        FROM JOBS
        JOIN USERS ON JOBS.employer_id = USERS.id
        WHERE 1=1";

$params = [];
$types = "";

// Add keyword search condition
if (!empty($search_keyword)) {
    $sql .= " AND (JOBS.title LIKE ? OR JOBS.description LIKE ?)";
    $params[] = '%' . $search_keyword . '%';
    $params[] = '%' . $search_keyword . '%';
    $types .= "ss";
}

// Add location search condition
if (!empty($search_location)) {
    $sql .= " AND JOBS.location LIKE ?";
    $params[] = '%' . $search_location . '%';
    $types .= "s";
}

// Add condition to only show jobs with a deadline in the future or no deadline
$sql .= " AND (JOBS.deadline IS NULL OR JOBS.deadline >= CURDATE())";

// Simple order by deadline
$sql .= " ORDER BY JOBS.deadline ASC";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);

// Dynamically bind parameters if any
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $jobs[] = $row;
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
    <title>Browse Jobs - Job Seeker Panel</title>
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
        <h2>Browse Available Jobs</h2>

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

        <!-- Search Form -->
        <div class="search-form">
            <form action="browse_jobs.php" method="GET">
                <input type="text" name="keyword" placeholder="Search by title or description" value="<?php echo htmlspecialchars($search_keyword); ?>">
                <input type="text" name="location" placeholder="Search by location" value="<?php echo htmlspecialchars($search_location); ?>">
                <button type="submit" class="button">Search</button>
                <?php if (!empty($search_keyword) || !empty($search_location)): ?>
                    <a href="browse_jobs.php" class="button button-secondary">Clear Search</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (count($jobs) > 0): ?>
            <div class="job-list">
                <?php foreach ($jobs as $job): ?>
                    <div class="job-item">
                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p><strong>Employer:</strong> <?php echo htmlspecialchars($job['employer_name']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                        <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
                        <p><strong>Deadline:</strong> <?php echo $job['deadline'] ? htmlspecialchars($job['deadline']) : 'N/A'; ?></p>
                        <p><?php echo nl2br(htmlspecialchars(substr($job['description'], 0, 200))) . (strlen($job['description']) > 200 ? '...' : ''); ?></p>
                        <p><a href="view_job.php?id=<?php echo $job['id']; ?>" class="button">View Details</a></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No jobs found matching your criteria.</p>
        <?php endif; ?>

    </main>
    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>