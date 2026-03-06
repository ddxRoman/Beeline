<?php
/**
 * ПОЛНОСТЬЮ ПРОКОММЕНТИРОВАННАЯ ВЕРСИЯ
 */

// Отключаем вывод системных ошибок в браузер, чтобы они не ломали JSON-структуру
ini_set('display_errors', 0);
// Но включаем логирование всех типов ошибок для сервера
error_reporting(E_ALL);

// Подключаем файл с настройками базы данных и созданием объекта $pdo
require_once 'db_config.php';

// Устанавливаем заголовок, что сервер возвращает данные в формате JSON и кодировке UTF-8
header('Content-Type: application/json; charset=utf-8');

/**
 * Функция для отправки JSON-ответа и немедленного завершения работы скрипта
 */
function sendResponse($data) {
    // Кодируем массив в JSON, разрешаем кириллицу (UNESCAPED_UNICODE) и делаем красивый вид (PRETTY_PRINT)
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    // Останавливаем выполнение PHP, чтобы ничего лишнего не попало в ответ
    exit;
}

try {
    // -------------------------------------------------------------------------
    // 1. БЛОК АВТОКОМПЛИТА (срабатывает при вводе букв в поле поиска)
    // -------------------------------------------------------------------------
    if (isset($_GET['q'])) {
        // Убираем пробелы по краям запроса
        $q = trim($_GET['q']);
        
        // Если введено меньше 2 символов — возвращаем пустой результат (экономим ресурсы БД)
        if (mb_strlen($q) < 2) {
            sendResponse(['status' => 'success', 'results' => []]);
        }

        // Подготавливаем строку для SQL поиска (добавляем проценты для поиска подстроки)
        $searchTerm = "%" . $q . "%";
        
        // SQL запрос: ищем совпадение либо в названии улицы, либо в склеенной строке "улица дом"
        $sql = "SELECT id, street, house, is_available FROM addresses 
                WHERE street LIKE ? OR CONCAT(street, ' ', house) LIKE ? LIMIT 10";
        
        // Готовим запрос к базе данных для защиты от SQL-инъекций
        $stmt = $pdo->prepare($sql);
        // Выполняем запрос, подставляя наши переменные вместо знаков вопроса
        $stmt->execute([$searchTerm, $searchTerm]);
        
        // Получаем все найденные строки в виде ассоциативного массива
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Отправляем массив результатов на фронтенд
        sendResponse(['status' => 'success', 'results' => $results]);
    } 
    
    // -------------------------------------------------------------------------
    // 2. ПРОВЕРКА ПО ID (срабатывает, когда адрес выбран из выпадающего списка)
    // -------------------------------------------------------------------------
    if (isset($_GET['address_id']) && !empty($_GET['address_id'])) {
        // Принудительно приводим ID к целому числу для безопасности
        $id = (int)$_GET['address_id'];
        
        // Выбираем статус доступности и данные адреса по его уникальному номеру
        $sql = "SELECT is_available, street, house FROM addresses WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        // Извлекаем только одну (первую) найденную строку
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            // Проверяем статус: если 1 — доступно, иначе нет
            $is_ok = (int)$row['is_available'] === 1;
            
            // Формируем успешный ответ с данными о возможности подключения
            sendResponse([
                'status' => 'success', 
                'is_available' => $is_ok,
                'availability_message' => $is_ok ? "Подключение доступно" : "Подключение недоступно",
                'address' => $row['street'] . " " . $row['house']
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // 3. ПРОВЕРКА ПО ТЕКСТУ (срабатывает при ручном вводе или параметре full_address)
    // -------------------------------------------------------------------------
    if (isset($_GET['full_address'])) {
        // Получаем текст адреса и очищаем его от лишних пробелов
        $addr = trim($_GET['full_address']);
        
        // Создаем "чистую" версию адреса для поиска: меняем запятые на пробелы
        $cleanAddr = str_replace(',', ' ', $addr);
        // Заменяем множественные пробелы на один одиночный
        $cleanAddr = preg_replace('/\s+/', ' ', $cleanAddr);

        // SQL: пытаемся найти совпадение по склеенным полям street и house
        $sql = "SELECT is_available, street, house FROM addresses 
                WHERE CONCAT(street, ' ', house) LIKE ? 
                OR CONCAT(street, ',', house) LIKE ? 
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        // Ищем с использованием %, чтобы небольшие опечатки в начале/конце не мешали
        $stmt->execute(["%$cleanAddr%", "%$cleanAddr%"]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            // Превращаем значение из БД в булево (true/false)
            $is_ok = (int)$row['is_available'] === 1;
            
            // Отправляем результат найденного адреса
            sendResponse([
                'status' => 'success', 
                'full_address' => $addr,
                'is_available' => $is_ok,
                'availability_message' => $is_ok ? "Подключение доступно" : "Подключение недоступно",
                'found_as' => $row['street'] . " " . $row['house']
            ]);
        } else {
            // Если в базе такого адреса не нашлось
            sendResponse([
                'status' => 'error', 
                'message' => 'not_found',
                'availability_message' => 'Адрес не найден в базе данных',
                'full_address' => $addr
            ]);
        }
    }

    // Если в запросе не было ни 'q', ни 'address_id', ни 'full_address'
    sendResponse(['status' => 'error', 'message' => 'no_params_provided']);

} catch (Exception $e) {
    // Если на любом этапе произошла ошибка БД или PHP (исключение)
    sendResponse([
        'status' => 'error', 
        'message' => 'db_error', 
        'details' => $e->getMessage() // Выводим текст ошибки для отладки
    ]);
}