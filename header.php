<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omnes Immobilier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <img src="https://cdn-icons-png.flaticon.com/512/69/69524.png" width="36" height="36" class="me-2">
            Omnes Immobilier
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>

                <li class="nav-item"><a class="nav-link" href="biens.php">Tout Parcourir</a></li>
                <li class="nav-item"><a class="nav-link" href="agents.php">Agents</a></li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item"><a class="nav-link" href="compte.php">Votre compte</a></li>
                    <li class="nav-item"><a class="nav-link" href="rdv.php">Rendez-vous</a></li>

                    <li class="nav-item"><a class="nav-link text-danger" href="deconnexion.php">Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Créer un compte</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container my-4">
