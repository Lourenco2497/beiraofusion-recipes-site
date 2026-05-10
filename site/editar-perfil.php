<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../connections/connections.php';
$conn = new_db_connection();


include_once __DIR__ . '/../scripts/sc_update_profile.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, email, profile_image FROM users WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php?error=user_not_found");
    exit();
}

include_once __DIR__ . '/../components/cp_head.php';

if (isset($_GET['status']) && $_GET['status'] === 'success') {
    echo '<div class="container mt-3"><div class="alert alert-success" role="alert">Perfil atualizado com sucesso!</div></div>';
}

if (!empty($errors)) {
    echo '<div class="container mt-3"><div class="alert alert-danger" role="alert">';
    foreach ($errors as $error) {
        echo htmlspecialchars($error) . '<br>';
    }
    echo '</div></div>';
}

include_once __DIR__ . '/../components/cp_editar_perfil.php';
?>

<script src="../js/bootstrap.bundle.min.js"></script>
<script src="../js/editar-perfil.js"></script>