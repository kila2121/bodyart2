<?php
session_start();
global $db;
include_once "connect.php";
require_once "classes/cache.php";

if (isset($_REQUEST['action'])) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if (!$token || !($isAjax ? validate_csrf_token($token) : verify_csrf_token($token))) {

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Ошибка безопасности: неверный токен.']);
                exit;
            }

            $activeTab = 'auth';
            if (isset($_POST['login']) && isset($_POST['pass']) && !isset($_POST['fio'])) {
                $activeTab = 'auth';
            } elseif (isset($_POST['fio']) && isset($_POST['phone']) && isset($_POST['email']) && isset($_POST['date_b'])) {
                $activeTab = 'reg';
            }
            $_SESSION['error'] = 'Ошибка: Неверный токен';
            $_SESSION['form_error'] = 'Ошибка безопасности: неверный или отсутствующий токен.';
            $_SESSION['active_tab'] = $activeTab;
            $_SESSION['form_data'] = $_POST;
            $redirect = $_SERVER['HTTP_REFERER'] ?? '/index.php';
            header("Location: $redirect");
            exit;
        }
    }
    // ========== АВТОРИЗАЦИЯ И РЕГИСТРАЦИЯ ==========

    if ($_REQUEST['action'] == 'reg') {
        if (
            empty($_REQUEST['login']) || empty($_REQUEST['pass']) || empty($_REQUEST['fio']) ||
            empty($_REQUEST['phone']) || empty($_REQUEST['email']) || empty($_REQUEST['date_b'])
        ) {
            $_SESSION['form_error'] = 'Заполните все обязательные поля';
            $_SESSION['form_data'] = $_REQUEST;
            $_SESSION['active_tab'] = 'reg';
            header("Location: /index.php");
            exit();
        }

        try {
            $login = trim($_REQUEST["login"]);
            $email = trim($_REQUEST["email"]);
            $phone = trim($_REQUEST["phone"]);
            $current_date = new DateTime();
            $date_b = new DateTime(trim($_REQUEST['date_b']));
            $age = $current_date->diff($date_b)->y;

            $r = $db->dbs->prepare('SELECT login, email, phone FROM user WHERE login = :login or email = :email or phone = :phone');
            $r->execute([':login' => $login, ':email' => $email, ':phone' => $phone]);
            $existing = $r->fetch(PDO::FETCH_ASSOC);

            if ($age < 18) {
                $_SESSION['form_error'] = 'Вам должно быть не менее 18 лет';
                $_SESSION['form_data'] = $_REQUEST;
                $_SESSION['active_tab'] = 'reg';
                header("Location: /index.php");
                exit();
            } elseif ($age > 100) {
                $_SESSION['form_error'] = 'Вам должно быть не более 100 лет';
                $_SESSION['form_data'] = $_REQUEST;
                $_SESSION['active_tab'] = 'reg';
                header("Location: /index.php");
            }

            if ($existing) {
                if ($existing['login'] == $login) {
                    $_SESSION['form_error'] = 'Пользователь с таким логином уже существует';
                } elseif ($existing['email'] == $email) {
                    $_SESSION['form_error'] = 'Пользователь с таким email уже существует';
                } elseif ($existing['phone'] == $phone) {
                    $_SESSION['form_error'] = 'Пользователь с таким телефоном уже существует';
                }
                $_SESSION['form_data'] = $_REQUEST;
                $_SESSION['active_tab'] = 'reg';

                header("Location: /index.php");
                exit();
            }


            $countUsers = $db->dbs->query("SELECT COUNT(*) FROM user")->fetchColumn();

            if ($countUsers == 0) {
                $role = 'admin';
                $status = 100;
            } else {
                $role = 'user';
                $status = 1;
            }

            $current_datetime = date('Y-m-d H:i:s');
            $fio = trim($_REQUEST['fio']);
            $phone = trim($_REQUEST['phone']);
            $email = trim($_REQUEST['email']);
            $hashedPassword = password_hash($_REQUEST['pass'], PASSWORD_DEFAULT);

            $mas = [
                'login' => $login,
                'pass' => $hashedPassword,
                'fio' => $fio,
                'phone' => $phone,
                'email' => $email,
                'date_b' => $date_b->format('Y-m-d'),
                'date_reg' => $current_date->format('Y-m-d'),
                'status' => $status,
                'role' => $role,
                'last_login' => $current_datetime,
                'avatar_url' => '/public/uploads/avatars/default.jpg'
            ];

            if ($db->actionTable('add', $mas, 'user')) {
                try {
                    session_regenerate_id(true);
                    ini_set('session.cookie_httponly', 1);
                    ini_set('session.cookie_lifetime', 7200);

                    $r = $db->dbs->prepare('SELECT id, login, pass, fio, status, role FROM user WHERE login = :login');
                    $r->execute([':login' => $login]);
                    $user = $r->fetch(PDO::FETCH_ASSOC);

                    $_SESSION['id'] = $user['id'];
                    $_SESSION['login'] = $user['login'];
                    $_SESSION['fio'] = $user['fio'];
                    $_SESSION['status'] = $user['status'];
                    $_SESSION['role'] = $user['role'];

                    $update = $db->dbs->prepare('UPDATE user SET last_login = NOW() WHERE id = :id');
                    $update->execute([':id' => $user['id']]);

                    $_SESSION['success'] = 'Вы успешно авторизовались';
                } catch (Exception $e) {
                    error_log('ошибка авторизации: ' . $e->getMessage());
                }

            } else {
                $_SESSION['form_error'] = 'Произошла ошибка в момент регистрации';
                $_SESSION['form_data'] = $_REQUEST; // НУЖНО ДОБАВИТЬ
                $_SESSION['active_tab'] = 'reg';
            }
        } catch (Exception $e) {
            error_log("Ошибка регистрации: " . $e->getMessage());
            $_SESSION['form_error'] = 'Ошибка базы данных';
            $_SESSION['form_data'] = $_REQUEST; // НУЖНО ДОБАВИТЬ
            $_SESSION['active_tab'] = 'reg';
        }
        header("Location: /index.php");
        exit();
    }

    if ($_REQUEST['action'] == 'auth') {
        if (empty($_REQUEST['login']) || empty($_REQUEST['pass'])) {
            $_SESSION['form_error'] = 'Не введены поля логин/пароль';
            $_SESSION['form_data'] = ['login' => $_REQUEST['login'] ?? ''];
            header("Location: /index.php");
            exit();
        }

        try {
            $r = $db->dbs->prepare('SELECT id, login, pass, fio, status, role FROM user WHERE login = :login');
            $r->execute([':login' => $_REQUEST['login']]);
            $user = $r->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($_REQUEST['pass'], $user['pass'])) {
                session_regenerate_id(true);
                ini_set('session.cookie_httponly', 1);
                ini_set('session.cookie_lifetime', 7200);

                $_SESSION['id'] = $user['id'];
                $_SESSION['login'] = $user['login'];
                $_SESSION['fio'] = $user['fio'];
                $_SESSION['status'] = $user['status'];
                $_SESSION['role'] = $user['role'];

                $update = $db->dbs->prepare('UPDATE user SET last_login = NOW() WHERE id = :id');
                $update->execute([':id' => $user['id']]);

                $_SESSION['success'] = 'Вы успешно авторизовались';
            } else {
                $_SESSION['form_error'] = 'Неверный логин или пароль';
                $_SESSION['active_tab'] = 'auth';
                $_SESSION['form_data'] = ['login' => $_REQUEST['login']];
            }
        } catch (Exception $e) {
            error_log("Ошибка авторизации: " . $e->getMessage());
            $_SESSION['form_error'] = 'Ошибка базы данных';
            $_SESSION['active_tab'] = 'auth';
            $_SESSION['form_data'] = ['login' => $_REQUEST['login']];
        }
        header("Location: /index.php");
        exit();
    }

    if ($_REQUEST['action'] == 'quit') {
        unset($_SESSION['id']);
        unset($_SESSION['login']);
        unset($_SESSION['fio']);
        unset($_SESSION['status']);
        unset($_SESSION['role']);
        session_destroy();
        $_SESSION['success'] = 'Вы вышли из системы';
        header("Location: /index.php?page=index");
        exit();
    }

    // ========== РАБОТА С МАСТЕРАМИ ==========

    // Добавление мастера
    if ($_REQUEST['action'] == 'add_master') {
        if (empty($_REQUEST['fio']) || empty($_REQUEST['spec']) || empty($_REQUEST['experience']) || empty($_REQUEST['description'])) {
            $_SESSION['error'] = 'Заполните все обязательные поля';
            header("Location: /index.php?page=admin");
            exit();
        }

        $avatar_url = '/public/uploads/avatars/default.jpg';

        if (!empty($_FILES['photo']['name'])) {
            $uploaded = $db->uploading('photo', '/public/uploads/avatars/master_avatars', 'master_' . time());

            if ($uploaded !== false && !empty($uploaded)) {
                $avatar_url = $uploaded[0];
            } else {
                $_SESSION['error'] = $db->last_error ?: 'Ошибка при загрузке фото';
                header("Location: /index.php?page=admin");
                exit;
            }
        }
        $mas = [
            'fio' => $_REQUEST['fio'],
            'phone' => $_REQUEST['phone'] ?? '',
            'email' => $_REQUEST['email'] ?? '',
            'spec' => $_REQUEST['spec'],
            'experience' => (int) $_REQUEST['experience'],
            'description' => $_REQUEST['description'],
            'is_Active' => 1,
            'avatar_url' => $avatar_url,
            'rating' => 0.00,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            if ($db->actionTable('add', $mas, 'master')) {
                Cache::delete('all_masters');
                Cache::delete('spec_masters');
                Cache::delete('aboutUs_stats');

                $services = $db->dbs->query("SELECT id FROM services")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($services as $id) {
                    Cache::delete('masters_by_service_' . $id);
                }

                $masters = $db->dbs->query("SELECT id FROM master")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($masters as $id) {
                    Cache::delete('services_by_master_' . $id);
                }

                $fioParts = explode(' ', trim($_REQUEST['fio']));
                $surname = $fioParts[0] ?? '';
                $name = $fioParts[1] ?? '';
                $patronymic = $fioParts[2] ?? '';

                $loginBase = $surname;
                if ($name) {
                    $loginBase .= '_' . mb_substr($name, 0, 1);
                }
                if ($patronymic) {
                    $loginBase .= mb_substr($patronymic, 0, 1);
                }

                $login = $db->translit($loginBase);
                $checkLogin = $db->dbs->prepare("SELECT id FROM user WHERE login = ?");
                $checkLogin->execute([$login]);
                if ($checkLogin->fetch()) {
                    $login = $login . '_' . time();
                }

                $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
                $phone = !empty($_REQUEST['phone']) ? trim($_REQUEST['phone']) : null;
                $email = !empty($_REQUEST['email']) ? trim($_REQUEST['email']) : null;

                $mas = [
                    'fio' => $_REQUEST['fio'],
                    'login' => $login,
                    'pass' => $defaultPassword,
                    'phone' => $phone,
                    'email' => $email,
                    'status' => 80,
                    'role' => 'master',
                    'date_b' => date('Y-m-d'),
                    'date_reg' => date('Y-m-d'),
                    'avatar_url' => '/public/uploads/avatars/default.jpg',
                    'last_login' => date('Y-m-d H:i:s'),
                ];

                if ($db->actionTable('add', $mas, 'user')) {
                    $_SESSION['success'] = 'Мастер и его аккаунт успешно добавлены';
                } else {
                    $_SESSION['error'] = 'Ошибка при добавлении мастера-юзера';
                }

            } else {
                $_SESSION['error'] = 'Ошибка при добавлении мастера';
            }
        } catch (Exception $e) {
            error_log("Ошибка добавления мастера: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка базы данных';
        }
        header("Location: /index.php?page=admin");
        exit();
    }

    // Редактирование мастера
    if ($_REQUEST['action'] == 'edit_master') {
        if (empty($_REQUEST['id'])) {
            $_SESSION['error'] = 'Не указан ID мастера';
            header("Location: /index.php?page=admin");
            exit();
        }

        try {
            $r = $db->dbs->prepare('SELECT avatar_url FROM master WHERE id = :id');
            $r->execute([':id' => $_REQUEST['id']]);
            $current_master = $r->fetch(PDO::FETCH_ASSOC);

            if (!$current_master) {
                $_SESSION['error'] = 'Мастер не найден';
                header("Location: /index.php?page=admin");
                exit();
            }

            $old_avatar = $current_master['avatar_url'];
            $avatar_url = $old_avatar;

            if (!empty($_FILES['photo']['name'])) {
                // Используем единый метод загрузки
                $uploaded = $db->uploading('photo', '/public/uploads/avatars/master_avatars', 'master_' . $_REQUEST['id'] . '_' . time());

                if ($uploaded) {
                    $new_avatar = $uploaded[0];

                    // Удаляем старый аватар, если это не дефолтный
                    if ($old_avatar && $old_avatar != '/public/uploads/avatars/default.jpg') {
                        $oldPath = $_SERVER['DOCUMENT_ROOT'] . $old_avatar;
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $avatar_url = $new_avatar;
                } else {
                    $_SESSION['error'] = $db->last_error ?: 'Ошибка при загрузке фото';
                    header("Location: /index.php?page=admin");
                    exit;
                }
            }

            $mas = [
                'id' => $_REQUEST['id'],
                'fio' => $_REQUEST['fio'],
                'phone' => $_REQUEST['phone'] ?? '',
                'email' => $_REQUEST['email'] ?? '',
                'spec' => $_REQUEST['spec'],
                'experience' => (int) $_REQUEST['experience'],
                'description' => $_REQUEST['description'],
                'avatar_url' => $avatar_url
            ];

            if ($db->actionTable('edit', $mas, 'master')) {
                Cache::delete('all_masters');
                Cache::delete('spec_masters');
                Cache::delete('aboutUs_stats');
                Cache::delete('master_' . $_REQUEST['id']);

                $services = $db->dbs->query("SELECT id FROM services")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($services as $id) {
                    Cache::delete('masters_by_service_' . $id);
                }

                $masters = $db->dbs->query("SELECT id FROM master")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($masters as $id) {
                    Cache::delete('services_by_master_' . $id);
                }

                $_SESSION['success'] = 'Мастер успешно обновлен';
            } else {
                $_SESSION['error'] = 'Ошибка при обновлении мастера';
            }
        } catch (Exception $e) {
            error_log("Ошибка редактирования мастера: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка базы данных';
        }
        header("Location: /index.php?page=admin");
        exit();
    }

    // Удаление мастера
    if ($_REQUEST['action'] == 'delete_master') {
        // FIX: добавили проверку на наличие отзывов
        if (empty($_REQUEST['id'])) {
            $_SESSION['error'] = 'Не указан ID мастера';
            header("Location: /index.php?page=admin");
            exit();
        }

        try {
            // Проверяем наличие записей
            $check = $db->dbs->prepare('SELECT COUNT(*) FROM appointment WHERE id_master = :id');
            $check->execute([':id' => $_REQUEST['id']]);
            $appointments_count = $check->fetchColumn();

            if ($appointments_count > 0) {
                $_SESSION['error'] = 'Невозможно удалить мастера, у него есть записи';
                header("Location: /index.php?page=admin");
                exit();
            }

            // Дополнительная проверка: есть ли отзывы, связанные с мастером (через записи)
            $checkReviews = $db->dbs->prepare('
                SELECT COUNT(*) FROM reviews r
                JOIN appointment a ON a.id = r.id_appointment
                WHERE a.id_master = :id
            ');
            $checkReviews->execute([':id' => $_REQUEST['id']]);
            $reviews_count = $checkReviews->fetchColumn();

            if ($reviews_count > 0) {
                $_SESSION['error'] = 'Невозможно удалить мастера, у него есть отзывы';
                header("Location: /index.php?page=admin");
                exit();
            }

            if ($db->actionTable('del', ['id' => $_REQUEST['id']], 'master')) {
                Cache::delete('all_masters');
                Cache::delete('spec_masters');
                Cache::delete('aboutUs_stats');
                Cache::delete('master_' . $_REQUEST['id']);

                $services = $db->dbs->query("SELECT id FROM services")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($services as $id) {
                    Cache::delete('masters_by_service_' . $id);
                }
                $masters = $db->dbs->query("SELECT id FROM master")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($masters as $id) {
                    Cache::delete('services_by_master_' . $id);
                }

                $_SESSION['success'] = 'Мастер успешно удален';
            } else {
                $_SESSION['error'] = 'Ошибка при удалении мастера';
            }
        } catch (Exception $e) {
            error_log("Ошибка удаления мастера: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка базы данных';
        }
        header("Location: /index.php?page=admin");
        exit();
    }

    // Изменение статуса мастера
    if ($_REQUEST['action'] == 'toggle_master_status') {
        // FIX: добавили проверку прав (только админ) и AJAX-проверку
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if (!isset($_SESSION['status']) || $_SESSION['status'] !== 100) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
            } else {
                $_SESSION['error'] = 'Доступ запрещен';
                header("Location: /index.php?page=admin");
            }
            exit();
        }

        if (empty($_REQUEST['id'])) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Не указан ID мастера']);
            } else {
                $_SESSION['error'] = 'Не указан ID мастера';
                header("Location: /index.php?page=admin");
            }
            exit();
        }

        try {
            $r = $db->dbs->prepare('SELECT is_Active FROM master WHERE id = :id');
            $r->execute([':id' => $_REQUEST['id']]);
            $master = $r->fetch(PDO::FETCH_ASSOC);

            if (!$master) {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'message' => 'Мастер не найден']);
                } else {
                    $_SESSION['error'] = 'Мастер не найден';
                    header("Location: /index.php?page=admin");
                }
                exit();
            }

            $new_status = $master['is_Active'] ? 0 : 1;

            $update = $db->dbs->prepare('UPDATE master SET is_Active = :status WHERE id = :id');
            $result = $update->execute([
                ':status' => $new_status,
                ':id' => $_REQUEST['id']
            ]);

            if ($result) {
                if ($isAjax) {
                    Cache::delete('all_masters');
                    Cache::delete('spec_masters');
                    Cache::delete('aboutUs_stats');
                    Cache::delete('master_' . $_REQUEST['id']);

                    $services = $db->dbs->query("SELECT id FROM services")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($services as $id) {
                        Cache::delete('masters_by_service_' . $id);
                    }

                    $masters = $db->dbs->query("SELECT id FROM master")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($masters as $id) {
                        Cache::delete('services_by_master_' . $id);
                    }
                    echo json_encode(['success' => true, 'status' => $new_status]);
                } else {
                    Cache::delete('all_masters');
                    Cache::delete('spec_masters');
                    Cache::delete('aboutUs_stats');
                    Cache::delete('master_' . $_REQUEST['id']);

                    $services = $db->dbs->query("SELECT id FROM services")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($services as $id) {
                        Cache::delete('masters_by_service_' . $id);
                    }

                    $masters = $db->dbs->query("SELECT id FROM master")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($masters as $id) {
                        Cache::delete('services_by_master_' . $id);
                    }

                    $_SESSION['success'] = 'Статус мастера изменен';
                    header("Location: /index.php?page=admin");
                }
            } else {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'message' => 'Ошибка при изменении статуса']);
                } else {
                    $_SESSION['error'] = 'Ошибка при изменении статуса';
                    header("Location: /index.php?page=admin");
                }
            }
        } catch (Exception $e) {
            error_log("Ошибка изменения статуса мастера: " . $e->getMessage());
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
            } else {
                $_SESSION['error'] = 'Ошибка базы данных';
                header("Location: /index.php?page=admin");
            }
        }
        exit();
    }
    // ========== РАБОТА С УСЛУГАМИ ==========

    // Добавление услуги
    if ($_REQUEST['action'] == 'add_service') {
        if (empty($_REQUEST['name']) || empty($_REQUEST['category']) || empty($_REQUEST['price']) || empty($_REQUEST['duration'])) {
            $_SESSION['error'] = 'Заполните все обязательные поля';
            header("Location: /index.php?page=admin");
            exit();
        }

        $mas = [
            'name' => $_REQUEST['name'],
            'category' => $_REQUEST['category'],
            'price' => (int) $_REQUEST['price'],
            'duration' => (int) $_REQUEST['duration'],
            'description' => $_REQUEST['description'] ?? '',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            if ($db->actionTable('add', $mas, 'services')) {
                Cache::delete('all_services');
                Cache::delete('categories_services');
                Cache::delete('popular_services');

                $masters = $db->dbs->query("SELECT id FROM master")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($masters as $id) {
                    Cache::delete('services_by_master_' . $id);
                }

                $services = $db->dbs->query("SELECT id FROM services")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($services as $id) {
                    Cache::delete('masters_by_service_' . $id);
                }
                $_SESSION['success'] = 'Услуга успешно добавлена';
            } else {
                $_SESSION['error'] = 'Ошибка при добавлении услуги';
            }
        } catch (Exception $e) {
            error_log("Ошибка добавления услуги: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка базы данных';
        }
        header("Location: /index.php?page=admin");
        exit();
    }

    // Редактирование услуги
    if ($_REQUEST['action'] == 'edit_service') {
        if (empty($_REQUEST['id'])) {
            $_SESSION['error'] = 'Не указан ID услуги';
            header("Location: /index.php?page=admin");
            exit();
        }

        try {
            $r = $db->dbs->prepare('SELECT id FROM services WHERE id = :id');
            $r->execute([':id' => $_REQUEST['id']]);
            if (!$r->fetch()) {
                $_SESSION['error'] = 'Услуга не найдена';
                header("Location: /index.php?page=admin");
                exit();
            }

            if (empty($_REQUEST['name']) || empty($_REQUEST['category']) || empty($_REQUEST['price']) || empty($_REQUEST['duration'])) {
                $_SESSION['error'] = 'Заполните все обязательные поля';
                header("Location: /index.php?page=admin");
                exit();
            }

            $mas = [
                'id' => $_REQUEST['id'],
                'name' => $_REQUEST['name'],
                'category' => $_REQUEST['category'],
                'price' => (int) $_REQUEST['price'],
                'duration' => (int) $_REQUEST['duration'],
                'description' => $_REQUEST['description'] ?? ''
            ];

            if ($db->actionTable('edit', $mas, 'services')) {
                Cache::delete('all_services');
                Cache::delete('categories_services');
                Cache::delete('popular_services');

                $masters = $db->dbs->query("SELECT id FROM master")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($masters as $id) {
                    Cache::delete('services_by_master_' . $id);
                }

                $services = $db->dbs->query("SELECT id FROM services")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($services as $id) {
                    Cache::delete('masters_by_service_' . $id);
                }

                $_SESSION['success'] = 'Услуга успешно обновлена';
            } else {
                $_SESSION['error'] = 'Ошибка при обновлении услуги';
            }
        } catch (Exception $e) {
            error_log("Ошибка редактирования услуги: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка базы данных';
        }
        header("Location: /index.php?page=admin");
        exit();
    }

    // Удаление услуги
    if ($_REQUEST['action'] == 'delete_service') {
        // FIX: добавили проверку на наличие отзывов через записи
        if (empty($_REQUEST['id'])) {
            $_SESSION['error'] = 'Не указан ID услуги';
            header("Location: /index.php?page=admin");
            exit();
        }

        try {
            $check = $db->dbs->prepare('SELECT COUNT(*) FROM appointment WHERE id_service = :id');
            $check->execute([':id' => $_REQUEST['id']]);
            $appointments_count = $check->fetchColumn();

            if ($appointments_count > 0) {
                $_SESSION['error'] = 'Невозможно удалить услугу, есть связанные записи';
                header("Location: /index.php?page=admin");
                exit();
            }

            // Проверяем, есть ли отзывы на записи с этой услугой
            $checkReviews = $db->dbs->prepare('
                SELECT COUNT(*) FROM reviews r
                JOIN appointment a ON a.id = r.id_appointment
                WHERE a.id_service = :id
            ');
            $checkReviews->execute([':id' => $_REQUEST['id']]);
            $reviews_count = $checkReviews->fetchColumn();

            if ($reviews_count > 0) {
                $_SESSION['error'] = 'Невозможно удалить услугу, есть связанные отзывы';
                header("Location: /index.php?page=admin");
                exit();
            }

            if ($db->actionTable('del', ['id' => $_REQUEST['id']], 'services')) {
                Cache::delete('all_services');
                Cache::delete('categories_services');
                Cache::delete('popular_services');

                $masters = $db->dbs->query("SELECT id FROM master")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($masters as $id) {
                    Cache::delete('services_by_master_' . $id);
                }

                $services = $db->dbs->query("SELECT id FROM services")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($services as $id) {
                    Cache::delete('masters_by_service_' . $id);
                }

                $_SESSION['success'] = 'Услуга успешно удалена';
            } else {
                $_SESSION['error'] = 'Ошибка при удалении услуги';
            }
        } catch (Exception $e) {
            error_log("Ошибка удаления услуги: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка базы данных';
        }
        header("Location: /index.php?page=admin");
        exit();
    }

    // ========== РАБОТА С ОТЗЫВАМИ ==========

    // Одобрение отзыва
    if ($_REQUEST['action'] == 'approve_review') {
        if (empty($_REQUEST['id'])) {
            $_SESSION['error'] = 'Не указан ID отзыва';
            header("Location: /index.php?page=admin");
            exit();
        }

        try {
            $update = $db->dbs->prepare('UPDATE reviews SET is_approved = 1 WHERE id = :id');
            $result = $update->execute([':id' => $_REQUEST['id']]);

            if ($result) {
                Cache::delete('hero_stats');
                Cache::delete('preview_reviews');

                $stmt = $db->dbs->prepare("SELECT a.id_master FROM reviews r JOIN appointment a ON a.id = r.id_appointment WHERE r.id = ?");
                $stmt->execute([$_REQUEST['id']]);
                $masterId = $stmt->fetchColumn();
                if ($masterId) {
                    Cache::delete('master_' . $masterId);
                }
                $_SESSION['success'] = 'Отзыв одобрен';
            } else {
                $_SESSION['error'] = 'Ошибка при одобрении отзыва';
            }
        } catch (Exception $e) {
            error_log("Ошибка одобрения отзыва: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка базы данных';
        }
        header("Location: /index.php?page=admin");
        exit();
    }

    // Отклонение отзыва (удаление)
    if ($_REQUEST['action'] == 'reject_review') {
        if (empty($_REQUEST['id'])) {
            $_SESSION['error'] = 'Не указан ID отзыва';
            header("Location: /index.php?page=admin");
            exit();
        }

        try {
            $delete = $db->dbs->prepare('DELETE FROM reviews WHERE id = :id');
            $result = $delete->execute([':id' => $_REQUEST['id']]);

            if ($result) {
                Cache::delete('hero_stats');
                Cache::delete('preview_reviews');

                $stmt = $db->dbs->prepare("SELECT a.id_master FROM reviews r JOIN appointment a ON a.id = r.id_appointment WHERE r.id = ?");
                $stmt->execute([$_REQUEST['id']]);
                $masterId = $stmt->fetchColumn();
                if ($masterId) {
                    Cache::delete('master_' . $masterId);
                }

                $_SESSION['success'] = 'Отзыв удален';
            } else {
                $_SESSION['error'] = 'Ошибка при удалении отзыва';
            }
        } catch (Exception $e) {
            error_log("Ошибка удаления отзыва: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка базы данных';
        }
        header("Location: /index.php?page=admin");
        exit();
    }

    // Добавление ответа админа
    if ($_REQUEST['action'] == 'reply_review') {
        if (empty($_REQUEST['id']) || empty($_REQUEST['admin_reply'])) {
            $_SESSION['error'] = 'Не указан ID отзыва или пустой ответ';
            header("Location: /index.php?page=admin");
            exit();
        }

        try {
            $update = $db->dbs->prepare('UPDATE reviews SET admin_reply = :reply WHERE id = :id');
            $result = $update->execute([
                ':reply' => $_REQUEST['admin_reply'],
                ':id' => $_REQUEST['id']
            ]);

            if ($result) {
                $_SESSION['success'] = 'Ответ добавлен';
            } else {
                $_SESSION['error'] = 'Ошибка при добавлении ответа';
            }
        } catch (Exception $e) {
            error_log("Ошибка добавления ответа: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка базы данных';
        }
        header("Location: /index.php?page=admin");
        exit();
    }

    // ========== РАБОТА С ПОЛЬЗОВАТЕЛЯМИ ==========

    // Удаление пользователя
    if ($_REQUEST['action'] == 'delete_user') {
        // FIX: запретили удаление пользователя-мастера
        if (empty($_REQUEST['id'])) {
            $_SESSION['error'] = 'Не указан ID пользователя';
            header("Location: /index.php?page=admin");
            exit();
        }

        try {
            $r = $db->dbs->prepare('SELECT status FROM user WHERE id = :id');
            $r->execute([':id' => $_REQUEST['id']]);
            $user = $r->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $_SESSION['error'] = 'Пользователь не найден';
                header("Location: /index.php?page=admin");
                exit();
            }

            if ($user['status'] == 100) {
                $_SESSION['error'] = 'Нельзя удалить администратора';
                header("Location: /index.php?page=admin");
                exit();
            }

            // Запрещаем удаление, если пользователь - мастер
            if ($user['status'] == 80) {
                $_SESSION['error'] = 'Нельзя удалить пользователя, который является мастером. Сначала измените его роль.';
                header("Location: /index.php?page=admin");
                exit();
            }

            $delete = $db->dbs->prepare('DELETE FROM user WHERE id = :id');
            $result = $delete->execute([':id' => $_REQUEST['id']]);

            if ($result) {
                $_SESSION['success'] = 'Пользователь успешно удален';
            } else {
                $_SESSION['error'] = 'Ошибка при удалении пользователя';
            }
        } catch (Exception $e) {
            error_log("Ошибка удаления пользователя: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка базы данных';
        }
        header("Location: /index.php?page=admin");
        exit();
    }

    // Изменение роли пользователя
    if ($_REQUEST['action'] == 'change_user_role') {
        // FIX: добавили проверку прав (только админ)
        if (!isset($_SESSION['status']) || $_SESSION['status'] !== 100) {
            echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        $role = $input['status'] ?? null;
        $status;
        switch ($role) {
            case 'admin':
                $status = 100;
                break;
            case 'master':
                $status = 80;
                break;
            default:
                $status = 1;
        }
        if (!$id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Не указан ID пользователя']);
            exit();
        }

        $allowed_statuses = ['admin', 'master', 'user'];
        if (!in_array($role, $allowed_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Недопустимая роль']);
            exit();
        }

        try {
            $r = $db->dbs->prepare('SELECT status, role FROM user WHERE id = :id');
            $r->execute([':id' => $id]);
            $user = $r->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
                exit();
            }

            $update = $db->dbs->prepare('UPDATE user SET status = :status, role = :role WHERE id = :id');
            $result = $update->execute([
                ':status' => $status,
                ':role' => $role,
                ':id' => $id
            ]);

            if ($result) {
                echo json_encode(['success' => true, 'status' => $status, 'role' => $role]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка при изменении роли']);
            }
        } catch (Exception $e) {
            error_log("Ошибка изменения роли: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
        }
        exit();
    }

    // ========== РАБОТА С ЗАПИСЯМИ ==========

    // Изменение статуса записи (уже есть try-catch, оставляем как есть)
    if ($_REQUEST['action'] == 'update_appointment_status') {
        // FIX: добавили проверку прав (админ или мастер, и мастер может менять только свои записи)
        if (!isset($_SESSION['status']) || ($_SESSION['status'] !== 100 && $_SESSION['role'] !== 'master')) {
            echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        $status = $input['status'] ?? null;

        if (!$id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Не указан ID записи или статус']);
            exit();
        }

        $allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($status, $allowed_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Недопустимый статус']);
            exit();
        }

        try {
            // Если мастер - проверяем, что запись принадлежит ему
            if ($_SESSION['role'] === 'master') {
                $stmt = $db->dbs->prepare('
                    SELECT a.id FROM appointment a
                    JOIN master m ON m.id = a.id_master
                    JOIN user u ON u.email = m.email OR u.phone = m.phone
                    WHERE a.id = ? AND u.id = ?
                ');
                $stmt->execute([$id, $_SESSION['id']]);
                if (!$stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Вы можете изменять статус только своих записей']);
                    exit();
                }
            }

            $update = $db->dbs->prepare('UPDATE appointment SET status = :status WHERE id = :id');
            $result = $update->execute([
                ':status' => $status,
                ':id' => $id
            ]);

            if ($status == 'completed') {
                Cache::delete('hero_stats');
                Cache::delete('aboutUs_stats');
            }

            if ($result) {
                echo json_encode(['success' => true, 'status' => $status]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении статуса']);
            }
        } catch (Exception $e) {
            error_log("Ошибка при обновлении статуса записи (ID $id): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
        }
        exit();
    }

    // Удаление записи
    if ($_REQUEST['action'] == 'delete_appointment') {
        if (empty($_REQUEST['id'])) {
            $_SESSION['error'] = 'Не указан ID записи';
            header("Location: /index.php?page=admin");
            exit();
        }

        try {
            $delete_reviews = $db->dbs->prepare('DELETE FROM reviews WHERE id_appointment = :id');
            $delete_reviews->execute([':id' => $_REQUEST['id']]);

            $delete = $db->dbs->prepare('DELETE FROM appointment WHERE id = :id');
            $result = $delete->execute([':id' => $_REQUEST['id']]);

            if ($result) {
                Cache::delete('hero_stats');
                Cache::delete('aboutUs_stats');

                $_SESSION['success'] = 'Запись успешно удалена';
            } else {
                $_SESSION['error'] = 'Ошибка при удалении записи';
            }
        } catch (Exception $e) {
            error_log("Ошибка удаления записи: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка базы данных';
        }
        header("Location: /index.php?page=admin");
        exit();
    }

    // Отмена записи пользователем
    if ($_REQUEST['action'] == 'cancel_appointment') {
        // Определяем, AJAX ли это
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Получаем ID (из тела JSON для AJAX, из GET/POST для обычной формы)
        $input = json_decode(file_get_contents('php://input'), true);
        $appointmentId = $input['id'] ?? $_POST['id'] ?? $_GET['id'] ?? null;

        if (!$appointmentId || !is_numeric($appointmentId)) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Не указан ID записи']);
                exit;
            }
            $_SESSION['error'] = 'Не указан ID записи';
            header("Location: /index.php?page=user");
            exit;
        }

        $appointmentId = (int) $appointmentId;

        if (!isset($_SESSION['id'])) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Необходимо авторизоваться']);
                exit;
            }
            header("Location: /index.php?page=index");
            exit;
        }

        $userId = $_SESSION['id'];

        try {
            $stmt = $db->dbs->prepare("
            SELECT id, status 
            FROM appointment 
            WHERE id = ? AND id_user = ? AND status IN ('pending', 'confirmed')
        ");
            $stmt->execute([$appointmentId, $userId]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$appointment) {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'message' => 'Запись не найдена или уже не может быть отменена']);
                    exit;
                }
                $_SESSION['error'] = 'Запись не найдена или уже не может быть отменена';
                header("Location: /index.php?page=user");
                exit;
            }

            $stmt = $db->dbs->prepare("UPDATE appointment SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$appointmentId]);

            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'Запись успешно отменена']);
                exit;
            }

            $_SESSION['success'] = 'Запись успешно отменена';
            header("Location: /index.php?page=user");
            exit;

        } catch (Exception $e) {
            error_log("Ошибка отмены записи: " . $e->getMessage());

            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
                exit;
            }

            $_SESSION['error'] = 'Ошибка при отмене записи';
            header("Location: /index.php?page=user");
            exit;
        }
    }
    // Обновление профиля пользователя
    if ($_REQUEST['action'] == 'update_profile') {
        if (!isset($_SESSION['id'])) {
            header("Location: /index.php?page=index");
            exit;
        }

        $userId = $_SESSION['id'];

        if (empty($_POST['fio']) || empty($_POST['email']) || empty($_POST['phone'])) {
            $_SESSION['error'] = 'Заполните все обязательные поля';
            header("Location: /index.php?page=user");
            exit;
        }

        try {
            $stmt = $db->dbs->prepare("
                UPDATE user SET 
                    fio = :fio,
                    email = :email,
                    phone = :phone,
                    date_b = :date_b
                WHERE id = :id
            ");

            $result = $stmt->execute([
                ':fio' => $_POST['fio'],
                ':email' => $_POST['email'],
                ':phone' => $_POST['phone'],
                ':date_b' => $_POST['date_b'] ?? null,
                ':id' => $userId
            ]);

            if ($result) {
                $_SESSION['success'] = 'Данные успешно обновлены';
                $_SESSION['fio'] = $_POST['fio'];
            } else {
                $_SESSION['error'] = 'Ошибка при обновлении данных';
            }
        } catch (Exception $e) {
            error_log("Ошибка обновления профиля: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка при обновлении данных';
        }

        if ($_SESSION['status'] == 80) {
            header('Location: /index.php?page=masterProfile');
        } elseif ($_SESSION['status'] == 1 || $_SESSION['status'] == 100) {
            header("Location: /index.php?page=user");
        }
        exit;
    }

    // Смена пароля
    if ($_REQUEST['action'] == 'change_password') {
        if (!isset($_SESSION['id'])) {
            header("Location: /index.php?page=index");
            exit;
        }

        $userId = $_SESSION['id'];

        if (empty($_POST['old_pass']) || empty($_POST['new_pass']) || empty($_POST['confirm_pass'])) {
            $_SESSION['error'] = 'Заполните все поля';
            header("Location: /index.php?page=user");
            exit;
        }

        if ($_POST['new_pass'] !== $_POST['confirm_pass']) {
            $_SESSION['error'] = 'Новый пароль и подтверждение не совпадают';
            header("Location: /index.php?page=user");
            exit;
        }

        // Проверка длины пароля (минимум 6 символов)
        if (strlen($_POST['new_pass']) < 6) {
            $_SESSION['error'] = 'Пароль должен быть не менее 6 символов';
            header("Location: /index.php?page=user");
            exit;
        }

        try {
            $stmt = $db->dbs->prepare("SELECT pass FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $_SESSION['error'] = 'Пользователь не найден';
                header("Location: /index.php?page=user");
                exit;
            }

            if (!password_verify($_POST['old_pass'], $user['pass'])) {
                $_SESSION['error'] = 'Неверный текущий пароль';
                header("Location: /index.php?page=user");
                exit;
            }

            $newPasswordHash = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);

            $update = $db->dbs->prepare("UPDATE user SET pass = ? WHERE id = ?");
            $result = $update->execute([$newPasswordHash, $userId]);

            if ($result) {
                $_SESSION['success'] = 'Пароль успешно изменен';
            } else {
                $_SESSION['error'] = 'Ошибка при смене пароля';
            }
        } catch (Exception $e) {
            error_log("Ошибка смены пароля: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка при смене пароля';
        }

        header("Location: /index.php?page=user");
        exit;
    }

    // Загрузка аватара
    if ($_REQUEST['action'] == 'upload_avatar') {
        if (!isset($_SESSION['id'])) {
            header("Location: /index.php?page=index");
            exit;
        }

        $userId = $_SESSION['id'];

        if (empty($_FILES['avatar']['name'])) {
            $_SESSION['error'] = 'Выберите файл для загрузки';
            redirectAfterAvatar();
            exit;
        }

        // Загружаем файл
        $uploaded = $db->uploading('avatar', '/public/uploads/avatars/users_avatars', 'avatar_' . $userId);

        if (!$uploaded) {
            $_SESSION['error'] = $db->last_error ?: 'Ошибка при загрузке файла';
            redirectAfterAvatar();
            exit;
        }

        // $uploaded[0] - это путь к загруженному файлу
        $avatarUrl = $uploaded[0];

        try {
            // Получаем старый аватар
            $stmt = $db->dbs->prepare("SELECT avatar_url FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            $oldAvatar = $stmt->fetchColumn();

            // Обновляем в БД
            $update = $db->dbs->prepare("UPDATE user SET avatar_url = ? WHERE id = ?");
            $update->execute([$avatarUrl, $userId]);

            // Удаляем старый аватар
            if ($oldAvatar && $oldAvatar != '/public/uploads/avatars/default.jpg') {
                $oldPath = $_SERVER['DOCUMENT_ROOT'] . $oldAvatar;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $_SESSION['success'] = 'Аватар успешно загружен';
        } catch (Exception $e) {
            error_log("Ошибка обновления аватара в БД: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка при сохранении аватара';
        }

        redirectAfterAvatar();
        exit;

    }

    // Удаление аватара
    if ($_REQUEST['action'] == 'delete_avatar') {
        if (!isset($_SESSION['id'])) {
            header("Location: /index.php?page=index");
            exit;
        }

        $userId = $_SESSION['id'];

        try {
            $stmt = $db->dbs->prepare("SELECT avatar_url FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            $avatarUrl = $stmt->fetchColumn();

            if ($avatarUrl && $avatarUrl != '/public/uploads/avatars/default.jpg') {
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $avatarUrl;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $update = $db->dbs->prepare("UPDATE user SET avatar_url = '/public/uploads/avatars/default.jpg' WHERE id = ?");
            $update->execute([$userId]);

            $_SESSION['success'] = 'Аватар удален';
        } catch (Exception $e) {
            error_log("Ошибка удаления аватара: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка при удалении аватара';
        }

        header("Location: /index.php?page=user");
        exit;
    }

    // Создание записи
    if ($_REQUEST['action'] == 'create_appointment') {
        if (!isset($_SESSION['id'])) {
            $_SESSION['error'] = 'Необходимо авторизоваться';
            header("Location: /index.php?page=user");
            exit;
        }

        if (empty($_POST['service_id']) || empty($_POST['master_id']) || empty($_POST['date']) || empty($_POST['time'])) {
            $_SESSION['error'] = 'Заполните все обязательные поля';
            header("Location: /index.php?page=services");
            exit;
        }

        $userId = $_SESSION['id'];
        $serviceId = (int) $_POST['service_id'];
        $masterId = (int) $_POST['master_id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $notes = $_POST['notes'] ?? '';

        $startTime = $date . ' ' . $time . ':00';

        try {
            // FIX: проверка активности мастера
            $stmt = $db->dbs->prepare("SELECT is_Active FROM master WHERE id = ?");
            $stmt->execute([$masterId]);
            $isActive = $stmt->fetchColumn();
            if (!$isActive) {
                $_SESSION['error'] = 'Мастер временно не принимает клиентов';
                header("Location: /index.php?page=services");
                exit;
            }

            $stmt = $db->dbs->prepare("SELECT duration FROM services WHERE id = ?");
            $stmt->execute([$serviceId]);
            $duration = $stmt->fetchColumn();

            if (!$duration) {
                $_SESSION['error'] = 'Услуга не найдена';
                header("Location: /index.php?page=services");
                exit;
            }

            $endTime = date('Y-m-d H:i:s', strtotime($startTime . ' + ' . $duration . ' minutes'));

            $check = $db->dbs->prepare("
                SELECT id FROM appointment
                WHERE id_master = ?
                AND (
                    (start_time <= ? AND stop_time > ?)
                    OR (start_time < ? AND stop_time >= ?)
                )
                AND status IN ('pending', 'confirmed')
            ");
            $check->execute([$masterId, $startTime, $startTime, $endTime, $endTime]);

            if ($check->rowCount() > 0) {
                $_SESSION['error'] = 'Это время уже занято';
                header("Location: /index.php?page=services");
                exit;
            }

            $insert = $db->dbs->prepare("
                INSERT INTO appointment (start_time, stop_time, status, id_user, id_service, id_master, notes)
                VALUES (?, ?, 'pending', ?, ?, ?, ?)
            ");
            $insert->execute([$startTime, $endTime, $userId, $serviceId, $masterId, $notes]);

            $_SESSION['success'] = 'Запись успешно создана! Ожидайте подтверждения.';
        } catch (Exception $e) {
            error_log("Ошибка создания записи: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка при создании записи';
        }

        // FIX: если в будущем понадобится AJAX, можно добавить ветку, пока оставляем как есть
        redirectAfterAvatar();
        exit;
    }

    // Добавление отзыва
    if ($_REQUEST['action'] == 'add_review') {
        if (!isset($_SESSION['id'])) {
            header("Location: /index.php?page=index");
            exit;
        }

        $userId = $_SESSION['id'];
        $appointmentId = $_POST['appointment_id'] ?? 0;
        $rating = $_POST['rating'] ?? 0;
        $comment = trim($_POST['comment'] ?? '');

        $errors = [];
        if (!$appointmentId)
            $errors[] = 'Не указана запись';
        if ($rating < 1 || $rating > 5)
            $errors[] = 'Укажите оценку от 1 до 5';
        if (empty($comment))
            $errors[] = 'Напишите отзыв';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header("Location: /index.php?page=user");
            exit;
        }

        try {
            $stmt = $db->dbs->prepare("
                SELECT id FROM appointment 
                WHERE id = ? AND id_user = ? AND status = 'completed'
            ");
            $stmt->execute([$appointmentId, $userId]);
            if (!$stmt->fetch()) {
                $_SESSION['error'] = 'Нельзя оставить отзыв на эту запись';
                header("Location: /index.php?page=user");
                exit;
            }

            $stmt = $db->dbs->prepare("SELECT id FROM reviews WHERE id_appointment = ?");
            $stmt->execute([$appointmentId]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Отзыв уже оставлен';
                header("Location: /index.php?page=user");
                exit;
            }

            $insert = $db->dbs->prepare("
                INSERT INTO reviews (id_appointment, id_user, rating, comment, is_approved, created_at) 
                VALUES (?, ?, ?, ?, 0, NOW())
            ");
            $insert->execute([$appointmentId, $userId, $rating, $comment]);

            $stmt = $db->dbs->prepare("SELECT id_master FROM appointment WHERE id = ?");
            $stmt->execute([$appointmentId]);
            $masterId = $stmt->fetchColumn();

            if ($masterId) {
                $updateMasterRating = $db->dbs->prepare("
                    UPDATE master m 
                    SET rating = (
                        SELECT COALESCE(AVG(r.rating), 0)
                        FROM reviews r 
                        JOIN appointment a ON a.id = r.id_appointment 
                        WHERE a.id_master = m.id AND r.is_approved = 1
                    )
                    WHERE m.id = ?
                ");
                $updateMasterRating->execute([$masterId]);
            }

            $_SESSION['success'] = 'Отзыв отправлен на модерацию';
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $_SESSION['error'] = 'Отзыв уже оставлен';
            } else {
                error_log("Ошибка добавления отзыва: " . $e->getMessage());
                $_SESSION['error'] = 'Ошибка при добавлении отзыва';
            }
        } catch (Exception $e) {
            error_log("Ошибка добавления отзыва: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка при добавлении отзыва';
        }

        header("Location: /index.php?page=user");
        exit;
    }

    // Загрузка работы мастера
    if ($_REQUEST['action'] == 'upload_work') {
        if (!isset($_SESSION['id'])) {
            $_SESSION['error'] = 'Необходимо авторизоваться';
            header("Location: /index.php");
            exit;
        }

        $userId = $_SESSION['id'];

        if (!isset($_FILES['work_photo']) || $_FILES['work_photo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Ошибка при загрузке файла';
            header('Location: index.php?page=masterProfile');
            exit;
        }

        if (empty($_POST['appointment_id']) || !is_numeric($_POST['appointment_id'])) {
            $_SESSION['error'] = 'Не выбран заказ';
            header('Location: index.php?page=masterProfile');
            exit;
        }

        $id_appointment = (int) $_POST['appointment_id'];

        try {
            // FIX: исправлена проверка принадлежности мастера
            // Получаем id мастера, связанного с записью
            $stmt = $db->dbs->prepare('SELECT id_master FROM appointment WHERE id = ?');
            $stmt->execute([$id_appointment]);
            $masterIdFromAppointment = $stmt->fetchColumn();

            if (!$masterIdFromAppointment) {
                $_SESSION['error'] = 'Запись не найдена';
                header('Location: index.php?page=masterProfile');
                exit;
            }

            // Находим id мастера, связанного с текущим пользователем
            $stmt = $db->dbs->prepare('SELECT id FROM master WHERE email = (SELECT email FROM user WHERE id = ?) OR phone = (SELECT phone FROM user WHERE id = ?)');
            $stmt->execute([$userId, $userId]);
            $currentMasterId = $stmt->fetchColumn();

            if ($currentMasterId != $masterIdFromAppointment) {
                $_SESSION['error'] = 'Это не ваш заказ';
                header('Location: index.php?page=masterProfile');
                exit;
            }

            $uploaded = $db->uploading('work_photo', '/public/uploads/gallery_work', 'work_' . $userId . '_' . $id_appointment);

            if (!$uploaded) {
                $_SESSION['error'] = $db->last_error ?: 'Ошибка при загрузке фото';
                header('Location: index.php?page=masterProfile');
                exit;
            }

            $workUrl = $uploaded[0];

            $title = $_POST['title'] ?? '';
            $category = $_POST['category'] ?? 'tattoo';
            $created_at = date('Y-m-d H:i:s');

            $add = $db->dbs->prepare('INSERT INTO gallery (url, id_appointment, title, category, is_featured, created_at) VALUES (?, ?, ?, ?, 0, ?)');
            $add->execute([$workUrl, $id_appointment, $title, $category, $created_at]);

            Cache::delete('all_gallery');
            Cache::delete('categories_gallery');
            Cache::delete('popular_services');

            $stmt = $db->dbs->prepare("SELECT id_master FROM appointment WHERE id = ?");
            $stmt->execute([$id_appointment]);
            $masterId = $stmt->fetchColumn();
            if ($masterId) {
                Cache::delete('master_' . $masterId);
            }

            $_SESSION['success'] = 'Фото успешно загружено';

        } catch (Exception $e) {
            error_log("Ошибка загрузки работы: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка при загрузке фото';
        }

        header('Location: index.php?page=masterProfile#works');
        exit;
    }

    // Удаление работы мастера
    if ($_REQUEST['action'] == 'delete_work') {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        $input = json_decode(file_get_contents('php://input'), true);
        $galleryId = $input['id'] ?? $_GET['id'] ?? null;

        if (!$galleryId || !is_numeric($galleryId)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Не указан ID работы']);
                exit;
            }
            $_SESSION['error'] = 'Не указан ID работы';
            header("Location: /index.php?page=masterProfile#works");
            exit;
        }

        $galleryId = (int) $galleryId;

        if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'master') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Недостаточно прав']);
                exit;
            }
            header("Location: /index.php");
            exit;
        }

        $userId = $_SESSION['id'];

        try {
            $stmt = $db->dbs->prepare("SELECT email, phone FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
                    exit;
                }
                $_SESSION['error'] = 'Пользователь не найден';
                header("Location: /index.php?page=masterProfile");
                exit;
            }

            $stmt = $db->dbs->prepare("SELECT id FROM master WHERE email = ? OR phone = ?");
            $stmt->execute([$user['email'], $user['phone']]);
            $master = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$master) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Профиль мастера не найден']);
                    exit;
                }
                $_SESSION['error'] = 'Профиль мастера не найден';
                header("Location: /index.php?page=masterProfile");
                exit;
            }

            $masterId = $master['id'];

            $stmt = $db->dbs->prepare("
            SELECT g.id, g.url FROM gallery g
            JOIN appointment a ON a.id = g.id_appointment
            WHERE g.id = ? AND a.id_master = ?
        ");
            $stmt->execute([$galleryId, $masterId]);
            $work = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$work) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Работа не найдена или у вас нет прав на удаление']);
                    exit;
                }
                $_SESSION['error'] = 'Работа не найдена или у вас нет прав на удаление';
                header("Location: /index.php?page=masterProfile");
                exit;
            }

            // Удаляем файл
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $work['url'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Удаляем запись из БД
            $delete = $db->dbs->prepare("DELETE FROM gallery WHERE id = ?");
            $result = $delete->execute([$galleryId]);

            if ($result) {
                if ($isAjax) {
                    Cache::delete('all_gallery');
                    Cache::delete('categories_gallery');
                    Cache::delete('popular_services');
                    Cache::delete('master_' . $masterId);

                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Работа удалена']);
                    exit;
                }
                Cache::delete('all_gallery');
                Cache::delete('categories_gallery');
                Cache::delete('popular_services');
                Cache::delete('master_' . $masterId);

                $_SESSION['success'] = 'Работа удалена';
                header("Location: /index.php?page=masterProfile#works");
                exit;
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Ошибка при удалении']);
                    exit;
                }
                $_SESSION['error'] = 'Ошибка при удалении';
                header("Location: /index.php?page=masterProfile");
                exit;
            }

        } catch (Exception $e) {
            error_log("Ошибка удаления работы: " . $e->getMessage());

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
                exit;
            }

            $_SESSION['error'] = 'Ошибка при удалении';
            header("Location: /index.php?page=masterProfile");
            exit;
        }
    }

    // Обновление профиля мастера
    if ($_REQUEST['action'] == 'update_master_profile') {
        if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'master') {
            header("Location: /index.php");
            exit;
        }

        $userId = $_SESSION['id'];

        try {
            $stmt = $db->dbs->prepare("SELECT email, phone FROM user WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                $_SESSION['error'] = 'Пользователь не найден';
                header("Location: /index.php?page=master");
                exit;
            }

            $stmt = $db->dbs->prepare("SELECT id FROM master WHERE email = ? OR phone = ?");
            $stmt->execute([$user['email'], $user['phone']]);
            $master = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$master) {
                $_SESSION['error'] = 'Профиль мастера не найден';
                header("Location: /index.php?page=master");
                exit;
            }
            $masterId = $master['id'];

            if (empty($_POST['spec']) || empty($_POST['experience']) || empty($_POST['description'])) {
                $_SESSION['error'] = 'Заполните все поля';
                header("Location: /index.php?page=master");
                exit;
            }

            $update = $db->dbs->prepare("
                UPDATE master SET 
                    spec = :spec,
                    experience = :experience,
                    description = :description,
                    phone = :phone,
                    email = :email
                WHERE id = :id
            ");
            $result = $update->execute([
                ':spec' => $_POST['spec'],
                ':experience' => (int) $_POST['experience'],
                ':description' => $_POST['description'],
                ':phone' => $_POST['phone'] ?? $user['phone'],
                ':email' => $_POST['email'] ?? $user['email'],
                ':id' => $masterId
            ]);

            if ($result) {
                Cache::delete('master_' . $masterId);
                Cache::delete('all_masters');
                Cache::delete('spec_masters');
                Cache::delete('aboutUs_stats');

                $services = $db->dbs->query("SELECT id FROM services")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($services as $id) {
                    Cache::delete('masters_by_service_' . $id);
                }

                $masters = $db->dbs->query("SELECT id FROM master")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($masters as $id) {
                    Cache::delete('services_by_master_' . $id);
                }

                $_SESSION['success'] = 'Профессиональные данные обновлены';
            } else {
                $_SESSION['error'] = 'Ошибка при обновлении';
            }
        } catch (Exception $e) {
            error_log("Ошибка обновления профиля мастера: " . $e->getMessage());
            $_SESSION['error'] = 'Ошибка базы данных';
        }

        header("Location: /index.php?page=masterProfile");
        exit;
    }
}

// Вспомогательная функция для редиректа после загрузки аватара
function redirectAfterAvatar()
{
    if ($_SESSION['status'] == 80) {
        header("Location: /index.php?page=masterProfile");
    } elseif ($_SESSION['status'] == 1 || $_SESSION['status'] == 100) {
        header("Location: /index.php?page=user");
    } else {
        header("Location: /index.php");
    }
}
?>