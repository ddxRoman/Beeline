<?php
/**
 * Файл для обработки заявок и отправки уведомлений в Telegram.
 * Интегрировано сохранение в БД (таблица applications).
 */

// Подключаем настройки базы данных (файл db_config.php должен быть в той же папке)
require_once 'db_config.php';



// Получаем данные из формы (защищаем от пустых значений)
$name    = $_POST['name'] ?? 'Не указано';
$phone   = $_POST['phone'] ?? 'Не указано';
$tariff  = $_POST['tariff_id'] ?? 'Не выбран';
$city = $_POST['city'] ?? '';
$street = $_POST['street'] ?? '';
$house = $_POST['house'] ?? '';
$apartment = $_POST['apartment'] ?? ''; // Если в форме есть поле адреса

$address = $city.' ул.'.$street.' д.'.$house.' кв'.$apartment;
// 1. Запись в БД
try {
    // Подготавливаем запрос (имена полей соответствуют вашей структуре)
    $sql = "INSERT INTO applications (user_name, phone, address, tariff_name) 
            VALUES (:user_name, :phone, :address, :tariff_name)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_name'   => $name,
        ':phone'       => $phone,
        ':address'     => $address,
        ':tariff_name' => $tariff
    ]);
} catch (PDOException $e) {
    // В случае ошибки записи в БД можно залогировать ошибку в системный лог
    // error_log("Ошибка БД: " . $e->getMessage());
}

// 2. Подготовка текста сообщения для Telegram
$text = "🚀 <b>Новая заявка!</b>\n\n";
$text .= "<b>Тариф:</b> " . htmlspecialchars($tariff) . "\n";
$text .= "<b>Имя:</b> " . htmlspecialchars($name) . "\n";
$text .= "<b>Телефон:</b> " . htmlspecialchars($phone);

if (!empty($address)) {
    $text .= "\n<b>Адрес:</b> " . htmlspecialchars($address);
}

// 3. Отправка в Telegram через cURL
$url = "https://api.telegram.org/bot{$token}/sendMessage";
$data = [
    'chat_id'    => $chat_id,
    'text'       => $text,
    'parse_mode' => 'HTML'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

$response = curl_exec($ch);
curl_close($ch);

?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Спасибо за заявку! | Билайн</title>
    <!-- Редирект через 5 секунд на главную -->
    <meta http-equiv="refresh" content="5;url=index.php">
    <style>
        :root {
            --beeline-yellow: #ffcc00;
            --beeline-black: #000000;
            --beeline-gray: #f6f6f6;
        }

        body {
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--beeline-gray);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--beeline-black);
        }

        .thanks-card {
            background: white;
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            text-align: center;
            max-width: 450px;
            width: 90%;
            position: relative;
            overflow: hidden;
        }

        /* Желтая полоска сверху как у Билайна */
        .thanks-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: repeating-linear-gradient(
                90deg,
                var(--beeline-yellow),
                var(--beeline-yellow) 50%,
                var(--beeline-black) 50%,
                var(--beeline-black) 100%
            );
            background-size: 40px 100%;
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background-color: var(--beeline-yellow);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .icon-box svg {
            width: 40px;
            height: 40px;
            fill: var(--beeline-black);
        }

        h1 {
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 800;
        }

        p {
            font-size: 16px;
            color: #666;
            line-height: 1.5;
            margin-bottom: 30px;
        }

        .timer-box {
            font-size: 13px;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .progress-bar {
            width: 100px;
            height: 4px;
            background: #eee;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--beeline-yellow);
            width: 100%;
            animation: shrink 5s linear forwards;
        }

        .btn-back {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--beeline-black);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: transform 0.2s, background-color 0.2s;
            margin-bottom: 25px;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            background-color: #333;
        }

        @keyframes scaleIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @keyframes shrink {
            from { width: 100%; }
            to { width: 0%; }
        }

        /* Декоративные круги на фоне */
        .bg-circle {
            position: fixed;
            z-index: -1;
            border-radius: 50%;
            background: var(--beeline-yellow);
            opacity: 0.1;
        }
    </style>
</head>
<body>

    <div class="bg-circle" style="width: 400px; height: 400px; top: -100px; right: -100px;"></div>
    <div class="bg-circle" style="width: 200px; height: 200px; bottom: 50px; left: -50px;"></div>

    <div class="thanks-card">
        <div class="icon-box">
            <svg viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            </svg>
        </div>
        
        <h1>Заявка принята!</h1>
        <p>Спасибо, что выбрали нас. <br>Наш менеджер перезвонит вам в течение 15 минут для уточнения деталей подключения.</p>
        
        <a href="index.php" class="btn-back">Вернуться сейчас</a>

        <div class="timer-box">
            <span>Вы будете перенаправлены через <span id="countdown">5</span> сек.</span>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
        </div>
    </div>

    <script>
        let seconds = 15;
        const countdownEl = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;
            if (seconds <= 0) clearInterval(timer);
        }, 1000);
    </script>
</body>
</html>