<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'session_control.php';
include 'db.php';

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_permission = 0;
$user_email = '';
$user_name = '';

$result = $conn->query("SELECT permission_id, email, first_name, last_name FROM users WHERE id = $user_id");
if($result && $row = $result->fetch_assoc()){
    $user_permission = $row['permission_id'];
    $user_email = $row['email'];
    $user_name = $row['first_name'] . ' ' . $row['last_name'];
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_location = $_POST['pickup_location'] ?? '';
    $delivery_location = $_POST['delivery_location'] ?? '';
    $cargo_type = $_POST['cargo_type'] ?? '';
    $cargo_weight = $_POST['cargo_weight'] ?? '';
    $security_level = $_POST['security_level'] ?? '';
    $pickup_date = $_POST['pickup_date'] ?? '';
    $special_requirements = $_POST['special_requirements'] ?? '';
    
    // Validare
    if (!empty($pickup_location) && !empty($delivery_location) && !empty($cargo_type) && 
        !empty($cargo_weight) && !empty($security_level) && !empty($pickup_date)) {
        
        // Salvează comanda în baza de date
        $sql_insert = "INSERT INTO transport_orders (user_id, pickup_location, delivery_location, cargo_type, cargo_weight, security_level, pickup_date, special_requirements, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $conn->prepare($sql_insert);
        
        // Auto-repair: Dacă tabelul nu există, încearcă să îl creezi
        if (!$stmt && strpos($conn->error, "doesn't exist") !== false) {
            $create_table_sql = "CREATE TABLE IF NOT EXISTS transport_orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                pickup_location VARCHAR(255) NOT NULL,
                delivery_location VARCHAR(255) NOT NULL,
                cargo_type VARCHAR(100) NOT NULL,
                cargo_weight DECIMAL(10,2) NOT NULL,
                security_level VARCHAR(50) NOT NULL,
                pickup_date DATE NOT NULL,
                special_requirements TEXT,
                status ENUM('pending', 'approved', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
                estimated_price DECIMAL(10,2),
                assigned_vehicle VARCHAR(100),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_status (status),
                INDEX idx_pickup_date (pickup_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            if ($conn->query($create_table_sql)) {
                // Încearcă din nou prepare după creare
                $stmt = $conn->prepare($sql_insert);
            }
        }
        
        if (!$stmt) {
            $message = "Eroare sistem (SQL Prepare): " . $conn->error . ". Posibil tabelul 'transport_orders' lipsește. Rulați install_features.php.";
        } else {
            $stmt->bind_param("isssdsss", $user_id, $pickup_location, $delivery_location, $cargo_type, $cargo_weight, $security_level, $pickup_date, $special_requirements);
            
            if ($stmt->execute()) {
                $order_id = $conn->insert_id;
                
                // Trimite email către admin
                require_once 'mailer.php';
                
                $admin_email = 'admin@blackshieldlogistics.com';
                $email_subject = "Comandă nouă de transport #$order_id";
                $email_body = "
                    <h2>Comandă nouă de transport</h2>
                    <p><strong>Comandă ID:</strong> #$order_id</p>
                    <p><strong>Client:</strong> $user_name ($user_email)</p>
                    <hr>
                    <p><strong>Locație preluare:</strong> $pickup_location</p>
                    <p><strong>Locație livrare:</strong> $delivery_location</p>
                    <p><strong>Tip marfă:</strong> $cargo_type</p>
                    <p><strong>Greutate:</strong> $cargo_weight kg</p>
                    <p><strong>Nivel securitate:</strong> $security_level</p>
                    <p><strong>Data preluare:</strong> $pickup_date</p>
                    <p><strong>Cerințe speciale:</strong> " . nl2br(htmlspecialchars($special_requirements)) . "</p>
                ";
                
                send_mail_smtp($admin_email, $email_subject, $email_body);
                
                // Trimite email de confirmare către client
                $client_subject = "Confirmare comandă transport #$order_id";
                $client_body = "
                    <h2>Bună ziua, $user_name!</h2>
                    <p>Vă mulțumim pentru comandă. Cererea dumneavoastră de transport a fost înregistrată cu succes.</p>
                    <p><strong>Număr comandă:</strong> #$order_id</p>
                    <p><strong>Status:</strong> În așteptare (pending)</p>
                    <hr>
                    <h3>Detalii comandă:</h3>
                    <p><strong>Preluare din:</strong> $pickup_location</p>
                    <p><strong>Livrare la:</strong> $delivery_location</p>
                    <p><strong>Data preluare:</strong> $pickup_date</p>
                    <p><strong>Tip marfă:</strong> $cargo_type</p>
                    <p><strong>Greutate:</strong> $cargo_weight kg</p>
                    <p><strong>Nivel securitate:</strong> $security_level</p>
                    <hr>
                    <p>Echipa noastră va analiza cererea și vă va contacta în cel mai scurt timp cu o ofertă detaliată.</p>
                    <p>Cu stimă,<br>Echipa Black Shield Logistics</p>
                ";
                
                send_mail_smtp($user_email, $client_subject, $client_body);
                
                $success = true;
                $message = "Comanda a fost înregistrată cu succes! Număr comandă: #$order_id. Veți primi un email de confirmare.";
            } else {
                $message = "Eroare la înregistrarea comenzii: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $message = "Toate câmpurile marcate cu * sunt obligatorii.";
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Comandă Transport - Black Shield Logistics</title>
    <link rel="stylesheet" href="style.css?v=1.7">
    <style>
        .order-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }
        .order-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
            color: #333;
            background-color: #fff;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .submit-btn {
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .submit-btn:hover {
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
        .info-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
            color: steelblue;
        }
        @media (max-width: 768px) {
            .form-row {
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
        <a href="news.php" class="nav-btn">Informații Externe</a>
        <a href="contact.php" class="nav-btn">Contact</a>
        <a href="order.php" class="nav-btn">Comandă Transport</a>
        <?php if($user_permission == 3): ?>
            <a href="users.php" class="nav-btn">Administrare utilizatori</a>
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

<div class="order-container">
    <h2>🚚 Comandă Transport Securizat</h2>
    
    <div class="info-box">
        <strong>ℹ️ Informație:</strong> Completați formularul de mai jos pentru a solicita un transport securizat. 
        Veți primi o ofertă personalizată în maxim 24 ore.
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="order-form">
        <form method="POST" action="order.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="pickup_location">📍 Locație preluare *</label>
                    <input type="text" id="pickup_location" name="pickup_location" required 
                           placeholder="Ex: București, Sector 1">
                </div>
                
                <div class="form-group">
                    <label for="delivery_location">📍 Locație livrare *</label>
                    <input type="text" id="delivery_location" name="delivery_location" required 
                           placeholder="Ex: Cluj-Napoca">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="cargo_type">📦 Tip marfă *</label>
                    <select id="cargo_type" name="cargo_type" required>
                        <option value="">Selectați tipul</option>
                        <option value="Documente confidențiale">Documente confidențiale</option>
                        <option value="Echipamente electronice">Echipamente electronice</option>
                        <option value="Valori monetare">Valori monetare</option>
                        <option value="Echipamente sensibile">Echipamente sensibile</option>
                        <option value="Altele">Altele</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="cargo_weight">⚖️ Greutate (kg) *</label>
                    <input type="number" id="cargo_weight" name="cargo_weight" required 
                           min="0.1" step="0.1" placeholder="Ex: 50">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="security_level">🛡️ Nivel securitate *</label>
                    <select id="security_level" name="security_level" required>
                        <option value="">Selectați nivelul</option>
                        <option value="Standard">Standard - Vehicul comercial</option>
                        <option value="Ridicat">Ridicat - Vehicul blindat + escortă</option>
                        <option value="Maxim">Maxim - Vehicul blindat + escortă armată</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="pickup_date">📅 Data preluare *</label>
                    <input type="date" id="pickup_date" name="pickup_date" required 
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="special_requirements">📝 Cerințe speciale</label>
                <textarea id="special_requirements" name="special_requirements" 
                          placeholder="Specificați orice cerințe suplimentare (ex: temperatură controlată, manevrare specială, etc.)"></textarea>
            </div>
            
            <button type="submit" class="submit-btn">📤 Trimite comandă</button>
        </form>
    </div>
</div>
</body>
</html>
