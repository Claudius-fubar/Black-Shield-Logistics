<?php
session_start();
include 'session_control.php';
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Change email
if(isset($_POST['change_email'])){
    $current_password = $_POST['current_password'];
    $new_email = $_POST['new_email'];

    // Verificăm parola curentă
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($password_hash);
    $stmt->fetch();
    $stmt->close();

    if(password_verify($current_password, $password_hash)){
        // Actualizare email
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->bind_param("si", $new_email, $user_id);
        if($stmt->execute()){
            $success = "Email-ul a fost actualizat cu succes!";
        } else {
            $error = "Eroare la actualizarea email-ului.";
        }
        $stmt->close();
    } else {
        $error = "Parola introdusă este incorectă!";
    }
}

// Delete account
if(isset($_POST['delete_account'])){
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    session_unset();
    session_destroy();

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>My Account - Black Shield Logistics</title>
    <link rel="stylesheet" href="style.css?v=1.8">
</head>
<body>
<div class="center-wrapper">
    <div class="login-box">
        <h2>My Account</h2>

        <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
        <?php if($success) echo "<p style='color:green;'>$success</p>"; ?>

        <!-- Formular schimbare email -->
        <form method="post">
            <h3>Change my email</h3>
            <input type="password" name="current_password" placeholder="Parola curentă" required><br><br>
            <input type="email" name="new_email" placeholder="Noul email" required><br><br>
            <button type="submit" name="change_email" class="btn" style="background:blue; color:white;">Change my email</button>
        </form>
        <hr>

        <!-- Delete account -->
        <form method="post">
            <button type="submit" name="delete_account" class="btn" style="background:red; color:white;">Delete my account</button>
        </form>
    </div>
</div>
</body>
</html>
