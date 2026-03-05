<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Контакты — Билайн Краснодар</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Глобальные настройки в стиле Билайн */
        :root {
            --beeline-yellow: #ffcc00;
            --beeline-black: #222222;
            --beeline-gray: #f2f2f2;
            --beeline-text-gray: #666666;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #fff;
            color: var(--beeline-black);
        }

        /* Шапка */
        .main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 10%;
            border-bottom: 1px solid #eee;
            background: #fff;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 24px;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: inherit;
        }

        .logo::before {
            content: '';
            width: 30px;
            height: 30px;
            background: linear-gradient(180deg, #000 50%, #ffcc00 50%);
            border-radius: 50%;
        }

        .phone {
            font-weight: 700;
            font-size: 18px;
            margin-right: 20px;
        }

        /* Навигация */
        .navbar {
            display: flex;
            justify-content: center;
            gap: 30px;
            padding: 15px 0;
            background: #fff;
            border-bottom: 1px solid #f5f5f5;
        }

        .navbar a {
            text-decoration: none;
            color: var(--beeline-black);
            font-weight: 500;
            font-size: 14px;
            transition: color 0.2s;
        }

        .navbar a:hover {
            color: var(--beeline-text-gray);
        }

        /* Контент страницы контактов */
        .contacts-container {
            padding: 60px 10%;
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            font-size: 48px;
            margin-bottom: 40px;
            font-weight: 800;
        }

        .contacts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .contact-card {
            background: #fff;
            border: 1px solid #eee;
            padding: 30px;
            border-radius: 24px;
            transition: transform 0.3s;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .contact-card h3 {
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .contact-card p {
            color: var(--beeline-text-gray);
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .contact-card .highlight {
            color: var(--beeline-black);
            font-weight: 700;
            font-size: 18px;
        }

        /* Карта */
        .map-section {
            width: 100%;
            height: 450px;
            border-radius: 32px;
            overflow: hidden;
            border: 1px solid #eee;
            margin-bottom: 60px;
        }

        /* Футер */
        footer {
            background: var(--beeline-black);
            color: #fff;
            padding: 60px 10%;
            text-align: center;
        }

        footer p {
            opacity: 0.6;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            h1 { font-size: 32px; }
            .contacts-container { padding: 30px 5%; }
        }
    </style>
</head>
<body>

<header class="main-header">
    <a href="index.php" class="logo">Билайн</a>
    <div class="header-info">
        <span class="phone">0611 / +7 800 700 0611</span>
    </div>
</header>

<nav class="navbar">
    <a href="index.php">Тарифы</a>
    <a href="index.php?cat=internet">Интернет</a>
    <a href="index.php?cat=tv_internet">Интернет + ТВ</a>
    <a href="index.php?cat=mobile_internet">Связь</a>
    <a href="#">Акции</a>
    <a href="contacts.php" style="font-weight: 700; border-bottom: 2px solid var(--beeline-yellow);">Контакты</a>
</nav>

<main class="contacts-container">
    <h1>Контакты и офисы</h1>

    <div class="contacts-grid">
        <div class="contact-card">
            <h3>📞 Поддержка</h3>
            <p>Для мобильной связи (бесплатно):</p>
            <p class="highlight">0611</p>
            <p>С любого телефона:</p>
            <p class="highlight">8 800 700 0611</p>
        </div>

        <div class="contact-card">
            <h3>🏢 Главный офис в Краснодаре</h3>
            <p>Адрес:</p>
            <p class="highlight">ул. Северная, 447</p>
            <p>Режим работы:</p>
            <p class="highlight">Пн-Вс: 09:00 — 21:00</p>
        </div>

        <div class="contact-card">
            <h3>🌐 Соцсети</h3>
            <p>Следите за новостями и задавайте вопросы:</p>
            <div style="margin-top: 15px;">
                <p class="highlight">VKontakte</p>
                <p class="highlight">Telegram</p>
            </div>
        </div>
    </div>

    <div class="map-section">
        <!-- Интеграция Яндекс Карт -->
        <script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A3a3c9e6c9e6c9e6c9e6c9e6c9e6c9e6c9e6c9e6c9e6c9e6c&amp;width=100%25&amp;height=450&amp;lang=ru_RU&amp;scroll=true"></script>
        <!-- Примечание: Для работы карты нужен реальный API ключ или ссылка из конструктора Яндекс.Карт -->
        <div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">
            <p>Здесь будут Яндекс Карты</p>
        </div>
    </div>
</main>

<footer>
    <p>© 2026 ПАО «ВымпелКом». Краснодарский край.</p>
</footer>

</body>
</html>