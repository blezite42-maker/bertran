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

// Delete food item
$stmt = $conn->prepare("DELETE FROM food_items WHERE food_id = ?");
$stmt->bind_param("i", $food_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Food item deleted successfully!";
} else {
    $_SESSION['error_message'] = "Failed to delete food item: " . $conn->error;
}

$stmt->close();
header("Location: manage_menu.php");
exit();

