<?php
include 'includes/header.php';
require_once 'includes/connexion.php';

// Récupérer l'ID du bien
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>Bien non trouvé.</div>";
    include 'includes/footer.php'; exit;
}
$bien_id = intval($_GET['id']);

// Récupère les infos du bien + agent
$stmt = $pdo->prepare("
    SELECT b.*, a.id AS agent_id, u.nom AS agent_nom, u.email AS agent_email, a.telephone AS agent_tel, a.specialite
    FROM biens b
    JOIN agents a ON b.agent_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE b.id = ?
");
$stmt->execute([$bien_id]);
$bien = $stmt->fetch();

if (!$bien) {
    echo "<div class='alert alert-danger'>Bien non trouvé.</div>";
    include 'includes/footer.php'; exit;
}

// Simuler une date de fin d'enchère pour la démo
$date_fin_enchere = date('Y-m-d H:i', strtotime('+3 days')); // À remplacer par une colonne si tu l'ajoutes plus tard

?>

    <div class="container my-5">
        <div class="row g-4 align-items-center">
            <div class="col-md-6 text-center">
                <img src="https://source.unsplash.com/700x500/?<?= urlencode($bien['categorie']) ?>,house" class="img-fluid rounded shadow-sm mb-3" alt="Bien immobilier">
            </div>
            <div class="col-md-6">
                <h1 class="fw-bold mb-2"><?= htmlspecialchars($bien['titre']) ?></h1>
                <span class="badge bg-primary mb-2" style="font-size:1.1em;">
        <?= ucfirst($bien['categorie']) ?>
                    <?php if ($bien['categorie'] == 'enchère'): ?>
                        <span class="badge bg-warning text-dark ms-2" style="font-size:0.9em;">Enchère</span>
                    <?php endif; ?>
      </span>
                <div class="mb-3" style="font-size:2em; font-weight:700;">
                    <?= number_format($bien['prix'], 0, ',', ' ') ?> €
                    <?php if ($bien['categorie'] == 'location') echo "/mois"; ?>
                </div>
                <div class="mb-4 fs-5"><?= nl2br(htmlspecialchars($bien['description'])) ?></div>
                <?php if ($bien['categorie'] == 'enchère'): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-3">
                        <i class="bi bi-gavel fs-3"></i>
                        <div>
                            <strong>Ce bien est en vente aux enchères !</strong><br>
                            Prix de départ : <b><?= number_format($bien['prix'], 0, ',', ' ') ?> €</b><br>
                            Fin de l’enchère : <b><?= htmlspecialchars($date_fin_enchere) ?></b><br>
                            <span class="text-muted">Le bien sera attribué au plus offrant. L’enchère se fait en ligne.</span>
                        </div>
                    </div>
                    <a href="encherir.php?bien=<?= $bien['id'] ?>" class="btn btn-warning btn-lg w-100 mb-3 fw-bold">
                        <i class="bi bi-currency-euro"></i> Participer à l’enchère
                    </a>
                <?php else: ?>
                    <div class="card shadow p-3 mb-2">
                        <div class="mb-2 fw-semibold"><i class="bi bi-person-vcard"></i> Agent responsable : <?= htmlspecialchars($bien['agent_nom']) ?> (<?= htmlspecialchars($bien['specialite']) ?>)</div>
                        <div class="mb-2"><i class="bi bi-telephone"></i> <?= htmlspecialchars($bien['agent_tel']) ?> — <i class="bi bi-envelope"></i> <a href="mailto:<?= htmlspecialchars($bien['agent_email']) ?>"><?= htmlspecialchars($bien['agent_email']) ?></a></div>
                        <div class="d-flex gap-2">
                            <a href="agent.php?id=<?= $bien['agent_id'] ?>" class="btn btn-outline-primary">Voir l’agent</a>
                            <a href="rdv.php?agent=<?= $bien['agent_id'] ?>&bien=<?= $bien['id'] ?>" class="btn btn-primary">Prendre RDV</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<?php include 'includes/footer.php'; ?>