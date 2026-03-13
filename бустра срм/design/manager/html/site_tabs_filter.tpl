{*
* Динамическое переключение вкладок "сайтов" на страницах с данными (клиенты, заявки) на основе site_id
*
* Подключив отдельно site_tabs_filter.js можно реализовать свой шаблон для переключателя, главное сохранить ключевые моменты:
* 1) Кнопки переключателя должны иметь класс js-site-tab-filter (чтобы скрипт мог повесить класс active на выбранную кнопку)
* 2) Каждая кнопка должна иметь атрибут data-site с уникальным значением site_id
*}

<div class="row my-3 float-right">
    <div class="ml-auto mr-3">
        <div class="btn-group" role="group">
            <a href="#" class="js-site-tab-filter btn btn-xs btn-outline-success" data-site="all">Все</a>
            <a href="#" class="js-site-tab-filter btn btn-xs btn-outline-info" data-site="boostra">Boostra</a>
            <a href="#" class="js-site-tab-filter btn btn-xs btn-outline-warning" data-site="soyaplace">Soyaplace</a>
            <a href="#" class="js-site-tab-filter btn btn-xs btn-outline-success" data-site="neomani">Neomani</a>
        </div>
    </div>
</div>

<script defer type="text/javascript" src="design/{$settings->theme|escape}/js/site_tabs_filter.js?v=1.1"></script>