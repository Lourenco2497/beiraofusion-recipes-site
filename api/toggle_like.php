<?php
session_start();
require_once '../connections/connections.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['recipe_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos']);
    exit();
}

$user_id = $_SESSION['user_id'];
$recipe_id = (int)$_POST['recipe_id'];

$link = new_db_connection();

// Verifica se já tem like
$stmt = $link->prepare("SELECT * FROM recipe_likes WHERE ref_user_id = ? AND ref_recipe_id = ?");
$stmt->bind_param("ii", $user_id, $recipe_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Já tem like → remover
    $stmt = $link->prepare("DELETE FROM recipe_likes WHERE ref_user_id = ? AND ref_recipe_id = ?");
    $stmt->bind_param("ii", $user_id, $recipe_id);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'action' => 'removed']);
} else {
    // Ainda não tem like → adicionar
    $stmt = $link->prepare("INSERT INTO recipe_likes (ref_user_id, ref_recipe_id, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $user_id, $recipe_id);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'action' => 'added']);
}
$link->close();
?>
