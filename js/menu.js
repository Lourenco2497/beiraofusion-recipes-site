function toggleMenu() {
    const menuOverlay = document.getElementById('menuOverlay');
    menuOverlay.classList.toggle('active');
}

document.getElementById('menuToggle').addEventListener('click', toggleMenu);

document.getElementById('closeMenuBtn').addEventListener('click', toggleMenu);

document.getElementById('menuOverlay').addEventListener('click', function (e) {
    if (e.target === this) {
        toggleMenu();
    }
});

function fecharMenuEAbrirModal() {

    const menuOverlay = document.getElementById('menuOverlay');
    if (menuOverlay.classList.contains('active')) {
        menuOverlay.classList.remove('active');
    }

    setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
        modal.show();
    }, 300);
}