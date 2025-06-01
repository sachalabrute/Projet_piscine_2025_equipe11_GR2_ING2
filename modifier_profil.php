<?php
require_once 'includes/db.php';
include 'includes/header.php';
session_start();

// Vérifier que l'utilisateur est bien connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php?redirect=modifier_profil.php");
    exit;
}

$user = $_SESSION['user'];

// Traitement du formulaire
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mdp = $_POST['mot_de_passe'];
    $mdp_confirm = $_POST['mot_de_passe_confirm'];

    // Validation
    if (!$nom || !$email) {
        $message = "<div class='card' style='background:#ffe6e6;color:#a93b7b;text-align:center;margin-bottom:1em;'>Nom et email sont obligatoires.</div>";
    } else if ($mdp && $mdp !== $mdp_confirm) {
        $message = "<div class='card' style='background:#ffe6e6;color:#a93b7b;text-align:center;margin-bottom:1em;'>Les mots de passe ne correspondent pas.</div>";
    } else {
        // Mise à jour
        if ($mdp) {
            $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, email=?, mot_de_passe=? WHERE id=?");
            $stmt->execute([$nom, $email, $mdp_hash, $user['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, email=? WHERE id=?");
            $stmt->execute([$nom, $email, $user['id']]);
        }
        // Rafraîchir la session
        $_SESSION['user']['nom'] = $nom;
        $_SESSION['user']['email'] = $email;
        $message = "<div class='card' style='background:#e6ffed;color:#287271;text-align:center;margin-bottom:1em;'>Profil mis à jour avec succès.</div>";
    }
}
?>

<section>
    <div class="container my-5">
        <h1 class="main-title"><i class="bi bi-pencil"></i> Modifier mon profil</h1>
        <?= $message ?>
        <div style="max-width: 500px; margin: 0 auto;">
            <form method="post" class="card" style="padding:2em 2em;">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nom</label>
                    <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($user['nom']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <input type="password" name="mot_de_passe" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="mot_de_passe_confirm" class="form-control">
                </div>
                <div class="d-flex gap-2" style="display:flex;gap:1em;">
                    <button type="submit" class="btn btn-primary w-50"><i class="bi bi-save"></i> Enregistrer</button>
                    <a href="compte.php" class="btn btn-outline-secondary w-50"><i class="bi bi-arrow-left"></i> Retour</a>
                </div>
            </form>
        </div>
    </div>
</section>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<?php include 'includes/footer.php'; ?>
