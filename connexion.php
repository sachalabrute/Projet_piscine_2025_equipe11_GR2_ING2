<?php
$host = 'localhost';
$db = 'omnes_immobilier';
$user = 'root';         // adapte selon ton serveur
$pass = '';             // adapte selon ton serveur

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

