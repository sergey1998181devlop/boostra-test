{$meta_title='Темы запросов ЦБ' scope=parent}

{capture name='page_styles'}
    <style>
        .subject-item {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: .25rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background-color 0.2s ease;
        }
        .subject-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .subject-item.inactive {
            opacity: 0.5;
        }
        .subject-name {
            font-size: 1rem;
            font-weight: 500;
        }
        .edit-form {
            display: none;
        }
        .edit-form.active {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-bank mr-2"></i>Темы запросов ЦБ</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="/cb-requests">Запросы ЦБ</a></li>
                    <li class="breadcrumb-item active">Темы запросов</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Список тем</h5>
                        <button class="btn btn-sm btn-success" id="btn-add-subject">
                            <i class="fas fa-plus mr-1"></i>Добавить тему
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="new-subject-form" style="display:none;" class="mb-3">
                            <form action="/cb-requests/subjects" method="POST" class="d-flex align-items-center" style="gap: 0.5rem;">
                                <input type="hidden" name="action" value="save_subject">
                                <input type="hidden" name="id" value="0">
                                <input type="hidden" name="is_active" value="1">
                                <input type="text" name="name" class="form-control" placeholder="Название новой темы" required style="max-width: 400px;">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" id="btn-cancel-add">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>

                        <div id="subjects-list">
                            {foreach $subjects as $subject}
                                <div class="subject-item {if !$subject->is_active}inactive{/if}" data-id="{$subject->id}">
                                    <div class="view-mode">
                                        <span class="subject-name">{$subject->name|escape}</span>
                                        {if !$subject->is_active}
                                            <span class="badge badge-secondary ml-2">Неактивна</span>
                                        {/if}
                                    </div>
                                    <div class="edit-form">
                                        <form action="/cb-requests/subjects" method="POST" class="d-flex align-items-center" style="gap: 0.5rem;">
                                            <input type="hidden" name="action" value="save_subject">
                                            <input type="hidden" name="id" value="{$subject->id}">
                                            <input type="text" name="name" value="{$subject->name|escape}" class="form-control" style="max-width: 400px;">
                                            <select name="is_active" class="form-control" style="max-width: 150px;">
                                                <option value="1" {if $subject->is_active}selected{/if}>Активна</option>
                                                <option value="0" {if !$subject->is_active}selected{/if}>Неактивна</option>
                                            </select>
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm btn-cancel-edit">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="subject-actions">
                                        <button class="btn btn-sm btn-outline-primary btn-edit-subject" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            {foreachelse}
                                <p class="text-muted">Темы не найдены</p>
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Информация</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Здесь можно управлять темами запросов ЦБ: добавлять новые, редактировать существующие и деактивировать неактуальные.
                        </p>
                        <p class="text-muted">
                            Деактивированные темы не будут доступны при создании новых запросов, но останутся видны в уже созданных.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>

{capture name='page_scripts'}
    <script>
        $(document).ready(function() {
            // Показать форму добавления
            $('#btn-add-subject').click(function() {
                $('#new-subject-form').slideDown();
                $(this).hide();
            });

            // Скрыть форму добавления
            $('#btn-cancel-add').click(function() {
                $('#new-subject-form').slideUp();
                $('#btn-add-subject').show();
            });

            // Показать форму редактирования
            $('.btn-edit-subject').click(function() {
                var $item = $(this).closest('.subject-item');
                $item.find('.view-mode').hide();
                $item.find('.subject-actions').hide();
                $item.find('.edit-form').addClass('active');
            });

            // Скрыть форму редактирования
            $('.btn-cancel-edit').click(function() {
                var $item = $(this).closest('.subject-item');
                $item.find('.edit-form').removeClass('active');
                $item.find('.view-mode').show();
                $item.find('.subject-actions').show();
            });
        });
    </script>
{/capture}
