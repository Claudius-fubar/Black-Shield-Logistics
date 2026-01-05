<?php
/**
 * Script de instalare automată pentru noile funcționalități
 * Rulați acest fișier o singură dată pentru a crea tabelele necesare
 */

include 'db.php';

echo "<h1>Instalare Black Shield Logistics - Noi Funcționalități</h1>";
echo "<p>Crearea tabelelor necesare...</p>";

$errors = [];
$success = [];

// Tabel contact_messages
$sql1 = "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql1) === TRUE) {
    $success[] = "✅ Tabel 'contact_messages' creat cu succes";
} else {
    $errors[] = "❌ Eroare la crearea tabelului 'contact_messages': " . $conn->error;
}

// Tabel transport_orders
$sql2 = "CREATE TABLE IF NOT EXISTS transport_orders (
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

if ($conn->query($sql2) === TRUE) {
    $success[] = "✅ Tabel 'transport_orders' creat cu succes";
} else {
    $errors[] = "❌ Eroare la crearea tabelului 'transport_orders': " . $conn->error;
}

// Tabel order_statistics
$sql3 = "CREATE TABLE IF NOT EXISTS order_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_month VARCHAR(7) NOT NULL,
    total_orders INT DEFAULT 0,
    completed_orders INT DEFAULT 0,
    cancelled_orders INT DEFAULT 0,
    total_revenue DECIMAL(12,2) DEFAULT 0,
    avg_delivery_time DECIMAL(5,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_period (period_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql3) === TRUE) {
    $success[] = "✅ Tabel 'order_statistics' creat cu succes";
} else {
    $errors[] = "❌ Eroare la crearea tabelului 'order_statistics': " . $conn->error;
}

// Inserează date demo pentru testare
$demo_data = "INSERT INTO transport_orders (user_id, pickup_location, delivery_location, cargo_type, cargo_weight, security_level, pickup_date, status) VALUES
(1, 'București', 'Cluj-Napoca', 'Documente confidențiale', 25.5, 'Ridicat', '2026-01-15', 'completed'),
(1, 'Timișoara', 'Constanța', 'Echipamente electronice', 150.0, 'Maxim', '2026-01-20', 'in_progress'),
(1, 'Brașov', 'Iași', 'Valori monetare', 50.0, 'Maxim', '2026-01-25', 'pending'),
(1, 'București', 'Sibiu', 'Echipamente sensibile', 75.0, 'Standard', '2025-12-10', 'completed'),
(1, 'Cluj-Napoca', 'București', 'Documente confidențiale', 30.0, 'Ridicat', '2025-12-15', 'completed'),
(1, 'Constanța', 'Timișoara', 'Altele', 100.0, 'Standard', '2025-11-20', 'completed'),
(1, 'Iași', 'București', 'Echipamente electronice', 200.0, 'Maxim', '2025-11-25', 'cancelled')";

if ($conn->query($demo_data) === TRUE) {
    $success[] = "✅ Date demo inserate cu succes pentru testare";
} else {
    // Nu e critică această eroare (poate exista deja date)
    $success[] = "ℹ️ Date demo: " . $conn->error;
}

// Afișează rezultatele
echo "<div style='margin: 20px; padding: 20px; background: #f0f0f0; border-radius: 8px;'>";

if (!empty($success)) {
    echo "<h3 style='color: green;'>Succese:</h3><ul>";
    foreach ($success as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul>";
}

if (!empty($errors)) {
    echo "<h3 style='color: red;'>Erori:</h3><ul>";
    foreach ($errors as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul>";
} else {
    echo "<h2 style='color: green;'>🎉 Instalarea s-a finalizat cu succes!</h2>";
    echo "<p>Toate tabelele au fost create și aplicația este gata de utilizare.</p>";
}

echo "</div>";

echo "<div style='margin: 20px; padding: 20px; background: #e3f2fd; border-radius: 8px;'>";
echo "<h3>Pași următori:</h3>";
echo "<ol>";
echo "<li>Configurați PHPMailer în <code>mailer.php</code> cu datele SMTP</li>";
echo "<li>Testați funcționalitățile:
    <ul>
        <li><a href='news.php'>Conținut extern</a></li>
        <li><a href='contact.php'>Formular contact</a></li>
        <li><a href='order.php'>Comandă transport</a> (necesită autentificare)</li>
        <li><a href='statistics.php'>Statistici și grafice</a> (necesită autentificare)</li>
        <li><a href='import_export.php'>Import/Export</a> (necesită permisiuni admin)</li>
    </ul>
</li>";
echo "<li>Ștergeți acest fișier după instalare pentru securitate: <code>install_features.php</code></li>";
echo "</ol>";
echo "</div>";

echo "<div style='margin: 20px;'>";
echo "<a href='index.php' style='display: inline-block; padding: 15px 30px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>🏠 Mergi la Pagina Principală</a>";
echo "</div>";

$conn->close();
?>
