<?php
session_start();
require_once '../connections/connections.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../site/login.php");
    exit();
}

$link = new_db_connection();
$recipe_id = (int)$_POST['recipe_id'];
$user_id = $_SESSION['user_id'];
$title = $_POST['titulo'];
$prep_time = (int)$_POST['prep_time'];
$difficulty = (int)$_POST['ref_difficulty_id'];
$category = (int)$_POST['ref_category_recipe_id'];
$image_url = null;

$link->begin_transaction();
try {
    // upload nova imagem se enviada
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../imgs/receitas/";
        $image_basename = basename($_FILES["imagem"]["name"]);
        $image_url = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9\.\-_]/", "", $image_basename);
        $target_file = $target_dir . $image_url;

        if (!move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
            throw new Exception("Erro ao mover imagem.");
        }
    }

    // update receita
    $sql = "UPDATE recipes SET title=?, prep_time=?, ref_difficulty_id=?, ref_category_recipe_id=?" .
        ($image_url ? ", image_url=?" : "") .
        " WHERE recipe_id=? AND ref_user_id=?";
    $stmt = $link->prepare($sql);
    if ($image_url) {
        $stmt->bind_param("siiiisii", $title, $prep_time, $difficulty, $category, $image_url, $recipe_id, $user_id);
    } else {
        $stmt->bind_param("siiiiii", $title, $prep_time, $difficulty, $category, $recipe_id, $user_id);
    }
    if (!$stmt->execute()) {
        throw new Exception("Erro no update da receita: " . $stmt->error);
    }
    $stmt->close();

    // limpar e reinserir instruções
    $link->query("DELETE FROM recipe_steps WHERE ref_recipe_id = $recipe_id");
    $stmt_step = $link->prepare("INSERT INTO recipe_steps (ref_recipe_id, step_number, description) VALUES (?, ?, ?)");
    foreach ($_POST['instrucao'] as $i => $desc) {
        $step = $i + 1;
        $stmt_step->bind_param("iis", $recipe_id, $step, $desc);
        $stmt_step->execute();
    }
    $stmt_step->close();

    // limpar e reinserir ingredientes
    $link->query("DELETE FROM recipe_ingredients WHERE ref_recipe_id = $recipe_id");
    $stmt_ing = $link->prepare("INSERT INTO recipe_ingredients (ref_recipe_id, ref_ingredient_id, quantity, unit) VALUES (?, ?, ?, ?)");
    foreach ($_POST['ingrediente_id'] as $i => $id_ing) {
        if ($id_ing !== 'new') {
            $qt = (float)$_POST['ingrediente_qt'][$i];
            $unit = $_POST['ingrediente_unit'][$i];
            $stmt_ing->bind_param("iids", $recipe_id, $id_ing, $qt, $unit);
            $stmt_ing->execute();
        }
    }
    $stmt_ing->close();

    $link->commit();
    header("Location: ../site/index.php?status=edit_success");
    exit();

} catch (Exception $e) {
    $link->rollback();
    error_log("Erro ao editar receita: " . $e->getMessage());
    header("Location: ../site/index.php?status=edit_error&message=" . urlencode($e->getMessage()));
    exit();
} finally {
    $link->close();
}
?>
