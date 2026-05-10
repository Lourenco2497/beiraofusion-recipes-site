<?php
require_once '../connections/connections.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['mensagem_erro'] = "Preencha todos os campos.";
        header("Location: ../site/login.php");
        exit();
    }

    $link = new_db_connection();

    $stmt = mysqli_stmt_init($link);
    $query = "SELECT user_id, username, password_hash, ref_type_id FROM users WHERE email = ?";

    if (mysqli_stmt_prepare($stmt, $query)) {
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $user_id, $username, $password_hash, $ref_type_id);

        if (mysqli_stmt_fetch($stmt)) {
            if (password_verify($password, $password_hash)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['ref_type_id'] = $ref_type_id;

                header("Location: ../site/index.php");
                exit();
            } else {
                $_SESSION['mensagem_erro'] = "Password incorreta.";
            }
        } else {
            $_SESSION['mensagem_erro'] = "Utilizador não encontrado.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['mensagem_erro'] = "Erro interno na autenticação.";
    }

    mysqli_close($link);
    header("Location: ../site/login.php");
    exit();
} else {
    header("Location: ../site/login.php");
    exit();
}
