<?php
function getGalleryItems()
{
    global $db;
    try {
        $sql = "SELECT * FROM gallery ORDER BY is_featured DESC, created_at DESC";
        $stmt = $db->dbs->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Ошибка получения галереи: " . $e->getMessage());
        return [];
    }
}

if (isset($GLOBALS['galleryData']) && !empty($GLOBALS['galleryData'])) {
    $galleryItems = $GLOBALS['galleryData'];
} else {
    $galleryItems = getGalleryItems();
}

function getCategoryName($category)
{
    switch ($category):
        case 'tattoo':
            return 'Тату';
        case 'piercing':
            return 'Пирсинг';
        case 'biomod':
            return 'Биомодификации';
        default:
            return $category;
    endswitch;
}

if (empty($galleryItems)) {
    echo '<p class="no-results">Работы временно недоступны</p>';
} else {
    foreach ($galleryItems as $item) {
        $categoryName = getCategoryName($item['category']);
        $featuredClass = $item['is_featured'] ? 'featured' : '';
        $imageUrl = htmlspecialchars($item['url']);
        $title = htmlspecialchars($item['title'] ?: 'Без названия');
        $createdAt = date('d.m.Y', strtotime($item['created_at']));
        ?>

        <div class="gallery-item <?php echo $featuredClass; ?>"
            data-category="<?php echo htmlspecialchars($item['category']); ?>">
            <div class="gallery-item-inner">
                <div class="gallery-image-wrapper">
                    <img src="<?php echo $imageUrl; ?>" alt="<?php echo $title; ?>" loading="lazy"
                        onerror="this.src='/public/uploads/gallery_work/default.jpg'">
                    <?php if ($item['is_featured']): ?>
                        <span class="featured-badge">★ Избранное</span>
                    <?php endif; ?>
                </div>

                <div class="gallery-item-info">
                    <h3 class="gallery-item-title"><?php echo $title; ?></h3>

                    <div class="gallery-item-meta">
                        <span class="category-badge" style="background: <?php
                        switch ($item['category']) {
                            case 'tattoo':
                                echo '#ff3366';
                                break;
                            case 'piercing':
                                echo '#10b981';
                                break;
                            case 'biomod':
                                echo '#f59e0b';
                                break;
                            default:
                                echo '#6c757d';
                        }
                        ?>">
                            <?php echo $categoryName; ?>
                        </span>
                        <span class="item-date">
                            <i class="far fa-calendar"></i>
                            <?php echo $createdAt; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }
}
?>