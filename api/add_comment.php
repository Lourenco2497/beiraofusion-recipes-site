<?php
session_start();
require_once '../connections/connections.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../site/registo.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe_id = $_POST['recipe_id'] ?? null;
    $comment_content = trim($_POST['comment_content'] ?? '');
    $parent_comment_id = !empty($_POST['parent_comment_id']) ? intval($_POST['parent_comment_id']) : null;

    if ($recipe_id && is_numeric($recipe_id) && !empty($comment_content)) {
        $user_id = $_SESSION['user_id'];
        $conn = new_db_connection();

        $stmt = $conn->prepare("INSERT INTO comments (ref_user_id, ref_recipe_id, content, parent_comment_id, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("iisi", $user_id, $recipe_id, $comment_content, $parent_comment_id);

        $stmt->execute();
        $stmt->close();
        $conn->close();

        header('Location: ../site/comentarios.php?id=' . $recipe_id);
        exit;
    } else {
        header('Location: ../site/comentarios.php?id=' . $recipe_id . '&error=1');
        exit;
    }
} else {
    header('Location: ../site/index.php');
    exit;
}
?>