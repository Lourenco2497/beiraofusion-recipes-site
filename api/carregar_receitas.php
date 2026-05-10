<?php
require_once '../connections/connections.php';

$link = new_db_connection();
$tipo = $_GET['tipo'] ?? 'cocktail';
$pagina = intval($_GET['pagina'] ?? 0);
$limite = 6;
$offset = $pagina * $limite;

$stmt = mysqli_stmt_init($link);

if ($tipo === 'cocktail') {
    $query = "SELECT title, image_url, recipe_id FROM recipes WHERE ref_category_recipe_id = ? ORDER BY RAND() LIMIT ? OFFSET ?";
    mysqli_stmt_prepare($stmt, $query);
    $categoria = 2;
    mysqli_stmt_bind_param($stmt, 'iii', $categoria, $limite, $offset);
} else {
    $query = "SELECT title, image_url, recipe_id FROM recipes WHERE ref_category_recipe_id IN (?, ?) ORDER BY RAND() LIMIT ? OFFSET ?";
    mysqli_stmt_prepare($stmt, $query);
    $cat1 = 1; $cat2 = 3;
    mysqli_stmt_bind_param($stmt, 'iiii', $cat1, $cat2, $limite, $offset);
}

mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $title, $image_url, $id);

while (mysqli_stmt_fetch($stmt)):
    ?>

    <div class="cocktail-card mt-3">
        <a href="../site/receita_detail.php?id=<?= $id?>">
        <img src="../imgs/receitas/<?= htmlspecialchars($image_url) ?>" alt="<?= htmlspecialchars($title) ?>">
        <div class="overlay"></div>
        <div class="cocktail-info mb-2">
            <h3 class="cocktail-name mb-3"><?= htmlspecialchars($title) ?></h3>
        </div>
        </a>
    </div>


<?php
endwhile;
mysqli_stmt_close($stmt);