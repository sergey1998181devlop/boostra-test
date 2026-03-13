{literal}
    <style>
        .modal {
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            max-width: 600px;
            position: relative;
        }

        .close-btn {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        .clickable-row:hover {
            cursor: pointer;
            background-color: #f8f9fa; /* необязательно — мягкий эффект наведения */
        }
    </style>
{/literal}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Редактирование ПДН</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item">Аналитика маркетологу</li>
                    <li class="breadcrumb-item active">Редактирование ПДН</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {if $notification}
                            <div class="alert alert-info" role="alert">
                                {$notification}
                            </div>
                        {/if}

                        <hr/>
                        <div class="row align-items-center mb-3">
                            <div class="col-10"></div>
                            <div class="col-2">
                                <button onclick="openUpdateFromCsvModal()"
                                        type="button"
                                        class="btn btn-secondary w-100" value="reset">
                                    <i class="ti-import"></i> Обновить из CSV
                                </button>
                            </div>
                        </div>
                        <hr/>

                        <h4 class="card-title">
                            Фильтр
                        </h4>

                        <form action="/pdn_edit">
                            <div class="row align-items-center mb-3">
                                <div class="col-1 text-end">
                                    <label for="order_id"
                                           class="col-form-label text-white">{$columns['order_id']}</label>
                                </div>
                                <div class="col-3">
                                    <input type="text" id="order_id" name="filters[order_id]" class="form-control">
                                </div>

                                <div class="col-1 text-end">
                                    <label for="date_create_from"
                                           class="col-form-label text-white">{$columns['date_create']}, от</label>
                                </div>
                                <div class="col-3">
                                    <input type="date" id="date_create_from" name="filters[date_create_from]"
                                           class="form-control">
                                </div>

                                <div class="col-1 text-end">
                                    <label for="success" class="col-form-label text-white">{$columns['success']}</label>
                                </div>
                                <div class="col-3">
                                    <select id="success" name="filters[success]" class="form-select">
                                        <option value="">-- Выберите --</option>
                                        <option value="1">Успешно</option>
                                        <option value="0">Неуспешно</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                                <div class="col-1 text-end">
                                    <label for="order_uid"
                                           class="col-form-label text-white">{$columns['order_uid']}</label>
                                </div>
                                <div class="col-3">
                                    <input type="text" id="order_uid" name="filters[order_uid]" class="form-control">
                                </div>

                                <div class="col-1 text-end">
                                    <label for="date_create_to"
                                           class="col-form-label text-white">{$columns['date_create']}, до</label>
                                </div>
                                <div class="col-3">
                                    <input type="date" id="date_create_to" name="filters[date_create_to]"
                                           class="form-control">
                                </div>

                                <div class="col-1 text-end">
                                    <label for="pdn_calculation_type"
                                           class="col-form-label text-white">{$columns['pdn_calculation_type']}</label>
                                </div>
                                <div class="col-3">
                                    <select id="pdn_calculation_type" name="filters[pdn_calculation_type]"
                                            class="form-select">
                                        <option value="">-- Выберите --</option>
                                        {foreach $pdnCalculationTypes as $title => $code}
                                            <option value="{$code}">{$title} ({$code})</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                                <div class="col-1 text-end">
                                    <label for="contract_number"
                                           class="col-form-label text-white">{$columns['contract_number']}</label>
                                </div>
                                <div class="col-3">
                                    <input type="text" id="contract_number" name="filters[contract_number]"
                                           class="form-control">
                                </div>

                                <div class="col-4">

                                </div>

                                <div class="col-1 text-end">
                                    <label for="sortedColumn" class="col-form-label text-white">Сортировать: </label>
                                </div>
                                <div class="col-1">
                                    <select id="sortedColumn" name="filters[sortedColumn]"
                                            class="form-select">
                                        <option value="">-- Выберите --</option>
                                        {foreach $sortedColumns as $sortedColumn}
                                            <option value="{$sortedColumn}">{$columns[$sortedColumn]}</option>
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="col-auto d-flex align-items-center" style="margin-left: 8px;">
                                    <label for="sortedDesc" class="form-label text-white mb-0"
                                           style="margin-right: 8px;">
                                        По убыванию:
                                    </label>
                                    <input type="checkbox" id="sortedDesc" name="filters[sortedDesc]" value="desc">
                                </div>
                                <div class="col-1">
                                </div>
                            </div>
                            <div class="row align-items-center mb-3">
                            </div>
                            <div class="row align-items-center mb-3">
                                <div class="col-3 d-flex">
                                    <b>Найдено: {$totalRows} записи(ей)</b>
                                    <button type="button" class="btn btn-outline-light btn-sm"
                                            style="margin-left: 10px;">
                                        Обновить все
                                    </button>
                                </div>
                                <div class="col-5">

                                </div>
                                <div class="col-2">
                                    <div>
                                        <button class="btn btn-info w-100" value="search"><i class="ti-search"></i>
                                            Поиск
                                        </button>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <button onclick="location.href = '/pdn_edit'"
                                            type="button"
                                            class="btn btn-danger w-100" value="reset">
                                        <i class="ti-close"></i> Сбросить
                                    </button>
                                </div>
                            </div>
                        </form>

                        <hr/>
                        <br/>

                        <h4 class="card-title">
                            Данные
                        </h4>

                        <table class="table table-striped table-hover table-responsive text-nowrap">
                            <tr>
                                {foreach $columns as $column}
                                    <th>{$column}</th>
                                {/foreach}
                            </tr>
                            {foreach $items as $pdnCalculation}
                                <tr
                                        onclick='openSingleModal({$pdnCalculation->order_id}, {json_encode(array_intersect_key((array)$pdnCalculation, $changedColumns))})'
                                        class="clickable-row"
                                >
                                    {foreach $pdnCalculation as $data}
                                        <td>{$data}</td>
                                    {/foreach}
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                </div>
            </div>

            {include file='html_blocks/pagination.tpl'}
        </div>
    </div>
</div>

<div id="updateAllModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h4 class="mb-3 text-danger">ВНИМАНИЕ!!! Вы обновляете {$totalRows} записи(ей)!</h4>

        <div id="popupFields">
            <form id="updateAllForm" method="post" action="{url_for_action action='updateAll'}">
                {foreach $changedColumns as $changedColumnCode => $changedColumn}
                    <div class="row align-items-center mb-3">
                        <div class="col-3">
                            <label class="col-form-label"
                                   for="update_all_{$changedColumnCode}">{$columns[$changedColumnCode]}: </label>
                        </div>
                        <div class="col-9 input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text" style="background-color: #e9ecef">
                                    <input id="update_all_{$changedColumnCode}" data-code="{$changedColumnCode}"
                                           name="update_all[{$changedColumnCode}][active]"
                                           type="checkbox" aria-label="Обновить"
                                           class="bg-white" onchange="switchInput(this)">
                                </div>
                            </div>
                            {if $changedColumn['type'] === 'number'}
                                <input id="column_update_all_{$changedColumnCode}"
                                       type="number"
                                       class="form-control bg-white text-dark"
                                       name="update_all[{$changedColumnCode}][value]"
                                       aria-label="{$columns[$changedColumnCode]}" disabled>
                            {/if}

                            {if $changedColumn['type'] === 'select'}
                                <select id="column_update_all_{$changedColumnCode}"
                                        name="update_all[{$changedColumnCode}][value]"
                                        class="form-select" disabled>
                                    <option value="">-- Выберите --</option>
                                    {foreach $changedColumn['options'] as $option => $optionCode}
                                        <option value="{$optionCode}">{$option}</option>
                                    {/foreach}
                                </select>
                            {/if}
                        </div>
                        <div class="col-1"></div>
                    </div>
                {/foreach}
            </form>
        </div>

        <div class="d-flex justify-content-end mt-4 gap-2">
            <button id="cancelUpdateAll" class="btn btn-danger">Отмена</button>
            <button id="confirmUpdateAll" onclick="document.getElementById('updateAllForm').submit()" class="btn btn-info" style="margin-left: 10px;">Обновить все</button>
        </div>
    </div>
</div>

<div id="updateSingleModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn" onclick="closeSingleModal()">&times;</span>
        <h4 class="mb-3 text-primary">Обновление записи</h4>

        <form id="updateSingleForm" method="post" action="{url_for_action action='updateSingle'}">
            <input type="hidden" name="orderId" id="single_record_id">

            <div id="popupSingleFields">
                {foreach $changedColumns as $changedColumnCode => $changedColumn}
                    <div class="row align-items-center mb-3">
                        <div class="col-3">
                            <label class="col-form-label"
                                   for="single_update_{$changedColumnCode}">{$columns[$changedColumnCode]}: </label>
                        </div>
                        <div class="col-9">
                            {if $changedColumn['type'] === 'number'}
                                <input id="single_update_{$changedColumnCode}" name="update[{$changedColumnCode}]"
                                       type="number"
                                       class="form-control bg-white text-dark"
                                       data-code="{$changedColumnCode}"
                                       step="0.001"
                                       required>
                            {/if}

                            {if $changedColumn['type'] === 'select'}
                                <select id="single_update_{$changedColumnCode}" name="update[{$changedColumnCode}]"
                                        class="form-select"
                                        data-code="{$changedColumnCode}"
                                        required>
                                    <option value="">-- Выберите --</option>
                                    {foreach $changedColumn['options'] as $option => $optionCode}
                                        <option value="{$optionCode}">{$option}</option>
                                    {/foreach}
                                </select>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>

            <div class="d-flex justify-content-end mt-4 gap-2">
                <button type="button" class="btn btn-secondary" onclick="closeSingleModal()">Отмена</button>
                <button type="submit" class="btn btn-success" style="margin-left: 10px;">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<div id="updateFromCsvModal" class="modal" style="display: none;">
    <form method="post" action="/pdn_edit?action=updateFromCsv" enctype="multipart/form-data">
        <div class="modal-content">
            <span class="close-btn" onclick="closeUpdateFromCsvModal()">&times;</span>
            <h4 class="mb-3 text-primary">Обновление записи из CSV</h4>

            <input type="file" id="csvFileInput" name="updateFromCsvFile" accept=".csv" required />

            <div id="columnMapping" style="margin-top: 20px; display: none;">
                    <h5 class="text-secondary">Выберите поле для идентификации:</h5>
                    <div id="identificationContainer"></div>
                    <br/>
                    <h5 class="text-secondary">Сопоставление столбцов:</h5>
                    <div id="mappingContainer"></div>
                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <button type="button" class="btn btn-secondary" onclick="closeUpdateFromCsvModal()">Отмена</button>
                        <button type="submit" class="btn btn-success" style="margin-left: 10px;">Сохранить</button>
                    </div>
            </div>
        </div>
    </form>
</div>


{capture name='page_scripts'}
{literal}
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            fillInputsFromURL();
            initModal();

        });

        let changedColumns = {/literal} {json_encode($changedColumns)}; {literal}
        let columnsName = {/literal} {json_encode($columns)}; {literal}
        let identificationColumns = {/literal} {json_encode($identificationColumns)}; {literal}

        function initModal() {
            const modal = document.getElementById('updateAllModal');
            const openBtn = document.querySelector('button.btn-outline-light');
            const closeBtn = modal.querySelector('.close-btn');
            const cancelBtn = document.getElementById('cancelUpdateAll');

            openBtn.addEventListener('click', () => {
                modal.style.display = 'block';
            });

            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });

            cancelBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });

            window.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        function switchInput(checkbox) {
            let code = checkbox.dataset.code;
            let input = document.getElementById(`column_update_all_${code}`);

            input.disabled = !checkbox.checked;
        }

        function fillInputsFromURL() {
            let params = new URLSearchParams(window.location.search);
            let appliedDefaultFilters = {/literal} {json_encode($appliedDefaultFilters)}; {literal}

            if (!params.size) {
                for (const [key, value] of Object.entries(appliedDefaultFilters)) {
                    params.append(key, value);
                }
            }

            params.forEach((value, key) => {
                let input = document.querySelector(`[name="${key}"]`);

                if (!input) {
                    input = document.getElementById(key);
                }

                if (input) {
                    if (input.type === 'checkbox') {
                        input.checked = value;
                    } else if (input.type === 'radio') {
                        input.checked = input.value === value;
                    } else {
                        input.value = value;
                    }
                }
            });
        }

        function openSingleModal(orderId, record) {
            let inputs = document.querySelectorAll('[id^="single_update_"]');

            for (const input of inputs) {
                let type = changedColumns[input.dataset.code]?.type;

                if (type === 'select') {

                } else {
                    input.value = 0;
                }
            }

            document.getElementById('single_record_id').value = orderId;

            for (const [key, value] of Object.entries(record)) {
                const input = document.getElementById(`single_update_${key}`);
                if (input) {
                    let type = changedColumns[input.dataset.code]?.type;

                    if (type === 'select') {
                        input.value = changedColumns[input.dataset.code]?.options[value];
                    } else {
                        input.value = value;
                    }

                }
            }

            document.getElementById('updateSingleModal').style.display = 'block';
        }

        function closeSingleModal() {
            document.getElementById('updateSingleModal').style.display = 'none';
        }

        function openUpdateFromCsvModal() {
            document.getElementById('updateFromCsvModal').style.display = 'block';
        }

        function closeUpdateFromCsvModal() {
            document.getElementById('updateFromCsvModal').style.display = 'none';
        }

        document.getElementById('csvFileInput').addEventListener('change', function (event) {
            let file = event.target.files[0];
            if (!file) return;

            let reader = new FileReader();
            reader.onload = function (e) {
                let text = e.target.result;
                let lines = text.split(/\r?\n/);

                if (lines.length === 0) {
                    return
                }

                let headers = lines[0].split(';').map(h => h.trim());
                showColumnMapping(headers);
            };
            reader.readAsText(file);
        });

        function showColumnMapping(headers) {
            const identificationContainer = document.getElementById('identificationContainer');
            identificationContainer.innerHTML = '';

            const wrapperI = document.createElement('div');
            wrapperI.style.marginBottom = '10px';
            wrapperI.classList.add('row', 'align-items-center', 'mb-3')

            const labelSelectDivI = document.createElement('div');
            labelSelectDivI.classList.add('col-5')

            const selectLabel = document.createElement('select');
            selectLabel.setAttribute('name', `importFromCSV[identification][local]`)
            selectLabel.appendChild(defaultOption());

            for (const [keyI, valueI] of Object.entries(identificationColumns)) {
                const option = document.createElement('option');
                option.value = keyI;
                option.textContent = columnsName[keyI];
                selectLabel.appendChild(option);
            }

            const selectDivI = document.createElement('div');
            selectDivI.classList.add('col-7')

            const selectI = document.createElement('select');
            selectI.setAttribute('name', `importFromCSV[identification][external]`)
            selectI.appendChild(defaultOption());

            headers.forEach(col => {
                const option = document.createElement('option');
                option.value = col;
                option.textContent = col;
                selectI.appendChild(option);
            });

            labelSelectDivI.appendChild(selectLabel)
            selectDivI.appendChild(selectI)
            wrapperI.appendChild(labelSelectDivI)
            wrapperI.appendChild(selectDivI)
            identificationContainer.appendChild(wrapperI)

            const mappingContainer = document.getElementById('mappingContainer');
            mappingContainer.innerHTML = '';

            for (const [key, value] of Object.entries(changedColumns)) {
                const wrapper = document.createElement('div');
                wrapper.style.marginBottom = '10px';
                wrapper.classList.add('row', 'align-items-center', 'mb-3')


                const labelDiv = document.createElement('div');
                labelDiv.classList.add('col-5')

                const label = document.createElement('label');
                label.textContent = columnsName[key] + ':';

                const selectDiv = document.createElement('div');
                selectDiv.classList.add('col-7')

                const select = document.createElement('select');
                select.setAttribute('name', `importFromCSV[update][${key}]`)
                select.appendChild(defaultOption());

                headers.forEach(col => {
                    const option = document.createElement('option');
                    option.value = col;
                    option.textContent = col;
                    select.appendChild(option);
                });

                labelDiv.appendChild(label)
                selectDiv.appendChild(select)
                wrapper.appendChild(labelDiv);
                wrapper.appendChild(selectDiv);
                mappingContainer.appendChild(wrapper);
            }

            document.getElementById('columnMapping').style.display = 'block';
        }

        function defaultOption() {
            const defaultOption = document.createElement('option');
            defaultOption.selected = true;
            defaultOption.value = '';
            defaultOption.textContent = '-- Выберите --';

            return defaultOption;
        }
    </script>
{/literal}
{/capture}