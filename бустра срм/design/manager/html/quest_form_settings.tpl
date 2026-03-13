{$meta_title="Настройки формы регистрации" scope=parent}

<div class="page-wrapper js-event-add-load" id="page_wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-animation"></i> Настройки формы регистрации
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Настройки формы регистрации</li>
                </ol>
            </div>
        </div>
        <div class="tab-content ">
            <div id="tab_order" class="tab-pane active" role="tabpanel">
                <div class="row" id="order_wrapper">
                    <div class="col-lg-12">
                        <div class="card card-outline-info">
                            <form class="" method="POST">

                                <div class="card-header">
                                    <h4 class="card-title">Источники (1 в строке)</h4>
                                </div>
                                <div class="card-body">
                                   <textarea class="form-control" name="sources" rows="10">{$sources}</textarea>
                                    <label for="enabled">Включено</label>
                                    <input type="checkbox" name="enabled" value="1" {if $enabled}checked{/if}/>
                                </div>
                                <div class="card-footer" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>
