<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle blog post deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM BLOG_POSTS WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Blog post deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting blog post.";
    }
    $stmt->close();
    header("Location: manage_blog.php");
    exit();
}

// Fetch all blog posts
$result = $conn->query("SELECT b.*, u.name as author_name 
                       FROM BLOG_POSTS b 
                       JOIN USERS u ON b.author_id = u.id 
                       ORDER BY b.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog Posts - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Admin Panel - Manage Blog Posts</h1>
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
        <div class="admin-header">
            <h2>Blog Posts</h2>
            <a href="add_blog.php" class="button">Add New Post</a>
        </div>

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

        <div class="blog-list">
            <?php if ($result->num_rows > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($post = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                <td><?php echo htmlspecialchars($post['category']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                <td>
                                    <a href="edit_blog.php?id=<?php echo $post['id']; ?>" class="button button-small">Edit</a>
                                    <a href="manage_blog.php?delete=<?php echo $post['id']; ?>" 
                                       class="button button-small button-danger"
                                       onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No blog posts found.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>