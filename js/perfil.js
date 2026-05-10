// função para abrir/fechar menu
function toggleMenu() {
    const menuOverlay = document.getElementById('menuOverlay');
    menuOverlay.classList.toggle('active');
}

document.getElementById('menuToggle').addEventListener('click', toggleMenu);


document.getElementById('closeMenuBtn').addEventListener('click', toggleMenu);

document.getElementById('menuOverlay').addEventListener('click', function(e) {
    if (e.target === this) {
        toggleMenu();
    }
});


document.addEventListener('DOMContentLoaded', function () {

    const tabs = {
        published: document.getElementById('tabPublished'),
        favorites: document.getElementById('tabFavorites'),
        saved: document.getElementById('tabSaved')
    };

    const contents = {
        published: document.getElementById('contentPublished'),
        favorites: document.getElementById('contentFavorites'),
        saved: document.getElementById('contentSaved')
    };

    const activeTabClass = 'active-tab';

    function showContent(contentKeyToShow) {

        for (const key in contents) {
            if (contents[key]) {
                contents[key].style.display = 'none';
            }
        }


        for (const key in tabs) {
            if (tabs[key]) {
                tabs[key].classList.remove(activeTabClass);
            }
        }


        if (contents[contentKeyToShow]) {
            contents[contentKeyToShow].style.display = 'block';
        }


        if (tabs[contentKeyToShow]) {
            tabs[contentKeyToShow].classList.add(activeTabClass);
        }
    }


    if (tabs.published) {
        tabs.published.addEventListener('click', function() {
            showContent('published');
        });
    }

    if (tabs.favorites) {
        tabs.favorites.addEventListener('click', function() {
            showContent('favorites');
        });
    }

    if (tabs.saved) {
        tabs.saved.addEventListener('click', function() {
            showContent('saved');
        });
    }

    if (contents.published) {
        showContent('published');
    }
});