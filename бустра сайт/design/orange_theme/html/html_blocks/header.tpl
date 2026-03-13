<header class="header">
    <nav class="container">
        <div class="header__top_menu">
            <div class="top_menu__left_section">
                <div class="top_menu__logo">
                    <a href="{$config->root_url}" id="link_logo_wrapper">
                        <img src="/design/boostra/img/svg/boostra-logo.svg" alt="{$settings->site_name}" />
                    </a>
                    <p class="logo-additional-text-delimiter">/</p>
                    <p class="logo-additional-text" href="">Каталог финансовых <br> продуктов</p>
                </div>
                <div class="top_menu__list_items hidden-mobile">
                    {*<div>
                        <a href="/info#demands">Условия</a>
                    </div>*}
                    <div>
                        <a href="/contacts">Связаться с нами</a>
                    </div>

                    {if $smarty.server.REQUEST_URI == '/about/company'}
                        <div>
                            <a href="partners/">Партнёры</a>
                        </div>
                    {/if}
                </div>
            </div>
            <div class="top_menu__login">
                {if $user}
                    <a href="user/logout" onclick="return confirm('Вы точно хотите выйти?')">
                        <img class="top_menu__logo" src="/design/boostra_mini_norm/assets/image/login-icon-1.png" alt="{$settings->site_name}"/>
                    </a>
                {else}
                    <a class="hidden-mobile" href="user/login">Войти</a>
                {/if}
                <a href="javascript:void(0)" rel="button" id="mobile-menu" class="burger top_menu__button_mobile_menu hidden-desktop">
                    <div class="burger__icon">
                        <div id="mobile-menu-open" class="burger__icon_open">
                            <svg fill="none" height="10" viewBox="0 0 27 10" width="27"
                                 xmlns="http://www.w3.org/2000/svg">
                                <rect fill="#1E262E" height="2" rx="1" width="27" y="0.5"/>
                                <rect fill="#1E262E" height="2" rx="1" width="20" y="7.5"/>
                            </svg>
                        </div>
                        <div id="mobile-menu-close" class="burger__icon_close">
                            <svg fill="none" height="12" viewBox="0 0 12 12" width="12"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path
                                        d="M6 4.66688L10.6669 0L12 1.33312L7.33312 6L12 10.6669L10.6659 12L5.99906 7.33312L1.33312 12L0 10.6659L4.66688 5.99906L0 1.33218L1.33312 0.000942735L6 4.66688Z"
                                        fill="#02113B"/>
                            </svg>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </nav>
    <div class="hidden-desktop">
        {include file='design/orange_theme/html/html_blocks/mobile_nav_menu.tpl'}
    </div>
</header>
<script type="text/javascript">
    $(".top_menu__button_mobile_menu").on('click', function () {
        $(".header").toggleClass('menu-open');
        $("body").toggleClass("lock");
        $(this).toggleClass('opened');
    });

</script>
