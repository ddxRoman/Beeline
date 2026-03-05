<?php
// Подключение к БД
$host = '127.0.1.13';
$db   = 'Beeline';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass);

// --- 17. Обработка удаления с подтверждением ---
if (isset($_GET['delete_tariff'])) {
    $id = $_GET['delete_tariff'];
    $stmt = $pdo->prepare("DELETE FROM tariffs WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
}

// --- 12. Обработка добавления/редактирования тарифа ---
if (isset($_POST['save_tariff'])) {
    $sql = "REPLACE INTO tariffs (id, name, price, speed, tv_channels, mobile_gb, mobile_minutes, mobile_sms, description, note, promo, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['id'] ?: null, 
        $_POST['name'], $_POST['price'], $_POST['speed'], 
        $_POST['tv_channels'], $_POST['mobile_gb'], $_POST['mobile_minutes'], 
        $_POST['mobile_sms'], $_POST['description'], $_POST['note'], 
        $_POST['promo'], $_POST['status']
    ]);
}

// ... (тут твое подключение к БД из предыдущего шага) ...

// --- 16. Обработка удаления адреса ---
if (isset($_GET['delete_address'])) {
    $id = $_GET['delete_address'];
    $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php#addresses_section"); // Возвращаемся к якорю
}

// --- 16. Обработка добавления/редактирования адреса ---
if (isset($_POST['save_address'])) {
    $sql = "REPLACE INTO addresses (id, city, street, house, is_available, max_speed) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    // Преобразуем чекбокс в 1 или 0
    $available = isset($_POST['is_available']) ? 1 : 0;
    
    $stmt->execute([
        $_POST['addr_id'] ?: null, 
        $_POST['city'], 
        $_POST['street'], 
        $_POST['house'], 
        $available, 
        $_POST['max_speed']
    ]);
    header("Location: admin.php#addresses_section");
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 20px; }
        .admin-section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; }
        .btn { padding: 5px 10px; cursor: pointer; border: none; border-radius: 3px; }
        .btn-edit { background: #ffc107; }
        .btn-del { background: #dc3545; color: white; }
        .btn-add { background: #28a745; color: white; margin-bottom: 10px; }
    </style>
</head>
<body>

<h1>Панель управления провайдером</h1>

<div class="admin-section">
    <h2>Управление тарифами</h2>
    <button class="btn btn-add" onclick="showTariffForm()">+ Добавить тариф</button>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Цена</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $tariffs = $pdo->query("SELECT * FROM tariffs")->fetchAll();
            foreach ($tariffs as $t): ?>
            <tr>
                <td><?= $t['id'] ?></td>
                <td><?= htmlspecialchars($t['name']) ?></td>
                <td><?= $t['price'] ?> ₽</td>
                <td><?= $t['status'] == 'active' ? 'Активный' : 'Архивный' ?></td>
                <td>
                    <button class="btn btn-edit" onclick='editTariff(<?= json_encode($t) ?>)'>Ред.</button>
                    <a href="?delete_tariff=<?= $t['id'] ?>" class="btn btn-del" onclick="return confirm('Вы точно хотите удалить тариф?')">Удалить</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="admin-section">
    <h2>База адресов</h2>
   <div class="admin-section" id="addresses_section">
    <h2>Управление адресной базой</h2>
    <button class="btn btn-add" onclick="showAddressForm()">+ Добавить адрес</button>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Город</th>
                <th>Улица</th>
                <th>Дом</th>
                <th>Возможность</th>
                <th>Макс. скорость</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $addresses = $pdo->query("SELECT * FROM addresses ORDER BY city, street")->fetchAll();
            foreach ($addresses as $a): ?>
            <tr>
                <td><?= $a['id'] ?></td>
                <td><?= htmlspecialchars($a['city']) ?></td>
                <td><?= htmlspecialchars($a['street']) ?></td>
                <td><?= htmlspecialchars($a['house']) ?></td>
                <td>
                    <span style="color: <?= $a['is_available'] ? 'green' : 'red' ?>">
                        <?= $a['is_available'] ? 'Доступно' : 'Нет связи' ?>
                    </span>
                </td>
                <td><?= $a['max_speed'] ?> Мбит/с</td>
                <td>
                    <button class="btn btn-edit" onclick='editAddress(<?= json_encode($a) ?>)'>Ред.</button>
                    <a href="?delete_address=<?= $a['id'] ?>" class="btn btn-del" onclick="return confirm('Удалить этот адрес из базы?')">Удалить</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="addressModal" style="display:none; position:fixed; top:10%; left:30%; width:40%; background:white; border:2px solid #333; padding:20px; z-index:101; box-shadow: 0 0 20px rgba(0,0,0,0.5);">
    <h3>Редактирование адреса</h3>
    <form method="POST">
        <input type="hidden" name="addr_id" id="a_id">
        <label>Город: <input type="text" name="city" id="a_city" required></label><br><br>
        <label>Улица: <input type="text" name="street" id="a_street" required></label><br><br>
        <label>Дом: <input type="text" name="house" id="a_house" required></label><br><br>
        <label>Макс. скорость: <input type="number" name="max_speed" id="a_speed"></label><br><br>
        <label>
            <input type="checkbox" name="is_available" id="a_available"> Возможность подключения
        </label><br><br>
        <button type="submit" name="save_address" class="btn btn-add">Сохранить</button>
        <button type="button" onclick="document.getElementById('addressModal').style.display='none'">Отмена</button>
    </form>
</div>
</div>

<div id="tariffModal" style="display:none; position:fixed; top:5%; left:25%; width:50%; background:white; border:2px solid #333; padding:20px; z-index:100;">
    <h3>Редактирование тарифа</h3>
    <form method="POST">
        <input type="hidden" name="id" id="f_id">
        <label>Название: <input type="text" name="name" id="f_name" required></label><br><br>
        <label>Цена: <input type="number" name="price" id="f_price"></label>
        <label>Скорость: <input type="number" name="speed" id="f_speed"></label><br><br>
        <label>Описание: <textarea name="description" id="f_desc"></textarea></label><br><br>
        <label>Статус: 
            <select name="status" id="f_status">
                <option value="active">Активный</option>
                <option value="archive">Архивный</option>
            </select>
        </label><br><br>
        <button type="submit" name="save_tariff" class="btn btn-add">Сохранить</button>
        <button type="button" onclick="document.getElementById('tariffModal').style.display='none'">Отмена</button>
    </form>
</div>

<script>
function editTariff(data) {
    document.getElementById('tariffModal').style.display = 'block';
    document.getElementById('f_id').value = data.id;
    document.getElementById('f_name').value = data.name;
    document.getElementById('f_price').value = data.price;
    document.getElementById('f_speed').value = data.speed;
    document.getElementById('f_desc').value = data.description;
    document.getElementById('f_status').value = data.status;
}

function showTariffForm() {
    document.getElementById('tariffModal').style.display = 'block';
    document.getElementById('f_id').value = '';
    // Очистить остальные поля...
}

function editAddress(data) {
    document.getElementById('addressModal').style.display = 'block';
    document.getElementById('a_id').value = data.id;
    document.getElementById('a_city').value = data.city;
    document.getElementById('a_street').value = data.street;
    document.getElementById('a_house').value = data.house;
    document.getElementById('a_speed').value = data.max_speed;
    document.getElementById('a_available').checked = parseInt(data.is_available) === 1;
}

function showAddressForm() {
    document.getElementById('addressModal').style.display = 'block';
    document.getElementById('a_id').value = '';
    document.getElementById('a_city').value = 'Краснодар'; // По умолчанию
    document.getElementById('a_street').value = '';
    document.getElementById('a_house').value = '';
    document.getElementById('a_speed').value = '100';
    document.getElementById('a_available').checked = true;
}

</script>

</body>
</html>