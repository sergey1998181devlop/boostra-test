{$meta_title = 'Настройки Цессии' scope=parent}

<script>
    {literal}
    function updateCounterpartyField(input) {
        const id = input.dataset.id;
        const value = input.value;
        $.post('ajax/cession_settings.php?action=update_counterparty_name', { id: id, value: value }, function (res) {
            if (!res.success) {
                const errorIcon = input.parentNode.querySelector('.error-icon');
                if (errorIcon) {
                    errorIcon.classList.remove('d-none');
                }

                input.classList.add('is-invalid');

                setTimeout(() => {
                    if (errorIcon) errorIcon.classList.add('d-none');
                    input.classList.remove('is-invalid');
                }, 3000);
            }
            location.reload();
        }, 'json');
    }
    {/literal}

    {literal}
    function deleteCounterparty(id) {
        if (confirm('Удалить этого контрагента и все его email?')) {
            $.post('ajax/cession_settings.php?action=delete_counterparty', { id: id }, function (res) {
                if (res === true || res === 'true' || (res && res.success)) {
                    location.reload();
                } else {
                    alert(res.error || 'Ошибка удаления');
                }
            }, 'json');
        }
    }
    {/literal}

    {literal}
    function deleteCounterpartyEmail(counterpartyId, email) {
        if (confirm('Удалить этот email?')) {
            $.post('ajax/cession_settings.php?action=delete_counterparty_email', {
                counterparty_id: counterpartyId,
                email
            }, function (res) {
                if (res === true || res === 'true' || (res && res.success)) {
                    location.reload();
                } else {
                    alert(res.error || 'Ошибка удаления email');
                }
            }, 'json');
        }
    }
    {/literal}

    {literal}
    function addEmailField(button, id) {
        const container = button.parentNode;
        const wrap = document.createElement('div');
        wrap.innerHTML = `
    <form class="mb-1 d-flex">
      <input type="hidden" name="counterparty_id" value="${id}">
      <input type="email" name="email" class="form-control form-control-sm mr-1" placeholder="Новый email" required>
      <button class="btn btn-sm btn-success">✔</button>
    </form>
  `;
        const form = wrap.querySelector('form');

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            $.post(
                'ajax/cession_settings.php?action=add_counterparty_email',
                $(form).serialize(),
                function (res) {
                    if (res === true || res === 'true' || (res && res.success)) {
                        location.reload();
                    } else {
                        alert((res && res.error) || 'Не удалось добавить email');
                    }
                },
                'json'
            );
        });

        container.insertBefore(wrap, button);
    }
    {/literal}
    {literal}
    function updateDirectorNameField(id) {
        var director_name_input = document.getElementById('director-name-' + id);
        var director_name = director_name_input.value.trim();

        if (!director_name) {
            alert('ФИО директора не может быть пустым');
            return;
        }

        $.post('ajax/cession_settings.php?action=update_counterparty_director_name', {
            id: id,
            director_name: director_name,
        }, function (res) {
            if (!res) {
                alert('Ошибка сохранения директора');
            }
            location.reload();
        }, 'json');
    }

    function updateDirectorPositionField(id) {

        var director_position_input = document.getElementById('director-position-' + id);
        var director_position = director_position_input.value.trim();

        if (!director_position) {
            alert('Должность не может быть пустой');
            return;
        }

        $.post('ajax/cession_settings.php?action=update_counterparty_director_position', {
            id: id,
            director_position: director_position
        }, function (res) {
            if (!res) {
                alert('Ошибка сохранения должности');
            }
            location.reload();
        }, 'json');
    }
    {/literal}
</script>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="white-box">
            <h2 class="box-title">Настройки Цессии</h2>

            {foreach from=$enumValues key=field item=values}
                <div class="mb-4 border-bottom pb-3">
                    <h4 class="mb-3">
                        {if $field == 'contract_form'}Форма договора{/if}
                        {if $field == 'cedent'}Цедент{/if}
                        {if $field == 'counterparty'}Контрагент{/if}
                        {if $field == 'importance'}Важность{/if}
                        {if $field == 'execution_status'}Статус исполнения{/if}
                    </h4>

                    <ul class="list-group mb-3">
                        {foreach from=$values item=value}
                            <li class="list-group-item px-2 py-1">
                                <div class="d-flex align-items-center" style="gap: 8px; max-width: 320px;">
                                    <input type="text" class="form-control form-control-sm" value="{$value}" disabled
                                           style="width: 250px; padding: 2px 6px; font-size: 13px;">
                                    {if !empty($deletableFlags[$field][$value])}
                                        <form method="post" action="ajax/cession_settings.php?action=delete_enum_value"
                                              class="mb-0">
                                            <input type="hidden" name="field" value="{$field}">
                                            <input type="hidden" name="value_to_delete" value="{$value}">
                                            <button type="submit" class="btn btn-sm btn-danger p-1"
                                                    style="font-size: 14px; line-height: 1;">✖
                                            </button>
                                        </form>
                                    {/if}
                                </div>
                            </li>
                        {/foreach}

                        <li class="list-group-item d-flex align-items-center px-2 py-1">
                            <form method="post" action="ajax/cession_settings.php?action=add_enum_value"
                                  class="form-inline w-100">
                                <input type="hidden" name="field" value="{$field}">
                                <input type="text" name="new_value" class="form-control form-control-sm mr-2"
                                       style="width: 250px;" placeholder="Новое значение..." required>
                                <button type="submit" class="btn btn-sm btn-success px-3 py-1">Добавить</button>
                            </form>
                        </li>
                    </ul>
                </div>
            {/foreach}
        </div>
        <div class="white-box">
            <h2 class="box-title">Контрагенты и Почты</h2>

            <table class="table table-bordered table-sm" style="max-width: 1200px;">
                <thead class="thead-light">
                <tr>
                    <th style="width: 20%;">Контрагент</th>
                    <th style="width: 30%;">Почты</th>
                    <th style="width: 20%;">ФИО директора</th>
                    <th style="width: 20%;">Должность</th>
                    <th style="width: 10%;">Действия</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$counterparties item=cp}
                    <tr>
                        <td>
                            <div class="position-relative">
                                <input type="text"
                                       class="form-control form-control-sm counterparty-name-input"
                                       value="{$cp->name}"
                                       data-id="{$cp->id}"
                                       id="counterparty-name-{$cp->id}">
                                <button class="btn btn-success btn-sm mt-1"
                                        onclick="updateCounterpartyField(document.getElementById('counterparty-name-{$cp->id}'))">
                                    Сохранить
                                </button>
                            </div>
                        </td>
                        <td>
                            {foreach from=$cp->emails item=email key=ei}
                                <div class="input-group input-group-sm mb-1" style="max-width: 100%;">
                                    <input type="email" class="form-control form-control-sm"
                                           value="{$email}"
                                           data-id="{$cp->id}"
                                           data-email-index="{$ei}"
                                           onchange="updateCounterpartyEmail(this)"
                                           style="min-width: 220px;">
                                    <div class="input-group-append">
                                        <button class="btn btn-danger btn-sm"
                                                onclick="deleteCounterpartyEmail({$cp->id}, '{$email}')">✖
                                        </button>
                                    </div>
                                </div>
                            {/foreach}
                            <button class="btn btn-link btn-sm px-0 mt-1" onclick="addEmailField(this, '{$cp->id}')">+
                                добавить почту
                            </button>
                        </td>
                        <td>
                            <div class="position-relative">
                                <input type="text"
                                       class="form-control form-control-sm"
                                       value="{$cp->director_name}"
                                       data-id="{$cp->id}"
                                       id="director-name-{$cp->id}">
                                <button class="btn btn-success btn-sm mt-1"
                                        onclick="updateDirectorNameField({$cp->id})">
                                    Сохранить
                                </button>
                            </div>
                        </td>
                        <td>
                            <div class="position-relative">
                                <input type="text"
                                       class="form-control form-control-sm"
                                       value="{$cp->director_position}"
                                       data-id="{$cp->id}"
                                       id="director-position-{$cp->id}">
                                <button class="btn btn-success btn-sm mt-1"
                                        onclick="updateDirectorPositionField({$cp->id})">
                                    Сохранить
                                </button>
                            </div>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-danger" onclick="deleteCounterparty({$cp->id})">Удалить
                            </button>
                        </td>
                    </tr>
                {/foreach}

                <!-- Add new row -->
                <tr>
                    <form method="post" action="ajax/cession_settings.php?action=add_counterparty">
                        <td>
                            <input type="text"
                                   name="name"
                                   class="form-control form-control-sm bg-dark text-white"
                                   placeholder="Имя агента"
                                   required
                                   style="min-width: 200px;">
                        </td>
                        <td>
                            <input type="email"
                                   name="emails[]"
                                   class="form-control form-control-sm bg-dark text-white"
                                   placeholder="Email"
                                   required
                                   style="min-width: 220px;">
                        </td>
                        <td>
                            <input type="text"
                                   name="director_name"
                                   class="form-control form-control-sm bg-dark text-white"
                                   placeholder="ФИО директора"
                                   required>
                        </td>
                        <td>
                            <input type="text"
                                   name="director_position"
                                   class="form-control form-control-sm bg-dark text-white"
                                   placeholder="Должность"
                                   required>
                        </td>
                        <td class="text-center">
                            <button type="submit" class="btn btn-sm btn-success px-3">Добавить</button>
                        </td>
                    </form>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>