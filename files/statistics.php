<?php
session_start();
include 'session_control.php';
include 'db.php';

// Verifică autentificarea
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_permission = 0;
$result = $conn->query("SELECT permission_id FROM users WHERE id = $user_id");
if($row = $result->fetch_assoc()){
    $user_permission = $row['permission_id'];
}

// Obține statistici pentru grafice
$stats = [];

// Statistici generale
$general_stats = $conn->query("SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM transport_orders");

$stats['general'] = $general_stats->fetch_assoc();

// Comenzi pe lună (ultimele 6 luni)
$monthly_stats = $conn->query("SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as count
    FROM transport_orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC");

$stats['monthly'] = [];
while ($row = $monthly_stats->fetch_assoc()) {
    $stats['monthly'][] = $row;
}

// Distribuție pe tip de marfă
$cargo_stats = $conn->query("SELECT 
    cargo_type,
    COUNT(*) as count
    FROM transport_orders
    GROUP BY cargo_type
    ORDER BY count DESC
    LIMIT 5");

$stats['cargo'] = [];
while ($row = $cargo_stats->fetch_assoc()) {
    $stats['cargo'][] = $row;
}

// Distribuție pe nivel de securitate
$security_stats = $conn->query("SELECT 
    security_level,
    COUNT(*) as count
    FROM transport_orders
    GROUP BY security_level
    ORDER BY count DESC");

$stats['security'] = [];
while ($row = $security_stats->fetch_assoc()) {
    $stats['security'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Statistici - Black Shield Logistics</title>
    <link rel="stylesheet" href="style.css?v=1.7">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        .statistics-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
        }
        .stat-value {
            font-size: 48px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-card.total .stat-value { color: #2196F3; }
        .stat-card.completed .stat-value { color: #4CAF50; }
        .stat-card.pending .stat-value { color: #FF9800; }
        .stat-card.progress .stat-value { color: #9C27B0; }
        .stat-card.cancelled .stat-value { color: #f44336; }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .chart-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .chart-card h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
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
        <a href="import_export.php" class="nav-btn">Import/Export</a>
        <a href="statistics.php" class="nav-btn">Statistici</a>
        <?php if($user_permission == 3): ?>
            <a href="users.php" class="nav-btn">Utilizatori</a>
        <?php endif; ?>
    </div>
    <div class="nav-right">
        <div class="dropdown">
            <button class="dropbtn">Cont ▾</button>
            <div class="dropdown-content">
                <a href="myaccount.php">My Account</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="statistics-container">
    <h2>📊 Statistici și Rapoarte</h2>
    
    <!-- Card-uri cu statistici generale -->
    <div class="stats-grid">
        <div class="stat-card total">
            <h3>Total Comenzi</h3>
            <div class="stat-value"><?php echo $stats['general']['total_orders'] ?? 0; ?></div>
        </div>
        
        <div class="stat-card completed">
            <h3>Completate</h3>
            <div class="stat-value"><?php echo $stats['general']['completed'] ?? 0; ?></div>
        </div>
        
        <div class="stat-card pending">
            <h3>În Așteptare</h3>
            <div class="stat-value"><?php echo $stats['general']['pending'] ?? 0; ?></div>
        </div>
        
        <div class="stat-card progress">
            <h3>În Derulare</h3>
            <div class="stat-value"><?php echo $stats['general']['in_progress'] ?? 0; ?></div>
        </div>
        
        <div class="stat-card cancelled">
            <h3>Anulate</h3>
            <div class="stat-value"><?php echo $stats['general']['cancelled'] ?? 0; ?></div>
        </div>
    </div>
    
    <!-- Grafice -->
    <div class="charts-grid">
        <!-- Grafic status comenzi (Pie Chart) -->
        <div class="chart-card">
            <h3>📈 Distribuție Status Comenzi</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
        
        <!-- Grafic comenzi pe lună (Bar Chart) -->
        <div class="chart-card">
            <h3>📅 Comenzi pe Lună (Ultimele 6 Luni)</h3>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        
        <!-- Grafic tip marfă (Doughnut Chart) -->
        <div class="chart-card">
            <h3>📦 Top Tipuri de Marfă</h3>
            <div class="chart-container">
                <canvas id="cargoChart"></canvas>
            </div>
        </div>
        
        <!-- Grafic nivel securitate (Bar Chart) -->
        <div class="chart-card">
            <h3>🛡️ Distribuție Nivel Securitate</h3>
            <div class="chart-container">
                <canvas id="securityChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Grafic Status Comenzi (Pie Chart)
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: ['Completate', 'În Așteptare', 'În Derulare', 'Anulate'],
        datasets: [{
            data: [
                <?php echo $stats['general']['completed'] ?? 0; ?>,
                <?php echo $stats['general']['pending'] ?? 0; ?>,
                <?php echo $stats['general']['in_progress'] ?? 0; ?>,
                <?php echo $stats['general']['cancelled'] ?? 0; ?>
            ],
            backgroundColor: [
                '#4CAF50',
                '#FF9800',
                '#9C27B0',
                '#f44336'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Grafic Comenzi pe Lună (Bar Chart)
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: [
            <?php 
            foreach ($stats['monthly'] as $m) {
                echo "'" . $m['month'] . "',";
            }
            ?>
        ],
        datasets: [{
            label: 'Număr Comenzi',
            data: [
                <?php 
                foreach ($stats['monthly'] as $m) {
                    echo $m['count'] . ",";
                }
                ?>
            ],
            backgroundColor: '#2196F3'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Grafic Tip Marfă (Doughnut Chart)
const cargoCtx = document.getElementById('cargoChart').getContext('2d');
new Chart(cargoCtx, {
    type: 'doughnut',
    data: {
        labels: [
            <?php 
            foreach ($stats['cargo'] as $c) {
                echo "'" . addslashes($c['cargo_type']) . "',";
            }
            ?>
        ],
        datasets: [{
            data: [
                <?php 
                foreach ($stats['cargo'] as $c) {
                    echo $c['count'] . ",";
                }
                ?>
            ],
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Grafic Nivel Securitate (Horizontal Bar Chart)
const securityCtx = document.getElementById('securityChart').getContext('2d');
new Chart(securityCtx, {
    type: 'bar',
    data: {
        labels: [
            <?php 
            foreach ($stats['security'] as $s) {
                echo "'" . addslashes($s['security_level']) . "',";
            }
            ?>
        ],
        datasets: [{
            label: 'Număr Comenzi',
            data: [
                <?php 
                foreach ($stats['security'] as $s) {
                    echo $s['count'] . ",";
                }
                ?>
            ],
            backgroundColor: ['#4CAF50', '#FF9800', '#f44336']
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
</body>
</html>
