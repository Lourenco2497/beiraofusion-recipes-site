document.addEventListener('DOMContentLoaded', function() {
    // reply
    const parentIdInput = document.getElementById('parent-comment-id-input');
    const replyInfo = document.querySelector('.reply-info');
    const replyToUsername = document.getElementById('reply-to-username');
    const cancelReplyBtn = document.getElementById('cancel-reply-btn');
    const textarea = document.getElementById('comment-content-textarea');

    document.querySelectorAll('.reply-btn').forEach(button => {
        button.addEventListener('click', function() {
            parentIdInput.value = this.dataset.commentId;
            replyToUsername.textContent = '@' + this.dataset.username;
            replyInfo.style.display = 'flex';
            textarea.placeholder = 'Escreva a sua resposta...';
            textarea.focus();
        });
    });

    cancelReplyBtn.addEventListener('click', function() {
        parentIdInput.value = '';
        replyInfo.style.display = 'none';
        textarea.placeholder = 'Escreva o seu comentário...';
    });

    // like
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.dataset.commentId;
            const icon = this.querySelector('i');
            const countSpan = this.querySelector('.like-count');

            fetch('../api/toggle_comment_like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ comment_id: commentId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        countSpan.textContent = data.like_count;
                        if (data.user_liked) {
                            icon.classList.remove('bi-heart', 'text-dark');
                            icon.classList.add('bi-heart-fill', 'text-danger');
                        } else {
                            icon.classList.remove('bi-heart-fill', 'text-danger');
                            icon.classList.add('bi-heart', 'text-dark');
                        }
                    } else if (data.error === 'Not logged in') {
                        window.location.href = 'login.php';
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
});