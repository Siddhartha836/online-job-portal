<?php
session_start();
require_once '../db.php';

// Ensure user is logged in as employer
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'employer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle logo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $file = $_FILES['logo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Validate file
    if (!in_array($file['type'], $allowed_types)) {
        $error = "Only JPG, PNG and GIF files are allowed.";
    } elseif ($file['size'] > $max_size) {
        $error = "File size must be less than 5MB.";
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Error uploading file. Please try again.";
    } else {
        // Create uploads directory if it doesn't exist
        $upload_dir = '../uploads/logos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . $user_id . '_' . time() . '.' . $extension;
        $filepath = $upload_dir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Update database with new logo path
            $relative_path = 'uploads/logos/' . $filename;
            $stmt = $conn->prepare("UPDATE USERS SET logo_path = ? WHERE id = ? AND user_type = 'employer'");
            $stmt->bind_param("si", $relative_path, $user_id);

            if ($stmt->execute()) {
                $message = "Logo uploaded successfully!";
                // Delete old logo if exists
                $old_logo = $conn->query("SELECT logo_path FROM USERS WHERE id = $user_id")->fetch_assoc();
                if ($old_logo && $old_logo['logo_path'] && file_exists('../' . $old_logo['logo_path'])) {
                    unlink('../' . $old_logo['logo_path']);
                }
            } else {
                $error = "Error updating database. Please try again.";
                unlink($filepath); // Remove uploaded file
            }
            $stmt->close();
        } else {
            $error = "Error moving uploaded file. Please try again.";
        }
    }
}

// Get current logo
$result = $conn->query("SELECT logo_path FROM USERS WHERE id = $user_id");
$current_logo = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Company Logo - Online Job Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Upload Company Logo</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <div class="form-container">
            <?php if ($message): ?>
                <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($current_logo && $current_logo['logo_path']): ?>
                <div class="current-logo">
                    <h3>Current Logo</h3>
                    <img src="../<?php echo htmlspecialchars($current_logo['logo_path']); ?>" 
                         alt="Company Logo" style="max-width: 200px;">
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="logo">Select Logo:</label>
                    <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif" required>
                    <small>Max file size: 5MB. Allowed formats: JPG, PNG, GIF</small>
                </div>

                <button type="submit" class="button">Upload Logo</button>
                <a href="dashboard.php" class="button button-secondary">Back to Dashboard</a>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Online Job Portal</p>
    </footer>
</body>
</html>