<div class="master-section contacts-section">
    <h2 class="section-heading">
        <i class="fas fa-phone-alt"></i>
        Контакты
    </h2>
    
    <div class="contacts-list">
        <?php if (!empty($master['phone'])): ?>
        <a href="tel:<?= htmlspecialchars($master['phone']) ?>" class="contact-item">
            <div class="contact-icon">
                <i class="fas fa-phone-alt"></i>
            </div>
            <div class="contact-details">
                <span class="contact-label">Телефон</span>
                <span class="contact-value"><?= htmlspecialchars($master['phone']) ?></span>
            </div>
        </a>
        <?php endif; ?>
        
        <?php if (!empty($master['email'])): ?>
        <a href="mailto:<?= htmlspecialchars($master['email']) ?>" class="contact-item">
            <div class="contact-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="contact-details">
                <span class="contact-label">Email</span>
                <span class="contact-value"><?= htmlspecialchars($master['email']) ?></span>
            </div>
        </a>
        <?php endif; ?>
        
        <div class="contact-item">
            <div class="contact-icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="contact-details">
                <span class="contact-label">Студия</span>
                <span class="contact-value">BodyArt Studio, центр</span>
            </div>
        </div>
    </div>
</div>