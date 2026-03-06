<?php
// Подключение к БД
require_once 'db_config.php';
require_once 'static_date.php';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

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
$tariffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Билайн — Интернет и ТВ в Краснодаре</title>
    <link rel="icon" href="favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="robots" content="index, follow"/>
    <meta name="description" content="Подключить интернет недорого в Краснодаре">
    <style>

    </style>
</head>
<body>

<header class="main-header">
    <a href="index.php">
        <img src="logo.svg" alt="Билайн">
    </a>
    <h1>Билайн домашний интернет и Телевидение</h1>
    <div class="header-info" style="display: flex; align-items: center;">
        <span class="phone"><?= $phone ?></span>
        <button class="btn-connect" onclick="openModal('Заявка')">Подключить</button>
    </div>
</header>

<nav class="navbar">
    <a href="index.php">Тарифы</a>
    <a href="index.php?cat=internet">Интернет</a>
    <a href="index.php?cat=tv_internet">Интернет + ТВ</a>
    <a href="index.php?cat=mobile_internet">Связь</a>
    <a href="#">Акции</a>
    <a href="contacts.php">Контакты</a>
</nav>

<main>
    <!-- ОБНОВЛЕННЫЙ БЛОК ПРОВЕРКИ АДРЕСА -->
    <section class="check-address">
        <i class="fa-solid fa-bugs bee-decor bee-1"></i>
        <i class="fa-solid fa-bugs bee-decor bee-2"></i>
        
        <h3>Проверьте возможность подключения по адресу</h3>
        <div class="search-container">
            <input type="text" id="addressInput" placeholder="Улица, дом (например: Красная, 1)">
            <button onclick="checkAddress()">
                <i class="fa-solid fa-magnifying-glass"></i> Проверить
            </button>
        </div>
    </section>

    <div class="tariff-grid">
        <?php foreach ($tariffs as $tariff): ?>
            <div class="tariff-card">
                <div class="card-header" style="background-image: url('<?= $tariff['image_url'] ?: 'https://img.freepik.com/free-photo/cute-bee-character-with-yellow-and-black-stripes_23-2151601673.jpg' ?>');">
                    <h3><?= htmlspecialchars($tariff['name']) ?></h3>
                    <p class="price"><?= number_format($tariff['price'], 0, '.', ' ') ?> ₽/мес</p>
                </div>

                <div class="card-body">
                    <?php if(!empty($tariff['promo'])): ?>
                        <div class="promo-text">🎁 <?= htmlspecialchars($tariff['promo']) ?></div>
                    <?php endif; ?>

                    <ul>
                        <?php if($tariff['speed'] !== null): ?>
                            <li>
                                <span class="label">Скорость интернета</span>
                                <span class="val"><?= $tariff['speed'] ?> Мбит/с</span>
                            </li>
                        <?php endif; ?>

                        <?php if($tariff['tv_channels'] !== null): ?>
                            <li>
                                <span class="label">Телевидение</span>
                                <span class="val"><?= $tariff['tv_channels'] ?> каналов</span>
                            </li>
                        <?php endif; ?>

                        <?php if($tariff['mobile_gb'] !== null): ?>
                            <li>
                                <span class="label">Мобильный интернет</span>
                                <span class="val"><?= $tariff['mobile_gb'] ?> ГБ</span>
                            </li>
                        <?php endif; ?>

                        <?php if($tariff['mobile_minutes'] !== null): ?>
                            <li>
                                <span class="label">Звонки</span>
                                <span class="val"><?= $tariff['mobile_minutes'] ?> мин.</span>
                            </li>
                        <?php endif; ?>

                        <?php if($tariff['mobile_sms'] !== null): ?>
                            <li>
                                <span class="label">SMS</span>
                                <span class="val"><?= $tariff['mobile_sms'] ?> шт.</span>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <?php if(!empty($tariff['description'])): ?>
                        <p class="description"><?= nl2br(htmlspecialchars($tariff['description'])) ?></p>
                    <?php endif; ?>

                    <?php if(!empty($tariff['note'])): ?>
                        <p class="note">* <?= htmlspecialchars($tariff['note']) ?></p>
                    <?php endif; ?>

                    <button class="btn-card" onclick="openModal('<?= htmlspecialchars($tariff['name']) ?>')">Выбрать тариф</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once 'footer.php'; ?>

<!-- МОДАЛЬНОЕ ОКНО -->
<div id="modalForm" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <form action="send_lead.php" method="POST">
            <h2 style="margin-top: 0;">Подключение</h2>
            <div id="selectedTariffBadge" class="tariff-badge"></div>
            <input type="hidden" name="tariff_id" id="hiddenTariff">
            <div class="form-grid">
                <div class="form-group half"><input type="text" name="phone" placeholder="+7 (___) ___-__-__" required></div>
                <div class="form-group half"><input type="text" name="name" placeholder="Ваше имя" required></div>
                <div class="form-group third"><input type="text" name="city" value="Краснодар" required></div>
                <div class="form-group third"><input type="text" name="street" placeholder="Улица" required></div>
                <div class="form-group third">
                    <div style="display: flex; gap: 5px;">
                        <input type="text" name="house" placeholder="Дом" required>
                        <input type="text" name="apartment" placeholder="Кв.">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-submit">Отправить заявку</button>
        </form>
    </div>
</div>

<script>
    function openModal(tariffName) {
        document.getElementById('modalForm').style.display = "flex";
        document.getElementById('hiddenTariff').value = tariffName;
        document.getElementById('selectedTariffBadge').innerText = "Тариф: " + tariffName;
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        document.getElementById('modalForm').style.display = "none";
        document.body.style.overflow = 'auto';
    }
    window.onclick = function(e) { if (e.target.className == 'modal') closeModal(); }
    
    // Заглушка для функции проверки адреса
    function checkAddress() {
        const addr = document.getElementById('addressInput').value;
        if(!addr) return alert('Введите адрес');
        alert('Проверяем техническую возможность для: ' + addr);
        // Здесь будет AJAX запрос к базе данных
    }
</script>
</body>
</html>