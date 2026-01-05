<?php
session_start();
require_once "db_connect.php";

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Correct columns: user_id, name, password
    $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $username, $hashed);
        $stmt->fetch();

        if (password_verify($password, $hashed)) {
            $_SESSION['user_id']  = $user_id;
            $_SESSION['username'] = $username; // keep username for display
            header("Location: payment.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <style>
    body { margin:0; font-family:Arial, sans-serif; background:#f2f7f9; display:flex; align-items:center; justify-content:center; min-height:100vh; }
    .card { width:95%; max-width:420px; background:#fff; border-radius:18px; padding:30px; box-shadow:0 10px 30px rgba(0,0,0,.1); animation: fadeIn .5s ease; }
    h2 { text-align:center; color:#2e7d32; margin-bottom:18px; }
    input { width:100%; padding:12px; margin:8px 0; border:1px solid #ddd; border-radius:8px; }
    button { width:100%; padding:12px; background:#2e7d32; color:#fff; border:none; border-radius:10px; font-size:16px; cursor:pointer; margin-top:12px; }
    button:hover { background:#256a29; }
    .error { color:red; text-align:center; margin-bottom:12px; }
    @keyframes fadeIn { from {opacity:0; transform:translateY(10px);} to {opacity:1; transform:translateY(0);} }
  </style>
</head>
<body>
  <div class="card">
    <h2>Login</h2>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <p style="text-align:center;margin-top:12px;">Don’t have an account? <a href="register.php">Register</a></p>
  </div>
</body>
</html>
