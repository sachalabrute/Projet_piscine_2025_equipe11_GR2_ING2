<?php
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_GET['bien']) || !is_numeric($_GET['bien'])) {
    echo "<div class='card' style='margin:2em auto;max-width:480px;background:#ffe6e6;color:#a93b7b;text-align:center;padding:1.4em;'>Bien non trouvé.</div>";
    include 'includes/footer.php'; exit;
}
$bien_id = intval($_GET['bien']);

// Récupérer le bien
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
    echo "<div class='card' style='margin:2em auto;max-width:480px;background:#ffe6e6;color:#a93b7b;text-align:center;padding:1.4em;'>Bien non trouvé ou n’est pas en enchère.</div>";
    include 'includes/footer.php'; exit;
}

$date_fin = date('Y-m-d H:i', strtotime('+2 days')); // À remplacer par $bien['date_fin_enchere'] si tu l’as

// Meilleure enchère
$stmt = $pdo->prepare("SELECT MAX(montant) FROM encheres WHERE bien_id = ?");
$stmt->execute([$bien_id]);
$prix_actuel = $stmt->fetchColumn();
if (!$prix_actuel) $prix_actuel = $bien['prix']; // Prix de départ si aucune enchère

// Formulaire
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offre = floatval($_POST['montant'] ?? 0);
    $pseudo = trim($_POST['pseudo'] ?? '');

    if ($offre <= $prix_actuel) {
        $message = "<div class='card' style='background:#ffe6e6;color:#a93b7b;text-align:center;margin-bottom:1em;'>Votre offre doit être supérieure au prix actuel !</div>";
    } elseif (!$pseudo) {
        $message = "<div class='card' style='background:#ffe6e6;color:#a93b7b;text-align:center;margin-bottom:1em;'>Merci de saisir un pseudo ou un identifiant.</div>";
    } else {
        // Simuler utilisateur_id = 0, ou intégrer le vrai utilisateur si connecté
        $stmt = $pdo->prepare("INSERT INTO encheres (bien_id, utilisateur_id, montant, date_enchere) VALUES (?, 0, ?, NOW())");
        $stmt->execute([$bien_id, $offre]);
        $message = "<div class='card' style='background:#e6ffed;color:#287271;text-align:center;margin-bottom:1em;'>Votre offre de ".number_format($offre,0,',',' ')." € a été prise en compte !</div>";
        $prix_actuel = $offre;
    }
}

// Top 5 enchères
$stmt = $pdo->prepare("SELECT montant, date_enchere FROM encheres WHERE bien_id = ? ORDER BY montant DESC LIMIT 5");
$stmt->execute([$bien_id]);
$meilleures = $stmt->fetchAll();
?>

<section>
    <div class="container my-5">
        <h1 class="main-title"><?= htmlspecialchars($bien['titre']) ?></h1>
        <div class="container" style="display:flex;flex-wrap:wrap;gap:2em;justify-content:center;">
            <div style="flex:1 1 340px;max-width:400px;text-align:center;">
                <img src="https://source.unsplash.com/700x500/?enchère,house" class="card-img-top" style="border-radius:1.4em;box-shadow:0 4px 24px #7382c41f;" alt="Bien immobilier">
            </div>
            <div style="flex:2 1 400px;max-width:470px;">
                <span class="badge" style="background:#ffe6e6;color:#a93b7b;font-size:1em;"><i class="bi bi-gavel"></i> Vente aux enchères</span>
                <div class="description" style="margin:0.8em 0 0.5em 0;"><?= htmlspecialchars($bien['description']) ?></div>
                <div class="price" style="font-size:1.12em;"><b>Prix de départ : </b><?= number_format($bien['prix'],0,',',' ') ?> €</div>
                <div class="price" style="font-size:1.18em;"><b>Prix actuel : </b><?= number_format($prix_actuel,0,',',' ') ?> €</div>
                <div class="mb-3"><b>Fin de l’enchère : </b><?= htmlspecialchars($date_fin) ?></div>
            </div>
        </div>

        <?= $message ?>

        <div class="card" style="max-width:560px;margin:2em auto 2em auto;padding:2em;">
            <h4 style="margin-bottom:1.1em;"><i class="bi bi-coin"></i> Placer une nouvelle offre</h4>
            <form method="post" class="row g-3 align-items-end" style="display:flex;gap:1.1em;">
                <div style="flex:1;">
                    <label for="pseudo" class="form-label">Votre pseudo</label>
                    <input type="text" name="pseudo" id="pseudo" class="form-control" required>
                </div>
                <div style="flex:1;">
                    <label for="montant" class="form-label">Montant proposé (€)</label>
                    <input type="number" min="<?= $prix_actuel + 1 ?>" step="1" name="montant" id="montant" class="form-control" required>
                </div>
                <div style="flex:1;align-self:flex-end;">
                    <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-gavel"></i> Enchérir</button>
                </div>
            </form>
        </div>

        <div class="card" style="max-width:560px;margin:2em auto;padding:1.6em;">
            <h5 class="main-title" style="font-size:1.07rem;margin-bottom:1em;"><i class="bi bi-star"></i> Meilleures offres</h5>
            <ol class="mb-0" style="padding-left:1.2em;">
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
</section>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<?php include 'includes/footer.php'; ?>
