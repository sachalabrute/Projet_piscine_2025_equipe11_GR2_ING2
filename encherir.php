<?php
include 'includes/header.php';
require_once 'includes/connexion.php';

if (!isset($_GET['bien']) || !is_numeric($_GET['bien'])) {
    echo "<div class='alert alert-danger mt-5'>Bien non trouvé.</div>";
    include 'includes/footer.php'; exit;
}
$bien_id = intval($_GET['bien']);

// 1. Récupérer le bien
$stmt = $pdo->prepare("
    SELECT b.*, a.id AS agent_id, u.nom AS agent_nom, u.email AS agent_email
    FROM biens b
    JOIN agents a ON b.agent_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE b.id = ? AND b.categorie = 'enchère'
");
$stmt->execute([$bien_id]);
$bien = $stmt->fetch();

if (!$bien) {
    echo "<div class='alert alert-danger mt-5'>Bien non trouvé ou n’est pas en enchère.</div>";
    include 'includes/footer.php'; exit;
}

// Simuler une date de fin si tu n’as pas la colonne dans la BDD :
$date_fin = date('Y-m-d H:i', strtotime('+2 days')); // À remplacer par $bien['date_fin_enchere'] si tu l’as

// 2. Récupérer la meilleure enchère
$stmt = $pdo->prepare("SELECT MAX(montant) FROM encheres WHERE bien_id = ?");
$stmt->execute([$bien_id]);
$prix_actuel = $stmt->fetchColumn();
if (!$prix_actuel) $prix_actuel = $bien['prix']; // Prix de départ si aucune enchère

// 3. Traitement du formulaire
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offre = floatval($_POST['montant'] ?? 0);
    $pseudo = trim($_POST['pseudo'] ?? '');

    if ($offre <= $prix_actuel) {
        $message = "<div class='alert alert-danger'>Votre offre doit être supérieure au prix actuel !</div>";
    } elseif (!$pseudo) {
        $message = "<div class='alert alert-danger'>Merci de saisir un pseudo ou un identifiant.</div>";
    } else {
        // Simuler utilisateur_id = 0, ou intégrer le vrai utilisateur si connecté
        $stmt = $pdo->prepare("INSERT INTO encheres (bien_id, utilisateur_id, montant, date_enchere) VALUES (?, 0, ?, NOW())");
        $stmt->execute([$bien_id, $offre]);
        $message = "<div class='alert alert-success'>Votre offre de ".number_format($offre,0,',',' ')." € a été prise en compte !</div>";
        // Mise à jour du prix actuel
        $prix_actuel = $offre;
    }
}

// 4. Récupérer les 5 meilleures enchères
$stmt = $pdo->prepare("SELECT montant, date_enchere FROM encheres WHERE bien_id = ? ORDER BY montant DESC LIMIT 5");
$stmt->execute([$bien_id]);
$meilleures = $stmt->fetchAll();
?>

    <div class="container my-5">
        <h1 class="fw-bold mb-3 text-center"><?= htmlspecialchars($bien['titre']) ?></h1>
        <div class="row g-4 align-items-center mb-4">
            <div class="col-md-6 text-center">
                <img src="https://source.unsplash.com/700x500/?enchère,house" class="img-fluid rounded shadow-sm" alt="Bien immobilier">
            </div>
            <div class="col-md-6">
                <div class="mb-3"><span class="badge bg-warning text-dark fs-5"><i class="bi bi-gavel"></i> Vente aux enchères</span></div>
                <div class="mb-2 text-muted"><?= htmlspecialchars($bien['description']) ?></div>
                <div class="mb-3 fs-4"><b>Prix de départ : </b><?= number_format($bien['prix'],0,',',' ') ?> €</div>
                <div class="mb-2 fs-4"><b>Prix actuel : </b><?= number_format($prix_actuel,0,',',' ') ?> €</div>
                <div class="mb-3"><b>Fin de l’enchère : </b><?= htmlspecialchars($date_fin) ?></div>
            </div>
        </div>

        <?= $message ?>

        <div class="card shadow-sm p-4 mb-4">
            <h4 class="mb-3"><i class="bi bi-coin"></i> Placer une nouvelle offre</h4>
            <form method="post" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="pseudo" class="form-label">Votre pseudo</label>
                    <input type="text" name="pseudo" id="pseudo" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="montant" class="form-label">Montant proposé (€)</label>
                    <input type="number" min="<?= $prix_actuel + 1 ?>" step="1" name="montant" id="montant" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-warning btn-lg w-100"><i class="bi bi-gavel"></i> Enchérir</button>
                </div>
            </form>
        </div>

        <div class="card shadow-sm p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-star"></i> Meilleures offres</h5>
            <ol class="mb-0">
                <?php if (count($meilleures) == 0): ?>
                    <li class="text-muted">Aucune offre pour le moment.</li>
                <?php else: ?>
                    <?php foreach ($meilleures as $m): ?>
                        <li><?= number_format($m['montant'],0,',',' ') ?> € — le <?= date('d/m/Y H:i', strtotime($m['date_enchere'])) ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ol>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<?php include 'includes/footer.php'; ?>
