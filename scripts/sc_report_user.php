<?php
session_start();
require_once '../connections/connections.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['reporter_id'], $_POST['reported_id'], $_POST['reason']) || !isset($_SESSION['user_id'])) {
        header("Location: ../site/index.php");
        exit();
    }

    $reporter_id = filter_var($_POST['reporter_id'], FILTER_VALIDATE_INT);
    $reported_id = filter_var($_POST['reported_id'], FILTER_VALIDATE_INT);
    $reason = trim($_POST['reason']);
    $session_user_id = $_SESSION['user_id'];

    if ($reporter_id === false || $reported_id === false || empty($reason) || $reporter_id != $session_user_id) {
        header("Location: ../site/denuncias.php?id=$reported_id&error=invalid_data");
        exit();
    }

    $link = new_db_connection();


    $status_id = 1;

    $stmt = $link->prepare("INSERT INTO reports (ref_reporter_id, ref_reported_id, reason, ref_status_id, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iisi", $reporter_id, $reported_id, $reason, $status_id);

    if ($stmt->execute()) {
        header("Location: ../site/perfil_utilizador.php?id=$reported_id&success=report_sent");
    } else {
        header("Location: ../site/denuncias.php?id=$reported_id&error=db_error");
    }

    $stmt->close();
    $link->close();
} else {
    header("Location: ../site/index.php");
    exit();
}