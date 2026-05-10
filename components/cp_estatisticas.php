<?php
require_once '../connections/connections.php';
$link = new_db_connection();

// --- Get Total Users ---
$total_users = 0;
$query_users = "SELECT COUNT(user_id) FROM users";
if ($stmt_users = mysqli_prepare($link, $query_users)) {
    mysqli_stmt_execute($stmt_users);
    mysqli_stmt_bind_result($stmt_users, $total_users);
    mysqli_stmt_fetch($stmt_users);
    mysqli_stmt_close($stmt_users);
}

// --- Get Total & Weekly Recipes ---
$total_recipes = 0;
$weekly_recipes = 0;
$query_recipes = "SELECT COUNT(recipe_id), (SELECT COUNT(recipe_id) FROM recipes WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) FROM recipes";
if ($stmt_recipes = mysqli_prepare($link, $query_recipes)) {
    mysqli_stmt_execute($stmt_recipes);
    mysqli_stmt_bind_result($stmt_recipes, $total_recipes, $weekly_recipes);
    mysqli_stmt_fetch($stmt_recipes);
    mysqli_stmt_close($stmt_recipes);
}

// --- Get Recipes by Category ---
$recipe_categories = [];
$query_cat_recipes = "SELECT cr.name, COUNT(r.recipe_id) as count FROM recipes r INNER JOIN category_recipe cr ON r.ref_category_recipe_id = cr.category_recipe_id GROUP BY cr.name ORDER BY count DESC";
if ($stmt_cat_recipes = mysqli_prepare($link, $query_cat_recipes)) {
    mysqli_stmt_execute($stmt_cat_recipes);
    $result_cat = mysqli_stmt_get_result($stmt_cat_recipes);
    while ($row = mysqli_fetch_assoc($result_cat)) {
        $recipe_categories[] = $row;
    }
    mysqli_stmt_close($stmt_cat_recipes);
}

// --- Get Monthly Recipes for the last 12 months ---
$monthly_data = array_fill(0, 12, 0);
$months_labels = [];
for ($i = 11; $i >= 0; $i--) {
    $date = new DateTime("first day of -$i month");
    $months_labels[] = $date->format('M');
}

$query_monthly = "SELECT YEAR(created_at) as r_year, MONTH(created_at) as r_month, COUNT(recipe_id) as count FROM recipes WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY r_year, r_month ORDER BY r_year, r_month";
if ($stmt_monthly = mysqli_prepare($link, $query_monthly)) {
    mysqli_stmt_execute($stmt_monthly);
    $result_monthly = mysqli_stmt_get_result($stmt_monthly);
    while ($row = mysqli_fetch_assoc($result_monthly)) {
        $date = new DateTime("{$row['r_year']}-{$row['r_month']}-01");
        $now = new DateTime("first day of this month");
        $interval = $now->diff($date);
        $months_ago = ($interval->y * 12) + $interval->m;
        if ($months_ago >= 0 && $months_ago < 12) {
            $monthly_data[11 - $months_ago] = $row['count'];
        }
    }
    mysqli_stmt_close($stmt_monthly);
}
$max_monthly = max($monthly_data) > 0 ? max($monthly_data) : 1;

mysqli_close($link);
?>

<body class="body-adicionar">

<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="../site/index.php" class="back-btn me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>
    <h1 class="mb-4 big-title">ESTATÍSTICAS</h1>

    <!-- Main Stats -->
    <div class="row g-3 g-md-4 text-center mb-4 mb-md-5">
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-3">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h2 class="display-5 fw-bold"><?= $total_users ?></h2>
                    <p class="text-muted mb-0">Utilizadores Registados</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-3">
                    <i class="fas fa-book-open fa-3x text-success mb-3"></i>
                    <h2 class="display-5 fw-bold"><?= $total_recipes ?></h2>
                    <p class="text-muted mb-0">Total de Receitas</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-3">
                    <i class="fas fa-calendar-week fa-3x text-info mb-3"></i>
                    <h2 class="display-5 fw-bold"><?= $weekly_recipes ?></h2>
                    <p class="text-muted mb-0">Novas Receitas (Semana)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Chart -->
    <div class="card shadow-sm mb-4 mb-md-5">
        <div class="card-body p-3 p-md-4">
            <h3 class="card-title text-center mb-4">Publicações Mensais</h3>
            <div style="overflow-x: auto; padding-bottom: 1rem;">
                <div style="min-width: 500px;">
                    <div class="bar-chart-container d-flex justify-content-between align-items-end px-3" style="height: 250px; border-bottom: 1px solid #dee2e6;">
                        <?php foreach ($monthly_data as $count): ?>
                            <div class="bar bg-primary rounded-top" style="width: 6%; height: <?= ($count / $max_monthly) * 100 ?>%;" data-bs-toggle="tooltip" title="<?= $count ?> publicações"></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="chart-labels d-flex justify-content-between mt-2 px-3">
                        <?php foreach ($months_labels as $label): ?>
                            <span class="text-muted small"><?= $label ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <div class="card shadow-sm">
        <div class="card-body p-3 p-md-4">
            <h3 class="card-title text-center mb-4">Publicações por Categoria</h3>
            <ul class="list-group list-group-flush">
                <?php if (!empty($recipe_categories)): ?>
                    <?php foreach ($recipe_categories as $cat): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-1">
                            <?= htmlspecialchars($cat['name']) ?>
                            <span class="badge bg-secondary rounded-pill"><?= $cat['count'] ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-center text-muted">Nenhuma categoria encontrada.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>


<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>