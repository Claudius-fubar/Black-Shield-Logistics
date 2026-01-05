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
    <title>Servicii - Black Shield Logistics</title>
    <link rel="stylesheet" href="style.css?v=1.7">
    <style>
        .services-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        .service-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .service-card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 24px;
        }
        .service-card .icon {
            font-size: 48px;
            margin-bottom: 20px;
            display: block;
        }
        .service-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .service-card ul {
            color: #666;
            line-height: 1.8;
            padding-left: 20px;
        }
        .service-card ul li {
            margin-bottom: 8px;
        }
        .services-intro {
            text-align: center;
            margin-bottom: 30px;
        }
        .services-intro h2 {
            font-size: 36px;
            color: #333;
            margin-bottom: 15px;
        }
        .services-intro p {
            font-size: 18px;
            color: #666;
            max-width: 800px;
            margin: 0 auto;
        }
        .cta-section {
            background: #f5f5f5;
            padding: 40px;
            border-radius: 8px;
            text-align: center;
            margin-top: 50px;
        }
        .cta-section h3 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #333;
        }
        .cta-section p {
            font-size: 16px;
            color: #666;
            margin-bottom: 25px;
        }
        .cta-btn {
            display: inline-block;
            padding: 15px 40px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        .cta-btn:hover {
            background: #0056b3;
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

<div class="services-container">
    <div class="services-intro">
        <h2>Serviciile Noastre</h2>
        <p>Black Shield Logistics oferă soluții complete de transport securizat și logistică pentru clienți care necesită cel mai înalt nivel de siguranță și profesionalism.</p>
    </div>

    <div class="services-grid">
        <div class="service-card">
            <span class="icon">🚛</span>
            <h3>Transport Securizat</h3>
            <p>Transportăm bunuri de valoare și echipamente sensibile cu cele mai înalte standarde de securitate.</p>
            <ul>
                <li>Escortă armată</li>
                <li>Vehicule blindate și securizate</li>
                <li>Trasee optimizate pentru siguranță</li>
                <li>Monitorizare GPS în timp real</li>
            </ul>
        </div>

        <div class="service-card">
            <span class="icon">🛡️</span>
            <h3>Servicii PMC</h3>
            <p>Suport logistic specializat pentru companii militare private și agenții de securitate.</p>
            <ul>
                <li>Transport echipamente tactice</li>
                <li>Coordonare operațiuni complexe</li>
                <li>Personal instruit și certificat</li>
                <li>Asistență 24/7</li>
            </ul>
        </div>

        <div class="service-card">
            <span class="icon">📦</span>
            <h3>Logistică Specializată</h3>
            <p>Soluții personalizate pentru nevoi logistice complexe și sensibile.</p>
            <ul>
                <li>Planificare trasee sigure</li>
                <li>Depozitare securizată</li>
                <li>Gestionare documentație</li>
                <li>Raportare detaliată</li>
            </ul>
        </div>

        <div class="service-card">
            <span class="icon">🔒</span>
            <h3>Consultanță Securitate</h3>
            <p>Evaluăm și îmbunătățim protocoalele de securitate pentru transportul bunurilor dvs.</p>
            <ul>
                <li>Analiză de risc</li>
                <li>Planuri de securitate personalizate</li>
                <li>Training echipe</li>
                <li>Audit securitate transport</li>
            </ul>
        </div>

        <div class="service-card">
            <span class="icon">🌍</span>
            <h3>Transport Internațional</h3>
            <p>Servicii de transport securizat la nivel internațional cu expertiză în reglementări vamale.</p>
            <ul>
                <li>Coordonare transfrontalieră</li>
                <li>Documentație vamală</li>
                <li>Conformitate internațională</li>
                <li>Rețea globală de parteneri</li>
            </ul>
        </div>

        <div class="service-card">
            <span class="icon">⚡</span>
            <h3>Intervenții Rapide</h3>
            <p>Servicii de urgență pentru situații care necesită răspuns rapid și coordonare precisă.</p>
            <ul>
                <li>Disponibilitate 24/7</li>
                <li>Timp de răspuns redus</li>
                <li>Echipe mobile</li>
                <li>Coordonare centralizată</li>
            </ul>
        </div>
    </div>

    <div class="cta-section">
        <h3>Interesat de serviciile noastre?</h3>
        <p>Contactați-ne pentru o ofertă personalizată sau pentru mai multe informații despre cum vă putem ajuta.</p>
        <a href="contact.php" class="cta-btn">Contactați-ne</a>
    </div>
</div>
</body>
</html>
