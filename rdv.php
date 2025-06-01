<?php
include 'includes/header.php';
include 'includes/connexion.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    echo "<p>Veuillez vous connecter pour voir vos rendez-vous.</p>";
    include 'includes/footer.php';
    exit;
}

$user_id = $_SESSION['user']['id'];

try {
    $sql = "SELECT rdv.*, u.nom AS agent_nom
            FROM rdv
            JOIN agents a ON rdv.agent_id = a.id
            JOIN utilisateurs u ON a.utilisateur_id = u.id
            WHERE rdv.client_id = :id AND rdv.statut = 'confirmé'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $user_id]);
    $rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de requête : " . $e->getMessage());
}
?>

<h2 class="mb-4">Vos rendez-vous confirmés</h2>

<?php if (empty($rendezvous)): ?>
    <div class="alert alert-info">Aucun rendez-vous confirmé.</div>
<?php else: ?>
    <?php foreach ($rendezvous as $rdv): ?>
        <div class="card mb-3 p-3">
            <h4 class="text-primary">Rendez-vous avec <?= htmlspecialchars($rdv['agent_nom']) ?></h4>
            <p><strong>Date :</strong> <?= htmlspecialchars($rdv['date']) ?> à <?= htmlspecialchars($rdv['heure']) ?></p>
            <p><strong>Adresse :</strong> <?= htmlspecialchars($rdv['adresse'] ?? 'Non renseignée') ?></p>
            <p><strong>Digicode :</strong> <?= htmlspecialchars($rdv['digicode'] ?? 'Non renseigné') ?></p>

            <form method="post" action="annuler_rdv.php" onsubmit="return confirm('Confirmer l\'annulation ?');">
                <input type="hidden" name="rdv_id" value="<?= $rdv['id'] ?>">
                <button type="submit" class="btn btn-danger">Annuler le RDV</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
