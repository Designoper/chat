<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("No autenticado");
}

$messageId = $_POST['message_id'];

// Verificar que el mensaje pertenece al usuario
$stmt = $db->prepare("SELECT user_id FROM messages WHERE id = ?");
$stmt->execute([$messageId]);
$owner = $stmt->fetchColumn();

if ($owner != $_SESSION['user_id']) {
    http_response_code(403);
    exit("No autorizado");
}

// Eliminar
$stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
$stmt->execute([$messageId]);

echo "Mensaje eliminado";
