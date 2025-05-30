<?php 
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='card' style='margin:2em auto;max-width:440px;padding:2em 1em;text-align:center;background:#f8d3e2;color:#a93b7b;'>Bien non trouvé.</div>";
    include 'includes/footer.php'; 
    exit;
}
$bien_id = intval($_GET['id']);

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
    echo "<div class='card' style='margin:2em auto;max-width:440px;padding:2em 1em;text-align:center;background:#f8d3e2;color:#a93b7b;'>Bien non trouvé.</div>";
    include 'includes/footer.php'; 
    exit;
}

// Utilise l'image de la BDD, ou une image par défaut si vide
$image = !empty($bien['image_url']) ? htmlspecialchars($bien['image_url']) : "assets/img/biens/default.jpg";
?>

<section class="fiche-bien">
  <div class="fiche-bien-contenu">
    <div class="fiche-bien-img">
      <img src="<?= $image ?>" alt="<?= htmlspecialchars($bien['titre']) ?>">
    </div>
    <div class="fiche-bien-info">
      <h1 class="fiche-bien-titre"><?= htmlspecialchars($bien['titre']) ?></h1>
      <div class="fiche-bien-categorie"><?= ucfirst($bien['categorie']) ?></div>
      <div class="fiche-bien-prix">
        <?= number_format($bien['prix'], 0, ',', ' ') ?> €<?= ($bien['categorie']=='location' ? '/mois' : '') ?>
      </div>
      <div class="fiche-bien-desc"><?= nl2br(htmlspecialchars($bien['description'])) ?></div>
      <div class="fiche-bien-agent-inline">
        <div class="fiche-bien-agent-card">
          <div style="display:flex;align-items:center;gap:0.8em;margin-bottom:0.4em;">
            <img src="https://api.dicebear.com/7.x/initials/svg?seed=<?= urlencode($bien['agent_nom']) ?>&radius=50"
                 width="44"
                 style="border-radius:50%;border:2px solid #7382c4;"
                 alt="Agent">
            <div style="text-align:left;">
              <strong><?= htmlspecialchars($bien['agent_nom']) ?></strong><br>
              <span style="color:#5861a7;font-size:0.99em"><?= ucfirst($bien['specialite']) ?></span><br>
              <span style="font-size:0.97em;">📞 <?= htmlspecialchars($bien['agent_tel']) ?></span><br>
              <span style="font-size:0.97em;">
                ✉️ <a href="mailto:<?= htmlspecialchars($bien['agent_email']) ?>" style="color:#7382c4"><?= htmlspecialchars($bien['agent_email']) ?></a>
              </span>
            </div>
          </div>
          <div class="card-actions" style="display:flex;gap:0.7em;">
            <a href="agent.php?id=<?= $bien['agent_id'] ?>" class="btn btn-outline-primary" style="padding:0.3em 0.7em;font-size:0.97em;">Voir l’agent</a>
            <a href="rdv.php?agent=<?= $bien['agent_id'] ?>&bien=<?= $bien['id'] ?>" class="btn btn-primary" style="padding:0.3em 0.9em;font-size:0.97em;">Prendre RDV</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
