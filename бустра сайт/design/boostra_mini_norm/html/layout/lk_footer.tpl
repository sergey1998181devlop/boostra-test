<link rel="stylesheet" href="/design/boostra_mini_norm/css/lk_footer.css?v=1.01" type="text/css" media="screen"/>
<footer class="footer">
    <div class="footer__content">
        <a class="footer__inner__logo" href="/">
            <img alt="logo home link"
                 loading="lazy"
                 src="design/orange_theme/img/landing/logo.svg">

        </a>

        <div class="footer__content__nav_container">
            <nav>
                <a href="/contacts">Связаться с нами</a>
                <a href="/user/extra_docs">Прочее</a>
                <a href="/user/additional_docs">Дополнительно</a>
                {if $user}
                    <a href="{$lk_url}">Личный кабинет</a>
                {/if}
            </nav>
            <p>
                ООО «Финтех-Маркет» осуществляет деятельность в сфере информационных технологий
            </p>
        </div>
        <hr>
        <div class="footer__content__contacts">
            <div>
                <p>
                    Нажмите, чтобы направить<br>обращение
                </p>
                <a href="mailto:info@boostra.ru" class="blue">info@boostra.ru</a>
                <p>
                    Электронная почта для <br> обращения граждан/клиентов
                </p>
                <a
                        href="/complaint"
                        {if $complaint_partner_href && !$same_page} target="_blank" onclick="clickHunter?.(14, window.complaint_partner_href);"{/if}
                >Пожаловаться</a>
            </div>
            <div>
                <p>
                    Возникли вопросы? Звони:
                </p>
                <div>
                    <a href="tel:88003333073" class="blue">8 800 333 30 73</a>
                </div>
                <p>
                    Клиентский сервис <br>
                    Время работы: круглосуточно
                </p>
            </div>
        </div>
    </div>
</footer>
