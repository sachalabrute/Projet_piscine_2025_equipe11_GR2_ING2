<?php
include 'includes/header.php';
require_once 'includes/connexion.php';

// Sécurité : Vérifier l’ID de l’agent
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger mt-5'>Agent non trouvé.</div>";
    include 'includes/footer.php'; exit;
}
$agent_id = intval($_GET['id']);

// Récupère l’agent + utilisateur
$stmt = $pdo->prepare("
    SELECT a.*, u.nom, u.email
    FROM agents a
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE a.id = ?
");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();

if (!$agent) {
    echo "<div class='alert alert-danger mt-5'>Agent non trouvé.</div>";
    include 'includes/footer.php'; exit;
}

// Planning (format JSON en BDD)
$jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
$planning = [];
if (!empty($agent['planning'])) {
    $planning = json_decode($agent['planning'], true);
}
?>

    <div class="container my-5">
        <div class="row g-5 align-items-center">
            <!-- Photo et infos principales -->
            <div class="col-md-4 text-center">
                <img src="https://api.dicebear.com/7.x/initials/svg?seed=<?= urlencode($agent['nom']) ?>&radius=50"
                     width="120" height="120" class="rounded-circle border shadow mb-3" alt="Photo Agent">
                <h2 class="fw-bold mb-1"><?= htmlspecialchars($agent['nom']) ?></h2>
                <span class="badge bg-primary mb-2"><?= ucfirst($agent['specialite']) ?></span><br>
                <span class="text-muted"><?= htmlspecialchars($agent['email']) ?></span><br>
                <span class="text-muted"><?= htmlspecialchars($agent['telephone']) ?></span>
                <div class="mt-4 d-flex gap-2 justify-content-center">
                    <a href="rdv.php?agent=<?= $agent['id'] ?>" class="btn btn-primary px-4 shadow">
                        <i class="bi bi-calendar2-plus"></i> Prendre RDV
                    </a>
                    <a href="mailto:<?= htmlspecialchars($agent['email']) ?>" class="btn btn-outline-primary px-4 shadow">
                        <i class="bi bi-envelope"></i> Écrire
                    </a>
                </div>
            </div>

            <!-- CV et planning -->
            <div class="col-md-8">
                <div class="card mb-4 shadow-sm p-4">
                    <h4 class="fw-bold mb-3"><i class="bi bi-person-vcard"></i> Présentation & CV</h4>
                    <p style="white-space:pre-line;"><?= nl2br(htmlspecialchars($agent['cv'])) ?></p>
                </div>

                <div class="card shadow-sm p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-calendar-week"></i> Planning de la semaine</h5>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                        <?php foreach ($jours as $i => $jour): ?>
                            <tr>
                                <td style="width:110px;"><?= $jour ?></td>
                                <td>
                                    <?= isset($planning[$i]) && $planning[$i] ?
                                        htmlspecialchars($planning[$i]) :
                                        "<span class='text-muted'>Non renseigné</span>" ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<?php include 'includes/footer.php'; ?>