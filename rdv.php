<?php
session_start();
include 'includes/header.php';
require_once 'includes/db.php';

// --- Protection connexion obligatoire
if (!isset($_SESSION['user'])) {
    header("Location: login.php?redirect=rdv.php");
    exit;
}

// Fonction pour calculer la date réelle du prochain jour de la semaine choisi
function getNextDate($jour) {
    $jours = ["Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi"];
    $today = date('N'); // 1=lundi ... 7=dimanche
    $idx = array_search($jour, $jours);
    if ($idx === false) return date('Y-m-d'); // fallback
    $delta = $idx + 1 - $today;
    if ($delta < 0) $delta += 7; // prochaine semaine si jour déjà passé
    return date('Y-m-d', strtotime("+$delta days"));
}

$message = "";

// --- Annulation d'un RDV (si demandé en POST)
if (isset($_POST['annuler_rdv_id'])) {
    $rdv_id = intval($_POST['annuler_rdv_id']);
    // Vérifie que le RDV appartient bien à l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM rdv WHERE id=? AND client_id=?");
    $stmt->execute([$rdv_id, $_SESSION['user']['id']]);
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE rdv SET statut='annulé' WHERE id=?")->execute([$rdv_id]);
        $message = "<div class='alert alert-success'>Le rendez-vous a été annulé.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Impossible d'annuler ce rendez-vous.</div>";
    }
}

// --- Prise de RDV (déjà fonctionnel, inchangé)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jour'], $_POST['moment'], $_POST['agent_id']) && !isset($_POST['annuler_rdv_id'])) {
    $client_id = $_SESSION['user']['id'];
    $agent_id = intval($_POST['agent_id']);
    $jour     = $_POST['jour'];
    $moment   = $_POST['moment'];
    $nom      = $_POST['nom'];
    $email    = $_POST['email'];
    $telephone= $_POST['telephone'];
    $message_rdv = trim($_POST['message']);
    $bien_id  = isset($_POST['bien_id']) ? intval($_POST['bien_id']) : null;

    $date_rdv = getNextDate($jour);
    $heure_rdv = $moment; // "AM" ou "PM"

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rdv WHERE agent_id=? AND date_rdv=? AND heure_rdv=? AND statut='validé'");
    $stmt->execute([$agent_id, $date_rdv, $heure_rdv]);
    $existe = $stmt->fetchColumn();

    if ($existe > 0) {
        $message = "<div class='alert alert-danger'>Ce créneau a déjà été réservé, veuillez en choisir un autre.</div>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO rdv (client_id, agent_id, bien_id, nom, email, telephone, date_rdv, heure_rdv, message, statut, date_demande)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'validé', NOW())");
        $stmt->execute([$client_id, $agent_id, $bien_id, $nom, $email, $telephone, $date_rdv, $heure_rdv, $message_rdv]);
        $message = "<div class='alert alert-success'>Votre rendez-vous a bien été pris pour <b>$jour ($moment)</b>.<br>Un email de confirmation vous a été envoyé.</div>";
    }
}

// --- Liste des RDV de l'utilisateur
$stmt = $pdo->prepare("
    SELECT r.*, u.nom AS agent_nom
    FROM rdv r
    JOIN agents a ON r.agent_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE r.client_id=? 
    ORDER BY r.date_demande DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$mes_rdv = $stmt->fetchAll();
?>

<div class="container my-5">
    <h1 class="fw-bold mb-4 text-center"><i class="bi bi-calendar-plus"></i> Prendre rendez-vous avec un agent</h1>
    <?= $message ?>

    <!-- Formulaire de RDV (reprends le tien) -->
    <!-- ... ton code de formulaire RDV ici (comme plus haut) ... -->

    <hr class="my-5">

    <h2 class="mb-4"><i class="bi bi-calendar-check"></i> Mes rendez-vous</h2>
    <?php if (count($mes_rdv) == 0): ?>
        <div class="alert alert-info">Aucun rendez-vous pris pour le moment.</div>
    <?php else: ?>
        <table class="table table-hover shadow">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Agent</th>
                    <th>Statut</th>
                    <th>Message</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($mes_rdv as $rdv): ?>
                <tr>
                    <td><?= htmlspecialchars($rdv['date_rdv']) ?></td>
                    <td><?= htmlspecialchars($rdv['heure_rdv']) ?></td>
                    <td><?= htmlspecialchars($rdv['agent_nom']) ?></td>
                    <td>
                        <span class="badge bg-<?= $rdv['statut'] == 'validé' ? 'success' : ($rdv['statut']=='annulé'?'danger':'warning') ?>">
                            <?= htmlspecialchars($rdv['statut']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($rdv['message']) ?></td>
                    <td>
                        <?php if ($rdv['statut']=='validé'): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="annuler_rdv_id" value="<?= $rdv['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Confirmer l\'annulation de ce RDV ?');">
                                <i class="bi bi-x-circle"></i> Annuler
                            </button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<?php include 'includes/footer.php'; ?>