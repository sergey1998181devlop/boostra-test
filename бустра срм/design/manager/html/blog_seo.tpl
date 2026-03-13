{$meta_title = 'Блог SЕО' scope=parent}

{capture name='page_scripts'}{/capture}
{capture name='page_styles'}{/capture}

<div class="page-wrapper">
  <div class="container-fluid">
    <div class="row page-titles">
      <div class="col-md-6 col-8 align-self-center">
        <h3 class="text-themecolor mb-0 mt-0">
          Настройки сайта
        </h3>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/">Главная</a></li>
          <li class="breadcrumb-item active">Блог SЕО</li>
        </ol>
      </div>
      <div class="col-md-6 col-4 align-self-center">
        <button class="btn float-right hidden-sm-down btn-success js-open-add-modal">
          <i class="mdi mdi-plus-circle"></i> <a href="/article" class="text-white">Добавить статью</a>
        </button>
      </div>
    </div>

    <div class="row">
      <div class="col12 w-100">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Статьи</h4>
            <div id="basicgrid" class="jsgrid" style="position: relative; overflow-x: auto; width: auto; white-space: normal; padding-bottom: 15px;">
              <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                <table class="table table-bordered table-hover">
                  <thead>
                  <tr class="jsgrid-header-row">
                    <td class="jsgrid-header-cell">Заголовок</td>
                    <td class="jsgrid-header-cell">Автор</td>
                    <td class="jsgrid-header-cell">Дата публикации</td>
                  </tr>
                  </thead>
                  <tbody>
                  {foreach $articles as $article}
                    <tr class="jsgrid-row">
                      <td class="jsgrid-cell">
                        <h4>{$article->title}</h4>
                          <a href="/article/{$article->id|escape}" class="bg-info text-white pt-2 pb-2 text-center btn-block w-25">Изменить</a>

                          <form class="form-horizontal mt-2 mb-2" method="POST">
                            <input type="hidden" name="action" value="delete-article">
                            <input type="hidden" name="id" value="{$article->id|escape}">
                            <input type="submit" class="text-white bg-danger border-0 p-2 btn-block w-25" value="Удалить">
                          </form>

                          <a target="_blank" href="{$config->front_url}/blog/{$article->slug|escape}" class="bg-purple text-white pt-2 pb-2 text-center btn-block w-25">Перейти</a>
                      </td>
                      <td class="jsgrid-cell">{$article->author_data->name}</td>
                      <td class="jsgrid-cell">{$article->created_at|date}</td>
                    </tr>
                  {/foreach}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  {include file='footer.tpl'}
</div>
