<?php
session_start();
require_once 'db.php';

// Only logged-in users can submit testimonials
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['content']);
    $rating = (int)$_POST['rating'];
    
    // Validate input
    if (empty($content) || $rating < 1 || $rating > 5) {
        $error = "Please provide valid content and rating.";
    } else {
        // Insert testimonial
        $stmt = $conn->prepare("INSERT INTO TESTIMONIALS (user_id, content, rating, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("isi", $user_id, $content, $rating);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Thank you for your testimonial!";
            header("Location: index.php");
            exit();
        } else {
            $error = "Error submitting testimonial. Please try again.";
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
    <title>Submit Testimonial - Online Job Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            cursor: pointer;
            font-size: 30px;
            color: #ddd;
            margin: 0 5px;
        }
        .star-rating label:before {
            content: '★';
        }
        .star-rating input:checked ~ label {
            color: #ffd700;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffd700;
        }
    </style>
</head>
<body>
    <header>
        <h1>Submit Your Testimonial</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if ($_SESSION['user_type'] === 'seeker'): ?>
                    <li><a href="seeker/dashboard.php">Dashboard</a></li>
                <?php elseif ($_SESSION['user_type'] === 'employer'): ?>
                    <li><a href="employer/dashboard.php">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="testimonial-form">
                <div class="form-group">
                    <label>Your Rating:</label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5"></label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4"></label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3"></label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2"></label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1"></label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="content">Your Experience:</label>
                    <textarea id="content" name="content" rows="6" required 
                              placeholder="Share your experience with our job portal..."></textarea>
                </div>

                <button type="submit" class="button">Submit Testimonial</button>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>