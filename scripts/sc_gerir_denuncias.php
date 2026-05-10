<?php
session_start();
require_once '../connections/connections.php';


if (!isset($_SESSION['user_id']) || !isset($_SESSION['ref_type_id']) || $_SESSION['ref_type_id'] != 2) {
    header("Location: ../site/index.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['report_id']) && isset($_POST['action'])) {
    $report_id = filter_var($_POST['report_id'], FILTER_VALIDATE_INT);
    $action = $_POST['action'];

    if ($report_id === false) {
        header("Location: ../site/gerir_denuncias.php?error=invalid_id");
        exit();
    }

    $link = new_db_connection();
    $stmt = $link->prepare("DELETE FROM reports WHERE report_id = ?");
    $stmt->bind_param("i", $report_id);

    if ($stmt->execute()) {
        if ($action === 'refuse') {
            header("Location: ../site/gerir_denuncias.php?success=report_rejected");
        } elseif ($action === 'delete_only') {
            header("Location: ../site/gerir_denuncias.php?success=report_deleted");
        } else {
            header("Location: ../site/gerir_denuncias.php?success=report_managed");
        }
    } else {
        header("Location: ../site/gerir_denuncias.php?error=db_error");
    }
    $stmt->close();
    $link->close();
} else {
    header("Location: ../site/gerir_denuncias.php");
    exit();
}