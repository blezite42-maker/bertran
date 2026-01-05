<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Calculate total amount from cart
$total_amount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $price = isset($item['price']) ? (float)$item['price'] : 0;
        $qty = isset($item['quantity']) ? (int)$item['quantity'] : 1;
        $total_amount += $price * $qty;
    }
}

// Save total to session
$_SESSION['total_amount'] = $total_amount;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment - Bertran Foods</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f8f8;
            padding: 20px;
            text-align: center;
        }
        .payment-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 400px;
            margin: 50px auto;
        }
        h2 {
            text-align: center;
            color: #ff6347;
        }
        select, input {
            width: 100%;
            padding: 10px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #ff6347;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #e5533d;
        }
        .hidden {
            display: none;
        }
        .notification {
            margin-top: 15px;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            color: green;
            background: #e6ffe6;
            display: none;
        }
        .btn-link {
            margin-top: 15px;
            padding: 12px 20px;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s ease;
        }
        .logout-btn {
            background: #dc3545;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .home-btn {
            background: #28a745;
        }
        .home-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<div class="payment-box">
    <h2>Payment Method</h2>
    <form id="paymentForm">
        <label>Select Payment Method:</label>
        <select id="paymentMethod" name="method" required>
            <option value="">-- Choose Method --</option>
            <optgroup label="Mobile Money">
                <option value="M-Pesa">M-Pesa</option>
                <option value="Airtel Money">Airtel Money</option>
                <option value="Halopesa">Halopesa</option>
                <option value="Mix by Yas">Mix by Yas</option>
            </optgroup>
            <optgroup label="Bank">
                <option value="NMB">NMB</option>
                <option value="CRDB">CRDB</option>
                <option value="NBC">NBC</option>
                <option value="Equity">Equity</option>
            </optgroup>
        </select>

        <div id="phoneField" class="hidden">
            <label>Enter Phone Number:</label>
            <input type="text" id="phoneNumber" placeholder="e.g. 0712xxxxxx">
        </div>

        <div id="bankField" class="hidden">
            <label>Enter Bank Account Number:</label>
            <input type="text" id="accountNumber" placeholder="e.g. 1234567890">
        </div>

        <button type="button" onclick="makePayment()">Proceed to Pay</button>
    </form>

    <div id="notification" class="notification"></div>

    <!-- Action Buttons -->
    <a href="index.php" class="btn-link home-btn">🏠 Go to Homepage</a>
    <a href="logout.php" class="btn-link logout-btn">Logout</a>
</div>

<script>
function makePayment() {
    let method = document.getElementById("paymentMethod").value;
    let phone = document.getElementById("phoneNumber").value;
    let account = document.getElementById("accountNumber").value;
    let notification = document.getElementById("notification");
    let amount = "<?php echo $total_amount; ?>";
    let systemName = "Bertran Foods";

    // Format amount with commas
    let formattedAmount = parseInt(amount).toLocaleString() + " Tsh";

    if (!method) {
        alert("Please select a payment method.");
        return;
    }

    if ((method === "M-Pesa" || method === "Airtel Money" || method === "Halopesa" || method === "Mix by Yas") && phone === "") {
        alert("Please enter your phone number.");
        return;
    }

    if ((method === "NMB" || method === "CRDB" || method === "NBC" || method === "Equity") && account === "") {
        alert("Please enter your account number.");
        return;
    }

    let message = "";
    if (phone) {
        message = `✅ ${systemName}: Payment of <b>${formattedAmount}</b> request sent to ${phone} via ${method}.`;
    } else {
        message = `✅ ${systemName}: Payment of <b>${formattedAmount}</b> request sent to Account ${account} at ${method}.`;
    }

    notification.innerHTML = message;
    notification.style.display = "block";
}

document.getElementById("paymentMethod").addEventListener("change", function() {
    let method = this.value;
    document.getElementById("phoneField").classList.add("hidden");
    document.getElementById("bankField").classList.add("hidden");

    if (method === "M-Pesa" || method === "Airtel Money" || method === "Halopesa" || method === "Mix by Yas") {
        document.getElementById("phoneField").classList.remove("hidden");
    } else if (method === "NMB" || method === "CRDB" || method === "NBC" || method === "Equity") {
        document.getElementById("bankField").classList.remove("hidden");
    }
});
</script>

</body>
</html>
