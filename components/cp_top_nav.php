<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
            <a href="descobre.php?tipo=cocktail" class="menu-item">Cocktails</a>
            <a href="descobre.php?tipo=gastronomia" class="menu-item">Gastronomia</a>
            <a href="cupoes.php" class="menu-item">Cupões</a>

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
        <div class="search-container">
            <input type="text" class="search-input" id="pesquisa" name="pesquisa" placeholder="Pesquisar">
            <button type="button" class="search-button">
                <i class="fa-solid fa-magnifying-glass text-black"></i>
            </button>
        </div>
    </div>
</nav>
<div id="search-results" class="recipe-list mt-3"></div>


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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="../js/resultados-pesquisa.js"></script>
