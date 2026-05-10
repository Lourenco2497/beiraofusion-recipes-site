<?php
session_start();
require_once '../connections/connections.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['ref_type_id']) || $_SESSION['ref_type_id'] != 2) {
    header("Location: ../site/index.php");
    exit();
}

if (!isset($_GET['action']) || !isset($_GET['id'])) {
    header("Location: ../site/gerir_receitas.php?error=missing_params");
    exit();
}

$action = $_GET['action'];
$recipe_id = (int)$_GET['id'];
$link = new_db_connection();

if ($action === 'approve') {

    $stmt = mysqli_stmt_init($link);
    $query = "UPDATE recipes SET ref_status_id = 2 WHERE recipe_id = ?";

    if (mysqli_stmt_prepare($stmt, $query)) {
        mysqli_stmt_bind_param($stmt, 'i', $recipe_id);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: ../site/gerir_receitas.php?success=approved");
        } else {
            header("Location: ../site/gerir_receitas.php?error=db_error");
        }
        mysqli_stmt_close($stmt);
    } else {
        header("Location: ../site/gerir_receitas.php?error=db_error");
    }

} elseif ($action === 'refuse') {

    mysqli_autocommit($link, false);
    $error = false;

    $related_tables = ['recipe_ingredients', 'recipe_steps', 'recipe_likes', 'recipe_saves', 'comments'];

    foreach ($related_tables as $table) {
        $stmt = mysqli_stmt_init($link);
        $query = "DELETE FROM $table WHERE ref_recipe_id = ?";
        if (mysqli_stmt_prepare($stmt, $query)) {
            mysqli_stmt_bind_param($stmt, 'i', $recipe_id);
            if (!mysqli_stmt_execute($stmt)) {
                $error = true;
                break;
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = true;
            break;
        }
    }


    if (!$error) {
        $stmt = mysqli_stmt_init($link);
        $query = "DELETE FROM recipes WHERE recipe_id = ?";
        if (mysqli_stmt_prepare($stmt, $query)) {
            mysqli_stmt_bind_param($stmt, 'i', $recipe_id);
            if (!mysqli_stmt_execute($stmt)) {
                $error = true;
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = true;
        }
    }


    if ($error) {
        mysqli_rollback($link);
        header("Location: ../site/gerir_receitas.php?error=db_error_delete");
    } else {
        mysqli_commit($link);
        header("Location: ../site/gerir_receitas.php?success=refused");
    }

} else {

    header("Location: ../site/gerir_receitas.php?error=invalid_action");
}

mysqli_close($link);
exit();
