
let isLiked = false;
let likeCount = 31;

function toggleLike() {
    const heartIcon = document.getElementById('heart-icon');
    const likeCountElement = document.getElementById('like-count');

    if (isLiked) {
        heartIcon.className = 'far fa-heart';
        heartIcon.style.color = '#1b120d';
        likeCount--;
        isLiked = false;
    } else {
        heartIcon.className = 'fas fa-heart';
        heartIcon.style.color = '#ce1f25';
        likeCount++;
        isLiked = true;
    }

    likeCountElement.textContent = likeCount;
}


function toggleComments() {
    alert('Funcionalidade de comentários em desenvolvimento!');
}


function shareRecipe() {
    if (navigator.share) {
        navigator.share({
            title: 'Sangria de Tinto com Beirão',
            text: 'Confira esta receita incrível de sangria!',
            url: window.location.href
        }).then(() => {
            console.log('Receita compartilhada com sucesso!');
        }).catch((error) => {
            console.log('Erro ao compartilhar:', error);
            fallbackShare();
        });
    } else {
        fallbackShare();
    }
}

function fallbackShare() {
    const url = window.location.href;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Link copiado para a área de transferência!');
        }).catch(() => {
            prompt('Copie o link da receita:', url);
        });
    } else {
        prompt('Copie o link da receita:', url);
    }
}


function initializeBookmark() {
    const bookmarkToggle = document.getElementById('bookmark-toggle');

    if (bookmarkToggle) {
        bookmarkToggle.addEventListener('change', function() {
            const icon = this.nextElementSibling.querySelector('i');

            if (this.checked) {
                icon.className = 'fas fa-bookmark';
                console.log('Receita adicionada aos favoritos');
                saveBookmark(true);
            } else {
                icon.className = 'far fa-bookmark';
                console.log('Receita removida dos favoritos');
                saveBookmark(false);
            }
        });

        loadBookmarkState();
    }
}


function saveBookmark(isBookmarked) {
    try {
        const bookmarks = JSON.parse(localStorage.getItem('recipeBookmarks') || '{}');
        const recipeId = 'sangria-beirao';

        if (isBookmarked) {
            bookmarks[recipeId] = {
                title: 'Sangria de Tinto com Beirão',
                timestamp: new Date().toISOString()
            };
        } else {
            delete bookmarks[recipeId];
        }

        localStorage.setItem('recipeBookmarks', JSON.stringify(bookmarks));
    } catch (error) {
        console.log('Erro ao salvar bookmark:', error);
    }
}


function loadBookmarkState() {
    try {
        const bookmarks = JSON.parse(localStorage.getItem('recipeBookmarks') || '{}');
        const recipeId = 'sangria-beirao';

        if (bookmarks[recipeId]) {
            const bookmarkToggle = document.getElementById('bookmark-toggle');
            const icon = bookmarkToggle.nextElementSibling.querySelector('i');

            bookmarkToggle.checked = true;
            icon.className = 'fas fa-bookmark';
        }
    } catch (error) {
        console.log('Erro ao carregar bookmark:', error);
    }
}

function initializeBackButton() {
    const backBtn = document.querySelector('.back-btn');

    if (backBtn) {
        backBtn.addEventListener('click', function(e) {
            e.preventDefault();

            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        });
    }
}


function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));

            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
}


function initializeApp() {
    initializeBookmark();
    initializeBackButton();
    initializeSmoothScrolling();

    console.log('Sangria Recipe App initialized!');
}


document.addEventListener('DOMContentLoaded', initializeApp);

window.RecipeApp = {
    toggleLike,
    toggleComments,
    shareRecipe
};