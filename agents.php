<?php
require_once 'includes/db.php';
include 'includes/header.php';

// Récupérer tous les agents + leur nom depuis la table utilisateurs
$stmt = $pdo->query("
    SELECT a.id, u.nom, u.email, a.specialite, a.cv 
    FROM agents a
    JOIN utilisateurs u ON a.utilisateur_id = u.id 
    ORDER BY a.specialite, u.nom
");
$agents = $stmt->fetchAll();
?>

<section>
    <h1 class="main-title">Nos agents immobiliers agréés</h1>
    <div class="container" style="display:flex;flex-wrap:wrap;gap:2em;justify-content:center;">
    <?php foreach ($agents as $agent): ?>
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 2em 1.5em;">
                <img src="https://api.dicebear.com/7.x/initials/svg?seed=<?= urlencode($agent['nom']) ?>&radius=50"
                     width="80" height="80"
                     class="card-img-top"
                     style="border-radius: 50%; border: 2.7px solid #7382c4; margin-bottom:1.1em;"
                     alt="Agent">
                <h4 class="card-title" style="margin-bottom:0.3em;"><?= htmlspecialchars($agent['nom']) ?></h4>
                <span class="badge" style="margin-bottom: 0.7em;"><?= ucfirst(htmlspecialchars($agent['specialite'])) ?></span>
                <div class="description" style="margin-bottom:0.9em;"><?= htmlspecialchars(substr($agent['cv'], 0, 70)) ?>...</div>
                <div style="display:flex;justify-content:center;gap:0.8em;margin-top:1.2em;">
                    <a href="agent.php?id=<?= $agent['id'] ?>" class="btn btn-primary">Profil</a>
                    <a href="mailto:<?= htmlspecialchars($agent['email']) ?>" class="btn btn-outline-primary" title="Envoyer un mail à l'agent">✉️</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
