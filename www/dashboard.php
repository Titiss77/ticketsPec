<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/head.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: /www/login.php");
    exit;
}

// Création ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $priority = $_POST['priority'];

    // Insertion du ticket
    $stmt = $pdo->prepare("INSERT INTO tickets (user_id, title, description, priority, status) VALUES (?, ?, ?, ?, 'ouvert')");
    $stmt->execute([$_SESSION['user_id'], $title, $desc, $priority]);

    $ticket_id = $pdo->lastInsertId();

    // Upload du fichier si présent
    if (!empty($_FILES['attachment']['name'])) {
    $uploadDir = __DIR__ . '/uploads/'; // chemin serveur
    $webDir = 'uploads/';                // chemin accessible depuis le navigateur
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $filename = basename($_FILES['attachment']['name']);
    $newFilename = time() . '_' . $filename;
    $filepathServer = $uploadDir . $newFilename;  // pour move_uploaded_file
    $filepathWeb = $webDir . $newFilename;        // pour stocker en BDD et afficher

    if (is_uploaded_file($_FILES['attachment']['tmp_name'])) {
        if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $filepathServer)) {
            $_SESSION['upload_error'] = "Le fichier n'a pas pu être enregistré.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO attachments (ticket_id, filename, filepath) VALUES (?, ?, ?)");
            $stmt->execute([$ticket_id, $filename, $filepathWeb]);
        }
    } else {
        $_SESSION['upload_error'] = "Erreur lors de l’upload du fichier.";
    }
}



    // Redirection PRG pour éviter le double envoi
    header("Location: /www/dashboard.php?success=1");
    exit;
}

// Récupérer tickets
if ($_SESSION['role'] === 'admin') {
    $stmt = $pdo->query("
        SELECT t.*, u.username as creator
        FROM tickets t
        JOIN users u ON t.user_id = u.id
        ORDER BY 
            CASE t.status
                WHEN 'termine' THEN 2
                ELSE 1
            END ASC, 
            CASE t.priority
                WHEN 'Haute' THEN 1
                WHEN 'Moyenne' THEN 2
                WHEN 'Faible' THEN 3
                ELSE 4
            END ASC
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT t.*, u.username as creator
        FROM tickets t
        JOIN users u ON t.user_id = u.id
        WHERE t.user_id=?
        ORDER BY 
            CASE t.status
                WHEN 'termine' THEN 2
                ELSE 1
            END ASC,
            CASE t.priority
                WHEN 'Haute' THEN 1
                WHEN 'Moyenne' THEN 2
                WHEN 'Faible' THEN 3
                ELSE 4
            END ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
}

$tickets = $stmt->fetchAll();
$admins = $pdo->query("SELECT id, username FROM users WHERE role='admin'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<?= addHead("dashboard");?>
<body>

<div class="sidebar">
    <h2>Tickets</h2>
    <a href="#create_ticket">Créer un ticket</a>
    <a href="#mes_tickets">Mes tickets</a>
    <form action="/www/logout.php" method="post">
        <button type="submit">Déconnexion</button>
    </form>
</div>

<div class="main-content">
    <!-- Messages -->
    <?php if (isset($_GET['success'])): ?>
        <p style="color:green;">Ticket créé avec succès !</p>
    <?php endif; ?>

    <?php if (isset($_SESSION['upload_error'])): ?>
        <p style="color:orange;"><?= $_SESSION['upload_error'] ?></p>
        <?php unset($_SESSION['upload_error']); ?>
    <?php endif; ?>

    <!-- Formulaire création ticket -->
    <h2 id="create_ticket">Créer un ticket</h2>
    <form method="post" class="create-ticket" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Titre général" required>
        <textarea name="description" placeholder="Plus votre description sera complète et précise, moins j'aurais besoins de vous demander des précisions." required></textarea>
        <select name="priority" required>
            <option value="Null" selected>Choisir l'importance</option>
            <option value="Faible" style="background-color: #008000ba">Importance plutôt faible</option>
            <option value="Moyenne" style="background-color: #ffa500ba">Importance moyenne</option>
            <option value="Haute" style="background-color: #ff0000ba">Importance très haute</option>
        </select>
        <input type="file" name="attachment">
        <button type="submit" name="create_ticket">Créer</button>
    </form>

    <!-- Liste tickets -->
    <h2 id="mes_tickets">Mes tickets</h2>
    <table>
        <tr>
            <th>ID</th><th>Titre</th><th>Status</th><th>Priorité</th><th>Créé par</th><th>Actions</th>
        </tr>
        <?php foreach ($tickets as $t): ?>
        <tr>
            <td><?= $t['id'] ?></td>
            <td><?= htmlspecialchars($t['title']) ?></td>
            <td>
                <?php
                switch ($t['status']) {
                    case 'ouvert': echo 'Ouvert'; break;
                    case 'en-cours': echo 'En cours'; break;
                    case 'termine': echo 'Terminé'; break;
                    default: echo '—';
                }
                ?>
            </td>
            <td class="<?php
                if ($t['priority'] == 'High' || $t['priority'] == 'Haute') echo 'priority-high';
                elseif ($t['priority'] == 'Medium' || $t['priority'] == 'Moyenne') echo 'priority-medium';
                else echo 'priority-low';
            ?>">
                <?= htmlspecialchars($t['priority']) ?>
            </td>
            <td>
                <form action="user.php" method="get" style="display:inline">
                    <input type="hidden" name="id" value="<?= $t['user_id'] ?>">
                    <button type="submit" class="btn-view-user">
                        <?= htmlspecialchars($t['creator']) ?>
                    </button>
                </form>
            </td>
            <td>
                <a href="/www/ticket.php?id=<?= $t['id'] ?>">Voir</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <form method="post" action="update_ticket.php" style="display:inline">
                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                        <select name="status">
                            <option value="ouvert" <?= $t['status']=='ouvert'?'selected':'' ?>>Ouvert</option>
                            <option value="en-cours" <?= $t['status']=='en-cours'?'selected':'' ?>>En cours</option>
                            <option value="termine" <?= $t['status']=='termine'?'selected':'' ?>>Terminé</option>
                        </select>
                        <button type="submit">Mettre à jour</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>