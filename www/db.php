<?php
if (file_exists(__DIR__ . '/../config.php')) {
    $config = require __DIR__ . '/../config.php';
} else {
    echo "config.php non trouvé ❌";
}


try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8",
        $config['user'],
        $config['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    // Message complet pour le log serveur
    error_log("Erreur PDO ByetHost : " . $e->getMessage());
    exit;
}
