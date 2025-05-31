<?php
include 'includes/connexion.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $role = 'client';
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$nom, $email, $mot_de_passe, $role])) {
        $msg = "Compte créé, connectez-vous !";
    } else {
        $msg = "Erreur lors de la création.";
    }
}
include 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card p-4">
            <h3 class="mb-3">Créer un compte</h3>
            <?php if ($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Nom :</label>
                    <input type="text" name="nom" required class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email :</label>
                    <input type="email" name="email" required class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe :</label>
                    <input type="password" name="mot_de_passe" required class="form-control">
                </div>
                <button class="btn btn-primary w-100">Créer le compte</button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>