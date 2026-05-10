<!-- Head -->
<?php include_once "../components/cp_head.php"; ?>

<!-- Main Content -->
<?php include_once "../components/cp_perfil_header_utilizador.php"; ?>

<div class="container mt-3">
    <?php if (isset($_GET['success']) && $_GET['success'] == 'report_sent'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Feito!</strong> A sua denúncia foi enviada com sucesso. Agradecemos por turnar a aplicação Beirão Fusion mais segura.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

<?php include_once "../components/cp_perfil_utilizador.php"; ?>

<!-- Bottom Nav -->
<?php include_once "../components/cp_bottom_nav.php"; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/perfil.js"></script>
<script src="../js/menu.js"></script>