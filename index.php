<?php
// Подключение к БД
$host = '127.0.1.13';
$db   = 'Beeline';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass);

// Получаем категорию из URL
$category = isset($_GET['cat']) ? $_GET['cat'] : 'all';

// Запрос к БД: выбираем только АКТИВНЫЕ тарифы
if ($category == 'all') {
    $stmt = $pdo->prepare("SELECT * FROM tariffs WHERE status = 'active'");
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("SELECT * FROM tariffs WHERE category = ? AND status = 'active'");
    $stmt->execute([$category]);
}
$tariffs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Провайдер "Связь-2026"</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

<header class="main-header">
    <div class="logo">LOGO</div>
    <div class="header-info">
        <span class="phone">+7 (999) 000-00-00</span>
        <button class="btn-connect" onclick="openModal('Общая заявка')">Подключить</button>
    </div>
</header>

<nav class="navbar">
    <a href="?cat=all">Все тарифы</a>
    <a href="?cat=internet">Домашний интернет</a>
    <a href="?cat=tv_internet">Интернет + ТВ</a>
    <a href="?cat=mobile_internet">Интернет + Мобильная связь</a>
    <a href="?cat=triple">Интернет + Моб. связь + ТВ</a>
    <a href="?cat=promo">Акции</a>
    <a href="contacts.php">Контакты</a>
</nav>

<main>
    <section class="check-address">
        <h3>Проверить возможность подключения</h3>
        <input type="text" id="addressInput" placeholder="Введите ваш адрес...">
        <button onclick="checkAddress()">Проверить</button>
        <div id="addressResult"></div>
    </section>

    <div class="tariff-grid">
        <?php foreach ($tariffs as $tariff): ?>
            <div class="tariff-card">
                <div class="card-header" style="background-image: url('<?= $tariff['image_url'] ?: 'tariff_bg.jpg' ?>');">
                    <h3><?= htmlspecialchars($tariff['name']) ?></h3>
                    <p class="price"><?= number_format($tariff['price'], 0, '.', ' ') ?> ₽/мес</p>
                </div>
                <div class="card-body">
                    <ul>
                        <li>⚡ Скорость: <?= $tariff['speed'] ?> Мбит/с</li>
                        <?php if($tariff['tv_channels']): ?><li>📺 ТВ: <?= $tariff['tv_channels'] ?> каналов</li><?php endif; ?>
                    </ul>
                    <button class="btn-card" onclick="openModal('<?= htmlspecialchars($tariff['name']) ?>')">Подключить</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- ОБНОВЛЕННОЕ МОДАЛЬНОЕ ОКНО -->
<div id="modalForm" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <form action="send_lead.php" method="POST">
            <h2>Заявка на подключение</h2>
            <div class="subtitle">Перезвоним, проверим возможность подключения и ответим на вопросы.</div>
            
            <div id="selectedTariffBadge" class="tariff-badge"></div>
            <input type="hidden" name="tariff_id" id="hiddenTariff">

            <div class="form-grid">
                <!-- Контактные данные -->
                <div class="form-group half">
                    <label>Номер телефона</label>
                    <input type="text" name="phone" placeholder="+7 (___) ___-__-__" required>
                </div>
                <div class="form-group half">
                    <label>Ваше имя</label>
                    <input type="text" name="name" placeholder="Имя" required>
                </div>

                <!-- Адресные данные (как на ваших примерах) -->
                <div class="form-group third">
                    <label>Город</label>
                    <input type="text" name="city" value="Краснодар" required>
                </div>
                <div class="form-group third">
                    <label>Улица</label>
                    <input type="text" name="street" placeholder="Улица" required>
                </div>
                <div class="form-group third">
                    <div style="display: flex; gap: 8px;">
                        <div style="flex: 1;">
                            <label>Дом</label>
                            <input type="text" name="house" placeholder="Дом" required>
                        </div>
                        <div style="flex: 1;">
                            <label>Кв.</label>
                            <input type="text" name="apartment" placeholder="Кв.">
                        </div>
                    </div>
                </div>

                <div class="consent">
                    Нажимая на кнопку Отправить, вы соглашаетесь на <a href="#">обработку ваших персональных данных</a>.
                </div>

                <div class="modal-buttons">
                    <button type="submit" class="btn-submit">Отправить</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">Отмена</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(tariffName) {
        const modal = document.getElementById('modalForm');
        const hiddenInput = document.getElementById('hiddenTariff');
        const badge = document.getElementById('selectedTariffBadge');
        
        hiddenInput.value = tariffName;
        badge.innerText = tariffName;
        
        // Устанавливаем flex для центрирования
        modal.style.display = "flex"; 
        document.body.style.overflow = 'hidden'; // Запрет прокрутки фона
    }

    function closeModal() {
        const modal = document.getElementById('modalForm');
        modal.style.display = "none";
        document.body.style.overflow = 'auto';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('modalForm');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
<script src="logic.js"></script>
</body>
</html>