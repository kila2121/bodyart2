<div id="users" class="tab-content">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Логин</th>
                    <th>Email</th>
                    <th>ФИО</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['login']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['fio'] ?: '-') ?></td>
                        <td>
                            <?php if ($u['status'] == 100): ?>
                                <span class="status-badge" style="background:#ff3366; color:white;">Админ</span>
                            <?php elseif ($u['status'] == 80): ?>
                                <span class="status-badge" style="background:#ff3366; color:white;">Мастер</span>
                            <?php else: ?>
                                <span class="status-badge" style="background:#6c757d; color:white;">Пользователь</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['status'] == 100): ?>
                                <span class="status-badge" style="background:#6c757d;">Защищен</span>
                                <select onchange="updateUserRole(<?= $u['id'] ?>, this.value)">
                                    <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Админ</option>
                                    <option value="master" <?= $u['role'] == 'master' ? 'selected' : '' ?>>Мастер</option>
                                    <option value="user" <?= $u['role'] == 'user' ? 'selected' : '' ?>>Пользователь</option>
                                </select>
                            <?php else: ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить пользователя?')"
                                    action="/index.php?action=delete_user">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-danger">Удалить</button>
                                </form>
                                <select onchange="updateUserRole(<?= $u['id'] ?>, this.value)">
                                    <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Админ</option>
                                    <option value="master" <?= $u['role'] == 'master' ? 'selected' : '' ?>>Мастер</option>
                                    <option value="user" <?= $u['role'] == 'user' ? 'selected' : '' ?>>Пользователь</option>
                                </select>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>