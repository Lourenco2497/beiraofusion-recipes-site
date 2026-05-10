document.getElementById('bookmark-toggle').addEventListener('click', function(e) {
    e.preventDefault();

    if (!userLoggedIn) {
        window.location.href = '../site/registo.php';
        return;
    }

    const recipeId = this.dataset.recipeId;
    const icon = this.querySelector('i');

    const formData = new FormData();
    formData.append('recipe_id', recipeId);

    fetch('../api/toggle_bookmark.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                icon.classList.toggle('fa-solid', data.action === 'added');
                icon.classList.toggle('fa-regular', data.action === 'removed');
            }
        });
});
