{$meta_title = 'Настройки Usedesk' scope=parent}

{capture name='page_scripts'}
    <script>
        document.getElementById('avatar-file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                alert('Максимальный размер файла: 2MB');
                return;
            }

            const formData = new FormData();
            formData.append('avatar', file);
            formData.append('action', 'upload');

            const uploadBtn = document.querySelector('.btn-upload-avatar');
            const originalText = uploadBtn.innerHTML;
            uploadBtn.innerHTML = '<i class="mdi mdi-timer-sand"></i> Загрузка...';
            uploadBtn.disabled = true;

            fetch('ajax/UsedeskSettings.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                uploadBtn.innerHTML = originalText;
                uploadBtn.disabled = false;

                if (data.status && data.result && data.result.success) {
                    document.getElementById('avatar-preview').src = data.result.url;

                    // Используем нативный alert если alert.success не определён
                    if (typeof alert.success === 'function') {
                        alert.success('Аватар успешно загружен!');
                    }

                    // Перезагружаем страницу для обновления статуса
                    setTimeout(() => {
                        window.location.href = '?module=UsedeskSettingsView';
                    }, 1000);
                } else {
                    if (typeof alert.error === 'function') {
                        alert.error('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                    } else {
                        window.alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                    }
                }
            })
            .catch(err => {
                uploadBtn.innerHTML = originalText;
                uploadBtn.disabled = false;
                alert.error('Ошибка загрузки: ' + err);
            });
        });

        (function() {
            var form = document.getElementById('usedesk-settings-form');
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var btn = form.querySelector('button[type="submit"]');
                var originalText = btn.innerHTML;
                btn.innerHTML = '<i class="mdi mdi-timer-sand"></i> Сохранение...';
                btn.disabled = true;

                fetch('ajax/UsedeskSettings.php', {
                    method: 'POST',
                    body: new FormData(form)
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    if (data.status && data.result && data.result.success) {
                        var wrap = document.getElementById('usedesk-settings-message-wrap');
                        var msg = document.getElementById('usedesk-settings-message');
                        if (wrap && msg) {
                            msg.textContent = 'Настройки сохранены';
                            wrap.classList.remove('d-none');
                            setTimeout(function() { wrap.classList.add('d-none'); }, 3000);
                        }
                        if (typeof alert.success === 'function') alert.success('Настройки сохранены');
                    } else {
                        if (typeof alert.error === 'function') alert.error(data.message || 'Ошибка сохранения');
                        else window.alert(data.message || 'Ошибка сохранения');
                    }
                })
                .catch(function(err) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    if (typeof alert.error === 'function') alert.error('Ошибка: ' + err);
                    else window.alert('Ошибка: ' + err);
                });
            });
        })();
    </script>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">{$meta_title}</h4>
            </div>
            <div class="col-md-7 align-self-center text-right">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="?">Главная</a></li>
                        <li class="breadcrumb-item"><a href="?module=SettingsView">Настройки</a></li>
                        <li class="breadcrumb-item active">{$meta_title}</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="alert alert-success alert-dismissible fade show {if !$message}d-none{/if}" role="alert" id="usedesk-settings-message-wrap">
            <span id="usedesk-settings-message">{$message}</span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0 text-white">Аватар оператора чата Usedesk</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" class="mb-4" id="usedesk-settings-form">
                            <input type="hidden" name="action" value="save_settings">
                            <div class="form-group mb-0">
                                <label class="mb-2">Кастомная иконка в виджете чата</label>
                                <div class="d-flex align-items-center">
                                    <select name="custom_icon_enabled" class="form-control" style="max-width: 200px;">
                                        <option value="1" {if $custom_icon_enabled}selected{/if}>Включено</option>
                                        <option value="0" {if !$custom_icon_enabled}selected{/if}>Выключено</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary ml-2">
                                        <i class="mdi mdi-content-save"></i> Сохранить
                                    </button>
                                </div>
                                <small class="form-text text-muted">
                                    При выключении в виджете будет отображаться стандартная иконка Usedesk.
                                </small>
                            </div>
                        </form>

                        <hr class="my-4">

                        <p class="text-muted">
                            Настройте аватар для оператора в чате Usedesk. Аватар будет отображаться в виджете чата.
                        </p>

                        <div class="d-flex align-items-center mb-4">
                            <img id="avatar-preview"
                                 src="{$current_avatar}"
                                 alt="Аватар оператора"
                                 class="img-thumbnail"
                                 style="max-width: 100px; max-height: 100px;">
                            {if $has_custom_avatar}
                                <span class="badge badge-success ml-2">Загруженный</span>
                            {else}
                                <span class="badge badge-secondary ml-2">По умолчанию</span>
                            {/if}
                        </div>

                        <div class="mb-3">
                            <input type="file" id="avatar-file" accept=".svg,.png,.jpg,.jpeg" style="display:none;">
                            <button type="button" class="btn btn-primary btn-upload-avatar" onclick="document.getElementById('avatar-file').click();">
                                <i class="mdi mdi-cloud-upload"></i> Загрузить новый аватар
                            </button>

                            {if $has_custom_avatar}
                            <form method="post" class="d-inline ml-2">
                                <input type="hidden" name="action" value="reset">
                                <button type="submit" class="btn btn-warning"
                                        onclick="return confirm('Вы уверены, что хотите сбросить аватар к значению по умолчанию?');">
                                    <i class="mdi mdi-rotate-left"></i> Сбросить к значению по умолчанию
                                </button>
                            </form>
                            {/if}
                        </div>

                        <div class="alert alert-info mb-3">
                            <i class="mdi mdi-information"></i>
                            Поддерживаемые форматы: PNG, JPG, JPEG. Рекомендуемый размер: 64x64 px. Максимальный размер файла: 2MB
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
