<?php
include 'includes/header.php';
require_once 'includes/connexion.php';

// Filtrage par catégorie
$categories = ["résidentiel", "commercial", "terrain", "location", "enchère"];
$categorie = $_GET['categorie'] ?? 'tous';
$where = ($categorie && in_array($categorie, $categories)) ? "WHERE b.categorie = :cat" : "";
$query = "
    SELECT b.*, a.id AS agent_id, u.nom AS agent_nom
    FROM biens b
    JOIN agents a ON b.agent_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    $where
    ORDER BY b.categorie, b.id DESC
";
$stmt = $pdo->prepare($query);
if ($where) $stmt->execute(['cat'=>$categorie]);
else $stmt->execute();
$biens = $stmt->fetchAll();
?>

    <div class="container mb-5">
        <h1 class="fw-bold mb-4 text-center">Tous les biens disponibles</h1>
        <div class="mb-4 text-center">
            <form class="d-inline-flex align-items-center gap-2" method="get">
                <label class="fw-semibold me-2">Filtrer par catégorie :</label>
                <select name="categorie" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="tous" <?= $categorie=="tous" ? "selected" : "" ?>>Tous</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= $categorie==$cat ? "selected" : "" ?>>
                            <?= ucfirst($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="row g-4">
            <?php if (count($biens) == 0): ?>
                <div class="alert alert-info">Aucun bien trouvé pour cette catégorie.</div>
            <?php endif; ?>
            <?php foreach ($biens as $bien): ?>
                <?php
                // Essaye d'utiliser une image Unsplash, sinon fallback Picsum
                $unsplash = "https://source.unsplash.com/600x400/?".urlencode($bien['categorie']).",house";
                $fallback = "https://picsum.photos/seed/".$bien['id']."/600/400";
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 animate__animated animate__fadeInUp">
                        <!-- IMAGE (Unsplash avec fallback Picsum si erreur de chargement) -->
                        <img src="<?= $unsplash ?>"
                             class="card-img-top rounded-top"
                             alt="Bien immobilier"
                             onerror="this.onerror=null;this.src='<?= $fallback ?>';">
                        <div class="card-body d-flex flex-column">
                            <h5 class="fw-bold"><?= htmlspecialchars($bien['titre']) ?></h5>
                            <span class="badge bg-primary mb-2"><?= ucfirst($bien['categorie']) ?></span>
                            <div class="mb-2 text-muted"><?= htmlspecialchars(substr($bien['description'],0,70)) ?>...</div>
                            <div class="fw-semibold mb-2" style="font-size:1.2em;">
                                <?= number_format($bien['prix'], 0, ',', ' ') ?> €<?= ($bien['categorie']=="location" ? "/mois" : "") ?>
                            </div>
                            <div class="mb-2 small">
                                <span class="fw-semibold text-secondary">Agent :</span>
                                <a href="agent.php?id=<?= $bien['agent_id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($bien['agent_nom']) ?>
                                </a>
                            </div>
                            <div class="d-flex gap-2 mt-auto">
                                <a href="bien.php?id=<?= $bien['id'] ?>" class="btn btn-outline-primary w-50">
                                    <i class="bi bi-house-door"></i> Voir
                                </a>
                                <a href="rdv.php?agent=<?= $bien['agent_id'] ?>&bien=<?= $bien['id'] ?>" class="btn btn-primary w-50">
                                    <i class="bi bi-calendar-plus"></i> Prendre RDV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Animations + icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>

<?php include 'includes/footer.php'; ?>