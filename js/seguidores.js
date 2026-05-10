document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.follow-button').forEach(button => {
        button.addEventListener('click', function () {
            const button = this;
            const userIdToToggle = button.dataset.userId;

            fetch('../api/toggle_follow.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + encodeURIComponent(userIdToToggle)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.action === 'followed') {
                        button.textContent = 'A seguir';
                        button.classList.remove('btn-primary');
                        button.classList.add('btn-outline-secondary');
                    } else if (data.action === 'unfollowed') {
                        button.textContent = 'Seguir';
                        button.classList.remove('btn-outline-secondary');
                        button.classList.add('btn-primary');
                    }
                } else {
                    alert(data.message || 'Ocorreu um erro.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocorreu um erro de comunicação.');
            });
        });
    });
});