<?php
require 'config.php';

// Устанавливаем правильный заголовок (чтобы JS понимал JSON)
header('Content-Type: application/json; charset=utf-8');

try {
    // Проверяем тип запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Некорректный метод запроса.');
    }

    // Проверка обязательных полей
    $required = ['name', 'phone', 'check_number'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Поле '{$field}' обязательно для заполнения.");
        }
    }

    // Проверка загруженного файла
    if (!isset($_FILES['check_photo']) || $_FILES['check_photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Ошибка при загрузке фото чека.');
    }

    $file = $_FILES['check_photo'];

    // Проверка размера
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Файл слишком большой (максимум 5 МБ).');
    }

    // Проверка типа
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png'])) {
        throw new Exception('Допустимы только файлы JPG или PNG.');
    }

    // Папка для загрузок
    $uploadsDir = dirname(__DIR__) . '/uploads';
    if (!file_exists($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }

    // Генерируем имя файла
    $fileName = uniqid('check_', true) . '.jpg';
    $targetPath = $uploadsDir . '/' . $fileName;

    // Сжимаем и сохраняем
    if ($mime === 'image/jpeg') {
        $img = imagecreatefromjpeg($file['tmp_name']);
    } else {
        $img = imagecreatefrompng($file['tmp_name']);
    }

    imagejpeg($img, $targetPath, 80);
    imagedestroy($img);

    $photoPath = 'uploads/' . $fileName;

    // Запись в базу
    $stmt = $pdo->prepare("
        INSERT INTO sale_participants 
        (name, phone, check_number, store_address, store_name, count_packs, photo_path, agree_conditions, agree_data)
        VALUES (:name, :phone, :check_number, :store_address, :store_name, :count_packs, :photo_path, :agree_conditions, :agree_data)
    ");

    $stmt->execute([
        ':name' => trim($_POST['name']),
        ':phone' => trim($_POST['phone']),
        ':check_number' => trim($_POST['check_number']),
        ':store_address' => $_POST['store_address'] ?? '',
        ':store_name' => $_POST['store_name'] ?? '',
        ':count_packs' => (int) ($_POST['count_packs'] ?? 0),
        ':photo_path' => $photoPath,
        ':agree_conditions' => isset($_POST['agree_promo']) ? 1 : 0,
        ':agree_data' => isset($_POST['agree_personal']) ? 1 : 0,
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Вы успешно зарегистрировались в акции! 🎉']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
