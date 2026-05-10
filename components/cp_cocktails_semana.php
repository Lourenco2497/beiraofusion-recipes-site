<?php
require_once '../connections/connections.php';
$link = new_db_connection();

$stmt = mysqli_stmt_init($link);
$query = "SELECT title, image_url, recipe_id FROM recipes WHERE ref_category_recipe_id = 2 AND ref_status_id = 2 ORDER BY RAND() LIMIT 5";


if (mysqli_stmt_prepare($stmt, $query)) {
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $title, $image_url, $id);

    if (mysqli_stmt_num_rows($stmt) > 0): ?>
        <h3 class="mb-3 pt-3">Cocktails da semana</h3>
        <div class="d-flex overflow-auto gap-3 pb-2 hide-scrollbar">
            <?php while (mysqli_stmt_fetch($stmt)): ?>
            <a href="../site/receita_detail.php?id=<?= $id?>">
                <div class="card card-carrousel">
                    <img src="../imgs/receitas/<?= htmlspecialchars($image_url) ?>" class="card-img-top" alt="<?= htmlspecialchars($title) ?>">
                    <div class="d-flex p-2 align-items-center justify-content-between">
                        <div class="small-wrapper">
                            <h4 class="card-title"><?= htmlspecialchars($title) ?></h4>
                        </div>
                    </div>
                </div>
            </a>

                <script src="../js/like.js"></script>

            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning mt-5">Nenhum cocktail disponível.</div>
    <?php endif;

    mysqli_stmt_close($stmt);
} else {
    echo "<div class='alert alert-danger'>Erro ao preparar a consulta de cocktails.</div>";
}
?>