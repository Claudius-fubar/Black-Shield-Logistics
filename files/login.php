<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalid.";
    } else {
        // prepared statement pentru siguranță
        $stmt = $conn->prepare("SELECT id, password_hash, permission_id, status_verification FROM users WHERE email = ?");
        if (!$stmt) {
            $error = "Eroare DB: " . $conn->error;
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                $error = "Email-ul nu există!";
                $stmt->close();
            } else {
                $stmt->bind_result($id, $password_hash, $permission_id, $status_verification);
                $stmt->fetch();
                $stmt->close();

                if (!password_verify($password, $password_hash)) {
                    $error = "Parola incorectă!";
                } else {
                    // optional: blocare daca sender/admin nu e aprobat
                    if ($permission_id != 1 && $status_verification !== 'approved') {
                        $error = "Contul tău este în curs de verificare de către administratori.";
                    } else {
                        // setăm sesiunea
                        $_SESSION['user_id'] = $id;
                        $_SESSION['permission_id'] = $permission_id;

                        header("Location: index.php");
                        exit();
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
    <meta charset="UTF-8">
    <title>Login - Black Shield</title>
    <link rel="stylesheet" href="style.css?v=clean">
</head>
<body>
<div class="center-wrapper">
  <div class="login-box">
    <h2>Login</h2>

    <?php if ($error): ?>
      <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" novalidate>
      <input type="email" name="email" placeholder="Email" required><br><br>
      <input type="password" name="password" placeholder="Parola" required><br><br>
      <button type="submit" class="btn">Login</button>
    </form>

    <p>Nu ai cont? <a href="register.php">Înregistrează-te</a></p>
  </div>
</div>
</body>
</html>
