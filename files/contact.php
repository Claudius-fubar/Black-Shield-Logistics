<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'session_control.php';
include 'db.php';

$user_permission = 0;
$user_email = '';
$user_name = '';
if(isset($_SESSION['user_id'])){
    $id = $_SESSION['user_id'];
    $result = $conn->query("SELECT permission_id, email, first_name, last_name FROM users WHERE id = $id");
    if($row = $result->fetch_assoc()){
        $user_permission = $row['permission_id'];
        $user_email = $row['email'];
        $user_name = $row['first_name'] . ' ' . $row['last_name'];
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
                
                $admin_email = 'admin@blackshieldlogistics.com'; // Schimbă cu email-ul real
                $email_subject = "Mesaj nou de contact: " . $subject;
                $email_body = "
                    <h2>Mesaj nou de contact</h2>
                    <p><strong>De la:</strong> $name</p>
                    <p><strong>Email:</strong> $email</p>
                    <p><strong>Subiect:</strong> $subject</p>
                    <p><strong>Mesaj:</strong></p>
                    <p>" . nl2br(htmlspecialchars($message_text)) . "</p>
                ";
                
                send_mail_smtp($admin_email, $email_subject, $email_body);
                
                // Trimite email de confirmare către utilizator
                $confirmation_subject = "Confirmăm primirea mesajului dumneavoastră";
                $confirmation_body = "
                    <h2>Bună ziua, $name!</h2>
                    <p>Vă mulțumim pentru că ne-ați contactat. Am primit mesajul dumneavoastră și vă vom răspunde în cel mai scurt timp posibil.</p>
                    <p><strong>Subiectul mesajului:</strong> $subject</p>
                    <p>Cu stimă,<br>Echipa Black Shield Logistics</p>
                ";
                
                send_mail_smtp($email, $confirmation_subject, $confirmation_body);
                
                $success = true;
                $message = "Mesajul a fost trimis cu succes! Veți primi un răspuns în curând.";
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
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .contact-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            background-color: #fff;
            color: #333;
        }
        .form-group textarea {
            min-height: 150px;
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
        .contact-info {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .contact-info h3 {
            margin-top: 0;
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
    
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="contact-form">
        <form method="POST" action="contact.php">
            <div class="form-group">
                <label for="name">Nume complet *</label>
                <input type="text" id="name" name="name" required 
                       value="<?php echo htmlspecialchars($user_name); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($user_email); ?>">
            </div>
            
            <div class="form-group">
                <label for="subject">Subiect *</label>
                <select id="subject" name="subject" required>
                    <option value="">Selectați un subiect</option>
                    <option value="Cerere ofertă">Cerere ofertă</option>
                    <option value="Informații servicii">Informații servicii</option>
                    <option value="Suport tehnic">Suport tehnic</option>
                    <option value="Reclamații">Reclamații</option>
                    <option value="Altele">Altele</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="message">Mesaj *</label>
                <textarea id="message" name="message" required 
                          placeholder="Descrieți cererea dumneavoastră în detaliu..."></textarea>
            </div>
            
            <button type="submit" class="submit-btn">Trimite mesaj</button>
        </form>
    </div>
    
    <div class="contact-info">
        <h3>Informații de contact</h3>
        <p><strong>📞 Telefon:</strong> +40 21 XXX XXXX</p>
        <p><strong>📧 Email:</strong> contact@blackshieldlogistics.com</p>
        <p><strong>📍 Adresă:</strong> București, România</p>
        <p><strong>🕒 Program:</strong> Luni - Vineri, 09:00 - 18:00</p>
    </div>
</div>
</body>
</html>
