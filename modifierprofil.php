<?php
include 'includes/header.php';
require_once 'includes/db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php?redirect=modifier_profil.php");
    exit;
};

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
        $message = "<div class='alert alert-danger'>Nom et email sont obligatoires.</div>";
    } else if ($mdp && $mdp !== $mdp_confirm) {
        $message = "<div class='alert alert-danger'>Les mots de passe ne correspondent pas.</div>";
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
        $message = "<div class='alert alert-success'>Profil mis à jour avec succès.</div>";
    }
}
?>

<div class="container my-5">
    <h1 class="fw-bold mb-4 text-center"><i class="bi bi-pencil"></i> Modifier mon profil</h1>
    <?= $message ?>
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <form method="post" class="card p-4 shadow-sm">
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
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-50"><i class="bi bi-save"></i> Enregistrer</button>
                    <a href="compte.php" class="btn btn-outline-secondary w-50"><i class="bi bi-arrow-left"></i> Retour</a>
                </div>
            </form>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<?php include 'includes/footer.php'; ?>
