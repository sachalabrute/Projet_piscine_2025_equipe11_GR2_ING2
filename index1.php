<?php
require_once 'includes/db.php';
include 'includes/header.php';
?>
;
<section>
    <div class="welcome" style="text-align:center;margin-bottom:2.4em;">
        <h1 class="main-title" style="margin-bottom:0.4em;">Bienvenue sur Omnes Immobilier</h1>
        <p class="lead" style="margin-bottom:1em;font-size:1.19rem;">Au service des besoins immobiliers de la communauté Omnes</p>
        <img src="https://cdn-icons-png.flaticon.com/512/69/69524.png" width="88" alt="Maison" style="margin-bottom:1.2em;">
    </div>
    <div class="container" style="display:flex; flex-wrap:wrap; gap:2.3em; justify-content:center;">
        <div class="card">
            <div class="card-body" style="text-align:center; padding:2em 1.5em;">
                <h3 class="card-title" style="margin-bottom:0.5em;">Tout Parcourir</h3>
                <p class="card-text" style="margin-bottom:1em;">Trouvez tous les biens disponibles : maisons, appartements, terrains...</p>
                <a href="biens.php" class="btn btn-primary">Voir les biens</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align:center; padding:2em 1.5em;">
                <h3 class="card-title" style="margin-bottom:0.5em;">Nos Agents</h3>
                <p class="card-text" style="margin-bottom:1em;">Consultez la liste de nos agents immobiliers agréés.</p>
                <a href="agents.php" class="btn btn-primary">Voir les agents</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align:center; padding:2em 1.5em;">
                <h3 class="card-title" style="margin-bottom:0.5em;">Prendre rendez-vous</h3>
                <p class="card-text" style="margin-bottom:1em;">Prenez RDV avec un agent facilement en ligne.</p>
                <a href="rdv.php" class="btn btn-primary">Prendre RDV</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
