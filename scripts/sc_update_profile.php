<?php

$user_id = $_SESSION['user_id'];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($conn)) {

    $username = trim($_POST['username']);
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : null;
    $new_password = $_POST['new_password'] ?? null;
    $confirm_new_password = $_POST['confirm_new_password'] ?? null;

    $update_fields = [];
    $param_values = [];
    $param_types = '';

    if (!empty($username)) {
        $update_fields[] = "username = ?";
        $param_values[] = $username;
        $param_types .= 's';
    } else {
        $errors[] = "O nome de utilizador não pode estar vazio.";
    }

    if ($email !== null) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $stmt->bind_param('si', $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->fetch_assoc()) {
                $errors[] = "Este e-mail já está em uso.";
            } else {
                $update_fields[] = "email = ?";
                $param_values[] = $email;
                $param_types .= 's';
            }
            $stmt->close();
        } else {
            $errors[] = "O e-mail fornecido não é válido.";
        }
    }

    if (!empty($new_password)) {
        if ($new_password !== $confirm_new_password) {
            $errors[] = "As passwords não coincidem.";
        } else {
            $update_fields[] = "password_hash = ?";
            $param_values[] = password_hash($new_password, PASSWORD_DEFAULT);
            $param_types .= 's';
        }
    }

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $upload_dir = dirname(__DIR__) . '/imgs/perfis/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }
        $filename = 'user_' . $user_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $destination = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $db_path = 'imgs/perfis/' . $filename;
            $update_fields[] = "profile_image = ?";
            $param_values[] = $db_path;
            $param_types .= 's';
        } else {
            $errors[] = "Falha ao carregar a foto de perfil.";
        }
    }

// se não houver erros e existirem campos para atualizar, executa a query
   if (empty($errors) && !empty($update_fields)) {

       $mostrar_guardadas = isset($_POST['mostrar_guardadas']) ? 1 : 0;
       $mostrar_gostadas = isset($_POST['mostrar_gostadas']) ? 1 : 0;

       $update_fields[] = "show_saved_recipes = ?";
       $param_values[] = $mostrar_guardadas;
       $param_types .= 'i';

       $update_fields[] = "show_liked_recipes = ?";
       $param_values[] = $mostrar_gostadas;
       $param_types .= 'i';

       $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE user_id = ?";
        $param_values[] = $user_id;
        $param_types .= 'i';


        $stmt = $conn->prepare($sql);
        $stmt->bind_param($param_types, ...$param_values);

        if ($stmt->execute()) {
            header("Location: ../site/perfil.php");
            exit();
        } else {
            $errors[] = "Falha ao atualizar o perfil.";
        }
        $stmt->close();
    }
}
?>