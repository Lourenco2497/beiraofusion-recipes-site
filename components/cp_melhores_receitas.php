<?php
// conexão à base de dados
require_once '../connections/connections.php';
$link = new_db_connection();

$stmt = mysqli_stmt_init($link);

$query = "SELECT r.title, r.recipe_id, r.prep_time, r.image_url, d.name AS difficulty
          FROM recipes r
          INNER JOIN difficulty d ON r.ref_difficulty_id = d.difficulty_id
          WHERE r.ref_status_id = 2
          ORDER BY r.created_at DESC
          LIMIT 5";

$num_resultados = 0;


if (mysqli_stmt_prepare($stmt, $query)) {
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
mysqli_stmt_bind_result($stmt, $title, $id, $prep_time, $image_url, $difficulty);
$num_resultados = mysqli_stmt_num_rows($stmt);
?>

    <div id="main-content">
<div class="container mt-3">
    <h1 class="mb-3">MELHORES RECEITAS</h1>
    <div class="carousel-container">
        <div class="carousel-wrapper" id="carouselWrapper">
            <?php while (mysqli_stmt_fetch($stmt)) : ?>

                <div class="carousel-slide">
                    <a href="../site/receita_detail.php?id=<?= $id?>">
                        <div class="melhor-card">
                            <img src="../imgs/receitas/<?= htmlspecialchars($image_url) ?>" alt="<?= htmlspecialchars($title) ?>">
                        <div class="gradient-overlay"></div>
                        <div class="card-content">
                            <div class="top-icons">
                                <div id="melhor-title">
                                <h2 class="card-title-b"><?= htmlspecialchars($title) ?></h2>
                                </div>
                            </div>
                            <div class="bottom-icons">
                                  <h5><i class="fa-regular fa-clock me-1"></i> <?= htmlspecialchars($prep_time) ?> min · <?= htmlspecialchars($difficulty) ?></h5>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>

            <?php endwhile; ?>
        </div>
    </div>

    <!-- Pagination Dots -->
    <div class="pagination-dots">
        <?php for ($i = 0; $i < $num_resultados; $i++) : ?>
            <div class="dot<?= $i === 0 ? ' active' : '' ?>" data-slide="<?= $i ?>"></div>
        <?php endfor; ?>
    </div>

    <!-- JS do Carrossel -->
    <script src="../js/carousel.js"></script>
    <script src="../js/like.js"></script>

    <?php
    mysqli_stmt_close($stmt);
    } else {
        echo "<div class='alert alert-danger'>Erro ao preparar a query.</div>";
    }
    ?>