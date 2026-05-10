<?php
require_once '../connections/connections.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../site/login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user to report.");
}

$reported_user_id = intval($_GET['id']);
$reporter_user_id = $_SESSION['user_id'];

// Fetch reported user's username
$link = new_db_connection();
$stmt = $link->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $reported_user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $reported_username = htmlspecialchars($row['username']);
} else {
    die("User to report not found.");
}
$stmt->close();
$link->close();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong>
                    <?php
                    if ($_GET['error'] == 'invalid_data') {
                        echo "The data provided is invalid. Please try again.";
                    } elseif ($_GET['error'] == 'db_error') {
                        echo "An error occurred while processing your report. Please try again later.";
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Denunciar Utilizador</h3>
                    <p class="text-center">Denunciar: <strong><?= $reported_username ?></strong></p>
                    <form action="../scripts/sc_report_user.php" method="post">
                        <input type="hidden" name="reported_id" value="<?= $reported_user_id ?>">
                        <input type="hidden" name="reporter_id" value="<?= $reporter_user_id ?>">

                        <div class="mb-3">
                            <label for="reason" class="form-label">Motivo pela denúncia:</label>
                            <textarea class="form-control" id="reason" name="reason" rows="5" required></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-danger">Enviar denúncia</button>
                            <a href="../site/perfil_utilizador.php?id=<?= $reported_user_id ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>