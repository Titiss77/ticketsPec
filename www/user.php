<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/head.php';

// Vérification de l'ID utilisateur
if (!isset($_GET['id'])) {
    die("ID utilisateur manquant !");
}

$user_id = intval($_GET['id']); // Sécurisation de l'ID
$user = getUserById($user_id);

if (!$user) {
    die("Utilisateur introuvable !");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php addHead("Profil de " . htmlspecialchars($user['username'])); ?>
</head>
<body>

<div class="profile-card">
    <h2>Informations sur <?= htmlspecialchars($user['username']) ?></h2>
    <ul>
        <li><strong>Nom :</strong> <?= htmlspecialchars($user['username']) ?></li>
        <li><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></li>
        <li><strong>Téléphone :</strong> <?= htmlspecialchars($user['numTel']) ?></li>
        <li><strong>Rôle :</strong> <?= htmlspecialchars($user['role']) ?></li>
    </ul>
    <a href="/www/dashboard.php">⬅️ Retour au tableau de bord</a>
</div>

</body>
</html>
