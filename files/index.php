<?php
session_start();
include 'session_control.php';
include 'db.php';

$user_permission = 0;
if(isset($_SESSION['user_id'])){
    $id = $_SESSION['user_id'];
    $result = $conn->query("SELECT permission_id FROM users WHERE id = $id");
    if($row = $result->fetch_assoc()){
        $user_permission = $row['permission_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Black Shield Logistics</title>
    <link rel="stylesheet" href="style.css?v=1.7">
</head>
<body>
<div class="navbar">
    <div class="nav-left">
        <h1>Black Shield Logistics</h1>
    </div>
    <div class="nav-center">
        <a href="index.php" class="nav-btn">Acasă</a>
        <a href="servicii.php" class="nav-btn">Servicii</a>
        <a href="contact.php" class="nav-btn">Contact</a>
        <?php if($user_permission == 3): ?>
            <a href="users.php" class="nav-btn">Administrare utilizatori</a>
        <?php endif; ?>
    </div>
    <div class="nav-right">
        <div class="dropdown">
            <button class="dropbtn">Cont ▾</button>
            <div class="dropdown-content">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="myaccount.php">My Account</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <h2>Bine ai venit!</h2>
    <p>Aici va apărea prezentarea companiei.</p>

    <?php if($user_permission >= 1): ?>
        <a href="descriere.php" class="btn">Vezi descrierea site-ului</a>
    <?php endif; ?>
</div>
</body>
</html>
