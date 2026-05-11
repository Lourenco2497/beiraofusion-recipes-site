<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../connections/connections.php';

// Admin check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['ref_type_id']) || $_SESSION['ref_type_id'] != 2) {
    header("Location: ../site/index.php");
    exit();
}

// Feedback messages
$feedback_messages = [
    'user_banned' => ['type' => 'success', 'text' => 'O utilizador foi banido e todas as suas denúncias foram removidas com sucesso.'],
    'report_rejected' => ['type' => 'success', 'text' => 'A denúncia foi rejeitada e removida com sucesso.'],
    'report_deleted' => ['type' => 'success', 'text' => 'A denúncia foi removida com sucesso sem banir o utilizador.'],
    'ban_failed' => ['type' => 'danger', 'text' => 'Ocorreu um erro ao tentar banir o utilizador. A operação foi cancelada.'],
    'db_error' => ['type' => 'danger', 'text' => 'Ocorreu um erro na base de dados. Por favor, tente novamente.'],
];

$message_key = $_GET['success'] ?? $_GET['error'] ?? null;
$message = $feedback_messages[$message_key] ?? null;

$link = new_db_connection();

// Fetch pending user reports
$query = "SELECT 
            r.report_id, 
            r.reason,
            reporter.username as reporter_username,
            reported.user_id as reported_id,
            reported.username as reported_username,
            reported.profile_image as reported_profile_image
          FROM reports r
          JOIN users reporter ON r.ref_reporter_id = reporter.user_id
          JOIN users reported ON r.ref_reported_id = reported.user_id
          WHERE r.ref_status_id = 1"; // Status 1 = pending

$stmt = mysqli_stmt_init($link);
if (mysqli_stmt_prepare($stmt, $query)) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}
?>

<div class="recipe-management-container">
    <!-- Header -->
    <div class="mt-3 mb-4">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>

    <div class="recipe-list">
        <h1 class="mt-4 mb-3">GESTÃO DE DENÚNCIAS</h1>
    </div>

    <!-- Display Feedback Message -->
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($message['type']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message['text']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div id="utilizadores" class="tab_denuncia active">
        <?php
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $profile_image = empty($row['reported_profile_image']) ? '../imgs/perfis/default.jpg' : '../' . htmlspecialchars($row['reported_profile_image']);
                ?>
                <div class="card_denuncia">
                    <div class="card_denuncia-info">
                        <div class="recipe-image">
                            <img src="<?= $profile_image ?>" alt="Foto de Perfil" class="recipe-img">
                        </div>
                        <div>
                            <strong><?= htmlspecialchars($row['reported_username']) ?></strong><br>
                            <span>Denunciado por: <?= htmlspecialchars($row['reporter_username']) ?></span><br>
                            <small><strong>Motivo:</strong> <?= htmlspecialchars($row['reason']) ?></small>
                        </div>
                    </div>
                    <div class="recipe-actions">
                        <a href="confirmar_denuncia.php?report_id=<?= $row['report_id'] ?>&reported_id=<?= $row['reported_id'] ?>" class="action-btn-circle check-btn" title="Aprovar Denúncia">
                            <i class="fas fa-check"></i>
                        </a>
                        <form action="../scripts/sc_gerir_denuncias.php" method="post" style="display: inline;">
                            <input type="hidden" name="report_id" value="<?= $row['report_id'] ?>">
                            <button type="submit" name="action" value="refuse" class="action-btn-circle close-btn" title="Recusar Denúncia">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<p class="text-center mt-4">Não existem denúncias pendentes.</p>';
        }
        if ($result) mysqli_stmt_close($stmt);
        mysqli_close($link);
        ?>
    </div>
</div>