<section class="cta-section">
    <div class="cta-content">
        <h2>Готовы изменить свой образ?</h2>
        <p>Запишитесь на консультацию или задайте вопросы нашим мастерам</p>
        <div class="cta-buttons">
            <a href="/index.php?page=services" class="btn btn-primary">Выбрать услугу</a>
            <a id="cont" class="btn btn-outline">Связаться с нами</a>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const contactPanel = document.getElementById('contactPanel');
        const panelOverlay = document.getElementById('panelOverlay');
        const contactButton = document.getElementById('cont');
        const closeButton = document.getElementById('closePanel');

        function openPanel() {
            contactPanel.classList.add('active');
            if (panelOverlay) panelOverlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Блокируем скролл
        }

        function closePanel() {
            contactPanel.classList.remove('active');
            if (panelOverlay) panelOverlay.classList.remove('active');
            document.body.style.overflow = ''; // Возвращаем скролл
        }

        // Открытие по кнопке
        if (contactButton && contactPanel) {
            contactButton.addEventListener('click', function (e) {
                e.preventDefault();
                openPanel();
            });
        }

        // Закрытие по крестику
        if (closeButton) {
            closeButton.addEventListener('click', closePanel);
        }

        // Закрытие по оверлею
        if (panelOverlay) {
            panelOverlay.addEventListener('click', closePanel);
        }

        // Закрытие по Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && contactPanel.classList.contains('active')) {
                closePanel();
            }
        });

        // Обработка формы (чтобы не перезагружалась)
        const contactForm = document.getElementById('quickContactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', function (e) {
                e.preventDefault();
                alert('Спасибо! Мы перезвоним вам в ближайшее время.');
                closePanel();
                // Здесь можно добавить AJAX отправку
            });
        }
    });
</script>