<section class="masters-hero">
    <div class="masters-hero-bg">
        <div class="bg-gradient"></div>
        <div class="bg-pattern"></div>
    </div>

    <div class="masters-hero-content">
        <div class="masters-hero-text">

            <h1 class="hero-title">
                Мастера <span class="hero-accent">своего дела</span>
            </h1>
            <p class="hero-description">
                Уникальный стиль, многолетний опыт и любовь к своему искусству —
                наши мастера создадут для вас нечто особенное
            </p>
            <div class="hero-buttons">
                <a href="#masters" id="scroll_to_masters" class="btn btn-primary">
                    Выбрать мастера
                    <i class="fas fa-arrow-down"></i>
                </a>
                <a href="/index.php?page=services" class="btn btn-outline">
                    Услуги
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="masters-hero-visual">
            <div class="hero-visual-wrapper">
                <div class="visual-ring"></div>
                <div class="visual-ring-2"></div>
                <div class="visual-icon master-icon tattoo">
                    <i class="fas fa-paintbrush"></i>
                </div>
                <div class="visual-icon master-icon piercing">
                    <i class="fas fa-gem"></i>
                </div>
                <div class="visual-icon master-icon biomod">
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
        const scrollBtn = document.getElementById('scroll_to_masters');
        if (scrollBtn) {
            scrollBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const mastersSection = document.querySelector('.masters-page');
                if (mastersSection) {
                    mastersSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }
    });
</script>