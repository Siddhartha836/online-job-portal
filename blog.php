<?php
session_start();
require_once 'db.php';

// Pagination settings
$posts_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// Get total number of posts
$total_posts = $conn->query("SELECT COUNT(*) as count FROM BLOG_POSTS")->fetch_assoc()['count'];
$total_pages = ceil($total_posts / $posts_per_page);

// Get posts for current page
$result = $conn->query("SELECT b.*, u.name as author_name 
                       FROM BLOG_POSTS b 
                       JOIN USERS u ON b.author_id = u.id 
                       ORDER BY b.created_at DESC 
                       LIMIT $offset, $posts_per_page");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Online Job Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Career Blog</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
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
        <section class="blog-posts">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($post = $result->fetch_assoc()): ?>
                    <article class="blog-post">
                        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                        <div class="post-meta">
                            <span>By <?php echo htmlspecialchars($post['author_name']); ?></span>
                            <span>|</span>
                            <span><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                            <span>|</span>
                            <span class="category"><?php echo htmlspecialchars($post['category']); ?></span>
                        </div>
                        <div class="post-excerpt">
                            <?php 
                            $excerpt = substr(strip_tags($post['content']), 0, 300);
                            echo htmlspecialchars($excerpt) . '...';
                            ?>
                        </div>
                        <a href="view_blog.php?id=<?php echo $post['id']; ?>" class="button">Read More</a>
                    </article>
                <?php endwhile; ?>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?>" class="button button-small">&laquo; Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="button button-small <?php echo $page === $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page + 1); ?>" class="button button-small">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>No blog posts available.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>