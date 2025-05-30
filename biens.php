<?php
require_once 'includes/db.php';
include 'includes/header.php';

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

<section>
    <h1 class="main-title">Tous les biens disponibles</h1>
    <div class="mb-4" style="text-align:center;">
        <form style="display:inline-flex; align-items:center; gap:0.4em;" method="get">
            <label style="font-weight:600;">Filtrer par catégorie :</label>
            <select name="categorie" class="form-select" onchange="this.form.submit()">
                <option value="tous" <?= $categorie=="tous" ? "selected" : "" ?>>Tous</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat ?>" <?= $categorie==$cat ? "selected" : "" ?>>
                        <?= ucfirst($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="container" style="display:flex;flex-wrap:wrap;gap:2.2em;justify-content:center;">
        <?php if (count($biens) == 0): ?>
            <div class="card" style="margin: 2em auto; padding: 1.4em 1em; background: #eef0fa;">
                Aucun bien trouvé pour cette catégorie.
            </div>
        <?php endif; ?>
        <?php foreach ($biens as $bien): ?>
            <?php
                // Affiche l'image du bien si elle existe, sinon l'image par défaut
                $image = !empty($bien['image_url']) && file_exists($bien['image_url'])
                    ? $bien['image_url']
                    : "assets/img/biens/default.jpg";
                $is_new = isset($bien['date_ajout']) && (strtotime($bien['date_ajout']) >= strtotime('-7 days'));
                $is_enchere = ($bien['categorie'] == "enchère");
            ?>
        <div class="card">
            <?php if ($is_enchere): ?>
                <span class="badge" style="position:absolute;top:15px;left:15px;font-size:1em;z-index:10;background:#e07a5f;">Enchère</span>
            <?php elseif ($is_new): ?>
                <span class="badge" style="position:absolute;top:15px;left:15px;font-size:1em;z-index:10;background:#72c47b;">Nouveau</span>
            <?php endif; ?>

            <img src="<?= htmlspecialchars($image) ?>" class="card-img-top" alt="Bien immobilier">

            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($bien['titre']) ?></h5>
                <span class="badge"><?= ucfirst($bien['categorie']) ?></span>
                <div class="description">
                    <?= htmlspecialchars(substr($bien['description'],0,70)) ?>...
                </div>
                <div class="price">
                    <?= number_format($bien['prix'], 0, ',', ' ') ?> €<?= ($bien['categorie']=="location" ? "/mois" : "") ?>
                </div>
                <div class="agent">
                    <span>Agent :</span>
                    <a href="agent.php?id=<?= $bien['agent_id'] ?>"><?= htmlspecialchars($bien['agent_nom']) ?></a>
                </div>
                <div class="card-actions">
                    <a href="bien.php?id=<?= $bien['id'] ?>" class="btn btn-outline-primary">Voir</a>
                    <a href="rdv.php?agent=<?= $bien['agent_id'] ?>&bien=<?= $bien['id'] ?>" class="btn btn-primary">Prendre RDV</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
