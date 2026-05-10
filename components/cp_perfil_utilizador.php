<?php
require_once '../connections/connections.php';
$link = new_db_connection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido.");
}

$user_id = intval($_GET['id']);

// buscar preferências de privacidade
$stmt_priv = $link->prepare("SELECT show_saved_recipes, show_liked_recipes FROM users WHERE user_id = ?");
$stmt_priv->bind_param("i", $user_id);
$stmt_priv->execute();
$stmt_priv->bind_result($show_saved, $show_liked);
$stmt_priv->fetch();
$stmt_priv->close();

// receitas publicadas
$stmt_published = $link->prepare("
    SELECT recipe_id, title, image_url
    FROM recipes
    WHERE ref_user_id = ? AND ref_status_id = 2
");
$stmt_published->bind_param("i", $user_id);
$stmt_published->execute();
$result_published = $stmt_published->get_result();

// receitas guardadas
$stmt_saved = $link->prepare("
    SELECT r.recipe_id, r.title, r.image_url
    FROM recipes r
    INNER JOIN recipe_saves rs ON r.recipe_id = rs.ref_recipe_id
    WHERE rs.ref_user_id = ?
");
$stmt_saved->bind_param("i", $user_id);
$stmt_saved->execute();
$result_saved = $stmt_saved->get_result();

// receitas com like
$stmt_likes = $link->prepare("
    SELECT r.recipe_id, r.title, r.image_url
    FROM recipes r
    INNER JOIN recipe_likes rl ON r.recipe_id = rl.ref_recipe_id
    WHERE rl.ref_user_id = ?
");
$stmt_likes->bind_param("i", $user_id);
$stmt_likes->execute();
$result_likes = $stmt_likes->get_result();
?>

<div class="container">
    <hr class="divider">
</div>

<section class="container nav-tabs-section mb-4">
    <div class="row text-center">
        <div class="col-4">
            <div class="nav-tab">
                <i id="tabPublished" class="bi bi-grid-3x3 fs-2"></i>
            </div>
        </div>
        <div class="col-4">
            <div class="nav-tab">
                <i id="tabFavorites" class="bi bi-heart fs-2"></i>
            </div>
        </div>
        <div class="col-4">
            <div class="nav-tab">
                <i id="tabSaved" class="bi bi-bookmark fs-2"></i>
            </div>
        </div>
    </div>
</section>

<!-- Receitas Publicadas -->
<section id="contentPublished" class="container recipe-cards-section mb-5">
    <h4 class="text-center mb-3 visually-hidden">Receitas Publicadas</h4>
    <div class="row g-2">
        <?php
        if ($result_published->num_rows > 0) {
            while ($row = $result_published->fetch_assoc()) {
                $recipe_title = htmlspecialchars($row['title']);
                $recipe_image = htmlspecialchars($row['image_url']);
                $recipe_id = $row['recipe_id'];
                $image_path = "../imgs/receitas/" . $recipe_image;
                ?>
                <div class="col-6">
                    <a href="../site/receita_detail.php?id=<?= $recipe_id ?>" class="text-decoration-none">
                        <div class="card recipe-card shadow-sm border-0">
                            <div class="position-relative">
                                <img src="<?= $image_path ?>" class="receita-imagem-fixa rounded-top" alt="<?= $recipe_title ?>">
                            </div>
                            <div class="card-body p-2">
                                <h5 class="card-title mb-1"><?= $recipe_title ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12"><p class="text-center">Este utilizador ainda não publicou receitas.</p></div>';
        }
        $stmt_published->close();
        ?>
    </div>
</section>

<!-- Receitas com Like -->
<section id="contentFavorites" class="container recipe-cards-section mb-5" style="display: none;">
    <h4 class="text-center mb-3 visually-hidden">Receitas com Like</h4>
    <div class="row g-2">
        <?php
        if (!$show_liked) {
            echo '<div class="col-12"><p class="text-center text-muted">Esta secção é privada.</p></div>';
        } elseif ($result_likes->num_rows > 0) {
            while ($row = $result_likes->fetch_assoc()) {
                $recipe_title = htmlspecialchars($row['title']);
                $recipe_image = htmlspecialchars($row['image_url']);
                $recipe_id = $row['recipe_id'];
                $image_path = "../imgs/receitas/" . $recipe_image;
                ?>
                <div class="col-6">
                    <a href="../site/receita_detail.php?id=<?= $recipe_id ?>" class="text-decoration-none">
                        <div class="card recipe-card shadow-sm border-0">
                            <div class="position-relative">
                                <img src="<?= $image_path ?>" class="receita-imagem-fixa rounded-top" alt="<?= $recipe_title ?>">
                            </div>
                            <div class="card-body p-2">
                                <h5 class="card-title mb-1"><?= $recipe_title ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12"><p class="text-center">Este utilizador ainda não tem receitas com like.</p></div>';
        }
        $stmt_likes->close();
        ?>
    </div>
</section>

<!-- Receitas Guardadas -->
<section id="contentSaved" class="container recipe-cards-section mb-5" style="display: none;">
    <h4 class="text-center mb-3 visually-hidden">Receitas Guardadas</h4>
    <div class="row g-2">
        <?php
        if (!$show_saved) {
            echo '<div class="col-12"><p class="text-center text-muted">Esta secção é privada.</p></div>';
        } elseif ($result_saved->num_rows > 0) {
            while ($row = $result_saved->fetch_assoc()) {
                $recipe_title = htmlspecialchars($row['title']);
                $recipe_image = htmlspecialchars($row['image_url']);
                $recipe_id = $row['recipe_id'];
                $image_path = "../imgs/receitas/" . $recipe_image;
                ?>
                <div class="col-6">
                    <a href="../site/receita_detail.php?id=<?= $recipe_id ?>" class="text-decoration-none">
                        <div class="card recipe-card shadow-sm border-0">
                            <div class="position-relative">
                                <img src="<?= $image_path ?>" class="receita-imagem-fixa rounded-top" alt="<?= $recipe_title ?>">
                            </div>
                            <div class="card-body p-2">
                                <h5 class="card-title mb-1"><?= $recipe_title ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12"><p class="text-center">Este utilizador ainda não tem receitas guardadas.</p></div>';
        }
        $stmt_saved->close();
        $link->close();
        ?>
    </div>
</section>
