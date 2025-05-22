<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle testimonial feature/unfeature
if (isset($_GET['toggle_feature']) && is_numeric($_GET['toggle_feature'])) {
    $id = $_GET['toggle_feature'];
    $stmt = $conn->prepare("UPDATE TESTIMONIALS SET is_featured = NOT is_featured WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Testimonial feature status updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating testimonial status.";
    }
    $stmt->close();
    header("Location: manage_testimonials.php");
    exit();
}

// Handle testimonial deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM TESTIMONIALS WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Testimonial deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting testimonial.";
    }
    $stmt->close();
    header("Location: manage_testimonials.php");
    exit();
}

// Fetch all testimonials
$result = $conn->query("SELECT t.*, u.name, u.user_type 
                       FROM TESTIMONIALS t 
                       JOIN USERS u ON t.user_id = u.id 
                       ORDER BY t.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonials - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Admin Panel - Manage Testimonials</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_blog.php">Manage Blog</a></li>
                <li><a href="manage_testimonials.php">Manage Testimonials</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <h2>Testimonials</h2>

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

        <div class="testimonial-list">
            <?php if ($result->num_rows > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Type</th>
                            <th>Content</th>
                            <th>Rating</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($testimonial = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($testimonial['name']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($testimonial['user_type'])); ?></td>
                                <td><?php echo htmlspecialchars($testimonial['content']); ?></td>
                                <td><?php echo $testimonial['rating']; ?>/5</td>
                                <td>
                                    <a href="manage_testimonials.php?toggle_feature=<?php echo $testimonial['id']; ?>" 
                                       class="button button-small <?php echo $testimonial['is_featured'] ? 'button-success' : ''; ?>">
                                        <?php echo $testimonial['is_featured'] ? 'Featured' : 'Not Featured'; ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="manage_testimonials.php?delete=<?php echo $testimonial['id']; ?>" 
                                       class="button button-small button-danger"
                                       onclick="return confirm('Are you sure you want to delete this testimonial?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No testimonials found.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>