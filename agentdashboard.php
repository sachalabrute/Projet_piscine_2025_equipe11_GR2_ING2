<?php
session_start();
require_once 'includes/db.php';
include 'includes/header.php';

// Sécurité : accès agent uniquement
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'agent') {
    header("Location: login.php?redirect=agentdashboard.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Récup info agent (table agents liée à utilisateurs)
$stmt = $pdo->prepare("SELECT * FROM agents WHERE utilisateur_id = ?");
$stmt->execute([$user_id]);
$agent = $stmt->fetch();
if (!$agent) {
    echo "<div class='alert alert-danger'>Aucun agent associé à ce compte.</div>";
    include 'includes/footer.php'; exit;
}
$agent_id = $agent['id'];

// --- Modification des infos agent ---
$success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_infos'])) {
    $tel = trim($_POST['telephone']);
    $specialite = trim($_POST['specialite']);
    $cv = trim($_POST['cv']);

    $stmt = $pdo->prepare("UPDATE agents SET telephone=?, specialite=?, cv=? WHERE id=?");
    $stmt->execute([$tel, $specialite, $cv, $agent_id]);
    $success = "<div class='alert alert-success text-center'>Informations mises à jour !</div>";
    header("Location: agentdashboard.php?ok=1"); exit;
}
if (isset($_GET['ok'])) $success = "<div class='alert alert-success text-center'>Mise à jour réussie !</div>";

// --- Modification du planning ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_planning'])) {
    $planning = [];
    foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $idx => $jour) {
        $planning[$idx] = [
            'AM' => isset($_POST["dispo_{$jour}_AM"]) ? "AM" : "",
            'PM' => isset($_POST["dispo_{$jour}_PM"]) ? "PM" : ""
        ];
    }
    $stmt = $pdo->prepare("UPDATE agents SET planning=? WHERE id=?");
    $stmt->execute([json_encode($planning), $agent_id]);
    header("Location: agentdashboard.php?ok=1"); exit;
}

// --- Gestion des biens (affichage) ---
$stmt = $pdo->prepare("SELECT * FROM biens WHERE agent_id=?");
$stmt->execute([$agent_id]);
$biens = $stmt->fetchAll();

// Planning actuel
$jours = ["Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi"];
$planning = json_decode($agent['planning'] ?? "[]", true) ?? [];
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10 col-md-12">

            <h1 class="main-title text-center mb-4"><i class="bi bi-person-circle"></i> Mon Espace Agent</h1>
            <?= $success ?>

            <!-- Navigation rapide agent SANS Accueil -->
            <div class="d-flex flex-wrap gap-3 justify-content-center mb-5">
                <a href="messagerie.php" class="btn btn-info px-4">
                    <i class="bi bi-chat-dots"></i> Messagerie
                </a>
                <a href="agentdashboard.php#profil" class="btn btn-secondary px-4">
                    <i class="bi bi-person"></i> Mon Profil
                </a>
                <a href="agentdashboard.php#planning" class="btn btn-warning px-4">
                    <i class="bi bi-calendar"></i> Mon Planning
                </a>
                <a href="agentdashboard.php#biens" class="btn btn-success px-4">
                    <i class="bi bi-building"></i> Mes Biens
                </a>
                <a href="agent_rdv.php" class="btn btn-outline-dark px-4">
                    <i class="bi bi-calendar-check"></i> Mes Rendez-vous
                </a>
            </div>

            <!-- Profil -->
            <div class="card mb-4 p-4 shadow-sm" id="profil">
                <h4 class="mb-3 text-center">Mes infos</h4>
                <form method="post" class="row g-3 justify-content-center">
                    <div class="col-md-6">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control text-center" value="<?= htmlspecialchars($_SESSION['user']['nom']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control text-center" value="<?= htmlspecialchars($_SESSION['user']['email']) ?>" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control text-center" value="<?= htmlspecialchars($agent['telephone']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Spécialité</label>
                        <input type="text" name="specialite" class="form-control text-center" value="<?= htmlspecialchars($agent['specialite']) ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">CV / Description</label>
                        <textarea name="cv" class="form-control" rows="2"><?= htmlspecialchars($agent['cv']) ?></textarea>
                    </div>
                    <div class="col-md-12 mt-2 text-center">
                        <button class="btn btn-primary px-5" type="submit" name="update_infos">
                            <i class="bi bi-check-circle"></i> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>

            <!-- Planning -->
            <div class="card mb-4 p-4 shadow-sm" id="planning">
                <h4 class="mb-3 text-center">Mon planning de disponibilité</h4>
                <form method="post">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center bg-white mb-3">
                            <thead>
                                <tr>
                                    <th>Jour</th>
                                    <th>Matin (AM)</th>
                                    <th>Après-midi (PM)</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach($jours as $i=>$jour): ?>
                                <tr>
                                    <td><?= $jour ?></td>
                                    <td>
                                        <input type="checkbox" name="dispo_<?= $jour ?>_AM"
                                            <?= (isset($planning[$i]['AM']) && $planning[$i]['AM']=="AM") ? 'checked' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="dispo_<?= $jour ?>_PM"
                                            <?= (isset($planning[$i]['PM']) && $planning[$i]['PM']=="PM") ? 'checked' : '' ?>>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary px-5" type="submit" name="update_planning">
                            <i class="bi bi-calendar-check"></i> Mettre à jour le planning
                        </button>
                    </div>
                </form>
            </div>

            <!-- Biens -->
            <div class="card p-4 shadow-sm" id="biens">
                <h4 class="mb-3 text-center">Mes biens immobiliers</h4>
                <div class="text-center mb-3">
                    <a href="ajouter_bien.php" class="btn btn-success px-4">
                        <i class="bi bi-plus-circle"></i> Ajouter un bien
                    </a>
                </div>
                <?php if (empty($biens)): ?>
                    <div class="alert alert-info text-center">Aucun bien enregistré.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($biens as $bien): ?>
                                <tr>
                                    <td><?= htmlspecialchars($bien['titre']) ?></td>
                                    <td><?= htmlspecialchars($bien['categorie']) ?></td>
                                    <td><?= number_format($bien['prix'],0,',',' ') ?> €</td>
                                    <td>
                                        <a href="modifier_bien.php?id=<?= $bien['id'] ?>" class="btn btn-outline-primary btn-sm mx-1">
                                            <i class="bi bi-pencil"></i> Modifier
                                        </a>
                                        <a href="supprimer_bien.php?id=<?= $bien['id'] ?>" class="btn btn-outline-danger btn-sm mx-1" onclick="return confirm('Supprimer ce bien ?')">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<?php include 'includes/footer.php'; ?>