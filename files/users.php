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

$message = '';
$error = '';

// Handle promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote_user_id'])) {
    $promote_id = intval($_POST['promote_user_id']);
    // Update user permission to 3 (Admin)
    $stmt = $conn->prepare("UPDATE users SET permission_id = 3 WHERE id = ?");
    $stmt->bind_param("i", $promote_id);
    if ($stmt->execute()) {
        $message = "Utilizatorul a fost promovat cu succes!";
    } else {
        $error = "Eroare la promovare: " . $conn->error;
    }
    $stmt->close();
}

// CRUD: Read - afișare utilizatori
$users_result = $conn->query("SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.permission_id, p.permission_name 
                              FROM users u 
                              LEFT JOIN user_permissions p ON u.permission_id = p.id");

if (!$users_result) {
    // Fallback if table user_permissions doesn't exist or other error
    $users_result = $conn->query("SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.permission_id, 'Unknown' as permission_name 
                                  FROM users u");
}

$roles_map = [
    1 => 'Client',
    2 => 'Sender',
    3 => 'Admin'
];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Administrare Utilizatori</title>
    <link rel="stylesheet" href="style.css?v=1.3">
    <style>
        .btn-promote {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-promote:hover {
            background-color: #45a049;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-success { background-color: #dff0d8; color: #3c763d; }
        .alert-error { background-color: #f2dede; color: #a94442; }
    </style>
</head>
<body>
<div class="navbar">
    <div class="nav-left">
        <h1>Black Shield Logistics</h1>
    </div>
    <div class="nav-center">
        <a href="index.php" class="nav-btn">Acasă</a>
        <a href="import_export.php" class="nav-btn">Import/Export</a>
        <a href="statistics.php" class="nav-btn">Statistici</a>
        <a href="users.php" class="nav-btn">Utilizatori</a>
    </div>
    <div class="nav-right">
        <div class="dropdown">
            <button class="dropbtn">Cont ▾</button>
            <div class="dropdown-content">
                <a href="myaccount.php">My Account</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="main-content" style="max-width: 1200px; margin: 40px auto; padding: 20px;">
    <h2>Administrare Utilizatori</h2>
    
    <?php if($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; background: white; color: black;">
        <tr style="background: #f2f2f2;">
            <th>ID</th>
            <th>Nume</th>
            <th>Prenume</th>
            <th>Email</th>
            <th>Telefon</th>
            <th>Permisiune</th>
            <th>Acțiuni</th>
        </tr>
        <?php 
        if ($users_result && $users_result->num_rows > 0):
            while($user = $users_result->fetch_assoc()): 
                $perm_name = $user['permission_name'];
                if (empty($perm_name) || $perm_name === 'Unknown') {
                    $perm_name = $roles_map[$user['permission_id']] ?? 'Unknown (' . $user['permission_id'] . ')';
                }
        ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['first_name']); ?></td>
            <td><?php echo htmlspecialchars($user['last_name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['phone']); ?></td>
            <td><?php echo htmlspecialchars($perm_name); ?></td>
            <td>
                <?php if($user['permission_id'] != 3): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="promote_user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="btn-promote" onclick="return confirm('Sigur vrei să promovezi acest utilizator la Admin?')">Promovează Admin</button>
                    </form>
                <?php else: ?>
                    <span style="color: green;">Admin</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php 
            endwhile; 
        else:
        ?>
        <tr>
            <td colspan="7" style="text-align:center;">Nu există utilizatori înregistrați.</td>
        </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
