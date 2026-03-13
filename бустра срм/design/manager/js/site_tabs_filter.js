/**
 * Динамическое переключение вкладок "сайтов" на страницах с данными (клиенты, заявки) на основе site_id
 * Используется через файл site_tabs_filter.tpl
 */

$(document).ready(function() {
    var SITE_STORAGE_KEY = 'selectedSiteId';

    function getSiteIdFromUrl() {
        const params = new URLSearchParams(window.location.search);
        return params.get('site_id');
    }

    function saveSiteToStorage(siteId) {
        localStorage.setItem(SITE_STORAGE_KEY, siteId || 'all');
    }

    function setActiveTab(siteId) {
        $('body').attr('data-site_id', siteId ? siteId : 'boostra');
        $('.js-site-tab-filter').removeClass('active');
        if (siteId) {
            var $tab = $('.js-site-tab-filter[data-site="' + siteId + '"]');
            if ($tab.length) {
                $tab.addClass('active');
                saveSiteToStorage(siteId);
            } else {
                $('.js-site-tab-filter[data-site="all"]').addClass('active');
                saveSiteToStorage('all');
                alert('Выбран неизвестный сайт "' + siteId + '"');
            }
        } else {
            $('.js-site-tab-filter[data-site="all"]').addClass('active');
            saveSiteToStorage('all');
        }
    }

    setActiveTab(getSiteIdFromUrl());

    $('.js-site-tab-filter').on('click', function(e) {
        e.preventDefault();

        if ($(this).hasClass('active')) {
            // Если вкладка уже активна, ничего не делаем
            return;
        }

        $('.jsgrid-load-panel').show();
        const site = $(this).data('site');
        saveSiteToStorage(site);
        const params = new URLSearchParams(window.location.search);
        params.delete('site_id');
        params.delete('organization_id');
        // Если выбран не all, добавляем один site_id
        if (site !== 'all') {
            params.set('site_id', site);
        }

        // Обновляем href у всех нужных ссылок в фильтрах
        $('.js-filter-status a:not(.js-site-tab-filter, .js-organization-filter a)').each(function() {
            let href = $(this).attr('href');
            if (!href) return;
            let urlObj;
            try {
                urlObj = new URL(href, window.location.origin);
            } catch (e) {
                urlObj = new URL(window.location.origin + href);
            }
            let linkParams = new URLSearchParams(urlObj.search);
            linkParams.delete('site_id');
            if (site !== 'all') {
                linkParams.set('site_id', site);
            }
            const paramsStr = linkParams.toString();
            $(this).attr('href', urlObj.pathname + (paramsStr ? '?' + paramsStr : ''));
        });

        let newUrl = window.location.pathname;
        const paramStr = params.toString();
        if (paramStr) {
            newUrl += '?' + paramStr;
        }
        // Обновляем URL без перезагрузки
        window.history.pushState({}, '', newUrl);
        // Параметры AJAX запроса лежат в url
        $.ajax({
            url: newUrl,
            beforeSend: function(){
                var preloaderTable = $('.preloader-table');
                if (preloaderTable.length) {
                    preloaderTable.show();
                } else {
                    $('.preloader').show();
                }
            },
            success: function(resp){
                // Заменяем весь #basicgrid
                $('#basicgrid').replaceWith($(resp).find('#basicgrid'));
                // Обновляем визуал вкладок
                setActiveTab(site !== 'all' ? site : null);
                $('.preloader, .preloader-table').hide();
                $('.jsgrid-load-panel').hide();
            },
            error: function(){
                $('.preloader, .preloader-table').hide();
                $('.jsgrid-load-panel').hide();
                alert('Ошибка при загрузке данных для выбранного сайта.');
            }
        });
    });
});
