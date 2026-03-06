<?php
// Подключение к БД


require_once 'db_config.php';
require_once 'static_date.php';

// ВКЛЮЧАЕМ ОТОБРАЖЕНИЕ ОШИБОК для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Подключаем конфигурацию БД
require_once 'db_config.php';

// --- УДАЛЕНИЕ ТАРИФА ---
if (isset($_GET['delete_tariff'])) {
    $id = $_GET['delete_tariff'];
    $stmt = $pdo->prepare("DELETE FROM tariffs WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

// --- СОХРАНЕНИЕ ТАРИФА (Добавление или Редактирование) ---
if (isset($_POST['save_tariff'])) {
    $sql = "REPLACE INTO tariffs (
        id, name, price, speed, tv_channels, mobile_gb, 
        mobile_minutes, mobile_sms, description, note, 
        promo, category, status, image_url
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['id'] ?: null,
        $_POST['name'],
        $_POST['price'] ?: 0,
        $_POST['speed'] ?: null,
        $_POST['tv_channels'] ?: null,
        $_POST['mobile_gb'] ?: null,
        $_POST['mobile_minutes'] ?: null,
        $_POST['mobile_sms'] ?: null,
        $_POST['description'],
        $_POST['note'],
        $_POST['promo'],
        $_POST['category'],
        $_POST['status'],
        $_POST['image_url'] ?: 'default_bg.webp'
    ]);
    header("Location: admin.php");
    exit;
}

// --- УДАЛЕНИЕ АДРЕСА ---
if (isset($_GET['delete_address'])) {
    $id = $_GET['delete_address'];
    $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php#addresses_section");
    exit;
}

// --- СОХРАНЕНИЕ АДРЕСА ---
if (isset($_POST['save_address'])) {
    $sql = "REPLACE INTO addresses (id, city, street, house, is_available, max_speed) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $available = isset($_POST['is_available']) ? 1 : 0;
    $stmt->execute([
        $_POST['addr_id'] ?: null,
        $_POST['city'],
        $_POST['street'],
        $_POST['house'],
        $available,
        $_POST['max_speed'] ?: 100
    ]);
    header("Location: admin.php#addresses_section");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель Билайн</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #222; border-bottom: 3px solid #ffcc00; padding-bottom: 10px; display: inline-block; }
        
        .admin-section { background: white; padding: 25px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; font-size: 20px; display: flex; justify-content: space-between; align-items: center; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #fafafa; font-weight: 600; color: #666; }
        tr:hover { background: #fdfdfd; }

        .btn { padding: 8px 16px; cursor: pointer; border: none; border-radius: 6px; font-weight: 600; transition: 0.2s; text-decoration: none; display: inline-block; font-size: 13px; }
        .btn-edit { background: #ffcc00; color: #000; }
        .btn-edit:hover { background: #e6b800; }
        .btn-del { background: #ff4d4d; color: white; margin-left: 5px; }
        .btn-del:hover { background: #cc0000; }
        .btn-add { background: #000; color: #ffcc00; margin-bottom: 15px; }
        .btn-add:hover { background: #333; }

        /* Стили модального окна */
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); z-index:1000; align-items: center; justify-content: center; overflow-y: auto; padding: 20px 0; }
        .modal-content { background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 800px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); position: relative; }
        .close-modal { position: absolute; right: 20px; top: 15px; font-size: 24px; cursor: pointer; color: #999; }
        
        /* Стили формы */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }
        .form-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        .form-group label { font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #888; text-transform: uppercase; }
        .form-group input, .form-group select, .form-group textarea { padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .form-group textarea { height: 80px; resize: vertical; }
        .full-width { grid-column: span 3; }
        .span-2 { grid-column: span 2; }

        .category-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .cat-all { background: #eee; }
        .cat-internet { background: #e3f2fd; color: #1976d2; }
        .cat-tv_internet { background: #f3e5f5; color: #7b1fa2; }
        .cat-mobile_internet { background: #fff3e0; color: #f57c00; }
        .cat-triple { background: #e8f5e9; color: #388e3c; }
        .cat-promo { background: #ffebee; color: #d32f2f; }
    </style>
</head>
<body>

<div class="container">
    <h1>Управление Билайн</h1>

    <!-- СЕКЦИЯ ТАРИФОВ -->
    <div class="admin-section">
        <h2>Тарифы <button class="btn btn-add" onclick="showTariffForm()">+ Новый тариф</button></h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Категория</th>
                    <th>Цена</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $tariffs = $pdo->query("SELECT * FROM tariffs ORDER BY id DESC")->fetchAll();
                foreach ($tariffs as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                    <td><span class="category-badge cat-<?= $t['category'] ?>"><?= $t['category'] ?></span></td>
                    <td><?= number_format($t['price'], 0, '.', ' ') ?> ₽</td>
                    <td>
                        <span style="color: <?= $t['status'] == 'active' ? '#28a745' : '#999' ?>">
                            <?= $t['status'] == 'active' ? '● Активен' : '○ Архив' ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-edit" onclick='editTariff(<?= json_encode($t, JSON_HEX_APOS) ?>)'>Ред.</button>
                        <a href="?delete_tariff=<?= $t['id'] ?>" class="btn btn-del" onclick="return confirm('Удалить тариф?')">Удалить</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- СЕКЦИЯ АДРЕСОВ -->
    <div class="admin-section" id="addresses_section">
        <h2>Адреса подключения <button class="btn btn-add" onclick="showAddressForm()">+ Добавить адрес</button></h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Город</th>
                    <th>Улица</th>
                    <th>Дом</th>
                    <th>Доступность</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $addresses = $pdo->query("SELECT * FROM addresses ORDER BY id DESC LIMIT 50")->fetchAll();
                foreach ($addresses as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['city']) ?></td>
                    <td><?= htmlspecialchars($a['street']) ?></td>
                    <td><?= htmlspecialchars($a['house']) ?></td>
                    <td><?= $a['is_available'] ? '<b style="color:green">Да</b>' : '<b style="color:red">Нет</b>' ?></td>
                    <td>
                        <button class="btn btn-edit" onclick='editAddress(<?= json_encode($a) ?>)'>Ред.</button>
                        <a href="?delete_address=<?= $a['id'] ?>" class="btn btn-del" onclick="return confirm('Удалить адрес?')">Удалить</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- МОДАЛКА ТАРИФА -->
<div id="tariffModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal('tariffModal')">&times;</span>
        <h3 id="tariffModalTitle">Редактирование тарифа</h3>
        <form method="POST">
            <input type="hidden" name="id" id="f_id">
            <div class="form-grid">
                <div class="form-group span-2">
                    <label>Название тарифа</label>
                    <input type="text" name="name" id="f_name" required>
                </div>
                <div class="form-group">
                    <label>Цена (₽/мес)</label>
                    <input type="number" step="0.01" name="price" id="f_price" required>
                </div>
                
                <div class="form-group">
                    <label>Категория</label>
                    <select name="category" id="f_category">
                        <option value="all">Все (Общая)</option>
                        <option value="internet">Только Интернет</option>
                        <option value="tv_internet">Интернет + ТВ</option>
                        <option value="mobile_internet">Связь + Интернет</option>
                        <option value="triple">Все в одном (Triple Play)</option>
                        <option value="promo">Акция</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Статус</label>
                    <select name="status" id="f_status">
                        <option value="active">Активный</option>
                        <option value="archive">Архивный</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Скорость (Мбит/с)</label>
                    <input type="number" name="speed" id="f_speed">
                </div>

                <div class="form-group">
                    <label>ТВ Каналы</label>
                    <input type="number" name="tv_channels" id="f_tv">
                </div>
                <div class="form-group">
                    <label>Мобильный инет (ГБ)</label>
                    <input type="number" name="mobile_gb" id="f_gb">
                </div>
                <div class="form-group">
                    <label>Минуты</label>
                    <input type="number" name="mobile_minutes" id="f_min">
                </div>

                <div class="form-group">
                    <label>СМС</label>
                    <input type="number" name="mobile_sms" id="f_sms">
                </div>
                <div class="form-group span-2">
                    <label>URL Картинки шапки</label>
                    <input type="text" name="image_url" id="f_img" placeholder="default_bg.webp">
                </div>

                <div class="form-group full-width">
                    <label>Акция (текст на плашке)</label>
                    <input type="text" name="promo" id="f_promo" placeholder="Например: 3 месяца в подарок">
                </div>

                <div class="form-group span-2">
                    <label>Описание (основной текст)</label>
                    <textarea name="description" id="f_desc"></textarea>
                </div>
                <div class="form-group">
                    <label>Примечание (мелкий шрифт)</label>
                    <textarea name="note" id="f_note"></textarea>
                </div>
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn" onclick="closeModal('tariffModal')">Отмена</button>
                <button type="submit" name="save_tariff" class="btn btn-add" style="margin-bottom:0">Сохранить изменения</button>
            </div>
        </form>
    </div>
</div>

<!-- МОДАЛКА АДРЕСА -->
<div id="addressModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <span class="close-modal" onclick="closeModal('addressModal')">&times;</span>
        <h3>Данные адреса</h3>
        <form method="POST">
            <input type="hidden" name="addr_id" id="a_id">
            <div class="form-group">
                <label>Город</label>
                <input type="text" name="city" id="a_city" required>
            </div>
            <div class="form-group">
                <label>Улица</label>
                <input type="text" name="street" id="a_street" required>
            </div>
            <div class="form-group">
                <label>Дом</label>
                <input type="text" name="house" id="a_house" required>
            </div>
            <div class="form-group">
                <label>Макс. скорость в доме</label>
                <input type="number" name="max_speed" id="a_speed">
            </div>
            <div class="form-group" style="flex-direction: row; align-items: center; gap: 10px;">
                <input type="checkbox" name="is_available" id="a_available" style="width: auto;">
                <label style="margin: 0;">Дом подключен к сети</label>
            </div>
            <button type="submit" name="save_address" class="btn btn-add" style="width: 100%; margin-top: 10px;">Сохранить адрес</button>
        </form>
    </div>
</div>

<script>
function editTariff(data) {
    document.getElementById('tariffModal').style.display = 'flex';
    document.getElementById('tariffModalTitle').innerText = 'Редактировать: ' + data.name;
    
    document.getElementById('f_id').value = data.id;
    document.getElementById('f_name').value = data.name;
    document.getElementById('f_price').value = data.price;
    document.getElementById('f_speed').value = data.speed;
    document.getElementById('f_tv').value = data.tv_channels;
    document.getElementById('f_gb').value = data.mobile_gb;
    document.getElementById('f_min').value = data.mobile_minutes;
    document.getElementById('f_sms').value = data.mobile_sms;
    document.getElementById('f_desc').value = data.description;
    document.getElementById('f_note').value = data.note;
    document.getElementById('f_promo').value = data.promo;
    document.getElementById('f_category').value = data.category;
    document.getElementById('f_status').value = data.status;
    document.getElementById('f_img').value = data.image_url;
}

function showTariffForm() {
    document.getElementById('tariffModal').style.display = 'flex';
    document.getElementById('tariffModalTitle').innerText = 'Новый тариф';
    document.getElementById('f_id').value = '';
    document.querySelector('#tariffModal form').reset();
    // Устанавливаем категорию по умолчанию
    document.getElementById('f_category').value = 'all';
    document.getElementById('f_status').value = 'active';
}

function editAddress(data) {
    document.getElementById('addressModal').style.display = 'flex';
    document.getElementById('a_id').value = data.id;
    document.getElementById('a_city').value = data.city;
    document.getElementById('a_street').value = data.street;
    document.getElementById('a_house').value = data.house;
    document.getElementById('a_speed').value = data.max_speed;
    document.getElementById('a_available').checked = parseInt(data.is_available) === 1;
}

function showAddressForm() {
    document.getElementById('addressModal').style.display = 'flex';
    document.getElementById('a_id').value = '';
    document.getElementById('a_city').value = 'Краснодар';
    document.getElementById('a_street').value = '';
    document.getElementById('a_house').value = '';
    document.getElementById('a_speed').value = '100';
    document.getElementById('a_available').checked = true;
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = "none";
    }
}
</script>

</body>
</html>