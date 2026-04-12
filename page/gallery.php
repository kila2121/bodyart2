<?php

$allGallery = [];
$categories = [];


if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id = (int) $_GET["id"];
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof openModalById === 'function') {
                openModalById(<?= $id ?>);
            }
        });
    </script>
    <?php
}
?>

<?php
try {
    if (!$db || !$db->dbs) {
        throw new Exception("Не удалось подключиться");
    }

    $allGallery = Cache::get('all_gallery');
    if ($allGallery === false) {
        $allGallery = $db->dbs->query("SELECT 
                                    g.*,
                                    a.id_master,
                                    m.fio AS master_name,
                                    a.created_at AS appointment_date,
                                    s.name AS service_name,
                                    s.category AS service_category
                                FROM gallery g
                                LEFT JOIN appointment a ON a.id = g.id_appointment
                                LEFT JOIN master m ON m.id = a.id_master
                                LEFT JOIN services s ON s.id = a.id_service
                                ORDER BY g.is_featured DESC, g.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        Cache::set('all_gallery', $allGallery, 3600);
    }

    $categories = Cache::get("categories_gallery");
    if ($categories === false) {
        $categories = $db->dbs->query("SELECT DISTINCT category FROM gallery ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
        Cache::set('categories_gallery', $categories, 3600);
    }


} catch (Exception $e) {
    error_log("Ошибка в gallery.php: " . $e->getMessage());
}

function changeNameCategories($s)
{
    switch ($s):
        case 'tattoo':
            return 'Тату';
        case 'piercing':
            return 'Пирсинг';
        case 'biomod':
            return 'Биомодификации';
        default:
            return $s;
    endswitch;
}

ob_start();
?>
<?php include_once "component/gallery/hero/hero.php"; ?>
<section class="gallery-page">
    <div class="page-header">
        <h1>Галерея работ</h1>
        <p class="page-description">Наши лучшие работы в различных стилях и направлениях</p>
    </div>

    <?php if (!empty($categories)): ?>
        <div class="gallery-filter" id="gallery-filter">
            <button class="filter-btn active" data-filter="all">Все работы</button>
            <?php foreach ($categories as $category): ?>
                <button class="filter-btn" data-filter="<?php echo htmlspecialchars($category); ?>">
                    <?php echo htmlspecialchars(changeNameCategories($category)); ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div id="gallery-container" class="gallery-grid">
        <?php
        if (!empty($allGallery)) {
            $GLOBALS['galleryData'] = $allGallery;
            include_once "component/gallery/card_gallery/card.php";
            include_once "component/gallery/card_gallery/modalCard.php";
        } else {
            echo '<p class="no-results">Работы временно недоступны</p>';
        }
        ?>
    </div>
</section>

<div class="modal-overlay"></div>
<div class="modal-gallery">
    <div class="gallery-modal-content">

    </div>
</div>

<script>
    window.openModalById = function (id) {
        console.log('openModalById вызван с id:', id);
        updateAllItems();
        const index = allItems.findIndex(item => item.dataset.id == id);
        if (index !== -1) showModalByIndex(index);
    };

    document.addEventListener('DOMContentLoaded', function () {
        console.log('DOM загружен, инициализация галереи');

        const modal = document.querySelector('.modal-gallery');
        const overlay = document.querySelector('.modal-overlay');
        const modalContent = document.querySelector('.gallery-modal-content');
        const galleryContainer = document.getElementById('gallery-container');

        if (!modal || !overlay || !modalContent || !galleryContainer) {
            console.error('Не найдены элементы:', { modal, overlay, modalContent, galleryContainer });
            return;
        }

        console.log('Все элементы найдены');

        let currentIndex = 0;
        let allItems = [];

        function updateAllItems() {
            const modalItems = document.querySelectorAll('.gallery-modal-item');
            console.log('Найдено модальных элементов:', modalItems.length);
            allItems = Array.from(modalItems).filter(item => {
                const cardId = item.dataset.id;
                const card = document.querySelector(`.gallery-item[data-id="${cardId}"]`);
                return card && card.style.display !== 'none';
            });
            console.log('Отфильтровано видимых элементов:', allItems.length);
        }

        function updateCounter() {
            const currentSpan = modalContent.querySelector('.gmodal-current');
            const totalSpan = modalContent.querySelector('.gmodal-total');
            if (currentSpan) currentSpan.textContent = currentIndex + 1;
            if (totalSpan) totalSpan.textContent = allItems.length;
            console.log('Счётчик обновлён:', currentIndex + 1, '/', allItems.length);
        }

        function showModalByIndex(index) {
            console.log('showModalByIndex вызван с индексом:', index);
            if (!allItems.length || index < 0 || index >= allItems.length) {
                console.log('Индекс вне диапазона или нет элементов');
                return;
            }
            const item = allItems[index];
            if (!item) {
                console.log('Элемент не найден по индексу');
                return;
            }

            console.log('Клонируем элемент:', item);
            const clone = item.cloneNode(true);
            clone.style.display = 'block';
            clone.style.width = '100%';
            clone.style.height = '100%';

            modalContent.innerHTML = '';
            modalContent.appendChild(clone);

            console.log('Клон добавлен в modalContent');

            currentIndex = index;
            updateCounter();

            modal.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('modal-open');
            console.log('Модальное окно открыто');
        }

        function closeModal() {
            console.log('closeModal вызван');
            modal.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('modal-open');
            modalContent.innerHTML = '';
        }

        function openModalById(id) {
            console.log('openModalById вызван с id:', id);
            updateAllItems();
            const index = allItems.findIndex(item => item.dataset.id == id);
            console.log('Найден индекс:', index);
            if (index !== -1) showModalByIndex(index);
        }

        document.body.addEventListener('click', function (e) {
            const navBtn = e.target.closest('.gmodal-nav-btn');
            if (navBtn && modal.classList.contains('active')) {
                console.log('Клик по кнопке навигации (body delegation):', navBtn);
                e.preventDefault();
                e.stopPropagation();

                if (navBtn.classList.contains('prev')) {
                    console.log('Нажата кнопка prev');
                    if (currentIndex > 0) {
                        showModalByIndex(currentIndex - 1);
                    } else {
                        console.log('Это первая работа');
                    }
                } else if (navBtn.classList.contains('next')) {
                    console.log('Нажата кнопка next');
                    if (currentIndex < allItems.length - 1) {
                        showModalByIndex(currentIndex + 1);
                    } else {
                        console.log('Это последняя работа');
                    }
                }
                return;
            }

            const closeBtn = e.target.closest('.gmodal-close-btn');
            if (closeBtn && modal.classList.contains('active')) {
                console.log('Клик по кнопке закрытия');
                e.preventDefault();
                closeModal();
                return;
            }
        });

        galleryContainer.addEventListener('click', function (e) {
            const card = e.target.closest('.gallery-item');
            if (card) {
                e.preventDefault();
                const id = card.getAttribute('data-id');
                console.log('Клик по карточке с id:', id);
                if (id) openModalById(id);
            }
        });

        overlay.addEventListener('click', function (e) {
            console.log('Клик по оверлею');
            closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (!modal.classList.contains('active')) return;
            console.log('Нажата клавиша:', e.key);

            if (e.key === 'Escape') {
                closeModal();
            } else if (e.key === 'ArrowLeft') {
                if (currentIndex > 0) showModalByIndex(currentIndex - 1);
            } else if (e.key === 'ArrowRight') {
                if (currentIndex < allItems.length - 1) showModalByIndex(currentIndex + 1);
            }
        });

        const filterButtons = document.querySelectorAll('.gallery-filter .filter-btn');
        function filterGallery(filterValue) {
            console.log('Фильтрация по:', filterValue);
            const cards = document.querySelectorAll('.gallery-item');
            cards.forEach(card => {
                const category = card.dataset.category;
                card.style.display = (filterValue === 'all' || category === filterValue) ? 'flex' : 'none';
            });
            updateAllItems();
            if (modal.classList.contains('active')) closeModal();
        }

        if (filterButtons.length) {
            filterButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    filterButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    filterGallery(this.dataset.filter);
                    localStorage.setItem('galleryFilter', this.dataset.filter);
                });
            });

            const savedFilter = localStorage.getItem('galleryFilter');
            if (savedFilter && savedFilter !== 'all') {
                const savedBtn = document.querySelector(`.gallery-filter button[data-filter="${savedFilter}"]`);
                if (savedBtn) savedBtn.click();
            }
        }

        updateAllItems();
        console.log('Инициализация завершена, количество элементов:', allItems.length);

        let touchstartX = 0;
        modal.addEventListener('touchstart', e => {
            touchstartX = e.changedTouches[0].screenX;
        }, { passive: true });

        modal.addEventListener('touchend', e => {
            if (!modal.classList.contains('active')) return;
            const diff = e.changedTouches[0].screenX - touchstartX;
            if (Math.abs(diff) > 50) {
                if (diff > 0 && currentIndex > 0) {
                    showModalByIndex(currentIndex - 1);
                } else if (diff < 0 && currentIndex < allItems.length - 1) {
                    showModalByIndex(currentIndex + 1);
                }
            }
        }, { passive: true });
    });
</script>

<?php
$content = ob_get_clean();

$template = new Template("BodyArt Studio - Галерея");
$template->addStyle("/styles/page/gallery.css");
$template->addStyle("/component/gallery/card_gallery/card.css");
$template->addStyle("/component/gallery/card_gallery/modalCard.css");
$template->addStyle("/component/gallery/hero/hero.css");
$template->render($content);
?>