<?php
require 'db.php';

function getUserById($id){
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getTicketById($id){
    global $pdo;
    $stmt = $pdo->prepare("SELECT t.*, u.username as creator 
                           FROM tickets t 
                           LEFT JOIN users u ON t.user_id = u.id
                           WHERE t.id=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

?>
