<?php
session_start();
require_once 'includes/db.php';

$message = '';
if (isset($_GET['register'])) {
    $message = "Inscription réussie ! Connectez-vous.";
}

$erreur = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');
    $mdp = $_POST["mot_de_passe"] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && (
      $mdp === $user['mot_de_passe'] 
      || password_verify($mdp, $user['mot_de_passe'])
   )) {
        $_SESSION["user"] = [
            "id" => $user['id'],
            "nom" => $user['nom'],
            "prenom" => $user['prenom'],
            "email" => $user['email'],
            "role" => $user['role'],
        ];

        // Redirection intelligente selon le rôle
        if ($user['role'] === 'agent') {
            header("Location: agentdashboard.php");
            exit;
        } elseif ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
            exit;
        } else {
            header("Location: compte.php");
            exit;
        }
    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>

<?php include 'includes/header.php'; ?>
<div class="container mt-5" style="max-width:400px;">
    <h2 class="mb-4 fw-bold">Connexion</h2>
    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="mot_de_passe" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Connexion</button>
    </form>
    <p class="mt-3 text-center">Pas encore de compte ? <a href="register.php">Créer un compte</a></p>
</div>
<?php include 'includes/footer.php'; ?>