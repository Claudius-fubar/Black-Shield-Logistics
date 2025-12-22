<?php
$servername = "localhost";
$username = "cenaches_BSL"; // sau userul tău
$password = "ukGqv6pCJXK78EU8pJq4";     // parola ta
$dbname = "cenaches_BSL";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}
?>

