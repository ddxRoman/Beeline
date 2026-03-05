<?php
// Подключение к БД
$host = '127.0.1.13';
$db   = 'Beeline';

$user = 'root';
$pass = '';
$charset = 'utf8mb4';

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
    <meta name="robots" content="all"/>
<meta name="robots" content="index, follow"/>
<meta
    name="description"
    content="Подключить интернет не дорого Краснодар"
>
</head>
<body>

<header class="main-header">
    <a href="index.php">
    <img src="logo.svg" alt="Билайн">
    </a>
    <h1>Билайн домашний интернет и Телевидение</h1>
    <div class="header-info" style="display: flex; align-items: center;">
        <span class="phone">+7 (999) 000-00-00</span>
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
    <section class="check-address">
        <h3>Проверьте возможность подключения по адресу</h3>
        <div style="display: flex; justify-content: center;">
            <input type="text" id="addressInput" placeholder="Улица, дом...">
            <button onclick="checkAddress()">Проверить</button>
        </div>
    </section>

    <div class="tariff-grid">
        <?php foreach ($tariffs as $tariff): ?>
            <div class="tariff-card">
                <!-- Фон карточки (заменил на стилизованную картинку с пчелкой) -->
                <div class="card-header" style="background-image: url('<?= $tariff['image_url'] ?: 'https://img.freepik.com/free-photo/cute-bee-character-with-yellow-and-black-stripes_23-2151601673.jpg' ?>');">
                    <h3><?= htmlspecialchars($tariff['name']) ?></h3>
                    <p class="price"><?= number_format($tariff['price'], 0, '.', ' ') ?> ₽/мес</p>
                </div>

                <div class="card-body">
                    <!-- Акция (promo) -->
                    <?php if(!empty($tariff['promo'])): ?>
                        <div class="promo-text">🎁 <?= htmlspecialchars($tariff['promo']) ?></div>
                    <?php endif; ?>

                    <ul>
                        <!-- Скорость (speed) -->
                        <?php if($tariff['speed'] !== null): ?>
                            <li>
                                <span class="label">Скорость интернета</span>
                                <span class="val"><?= $tariff['speed'] ?> Мбит/с</span>
                            </li>
                        <?php endif; ?>

                        <!-- ТВ каналы (tv_channels) -->
                        <?php if($tariff['tv_channels'] !== null): ?>
                            <li>
                                <span class="label">Телевидение</span>
                                <span class="val"><?= $tariff['tv_channels'] ?> каналов</span>
                            </li>
                        <?php endif; ?>

                        <!-- Мобильный интернет (mobile_gb) -->
                        <?php if($tariff['mobile_gb'] !== null): ?>
                            <li>
                                <span class="label">Мобильный интернет</span>
                                <span class="val"><?= $tariff['mobile_gb'] ?> ГБ</span>
                            </li>
                        <?php endif; ?>

                        <!-- Минуты (mobile_minutes) -->
                        <?php if($tariff['mobile_minutes'] !== null): ?>
                            <li>
                                <span class="label">Звонки</span>
                                <span class="val"><?= $tariff['mobile_minutes'] ?> мин.</span>
                            </li>
                        <?php endif; ?>

                        <!-- СМС (mobile_sms) -->
                        <?php if($tariff['mobile_sms'] !== null): ?>
                            <li>
                                <span class="label">SMS</span>
                                <span class="val"><?= $tariff['mobile_sms'] ?> шт.</span>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Описание (description) -->
                    <?php if(!empty($tariff['description'])): ?>
                        <p class="description"><?= nl2br(htmlspecialchars($tariff['description'])) ?></p>
                    <?php endif; ?>

                    <!-- Примечание (note) -->
                    <?php if(!empty($tariff['note'])): ?>
                        <p class="note">* <?= htmlspecialchars($tariff['note']) ?></p>
                    <?php endif; ?>

                    <button class="btn-card" onclick="openModal('<?= htmlspecialchars($tariff['name']) ?>')">Выбрать тариф</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<footer class="main-footer">
    <div class="footer-grid">
        <div class="footer-column">
            <h4>Продукты</h4>
            <ul>
                <li><a href="#">Домашний интернет</a></li>
                <li><a href="#">Мобильная связь</a></li>
                <li><a href="#">Билайн ТВ</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>Компания</h4>
            <ul>
                <li><a href="contacts.php">О нас</a></li>
                <li><a href="#">Вакансии</a></li>
                <li><a href="#">Новости</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>Помощь</h4>
            <ul>
                <li><a href="#">Личный кабинет</a></li>
                <li><a href="#">Оплата</a></li>
                <li><a href="#">Настройка роутера</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 ПАО «ВымпелКом». Краснодарский край.</p>
        <div>VK | TG | OK</div>
    </div>
</footer>

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
</script>
</body>
</html>