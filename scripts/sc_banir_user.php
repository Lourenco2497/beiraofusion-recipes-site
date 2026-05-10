<?php
session_start();
require_once '../connections/connections.php';


if (!isset($_SESSION['user_id']) || !isset($_SESSION['ref_type_id']) || $_SESSION['ref_type_id'] != 2) {
    header("Location: ../site/index.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reported_id'])) {
    $reported_id = filter_var($_POST['reported_id'], FILTER_VALIDATE_INT);

    if ($reported_id === false) {
        header("Location: ../site/gerir_denuncias.php?error=invalid_ids");
        exit();
    }

    $link = new_db_connection();
    $link->begin_transaction();

    try {

        $stmt_get_recipes = $link->prepare("SELECT recipe_id FROM recipes WHERE ref_user_id = ?");
        $stmt_get_recipes->bind_param("i", $reported_id);
        $stmt_get_recipes->execute();
        $result_recipes = $stmt_get_recipes->get_result();
        $recipe_ids = [];
        while ($row = $result_recipes->fetch_assoc()) {
            $recipe_ids[] = $row['recipe_id'];
        }
        $stmt_get_recipes->close();


        if (!empty($recipe_ids)) {
            $ids_placeholder = implode(',', array_fill(0, count($recipe_ids), '?'));
            $ids_types = str_repeat('i', count($recipe_ids));

            $tables_to_delete_from = ['comments', 'recipe_ingredients', 'recipe_likes', 'recipe_saves', 'recipe_steps'];
            foreach ($tables_to_delete_from as $table) {
                $stmt = $link->prepare("DELETE FROM $table WHERE ref_recipe_id IN ($ids_placeholder)");
                $stmt->bind_param($ids_types, ...$recipe_ids);
                $stmt->execute();
                $stmt->close();
            }
        }
        

        $stmt_recipes = $link->prepare("DELETE FROM recipes WHERE ref_user_id = ?");
        $stmt_recipes->bind_param("i", $reported_id);
        $stmt_recipes->execute();
        $stmt_recipes->close();

        $tables_to_delete_from_user = [
            'comments' => 'ref_user_id',
            'recipe_likes' => 'ref_user_id',
            'recipe_saves' => 'ref_user_id',
            'vouchers_users' => 'ref_user_id'
        ];
        foreach ($tables_to_delete_from_user as $table => $column) {
            $stmt = $link->prepare("DELETE FROM $table WHERE $column = ?");
            $stmt->bind_param("i", $reported_id);
            $stmt->execute();
            $stmt->close();
        }


        $stmt = $link->prepare("DELETE FROM follows WHERE follower_id = ? OR following_id = ?");
        $stmt->bind_param("ii", $reported_id, $reported_id);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $link->prepare("DELETE FROM reports WHERE ref_reporter_id = ? OR ref_reported_id = ?");
        $stmt->bind_param("ii", $reported_id, $reported_id);
        $stmt->execute();
        $stmt->close();
        

        $stmt_ban = $link->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt_ban->bind_param("i", $reported_id);
        $stmt_ban->execute();
        $stmt_ban->close();

        $link->commit();
        header("Location: ../site/gerir_denuncias.php?success=user_banned");

    } catch (mysqli_sql_exception $exception) {
        $link->rollback();

        error_log("Ban failed: " . $exception->getMessage());
        header("Location: ../site/gerir_denuncias.php?error=ban_failed");
    }

    $link->close();
} else {
    header("Location: ../site/gerir_denuncias.php");
    exit();
}