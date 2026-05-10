<?php
session_start();
require_once '../connections/connections.php';
include_once "../components/cp_head.php";


if (!isset($_SESSION['user_id']) || !isset($_SESSION['ref_type_id']) || $_SESSION['ref_type_id'] != 2) {
    header("Location: ../site/index.php");
    exit();
}

if (!isset($_GET['report_id']) || !isset($_GET['reported_id'])) {
    header("Location: ../site/gerir_denuncias.php?error=missing_ids");
    exit();
}

$report_id = filter_var($_GET['report_id'], FILTER_VALIDATE_INT);
$reported_id = filter_var($_GET['reported_id'], FILTER_VALIDATE_INT);

if ($report_id === false || $reported_id === false) {
    header("Location: ../site/gerir_denuncias.php?error=invalid_ids");
    exit();
}

$link = new_db_connection();
$stmt = $link->prepare("SELECT reason, reported.username FROM reports 
                       JOIN users as reported ON ref_reported_id = reported.user_id 
                       WHERE report_id = ? AND ref_reported_id = ?");
$stmt->bind_param("ii", $report_id, $reported_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: ../site/gerir_denuncias.php?error=report_not_found");
    exit();
}
$report = $result->fetch_assoc();
$stmt->close();
$link->close();
?>
<body class="body-adicionar">
<div class="recipe-management-container container mt-5">
    <div class="card">
        <div class="card-body text-center">
            <h3 class="card-title">Confirmar Ação</h3>
            <p>Está a aprovar uma denúncia contra <strong><?= htmlspecialchars($report['username']) ?></strong>.</p>
            <p><strong>Motivo:</strong> <?= htmlspecialchars($report['reason']) ?></p>
            <hr>
            <p class="fw-bold">Que ação pretende tomar?</p>
            <div class="d-grid gap-2 col-10 col-md-8 mx-auto">
                <form action="../scripts/sc_banir_user.php" method="post" class="d-grid">
                    <input type="hidden" name="report_id" value="<?= $report_id ?>">
                    <input type="hidden" name="reported_id" value="<?= $reported_id ?>">
                    <button type="submit" class="btn btn-danger">Banir Utilizador e Remover Denúncia</button>
                </form>
                <form action="../scripts/sc_gerir_denuncias.php" method="post" class="d-grid">
                    <input type="hidden" name="report_id" value="<?= $report_id ?>">
                    <button type="submit" name="action" value="delete_only" class="btn btn-warning">Apenas Remover Denúncia</button>
                </form>
                <a href="gerir_denuncias.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>