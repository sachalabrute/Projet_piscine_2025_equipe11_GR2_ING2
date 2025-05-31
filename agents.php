<?php
include 'includes/header.php';
require_once 'includes/connexion.php';

// Récupérer tous les agents + leur nom depuis la table utilisateurs
$stmt = $pdo->query("
    SELECT a.id, u.nom, u.email, a.specialite, a.cv
    FROM agents a
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    ORDER BY a.specialite, u.nom
");
$agents = $stmt->fetchAll();
?>

    <div class="container mb-5">
        <h1 class="text-center fw-bold mb-5" style="font-family:Poppins,sans-serif;">Nos agents immobiliers agréés</h1>
        <div class="row g-4">
            <?php foreach ($agents as $agent): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-lg h-100 animate__animated animate__fadeInUp" style="transition: box-shadow 0.3s;">
                        <div class="card-body text-center pb-2">
                            <img src="https://api.dicebear.com/7.x/initials/svg?seed=<?= urlencode($agent['nom']) ?>&radius=50" width="80" height="80" class="rounded-circle shadow mb-3 border border-2 border-primary" alt="Agent">
                            <h4 class="fw-bold mb-1"><?= htmlspecialchars($agent['nom']) ?></h4>
                            <span class="badge rounded-pill bg-primary mb-2" style="font-size:1rem;">
                        <?= ucfirst(htmlspecialchars($agent['specialite'])) ?>
                    </span>
                            <p class="small text-muted"><?= htmlspecialchars(substr($agent['cv'], 0, 70)) ?>...</p>
                            <div class="d-flex justify-content-center gap-2 mt-3">
                                <a href="agent.php?id=<?= $agent['id'] ?>" class="btn btn-primary px-3">
                                    <i class="bi bi-person-lines-fill"></i> Profil
                                </a>
                                <a href="mailto:<?= htmlspecialchars($agent['email']) ?>" class="btn btn-outline-secondary px-3" title="Envoyer un mail à l'agent">
                                    <i class="bi bi-envelope"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Optionnel : Animations et icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>

<?php include 'includes/footer.php'; ?>