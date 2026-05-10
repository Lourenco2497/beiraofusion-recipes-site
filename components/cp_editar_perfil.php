<?php
require_once '../connections/connections.php';
$conn = new_db_connection();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, show_saved_recipes, show_liked_recipes, profile_image FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

$mostrar_guardadas = $user['show_saved_recipes'] ?? 0;
$mostrar_gostadas = $user['show_liked_recipes'] ?? 0;

$profile_pic_path = !empty($user['profile_image']) ? '../' . htmlspecialchars($user['profile_image']) : '../imgs/perfis/default.jpg';
?>

<body class="body-adicionar">
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-4">
            <div>
                <a href="../site/perfil.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <form method="POST" action="../site/editar-perfil.php" enctype="multipart/form-data">
                    <div class="profile-picture" style="cursor: pointer;">
                        <img id="profile-image-preview" src="<?= $profile_pic_path ?>" alt="Profile Picture" class="img-fluid">
                        <div class="camera-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                        </div>
                    </div>

                    <input type="file" id="profile_image_input" name="profile_image" accept="image/*" style="display: none;">

                    <h4 class="edit-photo" style="cursor: pointer;">Editar foto</h4>

                    <div class="mb-1">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        <div class="text-end">
                            <a href="#" class="change-email">Alterar e-mail</a>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="username" class="form-label">Nome de utilizador</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" value="****************" disabled>
                        <div class="text-end">
                            <a href="#" class="change-password">Alterar password</a>
                        </div>
                    </div>

                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="mostrar_guardadas" id="mostrar_guardadas"
                            <?= $mostrar_guardadas ? 'checked' : '' ?>>
                        <label class="form-check-label" for="mostrar_guardadas">
                            Mostrar receitas guardadas no perfil
                        </label>
                    </div>

                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="mostrar_gostadas" id="mostrar_gostadas"
                            <?= $mostrar_gostadas ? 'checked' : '' ?>>
                        <label class="form-check-label" for="mostrar_gostadas">
                            Mostrar receitas com like no perfil
                        </label>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary save-button">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
