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
        <a href="news.php" class="nav-btn">Informații Externe</a>
        <a href="contact.php" class="nav-btn">Contact</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="order.php" class="nav-btn">Comandă Transport</a>
        <?php endif; ?>
        <?php if($user_permission >= 2): ?>
            <a href="statistics.php" class="nav-btn">Statistici</a>
            <a href="import_export.php" class="nav-btn">Import/Export</a>
        <?php endif; ?>
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
    <h2>Bine ai venit la Black Shield Logistics!</h2>
    <p>Servicii de transport securizat pentru clienți profesioniști.</p>

    <div style="margin-top: 30px;">
        <h3>🌟 Funcționalități disponibile:</h3>
        <ul style="line-height: 2; font-size: 16px;">
            <li>📰 <a href="news.php">Informații externe actualizate</a> - Știri, cursuri valutare, meteo</li>
            <li>📧 <a href="contact.php">Formular de contact</a> - Trimite-ne un mesaj</li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li>🚚 <a href="order.php">Comandă transport</a> - Solicitați un transport securizat</li>
            <?php endif; ?>
            <?php if($user_permission >= 2): ?>
                <li>📊 <a href="statistics.php">Statistici interactive</a> - Grafice și rapoarte</li>
                <li>📤 <a href="import_export.php">Import/Export date</a> - Excel, PDF, Word</li>
            <?php endif; ?>
        </ul>
    </div>

    <?php if($user_permission >= 1): ?>
        <div style="margin-top: 30px;">
            <a href="descriere.php" class="btn">📖 Vezi descrierea completă a site-ului</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
