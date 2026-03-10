<?php
/**
 * Главная страница сайта Билайн
 * Реализован живой поиск адреса (Autocomplete) с приоритетным отображением поверх контента.
 */
require_once 'db_config.php';
require_once 'static_date.php';

$category = isset($_GET['cat']) ? $_GET['cat'] : 'all';

try {
    if ($category == 'all') {
        $stmt = $pdo->prepare("SELECT * FROM tariffs WHERE status = 'active' ORDER BY id DESC");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tariffs WHERE category = ? AND status = 'active' ORDER BY id DESC");
        $stmt->execute([$category]);
    }
    $tariffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tariffs = [];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Билайн — Интернет и ТВ в Краснодаре</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="mobile_main.css">
    <meta name="robots" content="all"/>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<meta name="robots" content="index, follow"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --beeline-yellow: #ffcc00;
            --beeline-black: #000000;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #fff;
        }

        /* Шапка */
        .main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 8%;
            border-bottom: 1px solid #eee;
            position: relative;
            z-index: 1001;
            background: #fff;
        }

        .phone { font-weight: bold; font-size: 18px; }



        /* Блок поиска (Hero) */
        .hero {
            /* background: linear-gradient(90deg, var(--beeline-yellow) 0%, #ffe066 100%); */
            background-image: url('bg_check_adress.png');
            background-size: cover ;
            padding: 60px 8%;
            text-align: center;
            position: relative;
            z-index: 1000; 
        }

        .hero h1 { font-size: 32px; margin-bottom: 20px; color: #000; }

        .search-box {
            position: relative;
            max-width: 700px;
            margin: 0 auto;
            display: flex;
            gap: 10px;
        }

        #addressInput {
            flex: 1;
            padding: 16px 25px;
            border-radius: 30px;
            border: none;
            font-size: 16px;
            outline: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* Выпадающий список */
        #autocomplete-list {
            position: absolute;
            top: calc(30% + 10px);
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
            z-index: 99999 !important; 
            max-height: 350px;
            overflow-y: auto;
            display: none;
            border: 1px solid #ddd;
            text-align: left;
        }

        .autocomplete-item {
            padding: 15px 20px;
            cursor: pointer;
            border-bottom: 1px solid #f5f5f5;
        }

        .autocomplete-item:hover { background: #fff9e6; }
        .addr-main { display: block; font-weight: bold; color: #000; }
        .addr-sub { display: block; font-size: 12px; color: #888; }

        #checkBtn {
            background: #000;
            color: #fff;
            border: none;
            padding: 0 35px;
            border-radius: 30px;
            font-weight: bold;
            cursor: pointer;
        }

        /* Результаты */
        .result-box {
            margin: 25px auto 0;
            max-width: 700px;
            padding: 20px;
            background: #fff;
            border-radius: 15px;
            display: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* Сетка тарифов */
        .tariff-section {
            padding: 50px 8%;
            position: relative;
            z-index: 1; 
        }

        .tariff-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .tariff-card {
            border: 1px solid #eee;
            border-radius: 20px;
            overflow: hidden;
            transition: 0.3s;
            background: #fff;
        }

        .tariff-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }

        .card-header {
            height: 140px;
            background-size: cover;
            background-position: center;
            padding: 20px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            position: relative;
        }

        .card-header::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.4);
        }

        .card-header h3, .card-header .price { position: relative; z-index: 2; margin: 0; }
        .card-header .price { font-size: 24px; font-weight: bold; color: var(--beeline-yellow); }

        .card-body { padding: 20px; }
        .card-body ul { list-style: none; padding: 0; margin: 0 0 20px; }
        .card-body li { margin-bottom: 8px; font-size: 14px; display: flex; align-items: center; gap: 8px; }

        .btn-order {
            width: 100%;
            padding: 12px;
            background: var(--beeline-yellow);
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-order:hover { background: #e6b800; }

        /* Модалка */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.8);
            z-index: 100000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            position: relative;
        }
        .link_phone{
            color: black;
            text-decoration: none;
        }
    </style>
</head>
<body>

<header class="main-header">
    <a href="index.php"><img src="logo.svg" alt="Билайн" width="100"></a>
    <div class="phone">
        <a class="link_phone" href="tel:<?= $phone ?>?>">
        <?= $phone ?>
        </a>
    </div>
</header>



<main>
    <section class="hero">
        <h1>Подключить интернет в Краснодаре</h1>
        <div class="search-box">
            <input type="text" id="addressInput" placeholder="Улица и дом..."  autocomplete="off"><br>
            <button id="checkBtn" onclick="checkAddress()">Проверить</button>
            <div id="autocomplete-list"></div>
        </div>
        <div id="addressResult" class="result-box"></div>
    </section>
    <nav class="navbar">
    <a href="index.php" class="<?= $category == 'all' ? 'active' : '' ?>">Тарифы</a>
    <a href="index.php?cat=internet" class="<?= $category == 'internet' ? 'active' : '' ?>">Интернет</a>
    <a href="index.php?cat=tv_internet" class="<?= $category == 'tv_internet' ? 'active' : '' ?>">Интернет + ТВ</a>
    <a href="index.php?cat=mobile_internet" class="<?= $category == 'mobile_internet' ? 'active' : '' ?>">Связь</a>
    <a href="#">Акции</a>
    <a href="contacts.php">Контакты</a>
</nav>
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
                                <input type="hidden" name="verification" id="modalverification">
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

</main>

<div id="modalForm" class="modal">
    <div class="modal-content">
        <span onclick="closeModal()" style="position:absolute; top:15px; right:15px; cursor:pointer; font-size:24px;">&times;</span>
        <form action="send_lead.php" method="POST">
            <h2 id="modalTitle">Заявка</h2>
            <input type="hidden" name="tariff_id" id="hiddenTariff">
            <input type="text" name="phone" placeholder="+7 (___) ___-__-__" required>
            <input type="text" name="name" placeholder="Имя" required>
            <input type="text" name="street" id="modalStreet" placeholder="Улица">
            <input type="text" name="house" id="verification" placeholder="Дом">
            <input type="hidden" name="verification" id="modalverification">
            <button type="submit" class="btn-order" style="background:#000; color:#fff;">Отправить</button>
        </form>
    </div>
</div>

<script>
let selectedAddressData = null; // Храним объект адреса здесь
const input = document.getElementById('addressInput');
const list = document.getElementById('autocomplete-list');
const resBox = document.getElementById('addressResult');

input.addEventListener('input', async function() {
    const val = this.value.trim();
    if (val.length < 2) { list.style.display = 'none'; return; }

    try {
        const r = await fetch(`check_address_ajax.php?q=${encodeURIComponent(val)}`);
        const d = await r.json();

        if (d.status === 'success' && d.results.length > 0) {
            // Передаем ВЕСЬ объект i в функцию selectAddr, предварительно превратив его в строку JSON
            list.innerHTML = d.results.map(i => {
                const itemData = JSON.stringify(i).replace(/'/g, "&apos;");
                return `
                    <div class="autocomplete-item" onclick='selectAddr(${itemData})'>
                        <span class="addr-main">ул. ${i.street} ${i.house}</span>
                        <span class="addr-sub">${i.is_available == 1 ? 'Подключено' : 'Нет покрытия'}</span>
                    </div>
                `;
            }).join('');
            list.style.display = 'block';
        } else { list.style.display = 'none'; }
    } catch(e) { console.error(e); }
});

// Теперь эта функция получает сразу ВЕСЬ объект со всеми данными
function selectAddr(data) {
    input.value = `ул. ${data.street}, ${data.house}`;
    list.style.display = 'none';
    
    // Рисуем результат СРАЗУ, без лишних fetch
    renderResult(data);
}

function renderResult(d) {
    resBox.style.display = 'block';
    
    if (d.is_available == 1) {
        resBox.innerHTML = `
            <div style="padding: 15px; border: 1px solid #28a745; border-radius: 8px; background: #f8fff9;">
                <h3 style="color:green; margin:0">Доступно!</h3>
                <p>ул. ${d.street}, д. ${d.house}. <br>Скорость <strong>${d.max_speed || 100} Мбит/с</strong>.</p>
                <button class="btn-order" 
                        style="width:auto; padding:8px 25px; cursor:pointer; background:#ffdc00; border:none; border-radius:4px; font-weight:bold;" 
                        onclick="openModal('Заявка по адресу', '${d.street}', '${d.house}', 'Краснодар')">
                    Подключить
                </button>
            </div>
        `;
    } else {
        resBox.innerHTML = `
            <div style="padding: 15px; border: 1px solid #dc3545; border-radius: 8px; background: #fff8f8;">
                <h3 style="color:red; margin:0">Недоступно</h3>
                <p>По адресу ${d.street} ${d.house} сеть пока не проведена.</p>
            </div>
        `;
    }
}

// Эту функцию оставляем только для случая, если человек НЕ выбрал из списка, а просто нажал кнопку "Проверить"
async function checkAddress() {
    const val = input.value.trim();
    if (!val) return;
    
    const btn = document.getElementById('checkBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    try {
        const r = await fetch(`check_address_ajax.php?full_address=${encodeURIComponent(val)}`);
        const d = await r.json();
        
        // Берем первый результат из массива results
        if (d.results && d.results.length > 0) {
            renderResult(d.results[0]);
        } else {
            // Если вообще ничего не нашли
            resBox.style.display = 'block';
            resBox.innerHTML = '<div style="padding:15px; background:#eee;">Адрес не найден в базе</div>';
        }
    } catch (e) { 
        console.error(e); 
    } finally { 
        btn.innerHTML = 'Проверить'; 
    }
}
</script>
</body>
</html>