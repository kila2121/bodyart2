<section class="gallery-hero">
    <div class="gallery-hero-bg">
        <div class="bg-gradient"></div>
        <div class="bg-particles"></div>
        <div class="bg-grid"></div>
    </div>

    <div class="gallery-hero-content">
        <div class="gallery-hero-text">
            <h1 class="hero-title">
                Искусство,<br>
                <span class="hero-accent">запечатлённое навсегда</span>
            </h1>
            <p class="hero-description">
                Каждая работа — это уникальная история, рассказанная через линии, цвет и форму.
                Вдохновляйтесь нашими проектами и создавайте своё искусство вместе с нами.
            </p>
            <div class="hero-buttons">
                <a href="#gallery" id="scroll_to_gallery" class="btn btn-primary">
                    Смотреть работы
                    <i class="fas fa-arrow-down"></i>
                </a>
                <a href="/index.php?page=services" class="btn btn-outline">
                    Выбрать услугу
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="gallery-hero-visual">
            <div class="hero-visual-wrapper">
                <div class="visual-grid"></div>
                <div class="visual-frame frame-1">
                    <div class="frame-content">
                        <i class="fas fa-palette"></i>
                    </div>
                </div>
                <div class="visual-frame frame-2">
                    <div class="frame-content">
                        <i class="fas fa-image"></i>
                    </div>
                </div>
                <div class="visual-frame frame-3">
                    <div class="frame-content">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="visual-stripes"></div>
                <div class="floating-particles particle-1"></div>
                <div class="floating-particles particle-2"></div>
                <div class="floating-particles particle-3"></div>
            </div>
        </div>
    </div>
</section>

<div class="hero-transition">
    <div class="transition-gallery"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scrollBtn = document.getElementById('scroll_to_gallery');
        if (scrollBtn) {
            scrollBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const gallerySection = document.querySelector('.gallery-page');
                if (gallerySection) {
                    gallerySection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }
    });
</script>