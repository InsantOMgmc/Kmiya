<?php
require 'config.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Заголовки таблицы
$headers = [
    'A1' => 'Имя',
    'B1' => 'Телефон',
    'C1' => 'Номер чека',
    'D1' => 'Адрес магазина',
    'E1' => 'Кол-во пачек',
    'F1' => 'Фото чека',
    'G1' => 'Согласие с акцией',
    'H1' => 'Согласие на данные',
    'I1' => 'Дата регистрации'
];

foreach ($headers as $cell => $text) {
    $sheet->setCellValue($cell, $text);
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

// Получаем данные из БД
$stmt = $pdo->query("SELECT * FROM sale_participants ORDER BY id DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$row = 2;
foreach ($data as $participant) {
    $sheet->setCellValue("A{$row}", $participant['name']);
    $sheet->setCellValue("B{$row}", $participant['phone']);
    $sheet->setCellValue("C{$row}", $participant['check_number']);
    $sheet->setCellValue("D{$row}", $participant['store_address']);
    $sheet->setCellValue("E{$row}", $participant['count_packs']);

    // 📸 Добавляем ссылку на фото
    $photoUrl = 'http://localhost/kmiya/' . $participant['photo_path'];
    $sheet->setCellValue("F{$row}", $photoUrl);
    $sheet->getCell("F{$row}")->getHyperlink()->setUrl($photoUrl);
    $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    $sheet->setCellValue("G{$row}", $participant['agree_conditions'] ? 'Да' : 'Нет');
    $sheet->setCellValue("H{$row}", $participant['agree_data'] ? 'Да' : 'Нет');
    $sheet->setCellValue("I{$row}", $participant['created_at'] ?? '');
    $row++;
}

// Автоматическая ширина колонок
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Отправляем Excel пользователю
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="participants.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
