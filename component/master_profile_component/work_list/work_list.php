<div class="works-grid">
    <?php foreach ($works as $work): ?>
        <div class="work-card" id="work-<?= $work['id'] ?>">
            <div class="work-image">
                <img src="<?= htmlspecialchars($work['url']) ?>"
                    alt="<?= htmlspecialchars($work['title'] ?: 'Фото работы') ?>"
                    onerror="this.src='/public/uploads/gallery_work/default.jpg'">

                <?php if (!empty($work['title'])): ?>
                    <div class="work-title-overlay"><?= htmlspecialchars($work['title']) ?></div>
                <?php endif; ?>
            </div>
            <div class="work-info">
                <div class="work-date">
                    <i class="fas fa-calendar-alt"></i>
                    <?= date('d.m.Y', strtotime($work['created_at'])) ?>
                </div>
                <div class="work-actions">
                    <button class="work-delete" onclick="deleteWork(<?= $work['id'] ?>)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function deleteWork(workId) {
        if (confirm('Вы уверены, что хотите удалить это фото?')) {
            fetch('/action.php?action=delete_work', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: workId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                })
                .catch(e => {
                    console.error('Ошибка:', e);
                    alert('Ошибка удаления фото');
                });
        }
    }
</script>