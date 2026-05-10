class RecipeCarousel {
    constructor() {
        this.wrapper = document.getElementById('carouselWrapper');
        this.dots = document.querySelectorAll('.dot');
        this.currentSlide = 0;
        this.totalSlides = this.dots.length;
        this.startX = 0;
        this.endX = 0;

        this.init();
    }

    init() {
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                this.goToSlide(index);
            });
        });

        this.addTouchSupport();

    }

    goToSlide(slideIndex) {
        if (slideIndex < 0 || slideIndex >= this.totalSlides) return;

        this.currentSlide = slideIndex;
        const translateX = -slideIndex * 20; // 20% per slide
        this.wrapper.style.transform = `translateX(${translateX}%)`;

        this.updateDots();
    }

    updateDots() {
        this.dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === this.currentSlide);
        });
    }

    nextSlide() {
        const nextIndex = (this.currentSlide + 1) % this.totalSlides;
        this.goToSlide(nextIndex);
    }

    prevSlide() {
        const prevIndex = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
        this.goToSlide(prevIndex);
    }

    addTouchSupport() {
        const container = document.querySelector('.carousel-container');

        container.addEventListener('touchstart', (e) => {
            this.startX = e.touches[0].clientX;
        });

        container.addEventListener('touchend', (e) => {
            this.endX = e.changedTouches[0].clientX;
            this.handleSwipe();
        });


        container.addEventListener('mousedown', (e) => {
            this.startX = e.clientX;
            container.style.cursor = 'grabbing';
            e.preventDefault(); // Prevent text selection
        });

        container.addEventListener('mouseup', (e) => {
            this.endX = e.clientX;
            container.style.cursor = 'grab';
            this.handleSwipe();
        });


        container.style.cursor = 'grab';
    }

    handleSwipe() {
        const threshold = 50; // Minimum distance for swipe
        const diff = this.startX - this.endX;

        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.nextSlide();
            } else {
                this.prevSlide();
            }
        }
    }
}

class FavoriteManager {
    constructor() {
        this.init();
    }

    init() {
        const favoriteButtons = document.querySelectorAll('.favorite-btn');

        favoriteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleFavorite(button);
            });
        });
    }

    toggleFavorite(button) {
        const icon = button.querySelector('i');

        if (icon.classList.contains('bi-heart')) {
            icon.classList.remove('bi-heart');
            icon.classList.add('bi-heart-fill');
            button.classList.add('favorited');
        } else {
            icon.classList.remove('bi-heart-fill');
            icon.classList.add('bi-heart');
            button.classList.remove('favorited');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new RecipeCarousel();
    new FavoriteManager();
});