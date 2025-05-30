<?php
include 'includes/header.php';
require_once 'includes/db.php';

// Évènement dynamique
$evenement = [
    "titre" => "Portes ouvertes - Visite exclusive d’une villa de prestige",
    "date"  => "Samedi 1er juin 2025",
    "description" => "Venez découvrir en avant-première cette villa d’exception située à Cannes. Nos agents seront présents pour répondre à toutes vos questions !"
];

// Carousel : 3 biens au hasard
$biens = $pdo->query("SELECT * FROM biens ORDER BY RAND() LIMIT 3")->fetchAll();

// Adresse, mail, tel
$adresse = "15 Rue Hoche, 75008 Paris";
$mail    = "contact@omnesimmobilier.fr";
$tel     = "01 42 00 42 00";
?>

<section>
    <!-- HERO bienvenue -->
    <div class="container hero-omnes text-center mb-5" style="
        background: linear-gradient(135deg, #eef0fa 0%, #f5f7fd 100%);
        border-radius:32px;
        color:#5861a7;
        padding:3rem 1.5rem 1rem 1.5rem;
        box-shadow:0 8px 38px 0 rgba(88,97,167,0.10);
        margin-bottom:2.7rem;
    ">
        <h1 class="main-title" style="font-size:2.4rem;">Bienvenue chez Omnes Immobilier</h1>
        <div class="fs-5 mb-2">Votre partenaire pour tous vos projets immobiliers à Paris et en Île-de-France.</div>
        <div class="mb-3">Vente, achat, location, estimation ou conseil : <b>notre équipe d’experts vous accompagne de A à Z.</b></div>
        <a href="register.php" class="btn btn-primary btn-lg mt-2 px-4 shadow-sm" style="background:#7382c4;border:none;">
            <i class="bi bi-person-plus"></i> Créer un compte gratuitement
        </a>
    </div>

    <!-- Carte évènement de la semaine — Bannière large et centrée, images en dessous -->
    <div class="container mb-5">
        <div class="evt-card-wide" style="
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            background: #fff;
            border-radius: 2.2rem;
            box-shadow: 0 2px 24px 0 #e7eaf3;
            padding: 2.7rem 2.2rem 2.5rem 2.2rem;
            text-align: center;
            margin-bottom: 2.7rem;
        ">
            <!-- Grand titre -->
            <div style="font-size:2.1rem; font-weight: 800; color:#7382c4; margin-bottom:1.25rem; letter-spacing:0.7px;">
                Évènement de la semaine
            </div>
            <h2 style="color:#5861a7;font-size:2.2rem;font-weight:800; margin-bottom:0.65em;">
                <?= htmlspecialchars($evenement['titre']) ?>
            </h2>
            <div style="color:#7382c4; font-size:1.11rem; margin-bottom:0.8em;">
                <i class="bi bi-calendar-event"></i> <?= htmlspecialchars($evenement['date']) ?>
            </div>
            <div style="color:#22223b; font-size:1.21rem; margin-bottom:2em;">
                <?= htmlspecialchars($evenement['description']) ?>
            </div>
            <!-- 2 PHOTOS côte à côte, bien centrées -->
            <div style="display: flex; justify-content: center; gap: 1.5rem; margin-top: 0;">
                <img src="assets/img/villa-prestige.jpg"
                    alt="Villa de prestige" style="border-radius:1.2rem;box-shadow:0 2px 14px #7382c418;width:220px;height:145px;object-fit:cover;">
                <img src="assets/img/salon-luxe.jpg"
                    alt="Salon luxueux" style="border-radius:1.2rem;box-shadow:0 2px 14px #7382c418;width:220px;height:145px;object-fit:cover;">
            </div>
        </div>
    </div>
    
    <!-- Carousel des biens -->
    <div class="carousel">
        <h3 style="color:#5861a7;">Nos biens du moment</h3>
        <div class="carousel-container">
            <img src="assets/img/villa.jpg" alt="Villa avec piscine">
            <img src="assets/img/moderne.jpg" alt="Maison moderne">
            <img src="assets/img/ancien.jpg" alt="Maison style ancien">
        </div>
        <div class="carousel-controls">
            <button id="prev">&#8592;</button>
            <button id="next">&#8594;</button>
        </div>
    </div>
</section>

<!-- Services principaux -->
<div class="container my-5">
    <div class="row text-center g-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow h-100 py-4" style="border-radius:1.3rem;">
                <i class="bi bi-house-door-fill text-primary" style="font-size:2.2rem"></i>
                <h6 class="fw-bold my-2">Achat</h6>
                <span class="text-muted">Maisons, appartements</span>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow h-100 py-4" style="border-radius:1.3rem;">
                <i class="bi bi-key-fill text-success" style="font-size:2.2rem"></i>
                <h6 class="fw-bold my-2">Location</h6>
                <span class="text-muted">Logements, bureaux</span>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow h-100 py-4" style="border-radius:1.3rem;">
                <i class="bi bi-currency-euro text-warning" style="font-size:2.2rem"></i>
                <h6 class="fw-bold my-2">Enchères</h6>
                <span class="text-muted">Biens exclusifs</span>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow h-100 py-4" style="border-radius:1.3rem;">
                <i class="bi bi-person-video3 text-danger" style="font-size:2.2rem"></i>
                <h6 class="fw-bold my-2">Conseil Visio</h6>
                <span class="text-muted">RDV à distance</span>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/script.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<?php include 'includes/footer.php'; ?>
