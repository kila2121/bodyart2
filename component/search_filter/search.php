<div class="search-wrapper">
    <input type="text" class="search-input" id="service-search" placeholder="Поиск по услугам..." autocomplete="off">
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('service-search');
        const servicesContainer = document.getElementById('services-container');
        const filterButtons = document.querySelectorAll('.services-filter button');

        // Сохраняем оригинальные карточки при загрузке
        let originalCards = [];
        const originalCardsHTML = servicesContainer ? servicesContainer.innerHTML : '';

        if (!searchInput || !servicesContainer) return;

        let timeoutId;

        searchInput.addEventListener('input', function () {
            const query = this.value.trim();

            if (timeoutId) clearTimeout(timeoutId);

            if (query.length < 2) {
                // Если меньше 2 символов - показываем все услуги БЕЗ перезагрузки
                restoreAllServices();
                return;
            }

            timeoutId = setTimeout(() => {
                searchServices(query);
            }, 300);
        });

        function restoreAllServices() {
            // Восстанавливаем исходный HTML
            if (originalCardsHTML) {
                servicesContainer.innerHTML = originalCardsHTML;
            } else {
                // Если не сохранили - перезагружаем через API
                fetchAllServices();
            }
        }

        function fetchAllServices() {
            servicesContainer.innerHTML = '<div class="loading-spinner">Загрузка...</div>';

            fetch('/api/search.php?q=&type=services')
                .then(response => response.json())
                .then(data => {
                    if (data.results && data.results.services) {
                        displayServices(data.results.services);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.reload(); // В крайнем случае перезагрузка
                });
        }

        function searchServices(query) {
            servicesContainer.innerHTML = '<div class="loading-spinner">Поиск...</div>';

            fetch(`/api/search.php?q=${encodeURIComponent(query)}&type=services`)
                .then(response => response.json())
                .then(data => {
                    if (data.results && data.results.services && data.results.services.length > 0) {
                        displayServices(data.results.services);
                    } else {
                        servicesContainer.innerHTML = '<p class="no-results">Ничего не найдено</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    servicesContainer.innerHTML = '<p class="error-message">Ошибка при поиске</p>';
                });
        }

        function displayServices(services) {
            servicesContainer.innerHTML = '';

            services.forEach(service => {
                const categoryColor = getCategoryColor(service.category);

                const card = document.createElement('div');
                card.className = 'service-card';
                card.dataset.category = service.category;
                card.style.display = 'flex';

                card.innerHTML = `
                <div class="service-card-header" style="background: ${categoryColor}">
                    <h3>${escapeHtml(service.name)}</h3>
                    <span class="service-category">${escapeHtml(service.category)}</span>
                </div>
                <div class="service-card-body">
                    <div class="service-price">${service.price} ₽</div>
                    <button class="service-book-btn" onclick="bookService(${service.id})">
                        Записаться
                    </button>
                </div>
            `;

                servicesContainer.appendChild(card);
            });

            // После отображения результатов поиска, фильтр по категориям все еще работает
            applyCurrentFilter();
        }

        function applyCurrentFilter() {
            // Находим активную кнопку фильтра
            const activeFilter = document.querySelector('.services-filter button.active');
            if (activeFilter) {
                const filterValue = activeFilter.dataset.filter;
                const serviceCards = document.querySelectorAll('.service-card');

                serviceCards.forEach(card => {
                    if (filterValue === 'all') {
                        card.style.display = 'flex';
                    } else {
                        const cardCategory = card.dataset.category;
                        card.style.display = cardCategory === filterValue ? 'flex' : 'none';
                    }
                });
            }
        }

        function getCategoryColor(category) {
            const colors = {
                'Тату': '#ff3366',
                'Пирсинг': '#10b981',
                'Биомодификации': '#f59e0b',
                'Уход': '#8b5cf6'
            };
            return colors[category] || '#6c757d';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Переопределяем фильтры, чтобы они работали с новыми карточками
        if (filterButtons.length > 0) {
            filterButtons.forEach(button => {
                button.addEventListener('click', function () {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    const filterValue = this.dataset.filter;
                    const serviceCards = document.querySelectorAll('.service-card');

                    serviceCards.forEach(card => {
                        if (filterValue === 'all') {
                            card.style.display = 'flex';
                        } else {
                            const cardCategory = card.dataset.category;
                            card.style.display = cardCategory === filterValue ? 'flex' : 'none';
                        }
                    });
                });
            });
        }
    });

    function bookService(id) {
        window.location.href = `/index.php?page=services&action=book&id=${id}`;
    }
</script>