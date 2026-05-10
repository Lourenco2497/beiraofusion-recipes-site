<?php
session_start();

if (isset($_SESSION['mensagem_erro'])) {
    echo '<div class="alert alert-danger text-center p-3">' . $_SESSION['mensagem_erro'] . '</div>';
    unset($_SESSION['mensagem_erro']);
}
?>

    <!-- Head -->
<?php include_once "../components/cp_head.php" ?>

<!-- Main Content -->
<?php include_once "../components/cp_registo.php" ?>