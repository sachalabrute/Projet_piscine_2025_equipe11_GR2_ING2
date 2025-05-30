<?php
require_once 'includes/db.php';
include 'includes/header.php';

$motcle = trim($_GET['q'] ?? "");
$resultats = [
    'biens' => [],
    'agents' => [],
];

if ($motcle !== "") {
    // Recherche de biens (par ID, titre, description)
    $stmt = $pdo->prepare("
        SELECT b.*, u.nom AS agent_nom, a.id AS agent_id
        FROM biens b
        JOIN agents a ON b.agent_id = a.id
        JOIN utilisateurs u ON a.utilisateur_id = u.id
        WHERE b.id = :num OR b.titre LIKE :mc OR b.description LIKE :mc
    ");
    $stmt->execute([
        'num' => intval($motcle),
        'mc'  => '%'.$motcle.'%'
    ]);
    $resultats['biens'] = $stmt->fetchAll();

    // Recherche d'agents (par nom)
    $stmt2 = $pdo->prepare("
        SELECT a.*, u.nom, u.email
        FROM agents a
        JOIN utilisateurs u ON a.utilisateur_id = u.id
        WHERE u.nom LIKE :mc
    ");
    $stmt2->execute(['mc'=>'%'.$motcle.'%']);
    $resultats['agents'] = $stmt2->fetchAll();
}
?>

<section>
    <div class="container my-5">
        <h1 class="main-title"><i class="bi bi-search"></i> Résultats de recherche</h1>
        <div class="d-flex justify-content-center mb-5">
    <form method="get" class="w-100" style="max-width:700px;">
        <div class="input-group input-group-lg shadow rounded-4">
            <span class="input-group-text bg-white border-0 rounded-start-4">
                <i class="bi bi-search fs-3"></i>
            </span>
            <input type="text" name="q"
                class="form-control text-center fs-4 border-0"
                style="font-weight: bold; background: #f5f7fa;"
                placeholder="Nom d’agent, numéro de bien ou ville"
                value="<?= htmlspecialchars($motcle) ?>" required>
            <button class="btn btn-warning fw-bold rounded-end-4 px-5"
                style="font-size:1.4rem;" type="submit">
                Rechercher
            </button>
        </div>
    </form>
</div>

        <?php if ($motcle === ""): ?>
            <div class="card" style="background:#eef0fa;color:#5861a7;text-align:center;padding:1em 1.5em;">
                Entrez un mot-clé, un numéro de bien ou un nom d’agent ci-dessus.
            </div>
        <?php else: ?>
            <?php if (empty($resultats['biens']) && empty($resultats['agents'])): ?>
                <div class="card" style="background:#ffe6e6;color:#a93b7b;text-align:center;padding:1em 1.5em;">
                    Aucun résultat trouvé pour “<?= htmlspecialchars($motcle) ?>”.
                </div>
            <?php endif; ?>

            <?php if (!empty($resultats['biens'])): ?>
                <h3 class="main-title" style="font-size:1.22rem;margin-bottom:1em;margin-top:2em;">Biens immobiliers</h3>
                <div class="container" style="display:flex;flex-wrap:wrap;gap:2em;justify-content:center;">
                    <?php foreach ($resultats['biens'] as $bien): ?>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($bien['titre']) ?></h5>
                                <span class="badge"><?= htmlspecialchars($bien['categorie']) ?></span>
                                <div class="description"><?= htmlspecialchars(substr($bien['description'],0,80)) ?>...</div>
                                <div class="agent" style="margin-bottom:0.7em;">
                                    <i class="bi bi-person-badge"></i> Agent :
                                    <a href="agent.php?id=<?= $bien['agent_id'] ?>"><?= htmlspecialchars($bien['agent_nom']) ?></a>
                                </div>
                                <div class="price"><?= number_format($bien['prix'], 0, ',', ' ') ?> €</div>
                                <a href="bien.php?id=<?= $bien['id'] ?>" class="btn btn-outline-primary btn-sm" style="margin-top:0.5em;">Voir le bien</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($resultats['agents'])): ?>
                <h3 class="main-title" style="font-size:1.22rem;margin-bottom:1em;margin-top:2em;">Agents immobiliers</h3>
                <div class="container" style="display:flex;flex-wrap:wrap;gap:2em;justify-content:center;">
                    <?php foreach ($resultats['agents'] as $agent): ?>
                        <div class="card">
                            <div class="card-body text-center">
                                <img src="https://api.dicebear.com/7.x/initials/svg?seed=<?= urlencode($agent['nom']) ?>&radius=50" width="80" class="card-img-top" alt="">
                                <h5 class="card-title"><?= htmlspecialchars($agent['nom']) ?></h5>
                                <span class="badge"><?= htmlspecialchars($agent['specialite'] ?? '') ?></span>
                                <a href="agent.php?id=<?= $agent['id'] ?>" class="btn btn-outline-primary btn-sm" style="margin-top:0.5em;">Voir le profil</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<?php include 'includes/footer.php'; ?>
