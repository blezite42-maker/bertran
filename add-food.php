<?php
// add-food.php
session_start();
require_once 'db_connect.php';

// Create images folder if it doesn't exist
$imgDir = __DIR__ . '/images';
if (!is_dir($imgDir)) { mkdir($imgDir, 0775, true); }

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Validate inputs
    $name  = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    if ($name === '')  { $errors[] = "Name is required."; }
    if ($price === '' || !is_numeric($price)) { $errors[] = "Valid price is required."; }

    // 2) Validate file
    $filenameToStore = 'no-image.png'; // default
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Image upload error.";
        } else {
            $allowedExt = ['jpg','jpeg','png','webp','gif'];
            $maxSize    = 2 * 1024 * 1024; // 2MB
            $tmpName    = $_FILES['image']['tmp_name'];
            $origName   = $_FILES['image']['name'];
            $size       = $_FILES['image']['size'];

            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt)) {
                $errors[] = "Only JPG, JPEG, PNG, WEBP, GIF are allowed.";
            }
            if ($size > $maxSize) {
                $errors[] = "Image too large (max 2MB).";
            }

            if (!$errors) {
                // Safe, unique filename: slug + timestamp + random
                $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name));
                $filenameToStore = $slug . '-' . time() . '-' . mt_rand(1000,9999) . '.' . $ext;

                if (!move_uploaded_file($tmpName, $imgDir . '/' . $filenameToStore)) {
                    $errors[] = "Failed to save uploaded image.";
                }
            }
        }
    }

    // 3) Insert into DB
    if (!$errors) {
        $stmt = $conn->prepare("INSERT INTO food_items (name, price, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $name, $price, $filenameToStore);
        if ($stmt->execute()) {
            $success = "Food item added successfully!";
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Food Item</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    form { max-width: 480px; margin: 0 auto; border: 1px solid #ddd; padding: 16px; border-radius: 8px; }
    label { display:block; margin: 8px 0 4px; }
    input[type="text"], input[type="number"], input[type="file"] { width: 100%; padding: 8px; }
    button { margin-top: 12px; padding: 10px 14px; background:#ff6347; color:#fff; border:none; border-radius:6px; cursor:pointer; }
    .msg { max-width:480px; margin: 0 auto 12px; }
    .error { background:#ffe6e6; color:#900; padding:10px; border-radius:6px; }
    .success { background:#e6ffea; color:#065; padding:10px; border-radius:6px; }
    a { color:#ff6347; text-decoration:none; }
  </style>
</head>
<body>

<h2>Add Food Item</h2>

<?php if ($errors): ?>
  <div class="msg error">
    <ul><?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
  </div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="msg success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <label>Food Name</label>
  <input type="text" name="name" required>

  <label>Price (TZS)</label>
  <input type="number" step="0.01" name="price" required>

  <label>Image</label>
  <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.gif">

  <button type="submit">Save Item</button>
</form>

<p style="text-align:center;margin-top:10px;">
  <a href="index.php">← Back to Menu</a>
</p>

</body>
</html>
