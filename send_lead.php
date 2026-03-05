<?php
$token = "ТВОЙ_ТГ_БОТ_ТОКЕН";
$chat_id = "ТВОЙ_ЧАТ_АЙДИ";

$name = $_POST['name'];
$phone = $_POST['phone'];
$tariff = $_POST['tariff_id'];

// 1. Запись в БД (пример)
// $db->query("INSERT INTO applications ...");

// 2. Отправка в Telegram
$text = "🚀 Новая заявка!\nТариф: $tariff\nИмя: $name\nТелефон: $phone";
file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($text));

echo "Спасибо! Мы свяжемся с вами.";
?>