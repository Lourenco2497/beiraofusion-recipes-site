document.querySelectorAll('.favorite-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();

        if (!userLoggedIn) {
            window.location.href = '../site/registo.php';
            return;
        }

        const recipeId = this.dataset.recipeId;
        const icon = this.querySelector('i');
        const counter = this.querySelector('span');

        fetch('../api/toggle_like.php', {
            method: 'POST',
            body: new URLSearchParams({ recipe_id: recipeId })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    icon.classList.toggle('fa-solid');
                    icon.classList.toggle('fa-regular');
                    if (counter) {
                        let count = parseInt(counter.innerText);
                        counter.innerText = data.action === 'added' ? count + 1 : count - 1;
                    }
                }
            });
    });
});
