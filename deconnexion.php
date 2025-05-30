<?php
require_once 'includes/db.php';
session_start();
session_unset();
session_destroy();
include 'includes/header.php';
?>

<section>
    <div class="card" style="max-width:440px; margin:2.2em auto; padding:2.1em 1.2em; background:#eef0fa; color:#5861a7;">
        <h2 class="main-title" style="margin-bottom:0.6em;">Déconnexion réussie</h2>
        <p>Vous avez été déconnecté.<br>
        Redirection vers l'accueil...</p>
    </div>
</section>
<script>
    setTimeout(function(){ window.location.href = 'index.php'; }, 2200);
</script>

<?php include 'includes/footer.php'; ?>
