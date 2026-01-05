<?php
session_start();
include 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch food items
$sql = "SELECT * FROM food_items ORDER BY name ASC";
$result = $conn->query($sql);

// Function to detect image extensions
function getImagePath($name) {
    $folder = "uploads/";
    $extensions = ['.jpeg', '.jpg', '.png'];
    foreach ($extensions as $ext) {
        $file = $folder . $name . $ext;
        if (file_exists($file)) {
            return $file;
        }
    }
    return $folder . 'no-image.jpeg'; // fallback
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Menu</title>
<style>
body { font-family: Arial, sans-serif; margin: 0; background: #f5f7fa; }
.container { width: 90%; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
h1 { text-align: center; color: #2c3e50; }
.add-btn { display: inline-block; margin-bottom: 10px; padding: 8px 16px; background: #27ae60; color: white; border-radius: 4px; text-decoration: none; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border-bottom: 1px solid #ddd; padding: 10px; text-align: center; }
th { background: #1abc9c; color: white; }
tr:hover { background: #f1f1f1; }
img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
.btn { padding: 6px 12px; border: none; border-radius: 4px; color: white; text-decoration: none; font-size: 14px; margin: 2px; }
.btn-edit { background: #3498db; }
.btn-delete { background: #e74c3c; }
.btn-approve { background: #27ae60; }
.status-active { color: green; font-weight: bold; }
.status-pending { color: orange; font-weight: bold; }
</style>
</head>
<body>

<div class="container">
    <h1>Manage Menu</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div style="background: #d1e7dd; color: #0f5132; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div style="background: #f8d7da; color: #842029; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['admin_id'])): ?>
        <a href="add_menu.php" class="add-btn">+ Add New Menu</a>
    <?php endif; ?>

    <table>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Price (Tsh)</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php 
                $imageName = trim($row['image_url'] ?? '');
                $imgSrc = $imageName ? getImagePath(pathinfo($imageName, PATHINFO_FILENAME)) : 'uploads/no-image.jpeg';
                ?>
                <tr>
                    <td><img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>"></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo number_format($row['price']); ?></td>
                    <td>
                        <?php if (($row['status'] ?? '') === 'pending'): ?>
                            <span class="status-pending">Pending</span>
                        <?php else: ?>
                            <span class="status-active">Active</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (($row['status'] ?? '') === 'pending'): ?>
                            <a href="approve_food.php?id=<?php echo $row['food_id']; ?>" class="btn btn-approve">Approve</a>
                        <?php endif; ?>
                        <a href="update_food.php?id=<?php echo $row['food_id']; ?>" class="btn btn-edit">Edit</a>
                        <a href="delete_food.php?id=<?php echo $row['food_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this food item?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No food items found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
