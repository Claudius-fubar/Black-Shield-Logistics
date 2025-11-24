<?php
session_start();
include 'db.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password_hash, permission_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if($stmt->num_rows > 0){
        $stmt->bind_result($id, $password_hash, $permission_id);
        $stmt->fetch();
        if(password_verify($password, $password_hash)){
            $_SESSION['user_id'] = $id;
            $_SESSION['permission_id'] = $permission_id;
            header("Location: index.php");
            exit();
        } else {
            $error = "Parola incorectă!";
        }
    } else {
        $error = "Email-ul nu există!";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login - Black Shield Logistics</title>
    <link rel="stylesheet" href="style.css?v=1.7">
</head>
<body>
<div class="center-wrapper">
    <div class="login-box">
        <h2>Login</h2>
        <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Parola" required><br><br>
            <button type="submit" class="btn">Login</button>
        </form>
        <p>Nu ai cont? <a href="register.php">Înregistrează-te</a></p>
    </div>
</div>
</body>
</html>
