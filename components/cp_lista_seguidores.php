<?php

require_once '../connections/connections.php';
$link = new_db_connection();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

$profile_user_id = $_GET['id'] ?? $current_user_id;

// verificar se o utilizador do perfil existe
$stmt_followers = mysqli_prepare($link, "SELECT u.user_id, u.username, u.profile_image FROM follows f JOIN users u ON f.follower_id = u.user_id WHERE f.following_id = ?");
mysqli_stmt_bind_param($stmt_followers, "i", $profile_user_id);
mysqli_stmt_execute($stmt_followers);
$result_followers = mysqli_stmt_get_result($stmt_followers);
$followers = mysqli_fetch_all($result_followers, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_followers);

// obter a lista de utilizadores que seguem o utilizador do perfil
$stmt_following = mysqli_prepare($link, "SELECT u.user_id, u.username, u.profile_image FROM follows f JOIN users u ON f.following_id = u.user_id WHERE f.follower_id = ?");
mysqli_stmt_bind_param($stmt_following, "i", $profile_user_id);
mysqli_stmt_execute($stmt_following);
$result_following = mysqli_stmt_get_result($stmt_following);
$following = mysqli_fetch_all($result_following, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_following);

// obter a lista de utilizadores que o utilizador atual está a seguir
$stmt_current_following = mysqli_prepare($link, "SELECT following_id FROM follows WHERE follower_id = ?");
mysqli_stmt_bind_param($stmt_current_following, "i", $current_user_id);
mysqli_stmt_execute($stmt_current_following);
$result_current_following = mysqli_stmt_get_result($stmt_current_following);
$current_following_list = [];
while ($row = mysqli_fetch_assoc($result_current_following)) {
    $current_following_list[] = $row['following_id'];
}
mysqli_stmt_close($stmt_current_following);

mysqli_close($link);
?>
<body class="body-adicionar">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <a href="../site/perfil.php?id=<?= htmlspecialchars($profile_user_id) ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>

            <ul class="nav nav-tabs justify-content-center mt-4" id="followersTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active text-dark" id="followers-tab" data-bs-toggle="tab" data-bs-target="#followers" type="button" role="tab" aria-controls="followers" aria-selected="true"><?= count($followers) ?> seguidores</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-dark" id="following-tab" data-bs-toggle="tab" data-bs-target="#following" type="button" role="tab" aria-controls="following" aria-selected="false"><?= count($following) ?> a seguir</button>
                </li>
            </ul>

            <div class="container mt-3 mb-2 d-flex justify-content-center">
                <div class="search-container" style="max-width: 400px; width: 100%;">
                    <input type="text" class="search-input" id="pesquisa-seguidores" placeholder="Pesquisar utilizador...">
                    <button type="button" class="search-button">
                        <i class="fa-solid fa-magnifying-glass text-black"></i>
                    </button>
                </div>
            </div>



            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="followers" role="tabpanel" aria-labelledby="followers-tab">
    <div class="profile-list mt-3">
        <?php if (empty($followers)): ?>
            <p class="text-center text-muted mt-4">Ainda não tem seguidores.</p>
        <?php else: ?>
            <?php foreach ($followers as $follower): ?>
                <div class="profile-item">
                    <a href="../site/perfil_utilizador.php?id=<?= htmlspecialchars($follower['user_id']) ?>" class="profile-link d-flex align-items-center text-decoration-none text-dark flex-grow-1 min-w-0">
                        <div class="profile-avatar">
                            <img src="../<?= htmlspecialchars($follower['profile_image'] ?: 'imgs/perfis/default.jpg') ?>" alt="Avatar de <?= htmlspecialchars($follower['username']) ?>">
                        </div>
                        <div class="profile-info ms-3">
                            <h3 class="mb-0 d-block text-break"><?= htmlspecialchars($follower['username']) ?></h3>
                        </div>
                    </a>
                    <?php if ($follower['user_id'] != $current_user_id): ?>
                        <button class="follow-button btn btn-sm <?= in_array($follower['user_id'], $current_following_list) ? 'btn-outline-secondary' : 'btn-primary' ?>" data-user-id="<?= $follower['user_id'] ?>">
                            <?= in_array($follower['user_id'], $current_following_list) ? 'A seguir' : 'Seguir' ?>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<div class="tab-pane fade" id="following" role="tabpanel" aria-labelledby="following-tab">
     <div class="profile-list mt-3">
        <?php if (empty($following)): ?>
            <p class="text-center text-muted mt-4">Não segue nenhum utilizador.</p>
        <?php else: ?>
            <?php foreach ($following as $followed_user): ?>
                <div class="profile-item">
                    <a href="../site/perfil_utilizador.php?id=<?= htmlspecialchars($followed_user['user_id']) ?>" class="profile-link d-flex align-items-center text-decoration-none text-dark flex-grow-1 min-w-0">
                        <div class="profile-avatar">
                            <img src="../<?= htmlspecialchars($followed_user['profile_image'] ?: 'imgs/perfis/default.jpg') ?>" alt="Avatar de <?= htmlspecialchars($followed_user['username']) ?>">
                        </div>
                        <div class="profile-info ms-2">
                            <h3 class="mb-0 d-block text-break"><?= htmlspecialchars($followed_user['username']) ?></h3>
                        </div>
                    </a>
                    <?php if ($followed_user['user_id'] != $current_user_id): ?>
                        <button class="follow-button btn btn-sm <?= in_array($followed_user['user_id'], $current_following_list) ? 'btn-outline-secondary' : 'btn-primary' ?>" data-user-id="<?= $followed_user['user_id'] ?>">
                            <?= in_array($followed_user['user_id'], $current_following_list) ? 'A seguir' : 'Seguir' ?>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('pesquisa-seguidores').addEventListener('input', function () {
        const termo = this.value.toLowerCase();

        document.querySelectorAll('.profile-item').forEach(item => {
            const texto = item.textContent.toLowerCase();
            const visivel = texto.includes(termo);
            item.style.display = visivel ? 'flex' : 'none';
        });
    });
</script>

