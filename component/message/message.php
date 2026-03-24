<?php
// Проверяем наличие сообщений в сессии
$messages = [];

if (isset($_SESSION['success'])) {
    $messages[] = ['type' => 'success', 'text' => $_SESSION['success']];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $messages[] = ['type' => 'error', 'text' => $_SESSION['error']];
    unset($_SESSION['error']);
}
if (isset($_SESSION['form_error'])) {
    $messages[] = ['type' => 'error', 'text' => $_SESSION['form_error']];
    unset($_SESSION['form_error']);
}

if (empty($messages))
    return;

// Выводим сообщения с помощью метода message() с указанием типа
global $db;
foreach ($messages as $msg) {
    echo $db->message($msg['text'], $msg['type']);
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toasts = document.querySelectorAll('.toast');

        toasts.forEach(toast => {
            // Кнопка закрытия
            const closeBtn = toast.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    toast.classList.add('hide');
                    setTimeout(() => toast.remove(), 300);
                });
            }

            // Автоматическое закрытие через 5 секунд
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        });
    });
</script>