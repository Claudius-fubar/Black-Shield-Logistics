<?php
session_start();
include 'session_control.php';
include 'db.php';

// Verifică dacă utilizatorul este admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT permission_id FROM users WHERE id = $user_id");
$row = $result->fetch_assoc();
if ($row['permission_id'] < 2) {
    die("Acces interzis.");
}

require_once 'pdf_generator.php';

$query = "SELECT o.id, u.username, o.pickup_location, o.delivery_location, o.cargo_type, 
          o.cargo_weight, o.security_level, o.pickup_date, o.status, o.estimated_price, o.created_at
          FROM transport_orders o 
          JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC";

$result = $conn->query($query);
$orders = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

generate_orders_word($orders);
?>
