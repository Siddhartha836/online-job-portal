<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get blog post ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch blog post data
$stmt = $conn->prepare("SELECT * FROM BLOG_POSTS WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    $_SESSION['error'] = "Blog post not found.";
    header("Location: manage_blog.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = trim($_POST['category']);

    if (empty($title) || empty($content) || empty($category)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        $stmt = $conn->prepare("UPDATE BLOG_POSTS SET title = ?, content = ?, category = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $content, $category, $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Blog post updated successfully.";
            header("Location: manage_blog.php");
            exit();
        } else {
            $_SESSION['error'] = "Error updating blog post.";
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
    <title>Edit Blog Post - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Admin Panel - Edit Blog Post</h1>
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
        <h2>Edit Blog Post</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="blog-form">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="Career Advice" <?php echo $post['category'] == 'Career Advice' ? 'selected' : ''; ?>>Career Advice</option>
                    <option value="Industry News" <?php echo $post['category'] == 'Industry News' ? 'selected' : ''; ?>>Industry News</option>
                    <option value="Interview Tips" <?php echo $post['category'] == 'Interview Tips' ? 'selected' : ''; ?>>Interview Tips</option>
                    <option value="Job Search" <?php echo $post['category'] == 'Job Search' ? 'selected' : ''; ?>>Job Search</option>
                    <option value="Workplace" <?php echo $post['category'] == 'Workplace' ? 'selected' : ''; ?>>Workplace</option>
                </select>
            </div>

            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>

            <button type="submit" class="button">Update Post</button>
            <a href="manage_blog.php" class="button button-secondary">Cancel</a>
        </form>
    </main>

    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>