<div id="services" class="tab-content">
    <div class="actions">
        <button class="btn btn-primary" onclick="showForm('addServiceForm')">+ Добавить услугу</button>
    </div>

    <!-- Форма добавления услуги -->
    <div id="addServiceForm" class="form-container" style="display: none;">
        <h3>Добавить услугу</h3>
        <form method="POST" action="/index.php?action=add_service">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="text" name="name" placeholder="Название" required>
            <input type="text" name="category" placeholder="Категория" required>
            <input type="number" name="price" placeholder="Цена" required>
            <input type="number" name="duration" placeholder="Длительность (мин)" required>
            <textarea name="description" placeholder="Описание"></textarea>
            <div>
                <button type="submit" class="btn btn-success">Сохранить</button>
                <button type="button" class="btn" onclick="hideForm('addServiceForm')">Отмена</button>
            </div>
        </form>
    </div>

    <!-- Форма редактирования услуги -->
    <div id="editServiceForm" class="form-container" style="display: none;">
        <h3>Редактирование услуги</h3>
        <form method="POST" action="/index.php?action=edit_service" id="editServiceFormElement">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="id" id="edit_service_id" value="">

            <div class="form-group">
                <label>Название:</label>
                <input type="text" name="name" id="edit_service_name" placeholder="Название" required>
            </div>

            <div class="form-group">
                <label>Категория:</label>
                <input type="text" name="category" id="edit_service_category" placeholder="Категория" required>
            </div>

            <div class="form-group">
                <label>Цена (₽):</label>
                <input type="number" name="price" id="edit_service_price" placeholder="Цена" required>
            </div>

            <div class="form-group">
                <label>Длительность (мин):</label>
                <input type="number" name="duration" id="edit_service_duration" placeholder="Длительность" required>
            </div>

            <div class="form-group">
                <label>Описание:</label>
                <textarea name="description" id="edit_service_description" placeholder="Описание"></textarea>
            </div>

            <div>
                <button type="submit" class="btn btn-success">Сохранить изменения</button>
                <button type="button" class="btn" onclick="hideForm('editServiceForm')">Отмена</button>
            </div>
        </form>
    </div>

    <!-- Список услуг -->
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Категория</th>
                    <th>Цена</th>
                    <th>Длительность</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= htmlspecialchars($s['category']) ?></td>
                        <td><?= number_format($s['price'], 0, '', ' ') ?> ₽</td>
                        <td><?= htmlspecialchars($s['duration']) ?> мин</td>
                        <td>
                            <button class="btn" onclick="editService(<?= $s['id'] ?>)">✏️</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить услугу?')"
                                action="/index.php?action=delete_service">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>