<?php
session_start();
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php?redirect=mes_rdv.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Annulation d’un RDV
if (isset($_GET['annuler']) && is_numeric($_GET['annuler'])) {
    $rdv_id = intval($_GET['annuler']);
    // Vérifier que le RDV appartient à l’utilisateur et est valide
    $stmt = $pdo->prepare("SELECT * FROM rdv WHERE id=? AND client_id=? AND statut='validé'");
    $stmt->execute([$rdv_id, $user_id]);
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE rdv SET statut='annulé' WHERE id=?")->execute([$rdv_id]);
        echo "<div class='card' style='max-width:480px;margin:2em auto 0;background:#e6ffed;color:#287271;text-align:center;padding:1em;'>Le rendez-vous a été annulé et le créneau est à nouveau disponible !</div>";
    }
}

// Liste des RDV à venir
$stmt = $pdo->prepare("
    SELECT r.*, 
           a.id AS agent_id, u.nom AS agent_nom, u.email AS agent_email, a.telephone AS agent_tel,
           b.titre AS bien_titre
    FROM rdv r
    JOIN agents a ON r.agent_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    LEFT JOIN biens b ON r.bien_id = b.id
    WHERE r.client_id = :uid
    ORDER BY r.date, r.heure
");
$stmt->execute(['uid' => $user_id]);
$rdvs = $stmt->fetchAll();
?>

<section>
    <div class="container my-5">
        <h1 class="main-title"><i class="bi bi-calendar3"></i> Mes rendez-vous</h1>
        <?php if (count($rdvs) == 0): ?>
            <div class="card" style="background:#eef0fa;color:#5861a7;text-align:center;padding:1em 1.5em;">
                Aucun rendez-vous trouvé.
            </div>
        <?php else: ?>
            <table class="table" style="background:#fff;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Bien</th>
                        <th>Agent</th>
                        <th>Statut</th>
                        <th>Motif</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rdvs as $rdv): ?>
                    <tr>
                        <td><?= htmlspecialchars($rdv['date']) ?></td>
                        <td><?= htmlspecialchars($rdv['heure']) ?></td>
                        <td><?= htmlspecialchars($rdv['bien_titre'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($rdv['agent_nom'] ?? '—') ?></td>
                        <td>
                            <span class="badge" style="
                                background: <?= 
                                    $rdv['statut']=='validé' ? '#72c47b' :
                                    ($rdv['statut']=='refusé'?'#e07a5f':
                                    ($rdv['statut']=='annulé'?'#b2b7e2':'#ffe6e6')) ?>;
                                color:#22223b;">
                                <?= htmlspecialchars($rdv['statut']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($rdv['motif'] ?? '') ?></td>
                        <td>
                            <?php if ($rdv['statut']=='validé' && strtotime($rdv['date']) >= strtotime(date('Y-m-d'))): ?>
                                <a href="mes_rdv.php?annuler=<?= $rdv['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Annuler ce rendez-vous ?')">
                                    Annuler le RDV
                                </a>
                            <?php elseif ($rdv['statut']=='annulé'): ?>
                                <span class="text-muted small">Annulé</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<?php include 'includes/footer.php'; ?>
