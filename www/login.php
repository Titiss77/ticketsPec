<?php
    
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
define('BASE_PATH', __DIR__ . '/'); // si ce fichier est dans www/
require BASE_PATH . 'db.php';
require BASE_PATH . 'head.php';


// Connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: /www/dashboard.php");
        exit;
    } else {
        echo "<p style='color:red'>Identifiants incorrects !</p>";
    }
}

// Redirection vers création de compte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    header("Location: /www/create_users.php"); // page du formulaire de création d'utilisateur
    exit;
}
?>
<?php addHead("Connexion");?>

<div class="login-container">
    <h2>Connexion</h2>

    <?php if (isset($message) && $message): ?>
        <p class="error"><?= $message ?></p>
    <?php endif; ?>

    <form method="post">
        Nom d'utilisateur: <input type="text" name="username" required><br>
        Mot de passe: <input type="password" name="password" required><br>
        <button type="submit" name="login">Se connecter</button>
    </form>

    <form method="post" style="margin-top:10px;">
        <button type="submit" name="register">Créer un compte</button>
    </form>
</div>

