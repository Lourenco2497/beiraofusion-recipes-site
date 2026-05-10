<?php
session_start();
require_once '../connections/connections.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}

$link = new_db_connection();

$current_user_id = $_SESSION['user_id'];
$user_to_toggle_id = $_POST['user_id'];
$response = [];

if ($current_user_id == $user_to_toggle_id) {
    $response = ['status' => 'error', 'message' => 'You cannot follow yourself.'];
} else {
    $stmt_check = mysqli_prepare($link, "SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?");
    mysqli_stmt_bind_param($stmt_check, "ii", $current_user_id, $user_to_toggle_id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        // Unfollow
        $stmt_unfollow = mysqli_prepare($link, "DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
        mysqli_stmt_bind_param($stmt_unfollow, "ii", $current_user_id, $user_to_toggle_id);
        if (mysqli_stmt_execute($stmt_unfollow)) {
            $response = ['status' => 'success', 'action' => 'unfollowed'];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to unfollow.'];
        }
        mysqli_stmt_close($stmt_unfollow);
    } else {
        // Follow
        $stmt_follow = mysqli_prepare($link, "INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt_follow, "ii", $current_user_id, $user_to_toggle_id);
        if (mysqli_stmt_execute($stmt_follow)) {
            $response = ['status' => 'success', 'action' => 'followed'];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to follow.'];
        }
        mysqli_stmt_close($stmt_follow);
    }
    mysqli_stmt_close($stmt_check);
}

mysqli_close($link);

header('Content-Type: application/json');
echo json_encode($response);
?>