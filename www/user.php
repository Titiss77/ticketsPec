<?php
session_start();
require 'db.php';
require 'functions.php';

if (!isset($_GET['id'])) {
    die("ID utilisateur manquant !");
}

$user_id = $_GET['id'];
$user = getUserById($user_id);

if (!$user) {
    die("Utilisateur introuvable !");
}
?>
<!DOCTYPE html>
<html lang="fr">

    <?php require 'head.php'; addHead("Profil de <?= htmlspecialchars($user['username']) ?>");?>
<body>

<div class="profile-card">
    <h2>Informations sur <?= htmlspecialchars($user['username']) ?></h2>
    <ul>
        <li><strong>Nom :</strong> <?= htmlspecialchars($user['username']) ?></li>
        <li><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></li>
        <li><strong>Tel :</strong> <?= htmlspecialchars($user['numTel']) ?></li>
    </ul>
    <a href="dashboard.php">⬅️ Retour au tableau de bord</a>
</div>


</body>
</html>
