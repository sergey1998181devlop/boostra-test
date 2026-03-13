{capture name='page_scripts'}
<script src="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js?v=1.02"></script>
    <script>
        $(document).on('click', '.download-photo', function (e) {
            const filename = $(this).data('file-name') || 'downloaded_file';
            const id = $(this).data('id');

            if (!id) {
                return;
            }

            fetch('/export-photos?action=exportSinglePhoto&photoId=' + id)
                .then(async response => {
                    if (!response.ok) {
                        const errorJson = await response.json();
                        const message = errorJson.message || 'Ошибка загрузки файла';
                        throw new Error(message);
                    }

                    return response.blob();
                })
                .then(blob => {
                    const blobUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(blobUrl);
                })
                .catch(error => {
                    console.error('Ошибка при скачивании файла:', error);
                    alert(error.message);
                });
        });
    </script>
{/capture}
{capture name='page_styles'}
<link href="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css?v=1.02" rel="stylesheet" />
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
                    <i class="mdi mdi-image-album"></i>
                    <span>Выгрузка фото</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item">Выгрузка фото</li>
                </ol>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <!-- Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Выгрузка фото</h4>
                        <div class="contract-filter-panel">
                            <form action="/export-photos" method="get">
                                <div class="row">
                                    <div class="col-md-11">
                                        <label for="contractNumber">Введите номер договора</label>
                                        <input id="contractNumber" required type="text" class="form-control" placeholder="Номер договора" name="contractNumber" value="{$contractNumber}">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="submit" class="btn btn-success align-bottom">
                                            <span>Найти</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        {if $isRequestSent}
                        <div class="client-info mt-3">
                            {if $isHasContract}
                                <div class="client row">
                                    <div class="col-md-9">
                                        <h4>{$user->lastname} {$user->firstname} {$user->patronymic}</h4>
                                        <ul>
                                            {if $user->birth}
                                                <li>Дата рождения: {$user->birth}</li>
                                            {/if}
                                            {if $user->phone_mobile}
                                                <li>Телефон: {$user->phone_mobile}</li>
                                            {/if}
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        {if $files}
                                            <form action="/export-photos" method="post">
                                                <input type="hidden" name="action" value="exportPhotosToZip">
                                                <input type="hidden" name="userId" value="{$user->id}">
                                                <input type="hidden" name="contractNumber" value="{$contractNumber}">
                                                <button class="btn btn-success float-right">
                                                    <i class="fas fa-zip"></i> Скачать архив (все фото)
                                                </button>
                                            </form>
                                        {/if}
                                    </div>
                                </div>
                                <div class="files">
                                    {if $fileErrors|count > 0}
                                        <div class="files-errors alert alert-danger">
                                            <ul>
                                                {foreach $fileErrors as $error}
                                                    <li>{$error}</li>
                                                {/foreach}
                                            </ul>
                                        </div>
                                    {/if}
                                    {if $files|count > 0}
                                        <ul class="col-md-12 list-inline order-images-list">
                                            {foreach $files as $file}
                                                {if $file->status == 0}
                                                    {$item_class="border-warning"}
                                                    {$ribbon_class="ribbon-warning"}
                                                    {$ribbon_icon="fas fa-clock"}
                                                {elseif $file->status == 1}
                                                    {$item_class="border-info"}
                                                    {$ribbon_class="ribbon-info"}
                                                    {$ribbon_icon="fas fa-question"}
                                                {elseif $file->status == 2}
                                                    {$item_class="border-success border border-bg"}
                                                    {$ribbon_class="ribbon-success"}
                                                    {$ribbon_icon="fa fa-check-circle"}
                                                {elseif $file->status == 3}
                                                    {$item_class="border-danger border"}
                                                    {$ribbon_class="ribbon-danger"}
                                                    {$ribbon_icon="fas fa-times-circle"}
                                                {/if}
                                                <li class="ribbon-wrapper border {$item_class} js-image-item text-center"
                                                    id="file_{$file->id}"
                                                    data-id="{$file->id}"
                                                    style="
                                                    height: 150px;">
                                                    <a class="js-open-popup-image image-popup-fit-width" data-fancybox="user_image" href="{$config->front_url}/files/users/{$file->name}">
                                                        <img src="{$file->name|resize:100:100}" alt="" class="img-responsive js-image-thumb" style="max-width:100px;max-height:100px;" />
                                                    </a>
                                                    <div class="label label-primary image-label w-100" style="">
                                                        {if $file->type == 'face1'}Профиль
                                                        {elseif $file->type == 'face2'}Анфас
                                                        {elseif $file->type == 'passport'}Документ
                                                        {elseif $file->type == 'passport1'}Паспорт
                                                        {elseif $file->type == 'passport2'}Прописка
                                                        {elseif $file->type == 'passport3'}Брак
                                                        {elseif $file->type == 'passport4'}Карта
                                                        {elseif $file->type == 'selfi'}Селфи с паспортом
                                                        {else}{$file->type}{/if}
                                                    </div>
                                                    <div class="mt-3">
                                                        <button class="btn btn-sm btn-outline-primary download-photo"
                                                                data-id="{$file->id}"
                                                                data-file-name="{$file->name}"
                                                        >
                                                            Скачать
                                                        </button>
                                                    </div>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    {else}
                                        <div class="text-center mt-5">
                                            <p>У пользователя {$user->lastname} {$user->firstname} {$user->patronymic} нет загруженных файлов</p>
                                        </div>
                                    {/if}
                                </div>
                            {else}
                                <div class="text-center mt-5">
                                    <p>Договор номер «{$contractNumber}» не найден</p>
                                </div>
                            {/if}
                        </div>
                        {else}
                            <div class="text-center mt-5">
                                <p>Для продолжения работы введите номер договора и нажмите кнопку «Найти»</p>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
        <!-- Row -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
    <!-- End footer -->
    <!-- ============================================================== -->
</div>