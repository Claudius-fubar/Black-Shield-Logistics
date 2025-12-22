<?php
session_start();
include 'session_control.php';
include 'db.php';

// verifică dacă utilizatorul e logat și e admin
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];
$result = $conn->query("SELECT permission_id FROM users WHERE id = $id");
if($row = $result->fetch_assoc()){
    $user_permission = $row['permission_id'];
    if($user_permission != 3){
        die("Acces interzis!"); // doar admin
    }
} else {
    die("Acces interzis!");
}

// CRUD: Read - afișare utilizatori
$users_result = $conn->query("SELECT u.id, u.first_name, u.last_name, u.email, u.phone, p.permission_name 
                              FROM users u 
                              JOIN user_permissions p ON u.permission_id = p.id");
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Administrare Utilizatori</title>
    <link rel="stylesheet" href="style.css?v=1.3">
</head>
<body>
<div class="main-content">
    <h2>Administrare Utilizatori</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Nume</th>
            <th>Prenume</th>
            <th>Email</th>
            <th>Telefon</th>
            <th>Permisiune</th>
        </tr>
        <?php while($user = $users_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo $user['first_name']; ?></td>
            <td><?php echo $user['last_name']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td><?php echo $user['phone']; ?></td>
            <td><?php echo $user['permission_name']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
