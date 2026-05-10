<?php

session_start();

header('Content-Type: application/json');

require_once '../connections/connections.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

if (!isset($_POST['recipe_id']) || !is_numeric($_POST['recipe_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid recipe ID.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$recipe_id = intval($_POST['recipe_id']);
$conn = new_db_connection();

// Check if the bookmark already exists
$sql_check = "SELECT ref_user_id FROM recipe_saves WHERE ref_user_id = ? AND ref_recipe_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $recipe_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Exists, so remove it
    $sql_delete = "DELETE FROM recipe_saves WHERE ref_user_id = ? AND ref_recipe_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $user_id, $recipe_id);
    if ($stmt_delete->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove bookmark.']);
    }
    $stmt_delete->close();
} else {
    // Does not exist, so add it
    $sql_insert = "INSERT INTO recipe_saves (ref_user_id, ref_recipe_id) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ii", $user_id, $recipe_id);
    if ($stmt_insert->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'added']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add bookmark.']);
    }
    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();
?>