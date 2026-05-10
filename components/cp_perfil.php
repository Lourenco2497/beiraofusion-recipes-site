<?php
require_once '../connections/connections.php';
$conn = new_db_connection();

$user_id = $_SESSION['user_id'] ?? 0;

// receitas publicadas
$stmt_published = $conn->prepare(
    "SELECT recipe_id, title, image_url
     FROM recipes
     WHERE ref_user_id = ? AND ref_status_id = 2"
);
$stmt_published->bind_param("i", $user_id);
$stmt_published->execute();
$result_published = $stmt_published->get_result();

// receitas guardadas
$stmt_saved = $conn->prepare(
    "SELECT r.recipe_id, r.title, r.image_url
     FROM recipes r
     INNER JOIN recipe_saves rs ON r.recipe_id = rs.ref_recipe_id
     WHERE rs.ref_user_id = ?"
);
$stmt_saved->bind_param("i", $user_id);
$stmt_saved->execute();
$result_saved = $stmt_saved->get_result();

// receitas com like
$stmt_likes = $conn->prepare(
    "SELECT r.recipe_id, r.title, r.image_url
     FROM recipes r
     INNER JOIN recipe_likes rl ON r.recipe_id = rl.ref_recipe_id
     WHERE rl.ref_user_id = ?"
);
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
            echo '<div class="col-12"><p class="text-center">Ainda não publicou receitas.</p></div>';
        }
        $stmt_published->close();
        ?>
    </div>
    <div class="mt-3 pt-5"> </div>
</section>

<!-- Receitas Favoritas -->
<section id="contentFavorites" class="container recipe-cards-section mb-5" style="display: none;">
    <h4 class="text-center mb-3 visually-hidden">Receitas Favoritas</h4>
    <div class="row g-2">
        <?php
        if ($result_likes->num_rows > 0) {
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
            echo '<div class="col-12"><p class="text-center">Ainda não tem receitas favoritas.</p></div>';
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
        if ($result_saved->num_rows > 0) {
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
            echo '<div class="col-12"><p class="text-center">Ainda não tem receitas guardadas.</p></div>';
        }
        $stmt_saved->close();
        $conn->close();
        ?>
    </div>
</section>
