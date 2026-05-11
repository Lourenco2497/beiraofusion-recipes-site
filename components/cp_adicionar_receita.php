<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../connections/connections.php';

$conn = new_db_connection();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../site/login.php?message=2");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();
    try {

        $ref_user_id = $_SESSION['user_id'];
        $title = $_POST['titulo'];
        $prep_time = (int)$_POST['prep_time'];
        $ref_difficulty_id = (int)$_POST['ref_difficulty_id'];
        $ref_category_recipe_id = (int)$_POST['ref_category_recipe_id'];
        $ref_status_id = 1;
        $image_url = null;

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "../imgs/receitas/";
                if (!is_dir($target_dir)) {
                    if (!mkdir($target_dir, 0755, true)) {
                        throw new Exception("Falha ao criar o diretório de imagens. Verifique as permissões do servidor.");
                    }
                }

                $image_basename = basename($_FILES["imagem"]["name"]);
                $image_url = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9\.\-\_]/", "", $image_basename);
                $target_file = $target_dir . $image_url;

                if (!move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
                    throw new Exception("Houve um erro ao mover a sua imagem. Verifique as permissões do diretório de destino ('" . $target_dir . "').");
                }
            } else {

                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE   => "O ficheiro excede o limite de tamanho do servidor (upload_max_filesize).",
                    UPLOAD_ERR_FORM_SIZE  => "O ficheiro excede o limite de tamanho do formulário.",
                    UPLOAD_ERR_PARTIAL    => "O ficheiro foi apenas parcialmente carregado.",
                    UPLOAD_ERR_CANT_WRITE => "Falha ao escrever o ficheiro no disco.",
                    UPLOAD_ERR_EXTENSION  => "Uma extensão PHP impediu o upload do ficheiro.",
                ];
                $error_code = $_FILES['imagem']['error'];
                $errorMessage = $upload_errors[$error_code] ?? "Ocorreu um erro desconhecido no upload da imagem.";
                throw new Exception($errorMessage);
            }
        }

        $stmt_recipe = $conn->prepare(
            "INSERT INTO recipes (ref_user_id, title, prep_time, image_url, ref_difficulty_id, ref_status_id, ref_category_recipe_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt_recipe->bind_param("isissii", $ref_user_id, $title, $prep_time, $image_url, $ref_difficulty_id, $ref_status_id, $ref_category_recipe_id);
        $stmt_recipe->execute();
        $new_recipe_id = $conn->insert_id;
        $stmt_recipe->close();

        // instruções
        if (!empty($_POST['instrucao'])) {
            $stmt_steps = $conn->prepare("INSERT INTO recipe_steps (ref_recipe_id, step_number, description) VALUES (?, ?, ?)");
            foreach ($_POST['instrucao'] as $index => $description) {
                if (!empty(trim($description))) {
                    $step_number = $index + 1;
                    $stmt_steps->bind_param("iis", $new_recipe_id, $step_number, $description);
                    $stmt_steps->execute();
                }
            }
            $stmt_steps->close();
        }

        // ingredientes
        if (isset($_POST['ingrediente_qt']) && is_array($_POST['ingrediente_qt'])) {
            $stmt_find_ing_by_name = $conn->prepare("SELECT ingredient_id FROM ingredients WHERE name = ?");
            $stmt_add_ing = $conn->prepare("INSERT INTO ingredients (name, ref_category_ingredients_id) VALUES (?, ?)");
            $stmt_link_ing = $conn->prepare("INSERT INTO recipe_ingredients (ref_recipe_id, ref_ingredient_id, quantity, unit) VALUES (?, ?, ?, ?)");
            $default_ingredient_category_id = 1;

            foreach ($_POST['ingrediente_qt'] as $index => $quantity_value) {
                $quantity = (float)($quantity_value ?? 0);
                $unit = $_POST['ingrediente_unit'][$index] ?? null;
                $selected_ingredient_id = $_POST['ingrediente_id'][$index] ?? null;
                $new_ingredient_name = trim($_POST['ingrediente_nome_novo'][$index] ?? '');
                $ingredient_id_to_link = null;

                if (!empty($new_ingredient_name)) {
                    $stmt_find_ing_by_name->bind_param("s", $new_ingredient_name);
                    $stmt_find_ing_by_name->execute();
                    $result_find = $stmt_find_ing_by_name->get_result();
                    if ($row_find = $result_find->fetch_assoc()) {
                        $ingredient_id_to_link = $row_find['ingredient_id'];
                    } else {
                        $stmt_add_ing->bind_param("si", $new_ingredient_name, $default_ingredient_category_id);
                        $stmt_add_ing->execute();
                        if ($stmt_add_ing->affected_rows > 0) {
                            $ingredient_id_to_link = $conn->insert_id;
                        } else {
                            throw new Exception("Falha ao adicionar novo ingrediente: " . $new_ingredient_name);
                        }
                    }
                    $result_find->free();
                } elseif (!empty($selected_ingredient_id) && $selected_ingredient_id !== 'new') {
                    $ingredient_id_to_link = (int)$selected_ingredient_id;
                }

                if ($ingredient_id_to_link !== null && $quantity > 0) {
                    $stmt_link_ing->bind_param("iids", $new_recipe_id, $ingredient_id_to_link, $quantity, $unit);
                    $stmt_link_ing->execute();
                    if ($stmt_link_ing->error) {
                        throw new Exception("Erro ao ligar ingrediente à receita: " . $stmt_link_ing->error);
                    }
                }
            }
            $stmt_find_ing_by_name->close();
            $stmt_add_ing->close();
            $stmt_link_ing->close();
        }

        $conn->commit();
        header("Location: ../site/adicionar-receita.php?status=success");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in cp_adicionar_receita.php: " . $e->getMessage());
        $errorMessage = "Ocorreu um erro ao guardar a receita. Detalhe: " . $e->getMessage();
        header("Location: ../site/adicionar-receita.php?status=error&message=" . urlencode($errorMessage));
        exit();
    }

    $conn->close();
} else {
    header("Location: ../site/adicionar-receita.php");
    exit();
}
?>