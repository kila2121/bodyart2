<div class="profile-stats">
    <div class="stat-card">
        <div class="stat-value"><?= $totalAppointments ?></div>
        <div class="stat-label">Всего записей</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $completedAppointments ?></div>
        <div class="stat-label">Выполнено</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $upcomingAppointments ?></div>
        <div class="stat-label">Предстоящие</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($totalSpent, 0, '.', ' ') ?> ₽</div>
        <div class="stat-label">Потрачено</div>
    </div>
</div>