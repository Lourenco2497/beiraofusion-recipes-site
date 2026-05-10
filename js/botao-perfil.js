
    document.addEventListener('DOMContentLoaded', function () {
    const followBtn = document.querySelector('.follow-button-perfil');

    if (followBtn) {
    followBtn.addEventListener('click', function () {
    const userId = this.dataset.userId;

    fetch('../api/toggle_follow.php', {
    method: 'POST',
    headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
    'X-Requested-With': 'XMLHttpRequest'
},
    body: 'user_id=' + encodeURIComponent(userId)
})
    .then(response => response.json())
    .then(data => {
    if (data.status === 'success') {
    if (data.action === 'followed') {
    followBtn.textContent = 'A seguir';
} else if (data.action === 'unfollowed') {
    followBtn.textContent = 'Seguir';
}
} else {
    alert(data.message || 'Erro ao seguir utilizador.');
}
})
    .catch(error => {
    console.error('Erro:', error);
    alert('Erro de comunicação.');
});
});
}
});
