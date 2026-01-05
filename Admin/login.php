<?php
session_start();
include 'db_connect.php'; // Make sure this file is inside Admin folder

// If already logged in, go to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT admin_id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($admin_id, $db_password);
        $stmt->fetch();

        // since you use plain password "1234567"
        if ($password === $db_password) {
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Username not found.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login - Food System</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(135deg, #c5bcd8ff, #90a3aeff);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .login-box {
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.2);
        width: 350px;
        animation: fadeIn 0.8s ease-in-out;
    }
    .login-box h2 {
        margin-bottom: 25px;
        text-align: center;
        color: #2c3e50;
    }
    input[type=text], input[type=password] {
        width: 100%;
        padding: 12px;
        margin: 8px 0 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
    }
    button {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 8px;
        background: #1abc9c;
        color: white;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    button:hover {
        background: #16a085;
    }
    .error {
        color: red;
        margin-bottom: 15px;
        text-align: center;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(-20px);}
        to {opacity: 1; transform: translateY(0);}
    }
</style>
</head>
<body>
<div class="login-box">
    <h2>Admin Login</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
