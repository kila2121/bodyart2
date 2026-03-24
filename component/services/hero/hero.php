<section class="services-hero">
    <div class="services-hero-bg">
        <div class="bg-gradient"></div>
        <div class="bg-pattern"></div>
    </div>

    <div class="services-hero-content">
        <div class="services-hero-text">
            <h1 class="hero-title">
                Создайте свой<br>
                <span class="hero-accent">уникальный стиль</span>
            </h1>
            <p class="hero-description">
                От классической татуировки до современного пирсинга —
                наши мастера помогут воплотить любую вашу идею
            </p>
            <div class="hero-buttons">
                <a id="scroll_indicator" class="btn btn-primary">
                    Выбрать услугу
                    <i class="fas fa-arrow-down"></i>
                </a>
                <a href="/index.php?page=masters" class="btn btn-outline">
                    Наши мастера
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="services-hero-visual">
            <div class="hero-visual-wrapper">
                <div class="visual-ring"></div>
                <div class="visual-ring-2"></div>
                <div class="visual-icon tattoo-icon">
                    <i class="fas fa-paint-brush-fine"></i>
                </div>
                <div class="visual-icon piercing-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <div class="visual-icon biomod-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="floating-circle circle-1"></div>
                <div class="floating-circle circle-2"></div>
                <div class="floating-circle circle-3"></div>
            </div>
        </div>
    </div>
</section>

<div class="hero-transition">
    <div class="transition-pattern"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scrollBtn = document.getElementById('scroll_indicator');
        if (scrollBtn) {
            scrollBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const page_header = document.querySelector('.page-header');
                if (page_header) {
                    page_header.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        }
    })
</script>