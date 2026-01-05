<?php
session_start();
require_once "db_connect.php";

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username     = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // check if email exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $phone, $hashed);

            if ($stmt->execute()) {
    $_SESSION['user_id']  = $conn->insert_id;
    $_SESSION['username'] = $username; // or $name, depending on your form
    header("Location: payment.php"); // redirect to payment page
    exit();
} else {
    $error = "Error creating account. (" . $conn->error . ")";
}

        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register</title>
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
    <h2>Create Account</h2>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="text" name="phone" placeholder="Phone" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      <button type="submit">Register</button>
    </form>
    <p style="text-align:center;margin-top:12px;">Already have an account? <a href="login.php">Login</a></p>
  </div>
</body>
</html>
