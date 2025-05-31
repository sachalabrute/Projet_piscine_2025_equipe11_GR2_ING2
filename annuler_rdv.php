<?php
$host = 'localhost';
$db = 'omnes_immobilier';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rdv_id = $_POST['rdv_id'];

        $sql = "UPDATE rdv SET statut = 'annulé' WHERE id = :rdv_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['rdv_id' => $rdv_id]);

        echo "Rendez-vous annulé avec succès.";
    }
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
