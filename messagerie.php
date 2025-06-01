<?php
session_start();
require_once 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php?redirect=messagerie.php");
    exit;
};

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];
$confirmation = "";

// ENVOI DE MESSAGE (commune aux deux rôles)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['destinataire_id'], $_POST['message'])) {
    $to = intval($_POST['destinataire_id']);
    $msg = trim($_POST['message']);
    if ($to > 0 && $msg !== "") {
        $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, message, date_envoi) VALUES (?, ?, ?, NOW())")
            ->execute([$user_id, $to, $msg]);
        $confirmation = "<div class='alert alert-success text-center mb-4'>Message envoyé !</div>";
    }
}

// ----------- CLIENT -----------
if ($role == 'client') {
    // Liste des agents pour nouvelle conversation
    $agents = $pdo->query("SELECT a.id, u.nom, u.id as user_id FROM agents a JOIN utilisateurs u ON a.utilisateur_id = u.id")->fetchAll();

    // Historique des conversations (1 par agent)
    $stmt = $pdo->prepare("
        SELECT u.id as agent_id, u.nom
        FROM messages m
        JOIN utilisateurs u ON (u.id = m.expediteur_id OR u.id = m.destinataire_id)
        WHERE (m.expediteur_id = :uid OR m.destinataire_id = :uid) AND u.role = 'agent' AND u.id != :uid
        GROUP BY u.id
        ORDER BY MAX(m.date_envoi) DESC
    ");
    $stmt->execute(['uid' => $user_id]);
    $convs = $stmt->fetchAll();

    // Agent sélectionné ?
    $agent_id = isset($_GET['agent']) ? intval($_GET['agent']) : ($convs[0]['agent_id'] ?? null);

    // Messages de la conversation
    $messages = [];
    if ($agent_id) {
        $stmt = $pdo->prepare("
            SELECT m.*, u.nom AS nom_expediteur, u2.nom AS nom_destinataire
            FROM messages m
            JOIN utilisateurs u ON m.expediteur_id = u.id
            JOIN utilisateurs u2 ON m.destinataire_id = u2.id
            WHERE (m.expediteur_id = :uid AND m.destinataire_id = :aid)
               OR (m.expediteur_id = :aid AND m.destinataire_id = :uid)
            ORDER BY m.date_envoi ASC
        ");
        $stmt->execute(['uid' => $user_id, 'aid' => $agent_id]);
        $messages = $stmt->fetchAll();
    }
?>
<div class="container my-5">
    <h1 class="main-title text-center mb-4"><i class="bi bi-chat-dots"></i> Ma messagerie</h1>
    <?= $confirmation ?>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header text-center fw-bold">Agents</div>
                <div class="list-group list-group-flush">
                    <?php foreach ($convs as $c): ?>
                        <a href="messagerie.php?agent=<?= $c['agent_id'] ?>" class="list-group-item<?= ($agent_id == $c['agent_id'] ? ' active' : '') ?>">
                            <?= htmlspecialchars($c['nom']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer text-center">
                    <button class="btn btn-link" data-bs-toggle="collapse" data-bs-target="#nouveauMsg"><i class="bi bi-plus-circle"></i> Nouveau message</button>
                </div>
                <div class="collapse" id="nouveauMsg">
                    <form method="post" class="p-3">
                        <div class="mb-2">
                            <select name="destinataire_id" class="form-select" required>
                                <option value="">Choisir un agent</option>
                                <?php foreach ($agents as $a): ?>
                                    <option value="<?= $a['user_id'] ?>"><?= htmlspecialchars($a['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <textarea name="message" class="form-control" required placeholder="Votre message..."></textarea>
                        </div>
                        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-send"></i> Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <?php if ($agent_id): ?>
                <div class="card mb-4 p-3">
                    <h4 class="mb-3">Conversation avec <span class="text-primary"><?= htmlspecialchars(
                        ($convs[array_search($agent_id, array_column($convs, 'agent_id'))]['nom'] ?? '')
                    ) ?></span></h4>
                    <div style="max-height: 350px; overflow-y: auto; background:#f8f9fa; padding: 1em; border-radius: .5em;">
                        <?php foreach ($messages as $msg): ?>
                            <div class="mb-2 <?= $msg['expediteur_id']==$user_id ? 'text-end' : 'text-start' ?>">
                                <div class="d-inline-block px-3 py-2 rounded <?= $msg['expediteur_id']==$user_id ? 'bg-primary text-white' : 'bg-light' ?>">
                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                    <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <form method="post" class="mt-3 d-flex">
                        <input type="hidden" name="destinataire_id" value="<?= $agent_id ?>">
                        <input type="text" name="message" class="form-control me-2" required placeholder="Votre message...">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i></button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">Sélectionnez un agent ou envoyez un nouveau message.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
}

// ----------- AGENT -----------
elseif ($role == 'agent') {
    // Liste des clients ayant déjà écrit à l’agent (comme un historique)
    $stmt = $pdo->prepare("
        SELECT u.id, u.nom
        FROM messages m
        JOIN utilisateurs u ON (u.id = m.expediteur_id OR u.id = m.destinataire_id)
        WHERE (m.destinataire_id = :agent OR m.expediteur_id = :agent) AND u.role = 'client' AND u.id != :agent
        GROUP BY u.id
        ORDER BY MAX(m.date_envoi) DESC
    ");
    $stmt->execute(['agent'=>$user_id]);
    $clients = $stmt->fetchAll();

    $client_id = isset($_GET['client']) ? intval($_GET['client']) : ($clients[0]['id'] ?? null);

    // Messages avec ce client
    $messages = [];
    if ($client_id) {
        $stmt = $pdo->prepare("
            SELECT m.*, u.nom AS nom_expediteur, u2.nom AS nom_destinataire
            FROM messages m
            JOIN utilisateurs u ON m.expediteur_id = u.id
            JOIN utilisateurs u2 ON m.destinataire_id = u2.id
            WHERE (m.expediteur_id = :agent AND m.destinataire_id = :client)
               OR (m.expediteur_id = :client AND m.destinataire_id = :agent)
            ORDER BY m.date_envoi ASC
        ");
        $stmt->execute(['agent'=>$user_id, 'client'=>$client_id]);
        $messages = $stmt->fetchAll();
    }
?>
<div class="container my-5">
    <h1 class="main-title text-center mb-4"><i class="bi bi-chat-dots"></i> Ma messagerie</h1>
    <?= $confirmation ?>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header text-center fw-bold">Clients</div>
                <div class="list-group list-group-flush">
                    <?php foreach ($clients as $c): ?>
                        <a href="messagerie.php?client=<?= $c['id'] ?>" class="list-group-item<?= ($client_id == $c['id'] ? ' active' : '') ?>">
                            <?= htmlspecialchars($c['nom']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <?php if ($client_id): ?>
            <div class="card mb-4 p-3">
                <h4 class="mb-3">Conversation avec <span class="text-primary"><?= htmlspecialchars(
                    ($clients[array_search($client_id, array_column($clients, 'id'))]['nom'] ?? '')
                ) ?></span></h4>
                <div style="max-height: 350px; overflow-y: auto; background:#f8f9fa; padding: 1em; border-radius: .5em;">
                    <?php foreach ($messages as $msg): ?>
                        <div class="mb-2 <?= $msg['expediteur_id']==$user_id ? 'text-end' : 'text-start' ?>">
                            <div class="d-inline-block px-3 py-2 rounded <?= $msg['expediteur_id']==$user_id ? 'bg-primary text-white' : 'bg-light' ?>">
                                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form method="post" class="mt-3 d-flex">
                    <input type="hidden" name="destinataire_id" value="<?= $client_id ?>">
                    <input type="text" name="message" class="form-control me-2" required placeholder="Votre message...">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i></button>
                </form>
            </div>
            <?php else: ?>
                <div class="alert alert-info text-center">Sélectionnez un client pour afficher la conversation.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
}
include 'includes/footer.php';
?>
