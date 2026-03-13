{$meta_title = 'Настройки пролонгации' scope=parent}

{capture name='page_styles'}
    <link type="text/css" rel="stylesheet"
          href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css"/>
{/capture}

{capture name='page_scripts'}
    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    Настройки пролонгации
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">
                        Настройки пролонгации
                    </li>
                </ol>
            </div>
        </div>

        <ul class="mt-2 nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#banner-visible-tab" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-home"></i></span>
                    <span class="hidden-xs-down">Видимость баннера</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#banner-text-tab" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-home"></i></span>
                    <span class="hidden-xs-down">Текст баннера</span>
                </a>
            </li>
        </ul>

        <div class="tab-content">

            <div id="banner-visible-tab" class="tab-pane active" role="tabpanel">
                <form method="POST">
                    <input type="hidden" name="action" value="save_banner-visible">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <h3>Настройка появления всплывающего окна</h3>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <p>
                                                Позволяет <strong>выключить</strong> появление модалки с пролонгацией для определённых бакетов.
                                                <br>Если настройка для определённого баннера включена - он не будет показываться при совпадении условий.
                                                <br>Если настройка выключена - баннер будет показываться как обычно.
                                            </p>
                                        </div>
                                    </div>

                                    {foreach $settings->prolongation_visible as $day => $setting}
                                        <div class="prolongation-visible-setting mb-4" data-day="{$day}">
                                            <div class="row">
                                                <h4 class="col-12">{$day} день</h4>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 mt-auto mb-auto">
                                                    <div class="custom-control custom-checkbox mr-sm-2">
                                                        <input name="settings[prolongation_visible][{$day}][enabled]"
                                                               id="visible_enabled_{$day}"
                                                               type="checkbox" class="custom-control-input" value="1" {if $setting['enabled']}checked{/if}>
                                                        <label class="custom-control-label" for="visible_enabled_{$day}">
                                                            <span class="text-white">Настройка {if $setting['enabled']}включена{else}выключена{/if}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xl-2 col-4 mt-auto">
                                                    <span class="text-white">Мин.балл</span>
                                                    <input type="text" name="settings[prolongation_visible][{$day}][min_ball]"
                                                           value="{$setting['min_ball']}" class="form-control input-sm">
                                                </div>
                                                <div class="col-xl-2 col-4 mt-auto">
                                                    <span class="text-white">Макс.балл</span>
                                                    <input type="text" name="settings[prolongation_visible][{$day}][max_ball]"
                                                           value="{$setting['max_ball']}" class="form-control input-sm">
                                                </div>
                                            </div>
                                        </div>
                                    {/foreach}

                                    <button class="btn hidden-sm-down btn-success" type="submit">
                                        Сохранить
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <div id="banner-text-tab" class="tab-pane" role="tabpanel">
                <form method="POST">
                    <input type="hidden" name="action" value="save_banner-text">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <h3>Настройка текста в баннере</h3>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <p>
                                                Позволяет изменить текст в модалке с пролонгацией для определённых бакетов.
                                                <br><strong>Стандартный текст</strong> будет показан если оставить поле пустым.
                                            </p>
                                        </div>
                                    </div>

                                    {foreach $settings->prolongation_text as $day => $text}
                                        <div class="prolongation-visible-setting mb-4" data-day="{$day}">
                                            <div class="row">
                                                <h4 class="col-12">{$day} день</h4>
                                            </div>
                                            <div class="row">
                                                <div class="col-xl-8 col-12 mt-auto">
                                                    <span class="text-white">Замена текста</span>
                                                    <input type="text" name="settings[prolongation_text][{$day}]"
                                                           value="{$text}" class="form-control input-sm">
                                                </div>
                                            </div>
                                        </div>
                                    {/foreach}

                                    <button class="btn hidden-sm-down btn-success" type="submit">
                                        Сохранить
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>

    {include file='footer.tpl'}

</div>