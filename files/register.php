<?php
// register.php - înregistrare cu trimitere OTP prin PHPMailer
session_start();

// DEV: afișează erori temporar - elimină/ setează la 0 în producție
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/db.php';

// --- Include PHPMailer (pe server ai folderul "PHPmailer" cu fișierele direct în el) ---
require_once __DIR__ . '/PHPmailer/Exception.php';
require_once __DIR__ . '/PHPmailer/PHPMailer.php';
require_once __DIR__ . '/PHPmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';

// Procesare formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // colectare + sanitizare minima
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';
    $user_type  = $_POST['user_type'] ?? 'base';

    // validari simple
    if ($first_name === '' || $last_name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = "Completează toate câmpurile corect.";
    } elseif ($password !== $confirm) {
        $error = "Parolele nu coincid!";
    } else {
        // pregatire date
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // set permission_id in functie de tip
        $permission_id = 1; // default Base user
        $company_name = null;
        $id_verification = null;

        if ($user_type === 'sender') {
            $permission_id = 2;
            $company_name = trim($_POST['company_name'] ?? null);
            $id_verification = trim($_POST['id_verification'] ?? null);
        } elseif ($user_type === 'admin') {
            $permission_id = 3;
        }

        $status_verification = ($permission_id == 1) ? 'approved' : 'pending';

        // Insert user (prepared statement)
        $stmt = $conn->prepare("INSERT INTO users
            (first_name, last_name, email, phone, password_hash, permission_id, company_name, id_verification, status_verification)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $error = "Eroare DB (pregătire): " . $conn->error;
        } else {
            $stmt->bind_param(
                "sssssisss",
                $first_name,
                $last_name,
                $email,
                $phone,
                $password_hash,
                $permission_id,
                $company_name,
                $id_verification,
                $status_verification
            );

            if (!$stmt->execute()) {
                // eroare la insert (de ex. email duplicat)
                $error = "Eroare la înregistrare: " . htmlspecialchars($stmt->error);
                $stmt->close();
            } else {
                $new_user_id = $conn->insert_id;
                $stmt->close();

                // Genereaza OTP (6 cifre)
                $otp_plain = random_int(100000, 999999);
                $otp_hash = password_hash((string)$otp_plain, PASSWORD_DEFAULT);
                $expires_at = date('Y-m-d H:i:s', time() + 5*60); // 5 minute

                // Inseram tokenul OTP legat de user
                $ins = $conn->prepare("INSERT INTO otp_tokens (user_id, otp_hash, expires_at, attempts) VALUES (?, ?, ?, 0)");
                if (!$ins) {
                    $error = "Eroare DB (insert OTP): " . $conn->error;
                } else {
                    $ins->bind_param("iss", $new_user_id, $otp_hash, $expires_at);
                    if (!$ins->execute()) {
                        $error = "Eroare la inserare OTP: " . htmlspecialchars($ins->error);
                        $ins->close();
                    } else {
                        $ins->close();

                        // ---- TRIMITERE EMAIL CU PHPMailer ----
                        $mail = new PHPMailer(true);
                        try {
                            // === FILL IN SMTP DETAILS (you provided these values) ===
                            $smtpHost = 'mail.cenache.daw.ssmr.ro';
                            $smtpPort = 587;
                            $smtpUser = 'blackshieldlogistics@cenache.daw.ssmr.ro';
                            $smtpPass = 'Andreiul1@';
                            $smtpSecure = 'tls'; // 'tls' sau 'ssl'
                            $fromEmail = 'blackshieldlogistics@cenache.daw.ssmr.ro';
                            $fromName  = 'Black Shield Logistics';
                            // =====================================================

                            $mail->isSMTP();
                            $mail->Host       = $smtpHost;
                            $mail->SMTPAuth   = true;
                            $mail->Username   = $smtpUser;
                            $mail->Password   = $smtpPass;
                            $mail->SMTPSecure = $smtpSecure;
                            $mail->Port       = $smtpPort;

                            $mail->setFrom($fromEmail, $fromName);
                            $mail->addAddress($email, $first_name . ' ' . $last_name);

                            $mail->isHTML(true);
                            $mail->Subject = 'Codul tău OTP - Black Shield Logistics';
                            $mail->Body    = "<p>Bun venit, " . htmlspecialchars($first_name) . "!</p>"
                                           . "<p>Codul tău de verificare este: <strong>$otp_plain</strong></p>"
                                           . "<p>Acesta expiră în 5 minute.</p>";
                            $mail->AltBody = "Cod OTP: $otp_plain (expiră în 5 minute)";

                            $mail->send();
                            // marca succes trimitere
                            $_SESSION['otp_user_id'] = $new_user_id;
                            // redirectionare la pagina de verificare OTP
                            header("Location: otp_verify.php");
                            exit();

                        } catch (Exception $e) {
                            // eroare la trimitere mail
                            error_log("PHPMailer error: " . $mail->ErrorInfo);
                            // fallback DEV: salvam OTP in sesiune pentru verificare manuala (DOAR DEV)
                            $_SESSION['otp_user_id'] = $new_user_id;
                            $_SESSION['otp_test_code'] = $otp_plain;
                            // redirectionare la pagina de verificare OTP (utilizator va vedea codul in dev)
                            header("Location: otp_verify.php");
                            exit();
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="utf-8">
<title>Register - Black Shield Logistics</title>
<link rel="stylesheet" href="style.css?v=register-clean">
</head>
<body>
<div class="center-wrapper">
  <div class="login-box">
    <h2>Register</h2>

    <?php if ($error): ?>
      <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" novalidate>
      <input type="text" name="first_name" placeholder="Nume" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"><br><br>
      <input type="text" name="last_name" placeholder="Prenume" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"><br><br>
      <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"><br><br>
      <input type="text" name="phone" placeholder="Număr de telefon" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"><br><br>

      <input type="password" name="password" placeholder="Parolă" required><br><br>
      <input type="password" name="confirm_password" placeholder="Confirmă parola" required><br><br>

      <label for="user_type">Tip de utilizator:</label>
      <select id="user_type" name="user_type">
        <option value="base" <?php if(isset($_POST['user_type']) && $_POST['user_type']=='base') echo 'selected'; ?>>Base user</option>
        <option value="sender" <?php if(isset($_POST['user_type']) && $_POST['user_type']=='sender') echo 'selected'; ?>>Sender / Recepționer</option>
        <option value="admin" <?php if(isset($_POST['user_type']) && $_POST['user_type']=='admin') echo 'selected'; ?>>Admin</option>
      </select><br><br>

      <div id="extra-fields" style="display:none;">
        <input type="text" name="company_name" placeholder="Numele companiei"><br><br>
        <textarea name="id_verification" placeholder="Date complete de identificare"></textarea><br><br>
      </div>

      <button type="submit" class="btn">Înregistrează-te</button>
    </form>

    <?php if (isset($_SESSION['otp_test_code'])): ?>
      <p style="color:orange;">(DEV fallback) Cod OTP: <strong><?php echo htmlspecialchars($_SESSION['otp_test_code']); ?></strong></p>
    <?php endif; ?>

    <p>Ai deja cont? <a href="login.php">Login</a></p>
  </div>
</div>

<script>
// opțional: afișează extra fields dacă user_type = sender
document.addEventListener('DOMContentLoaded', function(){
  const select = document.getElementById('user_type');
  const extra = document.getElementById('extra-fields');
  function toggle(){ extra.style.display = (select.value === 'sender') ? 'block' : 'none'; }
  toggle();
  select.addEventListener('change', toggle);
});
</script>

</body>
</html>
