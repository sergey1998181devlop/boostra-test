{$meta_title = 'Настройки сайта' scope=parent}

{capture name='page_scripts'}
    <script src="js/jquery/jquery.js"  type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/apps/site_settings.js?v=1.0" type="text/javascript"></script>
    <script type="text/javascript">
        $(function() {
            // два поля ввода с суммой содержимого всегда 100%
            function updateInputs(changedInput, otherInput) {
                var changedValue = parseInt($(changedInput).val()) || 0;
                var newOtherValue = 100 - changedValue;

                if (newOtherValue < 0) {
                    newOtherValue = 0;
                    changedValue = 100;
                    $(changedInput).val(100);
                }

                $(otherInput).val(newOtherValue);
            }

            $('#banner_clicks_a, #banner_clicks_b').on('input', function() {
                var otherInput = this.id === 'banner_clicks_b' ? '#banner_clicks_a' : '#banner_clicks_b';
                updateInputs(this, otherInput);
            });
        });
    </script>
{/capture}

{capture name='page_styles'}
{/capture}

<div class="page-wrapper">
    <!-- Container fluid  -->
    <div class="container-fluid">
        <!-- Bread crumb -->
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    Настройки сайта
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Настройки сайта</li>
                </ol>
            </div>
        </div>

        <!-- Row -->
        <form method="POST">
            <input type="hidden" name="site_id" value="{if $current_site_id}{$current_site_id}{else}0{/if}" />

            {* Вкладки для выбора сайта *}
            <ul class="mt-2 nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link {if !$current_site_id}active{/if}" href="?module=SiteSettingsView">
                        <span class="hidden-sm-up"><i class="ti-home"></i></span>
                        <span class="hidden-xs-down">Общие (все сайты)</span>
                    </a>
                </li>
                {foreach $all_sites as $site}
                <li class="nav-item">
                    <a class="nav-link {if $current_site_id == $site->site_id}active{/if}" href="?module=SiteSettingsView&site_id={$site->site_id}">
                        <span class="hidden-sm-up"><i class="ti-settings"></i></span>
                        <span class="hidden-xs-down">{$site->title|escape}</span>
                    </a>
                </li>
                {/foreach}
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            {if $current_site_id && !$site_setting_names}
                                <div class="alert alert-warning" role="alert">
                                    Для этого сайта настройки не заданы. Чтобы они отображались здесь, необходимо вручную добавить их в таблицу `s_settings` с соответствующим `site_id`.
                                </div>
                            {/if}

                            {* Модульные секции настроек *}
                            {include file="site_settings/sections/auto_orders.tpl"}
                            {include file="site_settings/sections/prolongation.tpl"}
                            {include file="site_settings/sections/repayments.tpl"}
                            {include file="site_settings/sections/warning_banner.tpl"}
                            {include file="site_settings/sections/cards.tpl"}
                            {include file="site_settings/sections/payments.tpl"}
                            {include file="site_settings/sections/external_services.tpl"}
                            {include file="site_settings/sections/banners.tpl"}
                            {include file="site_settings/sections/flows.tpl"}
                            {include file="site_settings/sections/additional.tpl"}
                            {include file="site_settings/sections/registration.tpl"}
                            {include file="site_settings/sections/notifications.tpl"}
                            {include file="site_settings/sections/returns.tpl"}
                            {include file="site_settings/sections/utm_sources.tpl"}
                            {include file="site_settings/sections/integrations.tpl"}
                            {include file="site_settings/sections/misc.tpl"}
                            {include file="site_settings/sections/ping3.tpl"}

                            {* Кнопка сохранения *}
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-check"></i> Сохранить настройки
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Footer -->
        <footer class="footer text-center"> 2024 &copy; CRM </footer>
    </div>
</div>
