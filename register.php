<?php
session_start();
include 'db.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];

    if($password !== $confirm_password){
        $error = "Parolele nu coincid!";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $permission_id = 1;
        $company_name = null;
        $id_verification = null;

        if($user_type === 'sender'){
            $permission_id = 2;
            $company_name = $_POST['company_name'] ?? null;
            $id_verification = $_POST['id_verification'] ?? null;
        } else if($user_type === 'admin'){
            $permission_id = 3;
        }

        $status_verification = ($permission_id == 1) ? 'approved' : 'pending';

        $stmt = $conn->prepare("INSERT INTO users 
            (first_name, last_name, email, phone, password_hash, permission_id, company_name, id_verification, status_verification) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssisss", $first_name, $last_name, $email, $phone, $password_hash, $permission_id, $company_name, $id_verification, $status_verification);

        if($stmt->execute()){
            $last_id = $conn->insert_id;
            $_SESSION['user_id'] = $last_id;
            $_SESSION['permission_id'] = $permission_id;
            header("Location: index.php");
            exit();
        } else {
            $error = "A apărut o eroare la înregistrare.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Register - Black Shield Logistics</title>
    <link rel="stylesheet" href="style.css?v=1.7">
    <script>
        function toggleFields(){
            const type = document.getElementById('user_type').value;
            const extra = document.getElementById('extra-fields');
            const adminWarning = document.getElementById('admin-warning');

            if(type === 'base'){
                extra.style.display = 'none';
                adminWarning.style.display = 'none';
            } else if(type === 'sender'){
                extra.style.display = 'block';
                adminWarning.style.display = 'none';
            } else if(type === 'admin'){
                extra.style.display = 'none';
                adminWarning.style.display = 'block';
            }
        }
    </script>
</head>
<body>
<div class="center-wrapper">
    <div class="login-box">
        <h2>Register</h2>
        <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="post">
            <input type="text" name="first_name" placeholder="Nume" required><br><br>
            <input type="text" name="last_name" placeholder="Prenume" required><br><br>
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="text" name="phone" placeholder="Număr de telefon" required><br><br>
            <input type="password" name="password" placeholder="Parolă" required><br><br>
            <input type="password" name="confirm_password" placeholder="Confirmă parola" required><br><br>

            <label for="user_type">Tip de utilizator:</label>
            <select id="user_type" name="user_type" onchange="toggleFields()" required>
                <option value="base">Base user</option>
                <option value="sender">Sender / Recepționer</option>
                <option value="admin">Admin</option>
            </select><br><br>

            <div id="extra-fields" style="display:none;">
                <input type="text" name="company_name" placeholder="Numele companiei"><br><br>
                <textarea name="id_verification" placeholder="Date complete de identificare"></textarea><br><br>
            </div>

            <p id="admin-warning" style="color:orange; display:none;">Admin verification required for approval</p>

            <button type="submit" class="btn">Înregistrează-te</button>
        </form>
        <p>Ai deja cont? <a href="login.php">Login</a></p>
    </div>
</div>
</body>
</html>
