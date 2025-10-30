<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/head.php';


$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage et validation des entrées
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $numTel = htmlspecialchars(trim($_POST['numTel']));
    $password = $_POST['password'];

    // Validation email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email invalide.";
    }
    // Validation téléphone
    elseif (!preg_match('/^\+?[0-9\s\-\(\)]{7,15}$/', $numTel)) {
        $message = "Numéro de téléphone invalide.";
    }
    // Validation mot de passe (minimum 6 caractères)
    elseif (strlen($password) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères.";
    } 
    else {
        // Vérifier si l'utilisateur ou l'email existe déjà
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email
        ]);

        if ($stmt->rowCount() > 0) {
            $message = "Nom d'utilisateur ou email déjà utilisé.";
        } else {
            // Hashage du mot de passe
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; // utilisateur normal

            // Insertion sécurisée
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, numTel, password, role)
                 VALUES (:username, :email, :numTel, :password, :role)"
            );
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':numTel' => $numTel,
                ':password' => $passwordHash,
                ':role' => $role
            ]);

            $message = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
        }
    }
}
?>
<?php addHead("Creation");?>
<div class="create-account-container">
    <h2>Créer un compte</h2>

    <?php if($message): ?>
        <p class="<?= strpos($message, 'succès') !== false ? 'success' : 'error' ?>">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <form method="post" class="create-account-form">
        <label>Nom d'utilisateur:</label>
        <input type="text" name="username" required><br>

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Numéro de téléphone:</label>
        <input type="number" name="numTel" required><br>

        <label>Mot de passe:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Créer un compte</button>
    </form>

    <p><a href="/www/login.php">Retour à la connexion</a></p>
</div>
