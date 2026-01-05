<?php
session_start();
include 'session_control.php';
include 'db.php';

$user_permission = 0;
$user_email = '';
$user_name = '';
if(isset($_SESSION['user_id'])){
    $id = $_SESSION['user_id'];
    $result = $conn->query("SELECT permission_id, email, username FROM users WHERE id = $id");
    if($row = $result->fetch_assoc()){
        $user_permission = $row['permission_id'];
        $user_email = $row['email'];
        $user_name = $row['username'];
    }
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message_text = $_POST['message'] ?? '';
    
    // Validare
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message_text)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            
            // Salvează mesajul în baza de date
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $name, $email, $subject, $message_text);
            
            if ($stmt->execute()) {
                // Trimite email către admin
                require_once 'mailer.php';
                
                $admin_email = 'admin@blackshieldlogistics.com';
                $email_subject = "Mesaj nou de contact: " . $subject;
                $email_body = "
                    <h2>Mesaj nou de contact</h2>
                    <p><strong>De la:</strong> $name</p>
                    <p><strong>Email:</strong> $email</p>
                    <p><strong>Subiect:</strong> $subject</p>
                    <p><strong>Mesaj:</strong></p>
                    <p>" . nl2br(htmlspecialchars($message_text)) . "</p>
                ";
                
                if (sendEmail($admin_email, $email_subject, $email_body)) {
                    $message = "Mulțumim pentru mesaj! Vă vom contacta în cel mai scurt timp.";
                    $success = true;
                    
                    // Trimite confirmare și către utilizator
                    $confirm_body = "
                    <h2>Confirmare mesaj trimis</h2>
                    <p>Bună $name,</p>
                    <p>Vă mulțumim pentru că ne-ați contactat. Am primit mesajul dumneavoastră și vă vom răspunde în cel mai scurt timp posibil.</p>
                    <p><strong>Mesajul dvs.:</strong></p>
                    <p><em>$subject</em></p>
                    <p>" . nl2br(htmlspecialchars($message_text)) . "</p>
                    <hr>
                    <p>Cu respect,<br>Echipa Black Shield Logistics</p>
                    ";
                    sendEmail($email, "Confirmare: $subject", $confirm_body);
                } else {
                    $message = "Mesajul a fost salvat, dar emailul nu a putut fi trimis.";
                    $success = true;
                }
            } else {
                $message = "Eroare la salvarea mesajului. Vă rugăm încercați din nou.";
            }
        } else {
            $message = "Adresa de email nu este validă.";
        }
    } else {
        $message = "Toate câmpurile sunt obligatorii.";
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Contact - Black Shield Logistics</title>
    <link rel="stylesheet" href="style.css?v=1.7">
    <style>
        .contact-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .contact-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .contact-form h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        .submit-btn {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .contact-info {
            background: #f5f5f5;
            padding: 30px;
            border-radius: 8px;
        }
        .contact-info h3 {
            margin-bottom: 20px;
            color: #333;
        }
        .contact-info p {
            margin-bottom: 15px;
            color: #555;
            line-height: 1.6;
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

<div class="contact-container">
    <h2>Contactați-ne</h2>
    
    <?php if ($message): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="contact-form">
        <form method="POST" action="contact.php">
            <div class="form-group">
                <label for="name">Nume complet *</label>
                <input type="text" id="name" name="name" required 
                       value="<?= isset($_SESSION['user_id']) ? htmlspecialchars($user_name) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required 
                       value="<?= isset($_SESSION['user_id']) ? htmlspecialchars($user_email) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="subject">Subiect *</label>
                <select id="subject" name="subject" required>
                    <option value="">Selectează un subiect</option>
                    <option value="Informații servicii">Informații servicii</option>
                    <option value="Solicitare ofertă">Solicitare ofertă</option>
                    <option value="Suport tehnic">Suport tehnic</option>
                    <option value="Colaborare">Colaborare</option>
                    <option value="Altele">Altele</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="message">Mesaj *</label>
                <textarea id="message" name="message" required placeholder="Descrieți cererea dumneavoastră în detaliu..."></textarea>
            </div>
            
            <button type="submit" class="submit-btn">Trimite mesaj</button>
        </form>
    </div>

    <div class="contact-info">
        <h3>Informații de contact</h3>
        <p><strong>📧 Email:</strong> contact@blackshieldlogistics.com</p>
        <p><strong>📞 Telefon:</strong> +40 21 XXX XXXX</p>
        <p><strong>📍 Adresă:</strong> București, România</p>
        <p><strong>🕐 Program:</strong> Luni - Vineri: 08:00 - 18:00</p>
        <p><em>Servicii de urgență disponibile 24/7</em></p>
    </div>
</div>
</body>
</html>
