<?php
include 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];
?>

<div class="container">
    <h1 class="text-center my-4">Votre Compte</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Informations du Compte</h5>
                    <p class="card-text">Nom: <?php echo htmlspecialchars($user['nom']); ?></p>
                    <p class="card-text">Email: <?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="card-text">Adresse: <?php echo htmlspecialchars($user['adresse']); ?></p>
                    <a href="modifier_compte.php" class="btn btn-primary">Modifier les Informations</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
