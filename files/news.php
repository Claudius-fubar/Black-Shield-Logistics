<?php
session_start();
include 'session_control.php';
include 'db.php';
include 'external_content.php';

$user_permission = 0;
if(isset($_SESSION['user_id'])){
    $id = $_SESSION['user_id'];
    $result = $conn->query("SELECT permission_id FROM users WHERE id = $id");
    if($row = $result->fetch_assoc()){
        $user_permission = $row['permission_id'];
    }
}

// Obține date din surse externe
$news = get_cached_data('security_news', 'get_security_news', 7200);
$exchange_rates = get_cached_data('exchange_rates', 'get_exchange_rates', 3600);
$weather = get_weather_data('Bucharest');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Informații Externe - Black Shield Logistics</title>
    <link rel="stylesheet" href="style.css?v=1.7">
    <style>
        .external-content {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .content-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .content-card h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .news-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .news-item:last-child {
            border-bottom: none;
        }
        .news-title {
            font-weight: bold;
            color: #2196F3;
            margin-bottom: 5px;
        }
        .news-date {
            font-size: 0.85em;
            color: #666;
        }
        .rate-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .weather-info {
            text-align: center;
            padding: 20px;
        }
        .weather-temp {
            font-size: 3em;
            font-weight: bold;
            color: #FF9800;
        }
        .weather-condition {
            font-size: 1.2em;
            color: #666;
            margin: 10px 0;
        }
    </style>
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

<div class="external-content">
    <h2>Informații din Surse Externe</h2>
    <p>Date actualizate automat din diverse surse externe relevante pentru activitatea noastră.</p>
    
    <div class="content-grid">
        <!-- Știri despre securitate -->
        <div class="content-card">
            <h3>📰 Știri Securitate IT</h3>
            <?php if (!empty($news)): ?>
                <?php foreach ($news as $article): ?>
                    <div class="news-item">
                        <div class="news-title"><?php echo htmlspecialchars($article['title']); ?></div>
                        <div class="news-date"><?php echo date('d.m.Y', strtotime($article['pubDate'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nu s-au putut încărca știrile momentan.</p>
            <?php endif; ?>
        </div>

        <!-- Cursuri valutare -->
        <div class="content-card">
            <h3>💱 Cursuri Valutare (BNR)</h3>
            <?php if (!empty($exchange_rates)): ?>
                <?php 
                $major_currencies = ['USD', 'EUR', 'GBP', 'CHF'];
                foreach ($major_currencies as $currency): 
                    if (isset($exchange_rates[$currency])): 
                ?>
                    <div class="rate-item">
                        <strong><?php echo $currency; ?></strong>
                        <span><?php echo number_format($exchange_rates[$currency], 4); ?> RON</span>
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
                <p style="font-size: 0.85em; color: #666; margin-top: 15px;">
                    Actualizat: <?php echo date('d.m.Y H:i'); ?>
                </p>
            <?php else: ?>
                <p>Nu s-au putut încărca cursurile valutare momentan.</p>
            <?php endif; ?>
        </div>

        <!-- Informații meteo -->
        <div class="content-card">
            <h3>🌤️ Condiții Meteo</h3>
            <div class="weather-info">
                <div class="weather-temp"><?php echo $weather['temperature']; ?>°C</div>
                <div class="weather-condition"><?php echo $weather['condition']; ?></div>
                <div style="color: #666; margin-top: 10px;">
                    <div>💧 Umiditate: <?php echo $weather['humidity']; ?>%</div>
                    <div>💨 Vânt: <?php echo $weather['wind_speed']; ?> km/h</div>
                    <div>📍 <?php echo $weather['city']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
        <h4>ℹ️ Despre datele afișate:</h4>
        <ul>
            <li><strong>Știri</strong> - Parsate din RSS feed-uri publice despre securitate IT</li>
            <li><strong>Cursuri valutare</strong> - Date oficiale de la Banca Națională a României (XML feed)</li>
            <li><strong>Meteo</strong> - Informații pentru planificarea optimă a rutelor de transport</li>
        </ul>
        <p><small>Toate datele sunt procesate și parsate server-side, nu sunt încărcate direct prin iframe sau URL-uri externe.</small></p>
    </div>
</div>
</body>
</html>
