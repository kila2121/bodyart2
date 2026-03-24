<div class="master-section about-section">
    <h2 class="section-heading">
        <i class="fas fa-user"></i>
        О мастере
    </h2>
    <div class="about-text">
        <?= nl2br(htmlspecialchars($master['description'] ?: 'Опытный мастер с индивидуальным подходом к каждому клиенту.')) ?>
    </div>
    
    <?php if (!empty($masterServices)): ?>
    <div class="skills-list">
        <h3>Основные услуги:</h3>
        <div class="skill-tags">
            <?php foreach($masterServices as $service): ?>
            <span class="skill-tag"><?= htmlspecialchars($service['name']) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>