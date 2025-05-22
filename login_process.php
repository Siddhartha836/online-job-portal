<?php
session_start();
require_once 'db.php'; // Include the database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic validation and sanitization
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Fetch user from database
    $stmt = $conn->prepare("SELECT id, name, password, user_type FROM USERS WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $name, $hashed_password, $user_type);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            // Password is correct, start session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['user_type'] = $user_type;
            $_SESSION['loggedin'] = true;

            // Redirect based on user type
            switch ($user_type) {
                case 'seeker':
                    header("Location: seeker/dashboard.php");
                    break;
                case 'employer':
                    header("Location: employer/dashboard.php");
                    break;
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                default:
                    // Should not happen with ENUM, but good practice
                    $_SESSION['error'] = "Unknown user type.";
                    header("Location: login.php");
                    break;
            }
            exit();
        } else {
            // Incorrect password
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: login.php");
            exit();
        }
    } else {
        // User not found
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // If accessed directly without POST method
    header("Location: login.php");
    exit();
}
?>