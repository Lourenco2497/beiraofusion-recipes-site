<?php
require_once '../connections/connections.php';
session_start();

// Verificar se o formulário foi submetido corretamente
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verificar se os campos existem no POST
    $email = $_POST["email"] ?? null;
    $username = $_POST["username"] ?? null;
    $password = $_POST["password"] ?? null;

    // campos obrigatórios
    if (!$email || !$username || !$password) {
        $_SESSION['mensagem_erro'] = "Todos os campos são obrigatórios.";
        header("Location: ../site/registo.php");
        exit();
    }

    $link = new_db_connection();
    $ref_type_id = 1;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // verificar duplicados
    $check_query = "SELECT user_id FROM users WHERE username = ? OR email = ?";
    $check_stmt = mysqli_stmt_init($link);
    if (mysqli_stmt_prepare($check_stmt, $check_query)) {
        mysqli_stmt_bind_param($check_stmt, 'ss', $username, $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $_SESSION['mensagem_erro'] = "Este username ou email já está registado.";
            header("Location: ../site/registo.php");
            exit();
        }

        mysqli_stmt_close($check_stmt);
    }

    // inserir utilizador
    $insert_stmt = mysqli_stmt_init($link);
    $insert_query = "INSERT INTO users (username, email, password_hash, ref_type_id) VALUES (?, ?, ?, ?)";

    if (mysqli_stmt_prepare($insert_stmt, $insert_query)) {
        mysqli_stmt_bind_param($insert_stmt, 'sssi', $username, $email, $hashed_password, $ref_type_id);
        if (mysqli_stmt_execute($insert_stmt)) {
            header("Location: ../site/index.php");
            exit();
        } else {
            $_SESSION['mensagem_erro'] = "Erro ao criar conta. Tente novamente.";
            header("Location: ../site/registo.php");
            exit();
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        $_SESSION['mensagem_erro'] = "Erro interno ao preparar o registo.";
        header("Location: ../site/registo.php");
        exit();
    }

    mysqli_close($link);
} else {
    header("Location: ../site/registo.php");
    exit();
}
