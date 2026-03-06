<style>
<style>
    /* Переменные для консистентности цветов */
    :root {
        --beeline-yellow: #ffcc00;
        --beeline-black: #000000;
        --beeline-text-gray: #666666;
        --beeline-light-gray: #f9f9f9;
        --footer-border: #e0e0e0;
    }

    .main-footer {
        background-color: var(--beeline-light-gray);
        padding: 60px 10% 40px;
        border-top: 1px solid var(--footer-border);
        margin-top: 60px;
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 40px;
        margin-bottom: 50px;
        text-align: left;
    }

    .footer-column h4 {
        font-size: 18px;
        margin-bottom: 25px;
        font-weight: 700;
        color: var(--beeline-black);
        position: relative;
    }

    /* Желтая черта под заголовком */
    .footer-column h4::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -8px;
        width: 30px;
        height: 3px;
        background-color: var(--beeline-yellow);
    }

    .footer-column ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-column ul li {
        margin-bottom: 15px;
    }

    .footer-column ul li a {
        text-decoration: none;
        color: var(--beeline-text-gray);
        font-size: 15px;
        transition: color 0.3s ease, padding-left 0.3s ease;
        display: inline-block;
    }

    .footer-column ul li a:hover {
        color: var(--beeline-black);
        padding-left: 5px;
    }

    .footer-bottom {
        padding-top: 30px;
        border-top: 1px solid var(--footer-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .footer-bottom p {
        font-size: 14px;
        color: #888;
        margin: 0;
    }

    .social-links {
        display: flex;
        gap: 20px;
        font-weight: 600;
        font-size: 14px;
        color: var(--beeline-black);
    }

    .social-links span {
        cursor: pointer;
        transition: color 0.2s;
    }

    .social-links span:hover {
        color: var(--beeline-yellow);
    }

    /* Адаптивность для мобильных */
    @media (max-width: 768px) {
        .main-footer {
            padding: 40px 5% 20px;
        }
        
        .footer-grid {
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .footer-bottom {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .footer-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
</style>

<footer class="main-footer">
    <div class="footer-grid">
        <div class="footer-column">
            <h4>Продукты</h4>
            <ul>
<li>
    <a href="index.php"> Все тарифы</a>
</li>
    <li>
        <a href="index.php?cat=internet">Интернет</a>
        </li>
    <li>
        <a href="index.php?cat=tv_internet">Интернет + ТВ</a>
        </li>
    <li>
        <a href="index.php?cat=mobile_internet">Связь</a>
        </li>

            </ul>
        </div>
        <div class="footer-column">
            <h4>Прочее</h4>
            <ul>
    <li>
        <a href="#">Акции</a>
        </li>
    <li>
        <a href="contacts.php">Контакты</a>
        </li>
        <li>
            <a href="tel:<?= $phone ?>"><?= $phone ?></a>
        </li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>Помощь</h4>
            <ul>
                <li><a href="https://authentication.beeline.ru/dsc/login">Личный кабинет</a></li>
                <li><a href="https://krasnodar.beeline.ru/customers/products/replenishment-balance/">Оплата</a></li>

            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026  Краснодарский край.</p>
        <div class="social-links">
    <span>VK</span> | <span>TG</span> | <span>OK</span>
</div>
    </div>
</footer>