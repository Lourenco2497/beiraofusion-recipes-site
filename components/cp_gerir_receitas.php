<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../connections/connections.php';

// Admin check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['ref_type_id']) || $_SESSION['ref_type_id'] != 2) {
    header("Location: ../site/index.php");
    exit();
}

// Feedback messages for the user
$feedback_messages = [
    'approved' => ['type' => 'success', 'text' => 'A receita foi aprovada com sucesso.'],
    'refused' => ['type' => 'success', 'text' => 'A receita foi recusada e removida com sucesso.'],
    'db_error' => ['type' => 'danger', 'text' => 'Ocorreu um erro na base de dados. Por favor, tente novamente.'],
    'db_error_delete' => ['type' => 'danger', 'text' => 'Ocorreu um erro ao remover a receita.'],
    'missing_params' => ['type' => 'warning', 'text' => 'Ação inválida. Faltam parâmetros.'],
    'invalid_action' => ['type' => 'warning', 'text' => 'Ação desconhecida.'],
];

$message_key = $_GET['success'] ?? $_GET['error'] ?? null;
$message = $feedback_messages[$message_key] ?? null;

$link = new_db_connection();

// Fetch pending recipes (status = 1) along with the author's username
$query = "SELECT 
            r.recipe_id, 
            r.title,
            r.image_url,
            u.username as author_username
          FROM recipes r
          JOIN users u ON r.ref_user_id = u.user_id
          WHERE r.ref_status_id = 1";

$stmt = mysqli_stmt_init($link);
if (mysqli_stmt_prepare($stmt, $query)) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}
?>

    <div class="recipe-management-container">
        <div class="mt-3 mb-4">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>

        <div class="recipe-list">
            <h1 class="mt-4 mb-3">GESTÃO DE RECEITAS</h1>
        </div>

        <!-- Display Feedback Message -->
        <?php if ($message): ?>
            <div class="alert alert-<?= htmlspecialchars($message['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message['text']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div id="receitas-pendentes">
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $image_path = empty($row['image_url']) ? '../imgs/receitas/default.jpg' : '../imgs/receitas/' . htmlspecialchars($row['image_url']);
                    ?>
                    <div class="card_denuncia mb-3"> <!-- Reusing class for similar styling -->
                        <div class="card_denuncia-info">
                            <div class="recipe-image">
                                <img src="<?= $image_path ?>" alt="Foto da Receita" class="recipe-img">
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                                <span>Enviado por: <?= htmlspecialchars($row['author_username']) ?></span>
                            </div>
                        </div>
                        <div class="recipe-actions d-flex flex-column flex-sm-row gap-2">
                            <a href="../site/receita_detail.php?id=<?= $row['recipe_id'] ?>" class="btn btn-info btn-sm flex-fill">Ver Detalhes</a>
                            <a href="../scripts/sc_gerir_receitas.php?action=approve&id=<?= $row['recipe_id'] ?>" class="btn btn-success btn-sm flex-fill">Aprovar</a>
                            <a href="../scripts/sc_gerir_receitas.php?action=refuse&id=<?= $row['recipe_id'] ?>" class="btn text-white bg-danger btn-sm flex-fill" onclick="return confirm('Tem a certeza que quer recusar e apagar esta receita?');">Recusar</a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='alert alert-info'>Não existem receitas pendentes para aprovação.</div>";
            }
            mysqli_stmt_close($stmt);
            ?>
        </div>
    </div>
<?php
mysqli_close($link);
?>