<?php
require_once 'includes/db.php';
include 'includes/header.php';

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php?redirect=compte.php");
    exit;
};;

$user = $_SESSION['user'];
$user_id = $user['id'];
$role = $user['role'];

// Récupère les RDV de ce client (ou agent, adapté si tu veux)
$stmt = $pdo->prepare("
    SELECT r.*, 
           a.id AS agent_id, ua.nom AS agent_nom, a.specialite, a.telephone AS agent_tel, ua.email AS agent_email,
           b.titre AS bien_titre
    FROM rdv r
    JOIN agents a ON r.agent_id = a.id
    JOIN utilisateurs ua ON a.utilisateur_id = ua.id
    LEFT JOIN biens b ON r.bien_id = b.id
    WHERE r.client_id = :uid
    ORDER BY r.date_rdv DESC, r.heure_rdv
");
$stmt->execute(['uid' => $user_id]);
$rdvs = $stmt->fetchAll();
?>

<section>
    <div class="container my-5">
        <h1 class="main-title" style="margin-bottom:1.3em;"><i class="bi bi-person-circle"></i> Mon Compte</h1>
        <div class="compte-centerbox">
            <div class="compte-card">
                <h4 class="card-title" style="margin-bottom:0.7em;"><i class="bi bi-person"></i> Infos personnelles</h4>
                <ul style="list-style:none;padding-left:0;font-size:1.07rem;margin-bottom:1em;">
                    <li><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></li>
                    <li><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></li>
                    <li><strong>Rôle :</strong> <?= ucfirst($role) ?></li>
                </ul>
                <div class="compte-btn-row">
                    <a href="modifier_profil.php" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Modifier mon profil
                    </a>
                    <a href="deconnexion.php" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
        <h3 class="main-title" style="margin-bottom:1.2em;font-size:1.5rem;"><i class="bi bi-calendar3"></i> Mes rendez-vous</h3>
        <div style="max-width: 900px; margin: 0 auto;">
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rdvs as $rdv): ?>
                        <tr>
                            <td><?= htmlspecialchars($rdv['date_rdv']) ?></td>
                            <td><?= htmlspecialchars($rdv['heure_rdv']) ?></td>
                            <td><?= htmlspecialchars($rdv['bien_titre'] ?? '—') ?></td>
                            <td>
                                <a href="agent.php?id=<?= $rdv['agent_id'] ?>">
                                    <?= htmlspecialchars($rdv['agent_nom']) ?>
                                </a>
                            </td>
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</section>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<?php include 'includes/footer.php'; ?>
