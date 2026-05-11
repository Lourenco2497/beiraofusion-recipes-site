<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../connections/connections.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$link = new_db_connection();

// username e imagem
$stmt = mysqli_stmt_init($link);
$query_user = "SELECT username, profile_image FROM users WHERE user_id = ?";
if (mysqli_stmt_prepare($stmt, $query_user)) {
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $username, $profile_image);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}
if (empty($profile_image)) {
    $profile_image = 'imgs/perfis/default.jpg';
}

// seguidores
$query_followers = "SELECT COUNT(*) FROM follows WHERE following_id = ?";
$stmt = mysqli_stmt_init($link);
mysqli_stmt_prepare($stmt, $query_followers);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $followers);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// seguindo
$query_following = "SELECT COUNT(*) FROM follows WHERE follower_id = ?";
$stmt = mysqli_stmt_init($link);
mysqli_stmt_prepare($stmt, $query_following);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $following);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
?>

<body class="body-adicionar">

<div class="menu-overlay" id="menuOverlay">
    <div class="menu-container">
        <!-- Botão de fechar -->
        <button class="menu-close-btn" id="closeMenuBtn">
            <i class="fa-solid fa-times"></i>
        </button>

        <!-- Menu items -->
        <div class="menu-items">
            <a href="../site/descobre.php" class="menu-item">Cocktails</a>
            <a href="../site/descobre.php" class="menu-item">Gastronomia</a>
            <a href="../site/cupoes.php" class="menu-item">Cupões</a>

            <?php if (isset($_SESSION['ref_type_id']) && $_SESSION['ref_type_id'] == 2): ?>
                <a href="../site/gerir_denuncias.php" class="menu-item">Gerir denúncias</a>
                <a href="../site/gerir_receitas.php" class="menu-item">Gerir receitas</a>
                <a href="../site/estatisticas.php" class="menu-item">Estatísticas</a>
            <?php endif; ?>
        </div>

        <!-- Terminar sessão -->
        <div class="menu-footer">
            <a href="#" class="menu-logout" onclick="fecharMenuEAbrirModal()">Terminar sessão</a>
        </div>
    </div>
</div>

<nav>
    <div class="container">
        <button class="navbar-toggler" type="button" id="menuToggle">
            <i class="fa-solid fa-bars fa-lg text-black"></i>
        </button>
    </div>
</nav>

<section class="container profile-section mb-4 mt-5">
    <div class="row align-items-center">
        <div class="col-4">
            <div class="profile-image-container">
                <img src="../<?= htmlspecialchars($profile_image) ?>" alt="Foto de perfil" class="profile-image">
            </div>
        </div>
        <div class="col-8">
            <div class="profile-header text-center">
                <h3 class="mb-1 pb-2"><?= htmlspecialchars($username) ?></h3>
            </div>
            <div class="d-flex justify-content-center">
                <div class="text-center me-4">
                    <a href="lista-seguidores.php">
                        <h5><?= $followers ?></h5>
                        <h5>seguidores</h5>
                    </a>
                </div>
                <div class="text-center">
                    <a href="lista-seguidores.php">
                        <h5><?= $following ?></h5>
                        <h5>a seguir</h5>
                    </a>
                </div>
            </div>
        </div>
        <a href="editar-perfil.php" class="btn btn-edit-profile mt-2">Editar perfil</a>
    </div>
</section>

<!-- Confirmação de logout -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Terminar sessão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tens a certeza que queres terminar a sessão?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="../scripts/sc_logout.php" class="btn btn-danger">Terminar sessão</a>
            </div>
        </div>
    </div>
</div>

<script src="../js/menu.js"></script>
