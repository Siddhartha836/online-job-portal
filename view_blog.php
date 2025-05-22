<?php
session_start();
require_once 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch blog post
$stmt = $conn->prepare("SELECT b.*, u.name as author_name 
                       FROM BLOG_POSTS b 
                       JOIN USERS u ON b.author_id = u.id 
                       WHERE b.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    header("Location: blog.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Online Job Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Career Blog</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="blog.php">Blog</a></li>
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <?php if ($_SESSION['user_type'] === 'seeker'): ?>
                        <li><a href="seeker/dashboard.php">Dashboard</a></li>
                    <?php elseif ($_SESSION['user_type'] === 'employer'): ?>
                        <li><a href="employer/dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="container">
        <article class="blog-post-full">
            <h2><?php echo htmlspecialchars($post['title']); ?></h2>
            <div class="post-meta">
                <span>By <?php echo htmlspecialchars($post['author_name']); ?></span>
                <span>|</span>
                <span><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                <span>|</span>
                <span class="category"><?php echo htmlspecialchars($post['category']); ?></span>
            </div>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
            <a href="blog.php" class="button">&laquo; Back to Blog</a>
        </article>
    </main>

    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>