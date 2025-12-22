<?php
session_start();
include 'session_control.php';
require_once 'db.php';

$error = '';
$info = '';

if(!isset($_SESSION['otp_user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['otp_user_id'];

// luăm ultimul token valid
$now = date('Y-m-d H:i:s');
$stmt = $conn->prepare("SELECT id, otp_hash, expires_at, attempts FROM otp_tokens WHERE user_id = ? AND expires_at >= ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("is", $user_id, $now);
$stmt->execute();
$res = $stmt->get_result();
$token = $res->fetch_assoc();
$stmt->close();

if(!$token){
    $error = "Nu există un OTP valid (sau a expirat). Reîncearcă login/înregistrare.";
}

// Procesare formular
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp_code'])){
    $otp_input = trim($_POST['otp_code']);

    if(!$token){
        $error = "Token-ul OTP nu a fost găsit. Reîncearcă.";
    } else {
        if($token['attempts'] >= 5){
            $error = "Ai depășit numărul de încercări. Reîncearcă.";
        } else {
            if(password_verify($otp_input, $token['otp_hash'])){
                // autentificare finala
                $del = $conn->prepare("DELETE FROM otp_tokens WHERE user_id = ?");
                $del->bind_param("i", $user_id);
                $del->execute();
                $del->close();

                $_SESSION['user_id'] = $user_id;
                unset($_SESSION['otp_user_id']);
                unset($_SESSION['otp_test_code']);
                header("Location: index.php");
                exit();
            } else {
                $upd = $conn->prepare("UPDATE otp_tokens SET attempts = attempts + 1 WHERE id = ?");
                $upd->bind_param("i", $token['id']);
                $upd->execute();
                $upd->close();
                $error = "Codul introdus este incorect.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Verificare OTP - Black Shield Logistics</title>
    <link rel="stylesheet" href="style.css?v=2.1">
</head>
<body>
<div class="center-wrapper">
    <div class="login-box">
        <h2>Introdu codul OTP</h2>
        <?php if($error) echo "<p style='color:red;'>".htmlspecialchars($error)."</p>"; ?>
        <?php if($info) echo "<p style='color:green;'>".htmlspecialchars($info)."</p>"; ?>

        <?php if(isset($_SESSION['otp_test_code'])): ?>
            <p style="color:yellow;">(DEV) OTP test: <strong><?php echo htmlspecialchars($_SESSION['otp_test_code']); ?></strong></p>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="otp_code" placeholder="6 cifre" pattern="\d{6}" maxlength="6" required><br><br>
            <button type="submit" class="btn">Verifică</button>
        </form>
        <p>Dacă OTP-ul a expirat, reîncearcă procesul de login/înregistrare.</p>
    </div>
</div>
</body>
</html>
