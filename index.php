<?php
session_start();

// Database connection
require_once 'db_connect.php';

// Handle search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
if ($search !== '') {
    $result = $conn->query("SELECT * FROM food_items WHERE name LIKE '%$search%'");
} else {
    $result = $conn->query("SELECT * FROM food_items");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bertran Foods Menu</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: #f8f8f8;
            overflow-x: hidden;
        }

        /* Header animations */
        header {
            background: linear-gradient(45deg, #ff6347, #ff9966);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            flex-wrap: wrap;
            animation: slideDown 0.6s ease-out;
        }
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        header h1 { margin: 0; font-size: 28px; }
        nav a {
            color: white; text-decoration: none;
            margin-left: 15px; font-weight: bold;
            transition: 0.3s;
        }
        nav a:hover { color: yellow; }

        .cart {
            cursor: pointer;
            font-size: 20px;
            transition: transform 0.3s ease;
        }
        .cart:hover { transform: scale(1.2); }

        /* Search bar animation */
        .search-bar {
            margin: 10px 0;
            width: 100%;
            max-width: 400px;
            display: flex;
            justify-content: center;
            animation: fadeIn 1s ease-out;
        }
        .search-bar input[type="text"] {
            width: 80%;
            padding: 8px;
            border-radius: 8px 0 0 8px;
            border: 1px solid #ddd;
            outline: none;
            transition: box-shadow 0.3s ease;
        }
        .search-bar input[type="text"]:focus {
            box-shadow: 0 0 10px rgba(255,99,71,0.6);
        }
        .search-bar button {
            padding: 8px 12px;
            border: none;
            background: #ff6347;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border-radius: 0 8px 8px 0;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .search-bar button:hover {
            background: #e5533d;
            transform: scale(1.05);
        }

        /* Container and cards */
        .container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .food-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }
        .food-card:nth-child(1) { animation-delay: 0.1s; }
        .food-card:nth-child(2) { animation-delay: 0.2s; }
        .food-card:nth-child(3) { animation-delay: 0.3s; }
        .food-card:nth-child(4) { animation-delay: 0.4s; }
        /* You can extend delays for more cards */

        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .food-card:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .food-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .food-card:hover img {
            transform: scale(1.05);
        }
        .food-card h3 { margin: 10px 0 5px; transition: color 0.3s ease; }
        .food-card h3:hover { color: #ff6347; }
        .food-card p { color: green; font-weight: bold; margin: 5px 0 15px; }
        .food-card button {
            background: #ff6347; border: none;
            padding: 10px; width: 100%;
            color: white; cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .food-card button:hover { 
            background: #e5533d; 
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<header>
    <h1>Bertran Foods</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="cart.php">Cart</a>
    </nav>
    <div class="cart" onclick="window.location.href='cart.php'">
        🛒 (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)
    </div>

    <!-- Search Bar -->
    <form class="search-bar" method="GET" action="index.php">
        <input type="text" name="search" placeholder="Search food..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>
</header>

<div class="container">
    <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="food-card">
                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                <p><?php echo number_format($row['price']); ?> Tsh</p>
                <form action="cart.php" method="POST">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($row['name']); ?>">
                    <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                    <input type="hidden" name="image" value="<?php echo htmlspecialchars($row['image_url']); ?>">
                    <button type="submit" name="add_to_cart">Add to Cart</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="grid-column: 1/-1; text-align:center; font-weight:bold;">No food items found.</p>
    <?php endif; ?>
</div>

</body>
</html>
