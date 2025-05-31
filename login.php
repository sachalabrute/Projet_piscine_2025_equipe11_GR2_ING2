<?php

$host = 'localhost';
$db = 'omnes_immobilier';
$user = 'root';
$pass = '';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT id, nom, email, adresse FROM utilisateurs WHERE email = :email AND mot_de_passe = :password";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email, 'password' => $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user'] = $user;
            header('Location: compte.php');
            exit();
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } catch(PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center mb-4">Connexion</h2>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
