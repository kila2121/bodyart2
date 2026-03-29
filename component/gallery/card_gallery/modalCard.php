<?php
if (isset($GLOBALS['galleryData']) && !empty($GLOBALS['galleryData'])) {
    $galleryItems = $GLOBALS['galleryData'];
} else {
    $galleryItems = getGalleryItems();
}

if (empty($galleryItems)) {
    echo '<p class="no-results">Работы временно недоступны</p>';
} else {
    foreach ($galleryItems as $item) {
        if (!is_array($item) || !isset($item['id'])) {
            continue;
        }

        $categoryName = getCategoryName($item['category'] ?? '');
        $featuredClass = !empty($item['is_featured']) ? 'featured' : '';
        $imageUrl = htmlspecialchars($item['url'] ?? '');
        $title = htmlspecialchars(!empty($item['title']) ? $item['title'] : 'Без названия');
        $createdAt = isset($item['created_at']) ? date('d.m.Y', strtotime($item['created_at'])) : date('d.m.Y');
        $description = htmlspecialchars($item['description'] ?? '');
        $masterName = htmlspecialchars($item['master_name'] ?? 'Не указан');
        ?>

        <div class="gallery-modal-item <?php echo $featuredClass; ?>"
            data-category="<?php echo htmlspecialchars($item['category'] ?? ''); ?>" data-id="<?php echo $item['id']; ?>"
            style="display: none">

            <div class="gmodal-layout">
                <div class="gmodal-image">
                    <img src="<?php echo $imageUrl; ?>" alt="<?php echo $title; ?>" loading="lazy"
                        onerror="this.src='/public/uploads/gallery_work/default.jpg'">
                </div>

                <div class="gmodal-text-panel">
                    <h2 class="gmodal-title"><?php echo $title; ?></h2>
                    <span class="gmodal-category"><?php echo $categoryName; ?></span>
                </div>

                <div class="gmodal-actions-panel">
                    <?php if (!empty($item['is_featured'])): ?>
                        <span class="gmodal-featured-badge">
                            <i class="fas fa-star"></i>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="gmodal-bottom-bar">
                    <div class="gmodal-nav">
                        <button class="gmodal-nav-btn prev">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="gmodal-counter">
                            <span class="gmodal-current">1</span>
                            <span class="gmodal-separator">/</span>
                            <span class="gmodal-total"><?php echo count($galleryItems); ?></span>
                        </div>
                        <button class="gmodal-nav-btn next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="gmodal-master-card">
                    <div class="gmodal-master-content">
                        <div class="gmodal-master-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="gmodal-master-info">
                            <span class="gmodal-master-label">Мастер</span>
                            <span class="gmodal-master-name"><?php echo $masterName; ?></span>
                            <span class="gmodal-work-date">
                                <i class="far fa-calendar-alt"></i> <?php echo $createdAt; ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($description)): ?>
                        <div class="gmodal-master-description">
                            <p><?php echo nl2br($description); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php
    }
}
?>