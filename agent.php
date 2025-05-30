<?php
include 'includes/header.php';
require_once 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>Agent non trouvé.</div>";
    include 'includes/footer.php'; exit;
}
$agent_id = intval($_GET['id']);

// Récupère infos agent + utilisateur
$stmt = $pdo->prepare("
    SELECT a.*, u.nom, u.email 
    FROM agents a 
    JOIN utilisateurs u ON a.utilisateur_id = u.id 
    WHERE a.id = ?
");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();

if (!$agent) {
    echo "<div class='alert alert-danger'>Agent non trouvé.</div>";
    include 'includes/footer.php'; exit;
}

// Planning (JSON)
$jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
$planning = json_decode($agent['planning'], true) ?? [];

// RDV déjà pris (on ne prend en compte que les validés)
$stmt_rdv = $pdo->prepare("SELECT date_rdv, heure_rdv FROM rdv WHERE agent_id = ? AND statut='validé'");
$stmt_rdv->execute([$agent['id']]);
$rdv_list = $stmt_rdv->fetchAll(PDO::FETCH_ASSOC);

function getNextDate($jour) {
    $jours = ["Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi"];
    $today = date('N');
    $idx = array_search($jour, $jours);
    if ($idx === false) return date('Y-m-d');
    $delta = $idx + 1 - $today;
    if ($delta < 0) $delta += 7;
    return date('Y-m-d', strtotime("+$delta days"));
}
function isCreneauPris($jour, $moment, $rdv_list) {
    $date_rdv = getNextDate($jour);
    foreach ($rdv_list as $rdv) {
        if ($rdv['date_rdv'] == $date_rdv && $rdv['heure_rdv'] == $moment) return true;
    }
    return false;
}
?>
<div class="container my-5">
    <div class="row justify-content-center align-items-start">
        <!-- Colonne infos agent -->
        <div class="col-lg-4 col-md-5 text-center mb-4 mb-lg-0">
            <img src="https://api.dicebear.com/7.x/initials/svg?seed=<?= urlencode($agent['nom']) ?>&radius=50" width="120" class="rounded-circle border shadow mb-3" alt="Photo Agent">
            <h2 class="mb-2"><?= htmlspecialchars($agent['nom']) ?></h2>
            <span class="badge bg-primary mb-3"><?= ucfirst($agent['specialite']) ?></span><br>
            <span class="text-muted"><?= htmlspecialchars($agent['email']) ?></span><br>
            <span class="text-muted"><?= htmlspecialchars($agent['telephone']) ?></span>
            <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#commModal">
                    <i class="bi bi-chat-dots"></i> Communiquer
                </button>
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#cvModal">
                    <i class="bi bi-file-earmark-person"></i> Voir son CV
                </button>
                <a href="rdv.php?agent=<?= $agent['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-calendar-plus"></i> Prendre RDV
                </a>
            </div>
        </div>
        <!-- Colonne présentation et planning -->
        <div class="col-lg-8 col-md-7">
            <h3 class="mb-2">Présentation</h3>
            <p style="font-size:1.08rem; color:#22223b; margin-bottom:2em;">
                <?= nl2br(htmlspecialchars($agent['cv'])) ?>
            </p>
            <h4 class="mb-3">Planning de la semaine</h4>
            <div class="table-responsive rounded shadow-sm mb-4">
                <table class="table table-bordered align-middle text-center bg-white">
                    <thead class="table-primary">
                        <tr>
                            <th>Jour</th>
                            <th>Matin (AM)</th>
                            <th>Après-midi (PM)</th>
                        </tr>
                    </thead>
                    <!-- ... la suite du planning ... -->
                    <tbody>
                    <?php foreach ($jours as $i=>$jour): ?>
                        <tr>
                            <td class="fw-semibold"><?= $jour ?></td>
                            <?php foreach(['AM','PM'] as $moment):
                                $val = isset($planning[$i][$moment]) ? $planning[$i][$moment] : "Non";
                                $pris = isCreneauPris($jour, $moment, $rdv_list);
                                if ($val == $moment && !$pris): ?>
                                    <td>
                                        <button class="btn btn-success w-100 btn-creneau"
                                            data-jour="<?= $jour ?>"
                                            data-moment="<?= $moment ?>"
                                            data-agent="<?= $agent['id'] ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalRDV">
                                            Dispo
                                        </button>
                                    </td>
                                <?php elseif ($pris): ?>
                                    <td class="bg-primary text-white fw-bold">Réservé</td>
                                <?php else: ?>
                                    <td class="bg-danger text-white fw-bold">—</td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODALE PRISE DE RDV -->
<div class="modal fade" id="modalRDV" tabindex="-1" aria-labelledby="modalRDVLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded shadow">
      <form method="post" action="rdv.php" id="formRdv">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalRDVLabel"><i class="bi bi-calendar-plus"></i> Prendre rendez-vous</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="agent_id" id="modalAgentId">
            <input type="hidden" name="jour" id="modalJour">
            <input type="hidden" name="moment" id="modalMoment">
            <input type="hidden" name="bien_id" value="<?= isset($_GET['bien']) ? intval($_GET['bien']) : '' ?>">
            <p class="mb-3">Créneau choisi : <span id="resumeRdv"></span></p>
            <div class="mb-3">
                <label>Votre nom</label>
                <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($_SESSION['user']['nom'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label>Votre email</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label>Votre téléphone</label>
                <input type="text" name="telephone" class="form-control">
            </div>
            <div class="mb-3">
                <label>Message</label>
                <textarea name="message" class="form-control"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Confirmer le RDV</button>
        </div>
      </form>
    </div>
  </div>
</div>

<<!-- MODALE DE COMMUNICATION -->
<div class="modal fade" id="commModal" tabindex="-1" aria-labelledby="commModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded shadow">
      <div class="modal-header bg-secondary text-white">
        <h5 class="modal-title" id="commModalLabel"><i class="bi bi-chat-dots"></i> Contacter l'agent</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <a href="mailto:<?= htmlspecialchars($agent['email']) ?>" class="btn btn-outline-primary mb-2 w-100">
            <i class="bi bi-envelope"></i> Email : <?= htmlspecialchars($agent['email']) ?>
        </a>
        <a href="tel:<?= htmlspecialchars($agent['telephone']) ?>" class="btn btn-outline-success mb-2 w-100">
            <i class="bi bi-telephone"></i> Téléphone : <?= htmlspecialchars($agent['telephone']) ?>
        </a>
        <a href="https://meet.jit.si/<?= urlencode($agent['nom']) ?>" target="_blank" class="btn btn-outline-info mb-2 w-100">
            <i class="bi bi-camera-video"></i> Visio (Jitsi)
        </a>
        <!-- Nouveau bouton Messagerie Interne -->
        <a href="messagerie.php?destinataire=<?= $agent['utilisateur_id'] ?>" class="btn btn-outline-info w-100">
            <i class="bi bi-chat-left-dots"></i> Message via l’application
        </a>
      </div>
    </div>
  </div>
</div>

<!-- MODALE CV AGENT -->
<div class="modal fade" id="cvModal" tabindex="-1" aria-labelledby="cvModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cvModalLabel">CV de <?= htmlspecialchars($agent['nom']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <?php if (preg_match('/\.pdf$/i', $agent['cv'])): ?>
            <!-- CV PDF intégré -->
            <iframe src="<?= htmlspecialchars($agent['cv']) ?>" width="100%" height="600px" style="border:none;"></iframe>
        <?php else: ?>
            <!-- CV sous forme de texte (description) -->
            <div><?= nl2br(htmlspecialchars($agent['cv'])) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-creneau').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('modalAgentId').value = this.dataset.agent;
        document.getElementById('modalJour').value = this.dataset.jour;
        document.getElementById('modalMoment').value = this.dataset.moment;
        document.getElementById('resumeRdv').textContent = this.dataset.jour + " (" + (this.dataset.moment == "AM" ? "Matin" : "Après-midi") + ")";
        // Ouvre la modale Bootstrap
        var modal = new bootstrap.Modal(document.getElementById('modalRDV'));
        modal.show();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
