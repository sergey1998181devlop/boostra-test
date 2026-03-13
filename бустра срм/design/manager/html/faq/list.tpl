{$meta_title=$pageTitle scope=parent}

<div class="page-wrapper" id="page_wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="mb-0 mt-0"><i class="mdi mdi-help"></i>{$meta_title}</h3>
                <ol class="breadcrumb">
                    {foreach $breadcrumbs as $crumb}
                        {if $crumb.url}
                            <li class="breadcrumb-item"><a href="{$crumb.url}">{$crumb.title}</a></li>
                        {else}
                            <li class="breadcrumb-item active">{$crumb.title}</li>
                        {/if}
                    {/foreach}
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <button class="btn float-right btn-primary" id="addBlock" data-toggle="modal"
                        data-target="#createBlockModal">
                    <i class="mdi mdi-plus-circle"></i> Добавить блок
                </button>
            </div>
        </div>

        {* Табы для сайтов *}
        {if !empty($sites)}
            <div class="card mb-3">
                <div class="card-body p-2">
                    <ul class="nav nav-pills" id="siteTabs" role="tablist">
                        {foreach $sites as $site}
                            <li class="nav-item">
                                <a class="nav-link {if $site->site_id == $currentSiteId}active{/if}"
                                   id="tab-{$site->site_id}"
                                   data-toggle="pill"
                                   data-site-id="{$site->site_id}"
                                   href="#content-{$site->site_id}"
                                   role="tab"
                                   aria-controls="content-{$site->site_id}"
                                   aria-selected="{if $site->site_id == $currentSiteId}true{else}false{/if}">
                                    {$site->title}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            </div>

            {* Скрытое поле для site_id *}
            <input type="hidden" id="current_site_id" name="site_id" value="{$currentSiteId}">

            {* Контейнер для контента табов *}
            <div class="tab-content" id="siteTabsContent">
                {* Контент активного таба загружается изначально *}
                <div class="tab-pane fade show active" id="content-{$currentSiteId}" role="tabpanel">
        {/if}

        {* Основной контент FAQ (блоки, секции, вопросы) *}
        {if !empty($structuredBlocks)}
            {foreach $structuredBlocks as $block}
                <div class="card shadow-sm mb-4">
                    <div class="card-header border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-2 font-weight-bold">
                                    {$block.name}
                                </h4>
                                <div class="d-flex align-items-center flex-wrap">
                                    <small class="text-muted mr-3">
                                        Тип: {$block.type_label}
                                    </small>
                                    {if !empty($block.block_yandex_goal_id)}
                                        <small class="text-muted">
                                            <i class="mdi mdi-target"></i> Цель: <code>{$block.block_yandex_goal_id}</code>
                                        </small>
                                    {/if}
                                </div>
                            </div>
                            <div class="ml-3">
                                <button class="btn btn-sm btn-outline-success js-add-section mr-1"
                                        data-block_id="{$block.block_id}"
                                        data-toggle="modal"
                                        data-target="#createSectionModal"
                                        title="Добавить секцию">
                                    <i class="mdi mdi-plus"></i> Добавить секцию
                                </button>
                                <a href="#" class="btn btn-sm btn-outline-info mr-1"
                                   data-block_id="{$block.block_id}"
                                   data-block_name="{$block.name}"
                                   data-block_type="{$block.type}"
                                   data-block_yandex_goal_id="{$block.block_yandex_goal_id}"
                                   data-toggle="modal" data-target="#editBlockModal"
                                   title="Редактировать блок">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-danger"
                                   data-block_id="{$block.block_id}"
                                   title="Удалить блок">
                                    <i class="far fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {if !empty($block.sections)}
                            <div class="sortable-sections" data-block-id="{$block.block_id}">
                                {foreach $block.sections as $section}
                                    <div class="card mb-3 shadow-sm section-item"
                                         data-section-id="{$section.section_id}">
                                        <div class="card-header border-left border-primary border-3 py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0 font-weight-semibold cursor-move">
                                                    <i class="mdi mdi-drag-vertical text-muted mr-2"></i>
                                                    {$section.section_name}
                                                </h5>
                                                <div>
                                                    <button class="btn btn-sm btn-outline-success js-add-faq mr-1"
                                                            data-section_id="{$section.section_id}"
                                                            data-toggle="modal"
                                                            data-target="#createFAQModal"
                                                            title="Добавить вопрос">
                                                        <i class="mdi mdi-plus-circle"></i> Добавить вопрос
                                                    </button>
                                                    <a href="#" class="btn btn-sm btn-outline-info js-edit-section mr-1"
                                                       data-section_id="{$section.section_id}"
                                                       data-name="{$section.section_name}"
                                                       data-block_id="{$block.block_id}"
                                                       data-toggle="modal"
                                                       data-target="#editSectionModal"
                                                       title="Редактировать секцию">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-outline-danger js-delete-section"
                                                       data-section_id="{$section.section_id}"
                                                       title="Удалить секцию">
                                                        <i class="far fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-hover mb-0">
                                                <thead class="thead-light">
                                                <tr>
                                                    <th style="width: 50px" class="text-center">#</th>
                                                    <th>Вопрос</th>
                                                    <th style="width: 200px">Цель Яндекс Метрики</th>
                                                    <th style="width: 100px" class="text-center">Действия</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                {if !empty($section.faqs)}
                                                    {foreach $section.faqs as $faq name=faqLoop}
                                                        <tr id="faq_{$faq.faq_id}" class="faq-row">
                                                            <td class="text-center align-middle">
                                                                <span class="badge badge-light">{$smarty.foreach.faqLoop.iteration}</span>
                                                            </td>
                                                            <td class="align-middle">
                                                                <strong>{$faq.question}</strong>
                                                            </td>
                                                            <td class="align-middle">
                                                                {if !empty($faq.yandex_goal_id)}
                                                                    <code class="text-primary">{$faq.yandex_goal_id}</code>
                                                                {else}
                                                                    <span class="text-muted">—</span>
                                                                {/if}
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <div class="btn-group btn-group-sm" role="group">
                                                                    <a href="#" class="btn btn-outline-info js-edit-item"
                                                                       data-faq_id="{$faq.faq_id}"
                                                                       data-section_id="{$section.section_id}"
                                                                       data-question="{$faq.question|escape:'html'}"
                                                                       data-answer="{$faq.answer|escape:'html'}"
                                                                       data-goal_id="{$faq.yandex_goal_id|escape:'html'}"
                                                                       data-toggle="modal" data-target="#editFAQModal"
                                                                       title="Редактировать">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <a href="#" class="btn btn-outline-danger js-delete-item"
                                                                       data-faq_id="{$faq.faq_id}"
                                                                       title="Удалить">
                                                                        <i class="far fa-trash-alt"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    {/foreach}
                                                {else}
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">
                                                            <i class="mdi mdi-information-outline mr-1"></i>
                                                            Нет вопросов в этой секции
                                                        </td>
                                                    </tr>
                                                {/if}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        {else}
                            <div class="alert alert-info d-flex justify-content-between align-items-center" role="alert">
                                <span>
                                    <i class="mdi mdi-information mr-2"></i>
                                    Нет секций в этом блоке. Добавьте первую секцию, чтобы начать.
                                </span>
                                <button class="btn btn-sm btn-success js-add-section"
                                        data-block_id="{$block.block_id}"
                                        data-toggle="modal"
                                        data-target="#createSectionModal">
                                    <i class="mdi mdi-plus-circle"></i> Добавить секцию
                                </button>
                            </div>
                        {/if}
                    </div>
                </div>
            {/foreach}
        {else}
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="mdi mdi-help-circle-outline" style="font-size: 4rem; opacity: 0.5;"></i>
                    <h4 class="mt-3">Нет доступных FAQ</h4>
                    <p class="text-muted">Начните с создания первого блока вопросов и ответов</p>
                    <button class="btn btn-primary mt-2" data-toggle="modal" data-target="#createBlockModal">
                        <i class="mdi mdi-plus-circle"></i> Создать первый блок
                    </button>
                </div>
            </div>
        {/if}

        {* Закрываем контейнер табов если есть сайты *}
        {if !empty($sites)}
                </div>
            </div>
        {/if}
    </div>
</div>

{* Модальные окна - копируем из list.tpl *}
<!-- Модальное окно для создания FAQ -->
<div class="modal fade" id="createFAQModal" tabindex="-1" aria-labelledby="createFAQLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createFAQLabel">Создать FAQ</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createFAQForm">
                <input type="hidden" id="create_section_id" name="section_id">
                <input type="hidden" name="site_id" id="form_site_id" value="{$currentSiteId}">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_question" class="form-label">Вопрос</label>
                        <input type="text" class="form-control" id="create_question" name="question" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_answer" class="form-label">Ответ</label>
                        <textarea class="form-control faq-answer" id="create_answer" name="answer" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="create_yandex_goal_id" class="form-label">Идентификатор цели (Яндекс Метрика)</label>
                        <input type="text" class="form-control" id="create_yandex_goal_id" name="yandex_goal_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Создать</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования FAQ -->
<div class="modal fade" id="editFAQModal" tabindex="-1" role="dialog" aria-labelledby="editFAQModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFAQModalLabel">Редактировать FAQ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editFAQForm">
                <input type="hidden" name="site_id" class="form_site_id" value="{$currentSiteId}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_question">Вопрос</label>
                        <input type="text" class="form-control" id="edit_question" name="question" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_answer">Ответ</label>
                        <textarea class="form-control faq-answer" id="edit_answer" name="answer" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_yandex_goal_id">Идентификатор цели (Яндекс Метрика)</label>
                        <input type="text" class="form-control" id="edit_yandex_goal_id" name="yandex_goal_id">
                    </div>
                    <input type="hidden" id="edit_faq_id" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" id="saveFAQ">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Остальные модалки (Block, Section) -->
<div class="modal fade" id="editBlockModal" tabindex="-1" aria-labelledby="editBlockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBlockModalLabel">Редактировать блок</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBlockForm">
                <input type="hidden" id="edit_block_id" name="block_id">
                <input type="hidden" name="site_id" class="form_site_id" value="{$currentSiteId}">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_block_title" class="form-label">Название блока</label>
                        <input type="text" class="form-control" id="edit_block_title" name="block_title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_block_type" class="form-label">Тип блока</label>
                        <select class="form-control" id="edit_block_type" name="block_type" required>
                            {foreach $blockTypes as $blockType}
                                <option value="{$blockType.value}">{$blockType.label}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_block_yandex_goal_id" class="form-label">Идентификатор цели (Яндекс Метрика)</label>
                        <input type="text" class="form-control" id="edit_block_yandex_goal_id" name="block_yandex_goal_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" id="saveBlock">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="createBlockModal" tabindex="-1" aria-labelledby="createBlockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createBlockModalLabel">Создать блок</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createBlockForm">
                <input type="hidden" name="site_id" class="form_site_id" value="{$currentSiteId}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="block_title" class="form-label">Название блока</label>
                        <input type="text" class="form-control" id="block_title" name="block_title" required>
                    </div>
                    <div class="mb-3">
                        <label for="block_type" class="form-label">Тип блока</label>
                        <select class="form-control" id="block_type" name="block_type" required>
                            {foreach $blockTypes as $blockType}
                                <option value="{$blockType.value}">{$blockType.label}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Создать</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="createSectionModal" tabindex="-1" aria-labelledby="createSectionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createSectionForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSectionLabel">Создать секцию</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="block_id" id="create_section_block_id">
                    <input type="hidden" name="site_id" class="form_site_id" value="{$currentSiteId}">
                    <div class="mb-3">
                        <label for="section_name" class="form-label">Название секции</label>
                        <input type="text" class="form-control" id="section_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Создать</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editSectionModal" tabindex="-1" aria-labelledby="editSectionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editSectionForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSectionLabel">Редактировать секцию</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_section_id">
                    <input type="hidden" name="block_id" id="edit_section_block_id">
                    <input type="hidden" name="site_id" class="form_site_id" value="{$currentSiteId}">
                    <div class="mb-3">
                        <label for="edit_section_name" class="form-label">Название секции</label>
                        <input type="text" class="form-control" id="edit_section_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

{capture name='page_scripts'}
<style>
    .faq-row {
        transition: background-color 0.2s ease;
    }
    .faq-row:hover {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }
    .badge {
        font-weight: 500;
        font-size: 0.85rem;
    }
    .border-3 {
        border-width: 4px !important;
    }
    code {
        padding: 0.2rem 0.4rem;
        font-size: 87.5%;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 0.25rem;
    }
    .btn-group-sm > .btn, .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }
    .cursor-move {
        cursor: move;
    }
    .sortable-sections .section-item {
        transition: background-color 0.2s ease;
    }
    .sortable-sections .ui-sortable-helper {
        opacity: 0.8;
    }
    .nav-pills .nav-link {
        border-radius: 0.25rem;
        margin-right: 0.5rem;
    }
    .nav-pills .nav-link.active {
        background-color: #007bff;
    }
</style>

<script src="/design/manager/assets/plugins/tinymce/tinymce.min.js" referrerpolicy="origin"></script>

<script>
(() => {
    'use strict';

    const AJAX_URL = '{$ajaxUrl}';
    let currentSiteId = '{$currentSiteId}';

    // Утилиты
    const Utils = {
        showSuccess: (message) => {
            return Swal.fire({
                timer: 3000,
                text: message,
                type: 'success'
            }).then(() => location.reload());
        },
        showError: (message = 'Произошла ошибка') => {
            return Swal.fire({
                text: message,
                type: 'error'
            });
        },
        confirmDelete: (message) => {
            return Swal.fire({
                html: message,
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DD6B55',
                confirmButtonText: 'Да, удалить!',
                cancelButtonText: 'Отмена',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading()
            });
        },
        ajaxRequest: (data) => {
            // Если data - строка (из serialize()), добавляем site_id к ней
            if (typeof data === 'string') {
                if (data.indexOf('site_id=') === -1) {
                    data += '&site_id=' + encodeURIComponent(currentSiteId);
                }
            } else if (typeof data === 'object' && !data.site_id) {
                // Если data - объект, добавляем site_id как свойство
                data.site_id = currentSiteId;
            }

            return $.ajax({
                type: 'POST',
                url: AJAX_URL,
                data: data,
                dataType: 'json'
            });
        },
        getTinyMCE: (id) => {
            const editor = tinymce.get(id);
            if (!editor) {
                console.error('TinyMCE editor "' + id + '" not found');
            }
            return editor;
        }
    };

    // Обработка переключения табов
    const Tabs = {
        init: () => {
            $('#siteTabs a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                const newSiteId = $(e.target).data('site-id');
                currentSiteId = newSiteId;
                $('#current_site_id').val(newSiteId);
                $('.form_site_id').val(newSiteId);

                // Перезагружаем страницу с новым site_id
                window.location.href = AJAX_URL + '?site_id=' + newSiteId;
            });
        }
    };

    // FAQ операции
    const FAQ = {
        init: () => {
            $(document).on('click', '.js-add-faq', FAQ.prepareCreate);
            $(document).on('click', '.js-edit-item', FAQ.prepareEdit);
            $('#createFAQForm').on('submit', FAQ.create);
            $('#saveFAQ').on('click', FAQ.update);
            $(document).on('click', '.js-delete-item', FAQ.delete);
        },
        prepareCreate: function() {
            const sectionId = $(this).data('section_id');
            $('#create_section_id').val(sectionId);
            $('#form_site_id').val(currentSiteId);
        },
        prepareEdit: function() {
            const data = $(this).data();
            $('#edit_faq_id').val(data.faq_id);
            $('#edit_question').val(data.question);
            $('#edit_yandex_goal_id').val(data.goal_id);

            const editor = Utils.getTinyMCE('edit_answer');
            if (editor) {
                editor.setContent(data.answer || '');
            }
        },
        create: (e) => {
            e.preventDefault();
            const formData = $('#createFAQForm').serialize() + '&action=create';

            Utils.ajaxRequest(formData)
                .done(() => {
                    $('#createFAQModal').modal('hide');
                    Utils.showSuccess('FAQ успешно создан');
                })
                .fail(() => Utils.showError('Ошибка при создании FAQ'));
        },
        update: () => {
            const editor = Utils.getTinyMCE('edit_answer');
            if (editor) {
                $('#edit_answer').val(editor.getContent());
            }

            const formData = $('#editFAQForm').serialize() + '&action=update';

            Utils.ajaxRequest(formData)
                .done(() => {
                    $('#editFAQModal').modal('hide');
                    Utils.showSuccess('Данные успешно сохранены');
                })
                .fail(() => Utils.showError('Ошибка при сохранении данных'));
        },
        delete: function(e) {
            e.preventDefault();
            const faqId = $(this).data('faq_id');

            Utils.confirmDelete('Вы действительно хотите удалить этот FAQ?')
                .then((result) => {
                    if (result.value) {
                        return Utils.ajaxRequest({ action: 'delete', id: faqId });
                    }
                    return Promise.reject('cancelled');
                })
                .then(() => {
                    $('#faq_' + faqId).remove();
                    Utils.showSuccess('FAQ успешно удален');
                })
                .catch((error) => {
                    if (error !== 'cancelled') {
                        Utils.showError('Ошибка при удалении FAQ');
                    }
                });
        }
    };

    // Блоки операции
    const Block = {
        init: () => {
            $('#createBlockForm').on('submit', Block.create);
            $(document).on('click', '[data-toggle="modal"][data-target="#editBlockModal"]', Block.prepareEdit);
            $('#saveBlock').on('click', Block.update);
            $(document).on('click', '.btn-outline-danger[data-block_id]', Block.delete);
        },
        prepareEdit: function() {
            const data = $(this).data();
            $('#edit_block_id').val(data.block_id);
            $('#edit_block_title').val(data.block_name);
            $('#edit_block_type').val(data.block_type);
            $('#edit_block_yandex_goal_id').val(data.block_yandex_goal_id);
        },
        create: (e) => {
            e.preventDefault();
            const formData = $('#createBlockForm').serialize() + '&action=createBlock';

            Utils.ajaxRequest(formData)
                .done(() => {
                    $('#createBlockModal').modal('hide');
                    Utils.showSuccess('Блок успешно создан');
                })
                .fail(() => Utils.showError('Ошибка при создании блока'));
        },
        update: () => {
            const formData = $('#editBlockForm').serialize() + '&action=updateBlock';

            Utils.ajaxRequest(formData)
                .done(() => {
                    $('#editBlockModal').modal('hide');
                    Utils.showSuccess('Блок успешно обновлен');
                })
                .fail(() => Utils.showError('Ошибка при обновлении блока'));
        },
        delete: function(e) {
            e.preventDefault();
            const blockId = $(this).data('block_id');

            Utils.confirmDelete('Вы действительно хотите удалить этот блок?<br><b>Все секции и вопросы будут удалены!</b>')
                .then((result) => {
                    if (result.value) {
                        return Utils.ajaxRequest({ action: 'deleteBlock', id: blockId });
                    }
                    return Promise.reject('cancelled');
                })
                .then(() => Utils.showSuccess('Блок успешно удален'))
                .catch((error) => {
                    if (error !== 'cancelled') {
                        Utils.showError('Ошибка при удалении блока');
                    }
                });
        }
    };

    // Секции операции
    const Section = {
        init: () => {
            $(document).on('click', '.js-add-section', Section.prepareCreate);
            $('#createSectionForm').on('submit', Section.create);
            $(document).on('click', '.js-edit-section', Section.prepareEdit);
            $('#editSectionForm').on('submit', Section.update);
            $(document).on('click', '.js-delete-section', Section.delete);
            Section.initSortable();
        },
        prepareCreate: function() {
            $('#create_section_block_id').val($(this).data('block_id'));
        },
        prepareEdit: function() {
            const data = $(this).data();
            $('#edit_section_id').val(data.section_id);
            $('#edit_section_block_id').val(data.block_id);
            $('#edit_section_name').val(data.name);
        },
        create: (e) => {
            e.preventDefault();
            const formData = $('#createSectionForm').serialize() + '&action=createSection';

            Utils.ajaxRequest(formData)
                .done(() => {
                    $('#createSectionModal').modal('hide');
                    Utils.showSuccess('Секция успешно создана');
                })
                .fail(() => Utils.showError('Ошибка при создании секции'));
        },
        update: (e) => {
            e.preventDefault();
            const formData = $('#editSectionForm').serialize() + '&action=updateSection';

            Utils.ajaxRequest(formData)
                .done(() => {
                    $('#editSectionModal').modal('hide');
                    Utils.showSuccess('Секция успешно обновлена');
                })
                .fail(() => Utils.showError('Ошибка при обновлении секции'));
        },
        delete: function(e) {
            e.preventDefault();
            const sectionId = $(this).data('section_id');

            Utils.confirmDelete('Вы действительно хотите удалить эту секцию?<br><b>Все вопросы будут удалены!</b>')
                .then((result) => {
                    if (result.value) {
                        return Utils.ajaxRequest({ action: 'deleteSection', id: sectionId });
                    }
                    return Promise.reject('cancelled');
                })
                .then(() => Utils.showSuccess('Секция успешно удалена'))
                .catch((error) => {
                    if (error !== 'cancelled') {
                        Utils.showError('Ошибка при удалении секции');
                    }
                });
        },
        initSortable: () => {
            $('.sortable-sections').sortable({
                handle: 'h5',
                update: (event, ui) => {
                    const $container = $(event.target);
                    const newOrder = [];

                    $container.find('.section-item').each((index, element) => {
                        newOrder.push({
                            id: $(element).data('section-id'),
                            sequence: index + 1
                        });
                    });

                    Utils.ajaxRequest({
                        action: 'reorderSections',
                        order: newOrder
                    })
                    .done(() => {})
                    .fail(() => Utils.showError('Ошибка при изменении порядка секций'));
                }
            });
        }
    };

    // Инициализация TinyMCE
    const initTinyMCE = () => {
        tinymce.init({
            selector: '.faq-answer',
            menubar: false,
            plugins: 'lists link image preview',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
            height: 300
        });
    };

    // Главная инициализация
    $(document).ready(() => {
        initTinyMCE();
        Tabs.init();
        FAQ.init();
        Block.init();
        Section.init();
    });
})();
</script>
{/capture}
