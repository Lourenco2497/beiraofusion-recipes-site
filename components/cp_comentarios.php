<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../connections/connections.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID da receita inválido.");
}

$recipe_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'] ?? 0;

$conn = new_db_connection();


$stmt_recipe = $conn->prepare("SELECT title FROM recipes WHERE recipe_id = ?");
$stmt_recipe->bind_param("i", $recipe_id);
$stmt_recipe->execute();
$result_recipe = $stmt_recipe->get_result();
if ($result_recipe->num_rows === 0) {
    die("Receita não encontrada.");
}
$recipe = $result_recipe->fetch_assoc();
$stmt_recipe->close();


$stmt_comments = $conn->prepare("
    SELECT 
        c.comment_id, c.parent_comment_id, c.content, c.created_at, u.username, u.profile_image, u.user_id,
        (SELECT COUNT(*) FROM comment_likes cl WHERE cl.ref_comment_id = c.comment_id) AS like_count,
        (SELECT COUNT(*) FROM comment_likes cl WHERE cl.ref_comment_id = c.comment_id AND cl.ref_user_id = ?) AS user_liked
    FROM comments c
    JOIN users u ON c.ref_user_id = u.user_id
    WHERE c.ref_recipe_id = ?
    ORDER BY c.created_at ASC
");
$stmt_comments->bind_param("ii", $user_id, $recipe_id);
$stmt_comments->execute();
$result = $stmt_comments->get_result();
$comments_list = $result->fetch_all(MYSQLI_ASSOC);
$stmt_comments->close();

$comments_by_id = [];
foreach ($comments_list as $comment) {
    $comments_by_id[$comment['comment_id']] = $comment;
    $comments_by_id[$comment['comment_id']]['replies'] = [];
}

$root_comments = [];
foreach ($comments_by_id as $comment_id => &$comment) {
    if ($comment['parent_comment_id'] && isset($comments_by_id[$comment['parent_comment_id']])) {
        $comments_by_id[$comment['parent_comment_id']]['replies'][] = &$comment;
    } else {
        $root_comments[] = &$comment;
    }
}
unset($comment);

// mostrar os comentários mais recentes primeiro
$root_comments = array_reverse($root_comments);

// função para exibir comentários e as suas respostas
function display_comments($comments, $user_id, $is_reply = false) {
    foreach ($comments as $comment) {
        $container_classes = $is_reply ? 'comment-reply' : 'comment-parent';
        echo '<div class="' . $container_classes . '" id="comment-' . $comment['comment_id'] . '">';

        echo '<div class="d-flex">';
        echo '<div class="flex-shrink-0 me-3">
                <img src="../' . htmlspecialchars($comment['profile_image'] ?: 'imgs/perfis/default.jpg') . '" alt="Avatar" class="rounded-circle" style="width: 50px; height: 50px;">
              </div>';
        echo '<div class="flex-grow-1">';
        echo '<div class="d-flex justify-content-between align-items-center">';
        echo '  <h4 class="mb-1">
            <a href="perfil_utilizador.php?id=' . $comment['user_id'] . '" class="text-decoration-none text-dark">'
            . htmlspecialchars($comment['username']) .
            '</a>
        </h4>';
        echo '  <div class="d-flex align-items-center">';

        echo '    <small class="text-muted me-2">' . date('d/m/Y', strtotime($comment['created_at'])) . '</small>';

        if ($user_id === $comment['user_id'] || ($_SESSION['ref_type_id'] ?? null) === 2) {
            echo '<form action="../api/delete_comment.php" method="post" onsubmit="return confirm(\'Tens a certeza que queres apagar este comentário?\');" class="d-inline">';
            echo '  <input type="hidden" name="comment_id" value="' . $comment['comment_id'] . '">';
            echo '  <button type="submit" class="btn btn-sm btn-link text-danger p-0 ms-2"><i class="bi bi-trash text-danger"></i></button>';
            echo '</form>';
        }

        echo '  </div>';
        echo '</div>';

        echo '<p>' . nl2br(htmlspecialchars($comment['content'])) . '</p>';

        // reply e like buttons
        echo '<div class="d-flex align-items-center">';
        echo '<button class="btn btn-sm btn-link text-decoration-none reply-btn" data-comment-id="' . $comment['comment_id'] . '" data-username="' . htmlspecialchars($comment['username']) . '"><i class="bi bi-chat text-dark" style="font-size: 1.2rem;"></i></button>';

        if ($user_id > 0) {
            $liked_class = $comment['user_liked'] ? 'bi-heart-fill text-danger' : 'bi-heart text-dark';
            echo '<button class="btn btn-sm btn-link text-decoration-none like-btn ms-2" data-comment-id="' . $comment['comment_id'] . '">
                    <i class="bi ' . $liked_class . '" style="font-size: 1.1rem;"></i> 
                    <span class="like-count" style="font-size: 0.9rem; color: #6c757d;">' . $comment['like_count'] . '</span>
                  </button>';
        } else {
            echo '<a href="login.php" class="btn btn-sm btn-link text-decoration-none ms-2">
                    <i class="bi bi-heart text-dark" style="font-size: 1.1rem;"></i> 
                    <span style="font-size: 0.9rem; color: #6c757d;">' . $comment['like_count'] . '</span>
                  </a>';
        }
        echo '</div>';

        echo '</div>';
        echo '</div>';

        if (!empty($comment['replies'])) {
            display_comments($comment['replies'], $user_id, true);
        }

        echo '</div>';
    }
}
?>
<style>
    .comment-reply { margin-left: 2rem; margin-top: 1rem; padding-left: 1rem; border-left: 2px solid #e9ecef; }
    .comment-parent { margin-bottom: 1.5rem; }
    .reply-info { display: flex; justify-content: space-between; align-items: center; background-color: #f0f2f5; padding: 0.25rem 0.75rem; border-radius: 6px; }
    .like-btn i, .reply-btn i { transition: transform 0.2s ease-in-out; }
    .like-btn:hover i, .reply-btn:hover i { transform: scale(1.1); }
</style>
<body class="body-adicionar">
<div class="container mt-4 mb-5 pb-5">
    <div class="d-flex align-items-center mb-5">
        <a href="../site/receita_detail.php?id=<?= $recipe_id ?>" class="back-btn me-3"><i class="fas fa-arrow-left"></i></a>
    </div>
    <div class="comments-list">
        <?php if (!empty($root_comments)): ?>
            <?php display_comments($root_comments, $user_id); ?>
        <?php else: ?>
            <p class="text-center">Ainda não há comentários. Seja o primeiro a comentar!</p>
        <?php endif; ?>
    </div>
</div>
<div class="social-actions bg-white position-fixed bottom-0 start-0 end-0 p-3 border-top">
    <?php if ($user_id > 0): ?>
        <form action="../api/add_comment.php" method="post" class="container" id="comment-form">
            <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">
            <input type="hidden" name="parent_comment_id" id="parent-comment-id-input" value="">
            <div class="reply-info mb-1" style="display: none;">
                <small>A responder a <strong id="reply-to-username"></strong></small>
                <button type="button" class="btn-close btn-sm" aria-label="Close" id="cancel-reply-btn"></button>
            </div>
            <div class="d-flex align-items-center gap-2">
                <textarea class="form-control bg-warning-subtle border-0" name="comment_content" id="comment-content-textarea" rows="2" placeholder="Escreva o seu comentário..." required></textarea>
                <button type="submit" class="btn btn-danger rounded-circle d-flex align-items-center justify-content-center mb-2 p-2" style="width: 45px; height: 45px;"><i class="fas fa-arrow-up text-white"></i></button>
            </div>
        </form>
    <?php else: ?>
        <p class="text-center mb-0"><a href="../site/login.php">Inicie sessão</a> para deixar um comentário.</p>
    <?php endif; ?>
</div>
<?php $conn->close(); ?>
<script src="../js/comentarios_reply.js"></script>
</body>
</html>
