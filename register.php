<?php
require_once 'includes/db.php';
include 'includes/header.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $role = 'client';

    // Vérifie que l'email n'est pas déjà utilisé
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $msg = "Un compte existe déjà avec cet email.";
    } else {
        // Hash le mot de passe
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nom, $email, $mot_de_passe_hash, $role])) {
            $msg = "Compte créé, connectez-vous !";
        } else {
            $msg = "Erreur lors de la création.";
        }
    }
}
?>

<section>
    <div class="container" style="display: flex; justify-content: center; align-items: flex-start;">
        <div class="card" style="max-width:380px;margin:2.5em auto; padding:2em 2em 1.3em 2em;">
            <h3 class="main-title" style="margin-bottom:1.1em;font-size:1.7rem;">Créer un compte</h3>
            <?php if ($msg): ?>
                <div class="card" style="background:#eef0fa; color:#5861a7; padding:0.8em 1em;margin-bottom:1em;">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div style="margin-bottom:1.1em;text-align:left;">
                    <label for="nom" style="display:block;font-weight:600;">Nom :</label>
                    <input type="text" name="nom" id="nom" required class="form-control" style="width:100%;margin-top:0.4em;">
                </div>
                <div style="margin-bottom:1.1em;text-align:left;">
                    <label for="email" style="display:block;font-weight:600;">Email :</label>
                    <input type="email" name="email" id="email" required class="form-control" style="width:100%;margin-top:0.4em;">
                </div>
                <div style="margin-bottom:1.3em;text-align:left;">
                    <label for="mot_de_passe" style="display:block;font-weight:600;">Mot de passe :</label>
                    <input type="password" name="mot_de_passe" id="mot_de_passe" required class="form-control" style="width:100%;margin-top:0.4em;">
                </div>
                <button class="btn btn-primary" style="width:100%;">Créer le compte</button>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>