<div id="masters" class="tab-content active">
    <div class="actions">
        <button class="btn btn-primary" onclick="showForm('addMasterForm')">+ Добавить мастера</button>
    </div>

    <!-- Форма добавления мастера -->
    <div id="addMasterForm" class="form-container" style="display: none;">
        <h3>Добавить мастера</h3>
        <form method="POST" enctype="multipart/form-data" action="/index.php?action=add_master">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="text" name="fio" placeholder="ФИО" required>
            <input type="text" name="spec" placeholder="Специализация" required>
            <input type="number" name="experience" placeholder="Опыт (лет)" required>
            <textarea name="description" placeholder="Описание" required></textarea>
            <input type="file" name="photo">
            <div>
                <button type="submit" class="btn btn-success">Сохранить</button>
                <button type="button" class="btn" onclick="hideForm('addMasterForm')">Отмена</button>
            </div>
        </form>
    </div>

    <!-- Форма редактирования мастера -->
    <div id="editMasterForm" class="form-container" style="display: none;">
        <h3>Редактирование мастера</h3>
        <form method="POST" enctype="multipart/form-data" action="/index.php?action=edit_master"
            id="editMasterFormElement">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="id" id="edit_master_id" value="">

            <div class="form-group">
                <label>ФИО:</label>
                <input type="text" name="fio" id="edit_master_fio" placeholder="ФИО" required>
            </div>

            <div class="form-group">
                <label>Специализация:</label>
                <input type="text" name="spec" id="edit_master_spec" placeholder="Специализация" required>
            </div>

            <div class="form-group">
                <label>Опыт (лет):</label>
                <input type="number" name="experience" id="edit_master_experience" placeholder="Опыт (лет)" required>
            </div>

            <div class="form-group">
                <label>Описание:</label>
                <textarea name="description" id="edit_master_description" placeholder="Описание" required></textarea>
            </div>

            <div class="form-group">
                <label>Текущее фото:</label>
                <div id="current_master_photo"></div>
                <input type="file" name="photo">
                <small>Оставьте пустым, если не хотите менять фото</small>
            </div>

            <div>
                <button type="submit" class="btn btn-success">Сохранить изменения</button>
                <button type="button" class="btn" onclick="hideForm('editMasterForm')">Отмена</button>
            </div>
        </form>
    </div>

    <!-- Список мастеров -->
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Специализация</th>
                    <th>Опыт</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($masters as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['fio']) ?></td>
                        <td><?= htmlspecialchars($m['spec']) ?></td>
                        <td><?= htmlspecialchars($m['experience']) ?> лет</td>
                        <td>
                            <button class="btn" onclick="editMaster(<?= $m['id'] ?>)">✏️</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить мастера?')"
                                action="index.php?action=delete_master">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>