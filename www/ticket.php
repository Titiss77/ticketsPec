<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/head.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /www/login.php");
    exit;
}

$ticket_id = $_GET['id'];

// Ajouter un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = $_POST['comment'];
    $stmt = $pdo->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$ticket_id, $_SESSION['user_id'], $comment]);

    // Redirection PRG pour éviter le double envoi
    header("Location: /www/ticket.php?id=$ticket_id");
    exit;
}

// Récupérer le ticket
$stmt = $pdo->prepare("SELECT t.*, u.username FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

// Récupérer les commentaires
$stmt = $pdo->prepare("SELECT c.*, u.username FROM ticket_comments c JOIN users u ON c.user_id = u.id WHERE c.ticket_id = ?");
$stmt->execute([$ticket_id]);
$comments = $stmt->fetchAll();

// Récupérer les pièces jointes
$stmt = $pdo->prepare("SELECT * FROM attachments WHERE ticket_id=?");
$stmt->execute([$ticket_id]);
$attachments = $stmt->fetchAll();
?>
<?php addHead("Ticket");?>

<div class="ticket-container">
    <div class="ticket-header">
        <h2><?= htmlspecialchars($ticket['title']) ?></h2>
        <p>Status: <span class="status"><?php
                switch ($ticket['status']) {
                    case 'ouvert': echo 'Ouvert'; break;
                    case 'en-cours': echo 'En cours'; break;
                    case 'termine': echo 'Terminé'; break;
                    default: echo '—';
                }
                ?></span></p>
        <p>Créé par: <strong><?= htmlspecialchars($ticket['username']) ?></strong></p>
    </div>

    <div class="ticket-description">
        <p><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
    </div>

    <?php if ($attachments): ?>
    <div class="ticket-attachments">
        <h3>Pièces jointes</h3>
        <ul>
            <?php foreach($attachments as $file): ?>
                <li><a href="<?= htmlspecialchars($file['filepath']) ?>" target="_blank"><?= htmlspecialchars($file['filename']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="ticket-comments">
    <h3>Commentaires</h3>
    <?php if($comments): ?>
        <?php foreach ($comments as $c): ?>
            <div class="comment">
                <strong><?= htmlspecialchars($c['username']) ?>:</strong>
                <p><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun commentaire pour le moment.</p>
    <?php endif; ?>
</div>


    <div class="add-comment">
        <h3>Ajouter un commentaire</h3>
        <form method="post">
            <textarea name="comment" placeholder="Votre commentaire..." required></textarea><br>
            <button type="submit">Envoyer</button>
        </form>
    </div>
</div>
