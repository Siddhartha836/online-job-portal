<?php
session_start(); // Start the session at the beginning of the file
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Job Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Welcome to the Online Job Portal</h1>
        <nav>
            <ul>
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <li><span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span></li>
                    <li><a href="profile.php">Profile</a></li> <!-- Add or uncomment this line -->
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
                <li><a href="seeker/browse_jobs.php">Browse Jobs</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h2>Find Your Dream Job or the Perfect Candidate</h2>
            <p>Join us to connect job seekers with employers.</p>
            <div class="cta-buttons">
                <a href="register.php?type=seeker" class="button">Job Seeker Register</a>
                <a href="register.php?type=employer" class="button">Employer Register</a>
            </div>
        </section>

        <section class="features">
            <h3>Why Choose Us</h3>
            <div class="feature-list">
                <div class="feature-item">
                    <h4>Smart Job Matching</h4>
                    <p>Our intelligent system matches your skills with the perfect job opportunities.</p>
                </div>
                <div class="feature-item">
                    <h4>Easy Application Process</h4>
                    <p>Apply for multiple jobs with just a few clicks using your saved profile.</p>
                </div>
                <div class="feature-item">
                    <h4>Real-time Updates</h4>
                    <p>Get instant notifications about application status and new job matches.</p>
                </div>
                <div class="feature-item">
                    <h4>Professional Network</h4>
                    <p>Connect with top employers and grow your professional network.</p>
                </div>
                <div class="feature-item">
                    <h4>Career Resources</h4>
                    <p>Access resume tips, interview guides, and career development resources.</p>
                </div>
                <div class="feature-item">
                    <h4>Verified Employers</h4>
                    <p>All employers are verified to ensure safe and legitimate job opportunities.</p>
                </div>
            </div>
        </section>

        <section class="job-categories">
            <h3>Popular Job Categories</h3>
            <div class="feature-list">
                <div class="feature-item">
                    <h4>Technology</h4>
                    <p>Software Development, IT Support, Data Science</p>
                </div>
                <div class="feature-item">
                    <h4>Healthcare</h4>
                    <p>Nursing, Medical Technology, Healthcare Administration</p>
                </div>
                <div class="feature-item">
                    <h4>Finance</h4>
                    <p>Accounting, Banking, Financial Analysis</p>
                </div>
                <div class="feature-item">
                    <h4>Marketing</h4>
                    <p>Digital Marketing, Brand Management, Social Media</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>