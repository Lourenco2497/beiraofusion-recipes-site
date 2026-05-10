<?php
session_start();
require_once '../connections/connections.php';
$link = new_db_connection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido.");
}

$recipe_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'] ?? 0;
$is_admin = isset($_SESSION['ref_type_id']) && $_SESSION['ref_type_id'] == 2;

// obter detalhes da receita
$sql = "
    SELECT r.*, u.username, u.profile_image, d.name AS difficulty
    FROM recipes r
    JOIN users u ON r.ref_user_id = u.user_id
    JOIN difficulty d ON r.ref_difficulty_id = d.difficulty_id
    WHERE r.recipe_id = ?
";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();
$stmt->close();

if (!$recipe) {
    die("Receita não encontrada.");
}

// verificar se a receita está salva pelo utilizador
$is_saved = false;
if ($user_id > 0) {
    $sql_save = "SELECT ref_user_id FROM recipe_saves WHERE ref_user_id = ? AND ref_recipe_id = ?";
    $stmt_save = $link->prepare($sql_save);
    $stmt_save->bind_param("ii", $user_id, $recipe_id);
    $stmt_save->execute();
    $result_save = $stmt_save->get_result();
    if ($result_save->num_rows > 0) {
        $is_saved = true;
    }
    $stmt_save->close();
}

// ingredientes
$sql = "
    SELECT i.name, ri.quantity, ri.unit
    FROM recipe_ingredients ri
    JOIN ingredients i ON ri.ref_ingredient_id = i.ingredient_id
    WHERE ri.ref_recipe_id = ?
";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$ingredientes = $stmt->get_result();
$stmt->close();

// passos
$sql = "
    SELECT step_number, description
    FROM recipe_steps
    WHERE ref_recipe_id = ?
    ORDER BY step_number
";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$passos = $stmt->get_result();
$stmt->close();

// contar comentários
$sql_comments = "SELECT COUNT(*) AS comment_count FROM comments WHERE ref_recipe_id = ?";
$stmt_comments = $link->prepare($sql_comments);
$stmt_comments->bind_param("i", $recipe_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();
$comment_count = $result_comments->fetch_assoc()['comment_count'];
$stmt_comments->close();

// Verifica se o user já deu like
$stmt = $link->prepare("SELECT 1 FROM recipe_likes WHERE ref_user_id = ? AND ref_recipe_id = ?");
$stmt->bind_param("ii", $user_id, $recipe_id);
$stmt->execute();
$user_has_liked = $stmt->get_result()->num_rows > 0;

// Contar likes
$stmt = $link->prepare("SELECT COUNT(*) FROM recipe_likes WHERE ref_recipe_id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$stmt->bind_result($likes_count);
$stmt->fetch();

?>

<body>
<div class="container-fluid p-0">

    <div class="recipe-header position-relative d-flex justify-content-between">
        <img src="../imgs/receitas/<?= htmlspecialchars($recipe['image_url']) ?>" alt="<?= htmlspecialchars($recipe['title']) ?>" class="w-100 h-100 object-fit-cover">
        <a href="index.php" class="back-btn position-absolute"><i class="fas fa-arrow-left"></i></a>
        <?php if ($recipe['ref_user_id'] == $user_id): ?>
            <a href="../site/editar-receita.php?id=<?= $recipe_id ?>" class="edit-recipe-btn position-absolute d-flex justify-content-center align-items-center">
                <i class="fa-solid fa-ellipsis fa-2x"></i>
            </a>
        <?php endif; ?>

    </div>

    <div class="recipe-content bg-white">
        <div class="container pe-4 ps-4">
            <div class="d-flex justify-content-between align-items-start pt-4">
                <h2 class="recipe-title flex-grow-1 me-3 mb-2"><?= htmlspecialchars(mb_strtoupper($recipe['title'])) ?></h2>
                <a href="#" id="bookmark-toggle" data-recipe-id="<?= $recipe_id ?>">
                    <i class="fa-2x text-dark <?= $is_saved ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark' ?>"></i>
                </a>
            </div>

            <div class="d-flex align-items-center mb-3">
                <div class="time-info">
                    <i class="fas fa-clock"></i>
                    <span class="ms-1"><?= htmlspecialchars($recipe['prep_time']) ?> min</span>
                </div>
                <span class="difficulty ms-2">• <?= htmlspecialchars($recipe['difficulty']) ?></span>
            </div>

            <div class="d-flex align-items-center mb-4">
                <div class="author-img me-2">
                    <div class="w-100 h-100 bg-warning d-flex align-items-center justify-content-center">
                        <img src="../<?= htmlspecialchars($recipe['profile_image'] ?: 'imgs/perfis/default.jpg') ?>" alt="">
                    </div>
                </div>
                <a class="texto" href="../site/perfil_utilizador.php?id=<?= $recipe['ref_user_id'] ?>"><h5 class="m-0"><?= htmlspecialchars($recipe['username']) ?></h5></a>
            </div>

            <div class="mb-4">
                <h3 class="mb-3 fs-4">Ingredientes</h3>
                <div class="ingredients-grid">
                    <?php foreach ($ingredientes as $ing): ?>
                        <h4 class="ingredient-measure"><?= htmlspecialchars($ing['quantity']) . ' ' . htmlspecialchars($ing['unit']) ?></h4>
                        <h5 class="ingredient-name"><?= htmlspecialchars($ing['name']) ?></h5>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="preparation-section mb-5">
                <h3 class="mb-3 fs-4">Preparação</h3>
                <div class="steps">
                    <?php foreach ($passos as $passo): ?>
                        <div class="step mb-4">
                            <div class="step-number"><?= $passo['step_number'] ?></div>
                            <div class="step-text"><?= htmlspecialchars($passo['description']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- DEBUG -->
        <script>console.log("ID da receita no botão:", "<?= $recipe_id ?>");</script>

    </div>

    <?php if ($is_admin && $recipe['ref_status_id'] == 1): ?>
        <div class="admin-actions bg-white position-fixed bottom-0 start-0 end-0 p-3 border-top">
            <div class="container">
                <div class="d-flex justify-content-center align-items-center gap-3">
                    <a href="../scripts/sc_gerir_receitas.php?action=approve&id=<?= $recipe['recipe_id'] ?>" class="btn btn-success w-100">Aprovar Receita</a>
                    <a href="../scripts/sc_gerir_receitas.php?action=refuse&id=<?= $recipe['recipe_id'] ?>" class="btn btn-danger w-100" onclick="return confirm('Tem a certeza que quer recusar e apagar permanentemente esta receita?');">Recusar Receita</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="social-actions bg-white position-fixed bottom-0 start-0 end-0 p-3 border-top">
            <div class="container">
                <div class="d-flex justify-content-center align-items-center gap-4">
                    <button class="action-btn favorite-btn" data-recipe-id="<?= $recipe_id ?>">
                        <i class="fa<?= $user_has_liked ? '-solid' : '-regular' ?> fa-heart"></i>
                        <span><?= $likes_count ?></span>
                    </button>
                    <a href="../site/comentarios.php?id=<?= $recipe_id ?>" class="action-btn text-decoration-none text-dark"><i class="far fa-comment"></i><span><?= $comment_count ?></span></a>
                    <button class="action-btn"><i class="fa-solid fa-share-from-square"></i></button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    const userLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
</script>
<script src="../js/like.js"></script>
<script src="../js/bookmark.js"></script>
</body>
</html>