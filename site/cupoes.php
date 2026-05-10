<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!-- Head -->
<?php include_once "../components/cp_head.php"; ?>

<body class="body-adicionar">
    <div class="header_cupoes">
        <!-- Voltar atrás -->
        <a href="../site/index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>
    <h1 class="mb-4 big-title" >OS TEUS CUPÕES</h1>

    <?php
    include_once '../components/cp_cupoes.php';
    ?>

</body>
</html>