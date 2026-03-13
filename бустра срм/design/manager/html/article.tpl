{$meta_title = 'Статьи' scope=parent}

{capture name='page_scripts'}
  <script src="https://cdn.jsdelivr.net/npm/quill@2.0.1/dist/quill.js"></script>
  <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/editor.js"></script>
{/capture}


{capture name='page_styles'}
  <link href="https://cdn.jsdelivr.net/npm/quill@2.0.1/dist/quill.core.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/quill@2.0.1/dist/quill.snow.css" rel="stylesheet">
  <link href="design/{$settings->theme|escape}/css/editor.css" rel="stylesheet">
{/capture}


<div class="page-wrapper">
  <div class="container-fluid">
    <div class="row page-titles">
      <div class="col-md-6 col-8 align-self-center">
        <h3 class="text-themecolor mb-0 mt-0">
          Настройки сайта
        </h3>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/">Главная</a></li>
          <li class="breadcrumb-item"><a href="/blog_seo">Блог SEO</a></li>
          <li class="breadcrumb-item active">Статьи</li>
        </ol>
      </div>
      <div class="col-md-6 col-4 align-self-center">

      </div>
    </div>

    <div class="row">
      <div class="col12 w-100">
        <div class="card">
          <div class="card-body">
            <div id="basicgrid" class="jsgrid" style="position: relative; overflow-x: auto; width: auto; white-space: normal; padding-bottom: 15px;">
              <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                <form class="form-horizontal" method="POST">
                  <input type="hidden" name="id" value="{$article->id}" />

                  {if $errors}
                    <div class="col-md-12">
                      <ul class="alert alert-danger">
                        {if in_array('empty_title', (array)$errors)}<li>Укажите заголовок!</li>{/if}
                        {if in_array('empty_content', (array)$errors)}<li>Укажите описание!</li>{/if}
                      </ul>
                    </div>
                  {/if}

                  {if $message_success}
                    <div class="col-md-12">
                      <div class="alert alert-success">
                        {if $message_success == 'saved'}Данные сохранены{/if}
                      </div>
                    </div>
                  {/if}

                  <div class="form-group {if in_array('empty_title', (array)$errors)}has-danger{/if}">
                    <label class="col-md-12">Заголовок</label>
                    <div class="col-md-12">
                      <input type="text" name="title" value="{$article->title|escape}" class="form-control form-control-line" required="required" />
                      {if in_array('empty_title', (array)$errors)}<small class="form-control-feedback">Укажите заголовок!</small>{/if}
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-12">Превью статьи</label>
                    <div class="col-md-12">
                      <input type="text" name="description" value="{$article->description|escape}" class="form-control form-control-line" />
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-md-12">Теги через запятую</label>
                    <div class="col-md-12">
                      <input type="text" name="keywords" value="{$article->keywords|escape}" class="form-control form-control-line" />
                    </div>
                  </div>

                  <div class="form-group {if in_array('empty_slug', (array)$errors)}has-danger{/if}">
                    <label class="col-md-12">URL</label>
                    <div class="col-md-12">
                      <input type="text" name="slug" value="{$article->slug|escape}" class="form-control form-control-line" required="required" />
                      {if in_array('empty_slug', (array)$errors)}<small class="form-control-feedback">Укажите slug!</small>{/if}
                    </div>
                  </div>

                    <div class="form-group {if in_array('empty_title', (array)$errors)}has-danger{/if}">
                      <label class="col-md-12">Описание</label>
                      <div class="col-md-12">
                        <div class="custom-editor-container">
                          <div id="editor">
                            {$article->content}
                          </div>
                          <textarea hidden name="content" class="form-control form-control-line">{$article->content}</textarea>
                        </div>
                        {if in_array('empty_content', (array)$errors)}<small class="form-control-feedback">Укажите описание!</small>{/if}
                      </div>
                    </div>

                    <div class="form-group">
                      <div class="col-sm-12">
                        {if !$article->published}
                          <button class="btn btn-success" type="submit" name="publish" value="publish">Опубликовать</button>
                        {else}
                          <button class="btn btn-success" type="submit" name="update" value="update">Обновить</button>
                        {/if}

                        <button class="btn btn-success" type="submit" name="save" value="save"> Сохранить</button>
                      </div>
                    </div>
                </form>
              </div>
              {if !$article->published}
                <div class="col-md-12">
                  <span class="alert text-danger">Не опубликовано</span>
                </div>
              {/if}
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  {include file='footer.tpl'}

  <div class="modal fade custom-editor-file-modal" id="editor-file-modal" tabindex="-1" aria-labelledby="editor-file-modal-label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" data-toggle="tab" data-target="#editor-url" type="button" role="tab" aria-controls="editor-url" aria-selected="true">
                Вставить url
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-toggle="tab" data-target="#editor-file" type="button" role="tab" aria-controls="editor-file" aria-selected="false">
                Выбрать файл
              </button>
            </li>
          </ul>
          <span class="close">&times;</span>
        </div>
        <div class="modal-body py-4">
          <div class="tab-content">
            <div class="tab-pane fade show active" id="editor-url" role="tabpanel" aria-labelledby="editor-url-tab">
              <div class="input-group">
                <input type="text" id="imageUrl" class="form-control" placeholder="Введите URL изображения" />
                <div class="input-group-append">
                  <button class="btn btn-outline-secondary custom-editor-insert-image-link" type="button">Вставить</button>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="editor-file" role="tabpanel" aria-labelledby="editor-file">
              <div class="input-group">
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="custom-editor-file-id" />
                  <label class="custom-file-label" for="custom-editor-file-id" data-title="Выберите файл">Выберите файл</label>
                </div>
                <div class="input-group-append">
                  <button class="btn btn-outline-secondary custom-editor-file-upload-btn" type="button">Загрузить</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="custom-editor-spinner-wrap">
    <div class="spinner-border" role="status"></div>
  </div>
</div>
