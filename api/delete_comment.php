<?php
session_start();
require_once '../connections/connections.php';

if (!isset($_POST['comment_id']) || !is_numeric($_POST['comment_id']) || !isset($_SESSION['user_id'])) {
    die("Acesso inválido.");
}

$comment_id = (int)$_POST['comment_id'];
$user_id = $_SESSION['user_id'];

$link = new_db_connection();

// verifica se o comentário pertence ao utilizador
$stmt = $link->prepare("SELECT ref_user_id FROM comments WHERE comment_id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->bind_result($owner_id);
$stmt->fetch();
$stmt->close();

if ($owner_id != $user_id) {
    die("Sem permissão para apagar este comentário.");
}

// apagar o comentário
$stmt = $link->prepare("DELETE FROM comments WHERE comment_id = ?");
$stmt->bind_param("i", $comment_id);
if ($stmt->execute()) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
} else {
    echo "Erro ao apagar comentário.";
}
$stmt->close();
$link->close();
?>
