{$meta_title = 'Ключи для API' scope=parent}

{capture name='page_scripts'}

{/capture}

{capture name='page_styles'}

{/capture}


<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    Ключи для API
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Ключи для API</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <!-- Row -->
        <form class="" method="POST" autocomplete="off">
            <input type="hidden" name="site_id" value="{if $site_id}{$site_id}{else}0{/if}" />

            {* Вкладки для выбора сайта *}
            <ul class="mt-2 nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link {if !$site_id}active{/if}" href="/apikeys">
                        <span class="hidden-sm-up"><i class="ti-home"></i></span>
                        <span class="hidden-xs-down">Общие (все сайты)</span>
                    </a>
                </li>
                {foreach $all_sites as $site}
                <li class="nav-item" data-site_id="{$site->site_id}">
                    <a class="nav-link {if $site_id == $site->site_id}active{/if}" href="/apikeys?site_id={$site->site_id}">
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

                            {if !$site_id || $apikeys["dadata"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Dadata
                                        <a href="https://dadata.ru" target="_blank"><small>https://dadata.ru</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Ключ</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[dadata][key]" value="{$apikeys['dadata']['key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Секретный ключ</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[dadata][secret]" value="{$apikeys['dadata']['secret']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["recaptcha"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Recaptcha
                                        <a href="https://www.google.com/recaptcha/admin#list" target="_blank"><small>https://www.google.com/recaptcha/admin#list</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Ключ</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[recaptcha][key]" value="{$apikeys['recaptcha']['key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Секретный ключ</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[recaptcha][secret]" value="{$apikeys['recaptcha']['secret']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["cdoctor"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Кредитный доктор
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Токен</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[cdoctor][token]" value="{$apikeys['cdoctor']['token']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Активировать</label>
                                        <div class="">
                                            <div class="form-check form-check-inline">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="cdoctor_enable" class="custom-control-input" name="apikeys[cdoctor][enabled]" value="1" {if $apikeys['cdoctor']['enabled']}checked="true"{/if} placeholder="">
                                                <label class="custom-control-label" for="cdoctor_enable">Да</label>
                                            </div>
                                            </div>
                                            <div class="form-check form-check-inline">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="cdoctor_disable" class="custom-control-input" name="apikeys[cdoctor][enabled]" value="0" {if !$apikeys['cdoctor']['enabled']}checked="true"{/if} placeholder="">
                                                <label class="custom-control-label" for="cdoctor_disable">Нет</label>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["smstraffic"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Smstraffic
                                        <a href="//smstraffic.ru" target="_blank"> <small> smstraffic.ru</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Логин</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[smstraffic][login]" value="{$apikeys['smstraffic']['login']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Пароль</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[smstraffic][password]" value="{$apikeys['smstraffic']['password']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["smsc"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Smsc
                                        <a href="//smsc.ru" target="_blank"> <small> smsc.ru</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Логин</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[smsc][login]" value="{$apikeys['smsc']['login']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Пароль</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[smsc][password]" value="{$apikeys['smsc']['password']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["dadata"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Dadata
                                        <a href="//dadata.ru" target="_blank"> <small> dadata.ru</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">API-ключ</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[dadata][api_key]" value="{$apikeys['dadata']['api_key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Секретный ключ для стандартизации</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[dadata][secret_key]" value="{$apikeys['dadata']['secret_key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["mango"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Mango Office
                                        <a href="//mango-office.ru" target="_blank"> <small> mango-office.ru</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">API ключ (Уникальный код вашей АТС)</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[mango][api_key]" value="{$apikeys['mango']['api_key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">API соль (Ключ для создания подписи)</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[mango][api_salt]" value="{$apikeys['mango']['api_salt']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["anticaptcha"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Anticaptcha
                                        <a href="//anti-captcha.com" target="_blank"> <small> anti-captcha.com</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">API ключ</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[anticaptcha][api_key]" value="{$apikeys['anticaptcha']['api_key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["fssp"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        ФССП
                                        <a href="//fssp.gov.ru" target="_blank"> <small> fssp.gov.ru</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">API ключ</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[fssp][api_key]" value="{$apikeys['fssp']['api_key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || ($apikeys["scorista"] || $apikeys["scorista2"])}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Scorista
                                        <a href="//scorista.ru" target="_blank"> <small> scorista.ru</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="box-title">Аквариус</h4>
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Имя пользователя</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[scorista2][6][username]" value="{$apikeys['scorista2'][6]['username']}" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Токен</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[scorista2][6][token]" value="{$apikeys['scorista2'][6]['token']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="box-title">Финлаб</h4>
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Имя пользователя</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[scorista2][11][username]" value="{$apikeys['scorista2'][11]['username']}" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Токен</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[scorista2][11][token]" value="{$apikeys['scorista2'][11]['token']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="box-title">РЗС</h4>
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Имя пользователя</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[scorista2][13][username]" value="{$apikeys['scorista2'][13]['username']}" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Токен</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[scorista2][13][token]" value="{$apikeys['scorista2'][13]['token']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="box-title">ЛОРД</h4>
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Имя пользователя</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[scorista2][15][username]" value="{$apikeys['scorista2'][15]['username']}" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Токен</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[scorista2][15][token]" value="{$apikeys['scorista2'][15]['token']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="box-title">Море Денег</h4>
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Имя пользователя</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[scorista2][17][username]" value="{$apikeys['scorista2'][17]['username']}" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Токен</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[scorista2][17][token]" value="{$apikeys['scorista2'][17]['token']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="box-title">Форинт</h4>
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Имя пользователя</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[scorista2][14][username]" value="{$apikeys['scorista2'][14]['username']}" placeholder="">
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Токен</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[scorista2][14][token]" value="{$apikeys['scorista2'][14]['token']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" class="form-control" name="apikeys[scorista][username]" value="{$apikeys['scorista']['username']}" placeholder="">
                                <input type="hidden" class="form-control" name="apikeys[scorista][token]" value="{$apikeys['scorista']['token']}" placeholder="">
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["juicescore"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Juicescore
                                        <a href="//juicyscore.com" target="_blank"> <small> juicyscore.com</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">API ключ</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[juicescore][api_key]" value="{$apikeys['juicescore']['api_key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Токен для фронта</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[juicescore][token]" value="{$apikeys['juicescore']['token']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["dbrain"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        DBrain
                                        <a href="//dbrain.io" target="_blank"> <small> dbrain.io</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">API ключ</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[dbrain][api_key]" value="{$apikeys['dbrain']['api_key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["isphere"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Инфосфера
                                        <a href="//i-sphere.ru" target="_blank"> <small> i-sphere.ru</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Логин</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[isphere][login]" value="{$apikeys['isphere']['login']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Пароль</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[isphere][password]" value="{$apikeys['isphere']['password']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["vk_bot_api"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Апи VK бота для рассылок
                                        <a href="//vk.com/boostra_bot" target="_blank"> <small> vk.com/boostra_bot</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">API ключ</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[vk_bot_api][token]" value="{$apikeys['vk_bot_api']['token']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || $apikeys["cyberity"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Cyberity (Кабутек)
                                        <a href="//api.cyberity.ru" target="_blank"> <small> api.cyberity.ru</small></a>
                                    </h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Токен</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[cyberity][token]" value="{$apikeys['cyberity']['token']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Секретный ключ</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[cyberity][secret_key]" value="{$apikeys['cyberity']['secret_key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Активировать</label>
                                        <div class="">
                                            <div class="form-check form-check-inline">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="cyberity_enable" class="custom-control-input" name="apikeys[cyberity][enabled]" value="1" {if $apikeys['cyberity']['enabled']}checked="true"{/if} placeholder="">
                                                    <label class="custom-control-label" for="cyberity_enable">Да</label>
                                                </div>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="cyberity_disable" class="custom-control-input" name="apikeys[cyberity][enabled]" value="0" {if !$apikeys['cyberity']['enabled']}checked="true"{/if} placeholder="">
                                                    <label class="custom-control-label" for="cyberity_disable">Нет</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-3 mb-4" />
                            {/if}

                            {if !$site_id || ($apikeys["multipolis"] || $apikeys["vitamed"] || $apikeys["star_oracle"])}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">
                                        Список лицензионных ключей
                                    </h3>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Звездный Оракул</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[star_oracle][license_key]" value="{$apikeys['star_oracle']['license_key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Витамед</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[vitamed][license_key]" value="{$apikeys['vitamed']['license_key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class=" col-form-label">Консьерж  сервис</label>
                                        <div class="">
                                            <input type="text" class="form-control" name="apikeys[multipolis][license_key]" value="{$apikeys['multipolis']['license_key']}" placeholder="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {/if}

                            {if !$site_id || $apikeys["smtp"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">SMTP</h3>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="col-form-label">SMTP Host</label>
                                        <input type="text" class="form-control" name="apikeys[smtp][host]" value="{$apikeys['smtp']['host']}" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="col-form-label">SMTP Mail</label>
                                        <input type="text" class="form-control" name="apikeys[smtp][mail]" value="{$apikeys['smtp']['mail']}" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="col-form-label">SMTP Password</label>
                                        <input type="password" class="form-control" name="apikeys[smtp][password]" value="{$apikeys['smtp']['password']}" autocomplete="new-password" />
                                    </div>
                                </div>
                            </div>
                            {/if}

                            {if !$site_id || $apikeys["bp_photo"]}
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="box-title">BP платформа для фото</h3>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="col-form-label">Токен API</label>
                                        <input type="text" class="form-control" name="apikeys[bp_photo][token]" value="{$apikeys['bp_photo']['token']}" />
                                    </div>
                                </div>
                            </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                <div class="form-actions">
                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                </div>
            </div>
        </form>
        <!-- Row -->
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
</div>
