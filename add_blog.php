<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = trim($_POST['category']);
    $author_id = $_SESSION['user_id'];

    if (empty($title) || empty($content) || empty($category)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO BLOG_POSTS (title, content, category, author_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $content, $category, $author_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Blog post created successfully.";
            header("Location: manage_blog.php");
            exit();
        } else {
            $_SESSION['error'] = "Error creating blog post.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blog Post - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Admin Panel - Add Blog Post</h1>
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
        <h2>Add New Blog Post</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="blog-form">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="Career Advice">Career Advice</option>
                    <option value="Industry News">Industry News</option>
                    <option value="Interview Tips">Interview Tips</option>
                    <option value="Job Search">Job Search</option>
                    <option value="Workplace">Workplace</option>
                </select>
            </div>

            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" required></textarea>
            </div>

            <button type="submit" class="button">Create Post</button>
            <a href="manage_blog.php" class="button button-secondary">Cancel</a>
        </form>
    </main>

    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>