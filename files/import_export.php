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
    die("Acces interzis. Necesită permisiuni de administrator.");
}

$message = '';
$success = false;

// Export către Excel (CSV format compatibil cu Excel)
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $query = "SELECT o.id, u.username, o.pickup_location, o.delivery_location, o.cargo_type, 
              o.cargo_weight, o.security_level, o.pickup_date, o.status, o.estimated_price, o.created_at
              FROM transport_orders o 
              JOIN users u ON o.user_id = u.id 
              ORDER BY o.created_at DESC";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        // Setează headers pentru download Excel
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="comenzi_transport_' . date('Y-m-d') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Adaugă BOM pentru UTF-8
        echo "\xEF\xBB\xBF";
        
        // Creează tabel HTML (Excel poate deschide HTML)
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        echo '<style>table {border-collapse: collapse;} th, td {border: 1px solid black; padding: 5px;}</style>';
        echo '</head>';
        echo '<body>';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Client</th>';
        echo '<th>Loc Preluare</th>';
        echo '<th>Loc Livrare</th>';
        echo '<th>Tip Marfă</th>';
        echo '<th>Greutate (kg)</th>';
        echo '<th>Nivel Securitate</th>';
        echo '<th>Data Preluare</th>';
        echo '<th>Status</th>';
        echo '<th>Preț Estimat</th>';
        echo '<th>Data Creare</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['username']) . '</td>';
            echo '<td>' . htmlspecialchars($row['pickup_location']) . '</td>';
            echo '<td>' . htmlspecialchars($row['delivery_location']) . '</td>';
            echo '<td>' . htmlspecialchars($row['cargo_type']) . '</td>';
            echo '<td>' . htmlspecialchars($row['cargo_weight']) . '</td>';
            echo '<td>' . htmlspecialchars($row['security_level']) . '</td>';
            echo '<td>' . htmlspecialchars($row['pickup_date']) . '</td>';
            echo '<td>' . htmlspecialchars($row['status']) . '</td>';
            echo '<td>' . htmlspecialchars($row['estimated_price'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</body>';
        echo '</html>';
        exit();
    }
}

// Export către PDF
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
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
    
    generate_orders_pdf($orders);
    exit();
}

// Import din Excel/CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    $file = $_FILES['import_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, ['csv', 'xls', 'xlsx'])) {
            $handle = fopen($file['tmp_name'], 'r');
            
            if ($handle) {
                $imported = 0;
                $errors = 0;
                $header = fgetcsv($handle, 1000, ','); // Skip header row
                
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    // Presupunem că CSV-ul are: user_id, pickup_location, delivery_location, cargo_type, cargo_weight, security_level, pickup_date
                    if (count($data) >= 7) {
                        $stmt = $conn->prepare("INSERT INTO transport_orders (user_id, pickup_location, delivery_location, cargo_type, cargo_weight, security_level, pickup_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                        $stmt->bind_param("isssdss", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]);
                        
                        if ($stmt->execute()) {
                            $imported++;
                        } else {
                            $errors++;
                        }
                    }
                }
                
                fclose($handle);
                $success = true;
                $message = "Import finalizat: $imported înregistrări importate, $errors erori.";
            }
        } else {
            $message = "Tipul fișierului nu este suportat. Folosiți CSV, XLS sau XLSX.";
        }
    } else {
        $message = "Eroare la încărcarea fișierului.";
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Import/Export Date - Black Shield Logistics</title>
    <link rel="stylesheet" href="style.css?v=1.7">
    <style>
        .import-export-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        .section-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .section-card h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .export-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .export-btn {
            display: inline-block;
            padding: 15px 25px;
            background: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            transition: background 0.3s;
        }
        .export-btn:hover {
            background: #0b7dda;
        }
        .export-btn.excel {
            background: #217346;
        }
        .export-btn.excel:hover {
            background: #1a5c37;
        }
        .export-btn.pdf {
            background: #d32f2f;
        }
        .export-btn.pdf:hover {
            background: #b71c1c;
        }
        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin-top: 20px;
        }
        .upload-btn {
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .upload-btn:hover {
            background: #45a049;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info-text {
            color: #666;
            font-size: 0.9em;
            margin-top: 10px;
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
        <a href="users.php" class="nav-btn">Utilizatori</a>
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

<div class="import-export-container">
    <h2>📊 Import/Export Date</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <!-- Export Section -->
    <div class="section-card">
        <h3>📤 Export Date</h3>
        <p>Descărcați datele din aplicație în diferite formate:</p>
        
        <div class="export-buttons">
            <a href="import_export.php?export=excel" class="export-btn excel">
                📊 Export Excel (.xls)<br>
                <small class="info-text">Compatibil cu Microsoft Excel</small>
            </a>
            
            <a href="import_export.php?export=pdf" class="export-btn pdf">
                📄 Export PDF<br>
                <small class="info-text">Document portabil</small>
            </a>
            
            <a href="export_word.php" class="export-btn" style="background: #2b579a;">
                📝 Export Word (.doc)<br>
                <small class="info-text">Compatibil cu Microsoft Word</small>
            </a>
        </div>
        
        <p class="info-text">
            ℹ️ Export-ul va include toate comenzile de transport cu detaliile complete.
        </p>
    </div>
    
    <!-- Import Section -->
    <div class="section-card">
        <h3>📥 Import Date</h3>
        <p>Încărcați date din fișiere externe (CSV, Excel):</p>
        
        <div class="upload-area">
            <form method="POST" enctype="multipart/form-data">
                <p>📁 Selectați fișierul pentru import:</p>
                <input type="file" name="import_file" accept=".csv,.xls,.xlsx" required>
                <br><br>
                <button type="submit" class="upload-btn">⬆️ Încarcă și importă</button>
            </form>
        </div>
        
        <div class="info-text" style="margin-top: 20px; background: #fff3cd; padding: 15px; border-radius: 4px;">
            <strong>⚠️ Format CSV așteptat:</strong><br>
            user_id, pickup_location, delivery_location, cargo_type, cargo_weight, security_level, pickup_date<br>
            <strong>Exemplu:</strong> 1, București, Cluj, Documente, 50, Standard, 2026-01-10
        </div>
    </div>
    
    <!-- Statistici rapide -->
    <div class="section-card">
        <h3>📈 Statistici Rapide</h3>
        <?php
        $stats = $conn->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
            FROM transport_orders");
        
        if ($stats_row = $stats->fetch_assoc()) {
            echo "<p><strong>Total comenzi:</strong> " . $stats_row['total'] . "</p>";
            echo "<p><strong>Completate:</strong> " . $stats_row['completed'] . "</p>";
            echo "<p><strong>În așteptare:</strong> " . $stats_row['pending'] . "</p>";
        }
        ?>
    </div>
</div>
</body>
</html>
