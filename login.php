<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Job Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Online Job Portal</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="register.php">Register</a></li>
                <li><a href="#">Browse Jobs</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <section class="form-section">
            <h2>Login</h2>
            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="button">Login</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>