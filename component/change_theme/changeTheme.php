<div class="theme-switcher">
    <button class="theme-btn" data-theme="light">Светлая</button>
    <button class="theme-btn" data-theme="dark">Тёмная</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const themeBtns = document.querySelectorAll('.theme-btn');

        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);

        themeBtns.forEach(btn => {
            if (btn.dataset.theme === savedTheme) {
                btn.classList.add('active');
            }
        });

        themeBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const theme = this.dataset.theme;

                themeBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                document.documentElement.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
            });
        });
    });
</script>