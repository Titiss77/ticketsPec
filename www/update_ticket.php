<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/head.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Accès interdit !");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['ticket_id']) || empty($_POST['status'])) {
        die("Données manquantes !");
    }

    $ticket_id = $_POST['ticket_id'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
		$stmt->execute([$status, $ticket_id]);


        // Récupération du ticket et de son créateur
        $ticket = getTicketById($ticket_id);
        $user = getUserById($ticket['user_id']);

        // Redirection (PRG)
        header("Location: /www/dashboard.php?update_success=1");
        exit;
    } catch (PDOException $e) {
        die("Erreur SQL : " . $e->getMessage());
    }
}
?>
