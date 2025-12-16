<nav class="header__nav">
    <aside class="header-nav-backpanel"></aside>
    <div class="header__nav_wrapper">
        <div class="nav">
            <div class="nav__inner">

                <div class="nav__top">
                    <a href="/user/login"{if !$is_developer} onclick="ym(45594498,'reachGoal','click_voity')"{/if}>
                        <button class="button button--light">Личный кабинет</button>
                    </a>
                </div>
                {assign var="user_fully_filled" value=$user && $user->personal_data_added && $user->address_data_added && $user->accept_data_added && $user->card_added && $user->files_added && $user->additional_data_added}
                <ul class="nav__list">
                    {*<li class="nav__item">
                        <a href="info_partners">Партнеры</a>
                    </li>*}
                    {*<li class="nav__item">
                        <a href="/info#info">Условия</a>
                    </li>*}
                    {if !$user}
                        <li class="nav__item">
                            <a href="/about/company">О компании</a>
                        </li>
                        <li class="nav__item">
                            <a href="/faq/main">Вопросы и ответы</a>
                        </li>
                    {/if}
                    {if $user_fully_filled}
                        <li class="nav__item">
                            <a href="/user/upload">
                                Мои файлы
                            </a>
                        </li>
                        <li class="nav__item">
                            <a href="/user/docs">
                                Документы
                            </a>
                        </li>
                    {/if}
                    <li class="nav__item">
                        <a onclick="window.history.pushState(null, '', window.location.href)" href="#contacts">Контакты</a>
                    </li>
                    <li class="nav__item">
                        <a href="/user/contract">Внести платёж</a>
                    </li>
                    {if $user}
                        <li class="nav__item">
                            <a id="faq-link-mobile" href="/user/faq">Вопросы и ответы</a>
                        </li>
                        <li class="nav__item nav-tickets">
                            <a href="/user/tickets">Форма обращения</a>
                            <span class="nav-alert" id="mobile-operator-alert" title="Есть непрочитанные комментарии"></span>
                        </li>
                    {/if}
                    <li class="nav__item">
                        <a style="color: red;" href="/complaint">Пожаловаться</a>
                    </li>
                    {if $user_fully_filled}
                        <li class="nav__item">
                            <a href="/user/extra_docs">Прочее</a>
                        </li>
                        <li class="nav__item">
                            <a href="/user/additional_docs">Дополнительно</a>
                        </li>
                    {/if}
                    {if $all_orders->has_il_order}
                        <li class="nav__item">
                            <a href="user/schedule_payments">График платежей</a>
                        </li>
                    {/if}
                </ul>

                <div class="nav__bottom">
                    <div class="nav__contacts">
                        <div class="nav__contacts_section">
                            <a href="mailto:info@boostra.ru">info@boostra.ru</a>
                            <span>Электронная почта для обращения</span>
                        </div>
                        <div class="nav__contacts_section">
                            <a href="tel:+78003333073">+7 800 333 30 73</a>
                            <a href="tel:+78005518881">+7 800 551 88 81</a>
                            <span>Круглосуточный клиентский сервис</span>
                        </div>
                    </div>

                    <div class="nav__socials">
                        <section class="socials socials--smaller socials--colorful">

                            <!-- VK -->
                            <a href="https://vk.com/write-212426324">
                                <svg fill="none" height="24" viewBox="0 0 24 24" width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                            d="M12.7374 18.6331C5.37193 18.6331 1.17229 13.8146 1 5.78373H4.70429C4.82274 11.673 7.53635 14.1646 9.69001 14.6794V5.78373H13.1682V10.8596C15.2895 10.6434 17.5293 8.32683 18.2831 5.77344H21.7505C21.4678 7.09519 20.903 8.347 20.0915 9.45066C19.28 10.5543 18.2391 11.4861 17.034 12.1878C18.3789 12.8278 19.5666 13.7332 20.5188 14.8441C21.471 15.9551 22.166 17.2465 22.5581 18.6331H18.7354C17.917 16.1929 15.871 14.2985 13.1682 14.0411V18.6331H12.7482H12.7374Z"
                                            fill="#16A3F2"/>
                                </svg>
                            </a>

                            <!-- TG -->
                            <a href="https://telegram.me/boostra_bot">
                                <svg fill="none" height="24" viewBox="0 0 24 24" width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                            d="M19.4576 4.18176L2.62445 10.7066C1.94706 11.0104 1.71795 11.6189 2.4607 11.9491L6.77915 13.3286L17.2205 6.84227C17.7906 6.43506 18.3743 6.54365 17.8721 6.99158L8.90432 15.1532L8.62261 18.6072C8.88354 19.1405 9.36129 19.143 9.66602 18.878L12.1471 16.5182L16.3963 19.7165C17.3832 20.3038 17.9202 19.9248 18.1325 18.8484L20.9197 5.58293C21.209 4.25791 20.7155 3.67411 19.4576 4.18176Z"
                                            fill="#16A3F2"/>
                                </svg>
                            </a>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
