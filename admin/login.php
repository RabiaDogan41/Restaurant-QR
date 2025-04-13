<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<link rel="stylesheet" href="../css/style.css">

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login">
        <form method="POST">
            <h2>Admin Login</h2>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        </form>
</body>

<style>
    :root {
    --primary-color:rgb(167, 156, 51);
    --primary-dark: rgb(167, 156, 51);
    --secondary-color: #64748b;
    --background-color: #f1f5f9;
    --card-color: #ffffff;
    --text-color: #334155;
    --error-color: #ef4444;
    --success-color: #10b981;
    --input-bg: #f8fafc;
    --input-border: #e2e8f0;
    --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--background-color);
    color: var(--text-color);
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-image: linear-gradient(45deg, rgb(167, 156, 51), rgb(167, 156, 51));
}

.login-container {
    width: 100%;
    max-width: 400px;
    padding: 20px;
}

.login-card {
    background-color: var(--card-color);
    border-radius: 12px;
    box-shadow: var(--shadow);
    overflow: hidden;
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.login-header {
    padding: 2rem;
    text-align: center;
}

.login-header h1 {
    color: var(--primary-color);
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
}

.login-header p {
    color: var(--secondary-color);
    font-size: 0.9rem;
}

.login-form {
    padding: 0 2rem 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    font-weight: 500;
}

.input-wrapper {
    position: relative;
}

.input-wrapper i {
    position: absolute;
    top: 50%;
    left: 1rem;
    transform: translateY(-50%);
    color: var(--secondary-color);
}

.form-group input {
    width: 100%;
    padding: 12px 12px 12px 2.5rem;
    border: 1px solid var(--input-border);
    border-radius: 8px;
    background-color: var(--input-bg);
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    font-size: 0.85rem;
}

.remember-me {
    display: flex;
    align-items: center;
}

.remember-me input {
    margin-right: 0.5rem;
}

.forgot-password {
    color: var(--primary-color);
    text-decoration: none;
}

.forgot-password:hover {
    text-decoration: underline;
}

.login-button {
    width: 100%;
    padding: 14px;
    background-color: black;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: all 0.3s ease;
}

.login-button:hover {
    background-color: var(--primary-dark);
    transform: translateY(-2px);
}

.login-button span {
    margin-right: 8px;
}

.error-message {
    background-color: rgba(239, 68, 68, 0.1);
    color: var(--error-color);
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    font-size: 0.9rem;
}

.error-message i {
    margin-right: 8px;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .login-container {
        padding: 10px;
    }
    
    .login-header {
        padding: 1.5rem;
    }
    
    .login-form {
        padding: 0 1.5rem 1.5rem;
    }
}
</style>
</html>
