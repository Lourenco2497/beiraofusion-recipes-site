<?php
session_start();
require_once '../connections/connections.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$comment_id = $data['comment_id'] ?? null;

if (!is_numeric($comment_id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid comment ID']);
    exit;
}

$link = new_db_connection();

$stmt_check = $link->prepare("SELECT ref_user_id FROM comment_likes WHERE ref_user_id = ? AND ref_comment_id = ?");
$stmt_check->bind_param("ii", $user_id, $comment_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

$user_liked = false;

if ($result_check->num_rows > 0) {
    // Like exists, so remove it (unlike)
    $stmt_delete = $link->prepare("DELETE FROM comment_likes WHERE ref_user_id = ? AND ref_comment_id = ?");
    $stmt_delete->bind_param("ii", $user_id, $comment_id);
    $stmt_delete->execute();
    $user_liked = false;
} else {
    // Like does not exist, so add it (like)
    $stmt_insert = $link->prepare("INSERT INTO comment_likes (ref_user_id, ref_comment_id) VALUES (?, ?)");
    $stmt_insert->bind_param("ii", $user_id, $comment_id);
    $stmt_insert->execute();
    $user_liked = true;
}
$stmt_check->close();

// Get the new total like count for the comment
$stmt_count = $link->prepare("SELECT COUNT(*) AS like_count FROM comment_likes WHERE ref_comment_id = ?");
$stmt_count->bind_param("i", $comment_id);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$like_count = $result_count->fetch_assoc()['like_count'];
$stmt_count->close();

$link->close();

echo json_encode(['success' => true, 'user_liked' => $user_liked, 'like_count' => $like_count]);
?>
