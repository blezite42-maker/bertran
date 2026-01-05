<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$food_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$food_id) {
    header("Location: manage_menu.php");
    exit();
}

$errors = [];
$success = "";

// Fetch food item
$stmt = $conn->prepare("SELECT * FROM food_items WHERE food_id = ?");
$stmt->bind_param("i", $food_id);
$stmt->execute();
$result = $stmt->get_result();
$food = $result->fetch_assoc();
$stmt->close();

if (!$food) {
    header("Location: manage_menu.php");
    exit();
}

// Create uploads folder if it doesn't exist
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if ($name === '') {
        $errors[] = "Name is required.";
    }
    if ($price === '' || !is_numeric($price) || $price <= 0) {
        $errors[] = "Valid price is required.";
    }
    
    $imageUrl = $food['image_url'];
    
    // Handle image upload if new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            $tmpName = $_FILES['image']['tmp_name'];
            $origName = $_FILES['image']['name'];
            $size = $_FILES['image']['size'];
            
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt)) {
                $errors[] = "Only JPG, JPEG, PNG, WEBP, GIF are allowed.";
            }
            if ($size > $maxSize) {
                $errors[] = "Image too large (max 5MB).";
            }
            
            if (empty($errors)) {
                $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name));
                $filenameToStore = $slug . '-' . time() . '-' . mt_rand(1000, 9999) . '.' . $ext;
                $targetPath = $uploadDir . '/' . $filenameToStore;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $imageUrl = 'Admin/uploads/' . $filenameToStore;
                } else {
                    $errors[] = "Failed to save uploaded image.";
                }
            }
        }
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE food_items SET name = ?, price = ?, image_url = ?, status = ? WHERE food_id = ?");
        $stmt->bind_param("sdssi", $name, $price, $imageUrl, $status, $food_id);
        
        if ($stmt->execute()) {
            $success = "Food item updated successfully!";
            // Refresh food data
            $food['name'] = $name;
            $food['price'] = $price;
            $food['image_url'] = $imageUrl;
            $food['status'] = $status;
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Product - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            color: #1abc9c;
            text-decoration: none;
            margin-right: 15px;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"],
        input[type="number"],
        input[type="file"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background: #2980b9;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #842029;
        }
        .success {
            background: #d1e7dd;
            color: #0f5132;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 5px;
        }
        .current-image {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="index.php">← Dashboard</a>
            <a href="manage_menu.php">Manage Menu</a>
            <a href="logout.php">Logout</a>
        </div>
        
        <h1>Update Product</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="message error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($food['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="price">Price (TZS) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($food['price']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?php echo $food['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="pending" <?php echo $food['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Current Image</label>
                <?php if ($food['image_url']): ?>
                    <?php
                    $imgPath = '../' . $food['image_url'];
                    if (file_exists(__DIR__ . '/../' . $food['image_url'])) {
                        echo '<img src="' . htmlspecialchars($imgPath) . '" class="preview-image" alt="Current image">';
                    } else {
                        echo '<p>Image not found</p>';
                    }
                    ?>
                <?php else: ?>
                    <p>No image</p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="image">Update Image (optional)</label>
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,.gif" onchange="previewImage(this)">
                <img id="imagePreview" class="preview-image" style="display: none;" alt="Preview">
            </div>
            
            <button type="submit">Update Product</button>
        </form>
    </div>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>

