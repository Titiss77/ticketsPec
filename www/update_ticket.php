<?php
session_start();
require 'db.php';
require 'functions.php';

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
        header("Location: dashboard.php?update_success=1");
        exit;
    } catch (PDOException $e) {
        die("Erreur SQL : " . $e->getMessage());
    }
}
?>
