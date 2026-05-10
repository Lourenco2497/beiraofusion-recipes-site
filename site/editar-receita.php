<?php
session_start();
require_once '../connections/connections.php';
require_once '../components/cp_head.php';


if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../site/login.php");
    exit();
}

$recipe_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$conn = new_db_connection();

$stmt = $conn->prepare("SELECT title, prep_time, image_url, ref_difficulty_id, ref_category_recipe_id FROM recipes WHERE recipe_id = ? AND ref_user_id = ?");
$stmt->bind_param("ii", $recipe_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $prep_time, $image_url, $difficulty_id, $category_id);

if (!$stmt->fetch()) {
    echo "Receita não encontrada ou não tem permissão.";
    exit();
}
$stmt->close();

$steps = [];
$stmt_steps = $conn->prepare("SELECT step_number, description FROM recipe_steps WHERE ref_recipe_id = ? ORDER BY step_number ASC");
$stmt_steps->bind_param("i", $recipe_id);
$stmt_steps->execute();
$result_steps = $stmt_steps->get_result();
while ($row = $result_steps->fetch_assoc()) {
    $steps[] = $row;
}
$stmt_steps->close();

$ingredients = [];
$stmt_ing = $conn->prepare("SELECT quantity, unit, i.ingredient_id, i.name FROM recipe_ingredients ri JOIN ingredients i ON ri.ref_ingredient_id = i.ingredient_id WHERE ri.ref_recipe_id = ?");
$stmt_ing->bind_param("i", $recipe_id);
$stmt_ing->execute();
$result_ing = $stmt_ing->get_result();
while ($row = $result_ing->fetch_assoc()) {
    $ingredients[] = $row;
}
$stmt_ing->close();

$all_ingredients = [];
$res_all = $conn->query("SELECT ingredient_id, name FROM ingredients ORDER BY name ASC");
while ($row = $res_all->fetch_assoc()) {
    $all_ingredients[] = $row;
}

$conn->close();
?>

<body class="body-adicionar">
<div class="container-fluid bg-white">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4 py-7 px-6 position-relative mt-3">
            <form id="recipe-form" action="../components/cp_editar_receita.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">

                <a href="index.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <div class="card-upload mt-5 text-center d-flex justify-content-center align-items-center">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                        <div id="image-preview-container">
                            <label for="imagem" class="btn btn-outline-secondary rounded-circle">
                                <i class="fa-solid fa-image"></i>
                            </label>
                            <h5 class="mt-3 text-secondary small">Atualiza a imagem</h5>
                        </div>
                        <input type="file" id="imagem" name="imagem" class="d-none" accept="image/*">
                    </div>
                </div>

                <div class="mt-4">
                    <h3 class="form-label fw-medium">Título</h3>
                    <input type="text" name="titulo" class="form-control bg-warning-subtle border-0" value="<?= htmlspecialchars($title) ?>" required>
                </div>

                <div class="mt-4">
                    <h3 class="form-label fw-medium">Categoria</h3>
                    <div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ref_category_recipe_id" id="prato-principal" value="1" <?= $category_id == 2 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="prato-principal">Prato principal</label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="ref_category_recipe_id" id="sobremesa" value="3" <?= $category_id == 3 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sobremesa">Sobremesa</label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="ref_category_recipe_id" id="cocktail" value="2" <?= $category_id == 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="cocktail">Cocktail</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h3 class="form-label fw-medium">Tempo de preparação (minutos)</h3>
                    <input type="number" name="prep_time" class="form-control bg-warning-subtle border-0" value="<?= $prep_time ?>" required>
                </div>

                <div class="mt-4">
                    <h3 class="form-label fw-medium">Dificuldade</h3>
                    <div class="d-flex justify-content-between">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ref_difficulty_id" id="facil" value="1" <?= $difficulty_id == 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="facil">Fácil</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ref_difficulty_id" id="medio" value="2" <?= $difficulty_id == 2 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="medio">Médio</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ref_difficulty_id" id="dificil" value="3" <?= $difficulty_id == 3 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="dificil">Difícil</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h3 class="form-label fw-medium">Ingredientes</h3>
                    <div class="ingredient-list">
                        <?php foreach ($ingredients as $i => $ing): ?>
                            <div class="d-flex gap-2 mb-2 ingredient-row">
                                <input type="text" name="ingrediente_qt[]" class="form-control bg-warning-subtle border-0" value="<?= $ing['quantity'] ?>" placeholder="Qt." style="width: 70px">
                                <input type="text" name="ingrediente_unit[]" class="form-control bg-warning-subtle border-0" value="<?= htmlspecialchars($ing['unit']) ?>" placeholder="Unidade" style="width: 100px">
                                <select name="ingrediente_id[]" class="form-select bg-warning-subtle border-0 ingredient-select flex-grow-1 h-50">
                                    <?php foreach ($all_ingredients as $opt): ?>
                                        <option value="<?= $opt['ingredient_id'] ?>" <?= $opt['ingredient_id'] == $ing['ingredient_id'] ? 'selected' : '' ?>><?= htmlspecialchars($opt['name']) ?></option>
                                    <?php endforeach; ?>
                                    <option value="new">Adicionar novo...</option>
                                </select>
                                <input type="text" name="ingrediente_nome_novo[]" class="form-select bg-warning-subtle border-0 new-ingredient-name flex-grow-1 h-50" placeholder="Nome do novo ingrediente" style="display: none;">
                                <button type="button" class="btn bg-warning-subtle border-0 remove-ingredient">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <h3><button type="button" class="btn bg-warning border-0 d-block mx-auto mt-3" id="add-ingredient">+ Adicionar ingrediente</button></h3>
                </div>

                <div class="mt-4">
                    <h3 class="form-label fw-medium">Instruções</h3>
                    <div class="instruction-list">
                        <?php foreach ($steps as $step): ?>
                            <div class="d-flex gap-2 mb-2">
                                <input type="text" name="instrucao[]" class="form-control bg-warning-subtle border-0" value="<?= htmlspecialchars($step['description']) ?>" required>
                                <button type="button" class="btn bg-warning-subtle border-0 remove-instruction">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <h3><button type="button" class="btn bg-warning border-0 d-block mx-auto mt-3" id="add-instruction">+ Adicionar instrução</button></h3>
                </div>

                <h3><button type="submit" class="btn btn-danger d-block mx-auto mt-4 fw-medium">Guardar Alterações</button></h3>
            </form>
        </div>
    </div>
</div>

<script>
    const ingredientsData = <?= json_encode($all_ingredients) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/adicionar-receita.js"></script>
</body>
