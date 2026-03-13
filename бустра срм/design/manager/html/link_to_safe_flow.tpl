{$meta_title='Ссылки для безопасного флоу' scope=parent}

{capture name='page_scripts'}
    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.js"></script>
{/capture}

{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css"
          rel="stylesheet"/>
    <link href="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet"/>
    <link type="text/css" rel="stylesheet"
          href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css"/>
    <link type="text/css" rel="stylesheet"
          href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css"/>
    <style>
        .jsgrid-table {
            margin-bottom: 0
        }

        #level_select {
            width: 115px;
        }
    </style>
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
                    <i class="mdi mdi-animation"></i> Список ссылок
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Список ссылок</li>
                </ol>
            </div>
        </div>

        <div class="col-6 col-md-4 mb-3">
            <button type="button" class="btn btn-success" id="generate_button"><i class="ti-save"></i> Сгенерировать
                ссылку
            </button>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <div class="clearfix">
                            <h4 class="card-title float-left">Список кодов</h4>
                        </div>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <table class="jsgrid-table table table-striped table-hover">
                                <thead>
                                <tr class="jsgrid-header-row">
                                    <th>ID</th>
                                    <th style="width: 500px;">Ссылка</th>
                                    <th>Дата и время генерации</th>
                                    <th>Переходы на сайт</th>
                                    <th>Заявки</th>
                                    <th>Выдачи</th>
                                    <th>Срок действия ссылки</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $links_stat as $link}
                                    <tr class="jsgrid-row js-promo-row">
                                        <td>{$link->id}</td>
                                        <td style="width: 500px;">{$link->link}</td>
                                        <td>{$link->created_at}</td>
                                        <td>{$link->clicks_count}</td>
                                        <td>{$link->applications_count}</td>
                                        <td>{$link->loans_count}</td>
                                        <td id="countdown-{$link->id}"></td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>

                            {include file="html_blocks/pagination.tpl"}

                            <div class="jsgrid-load-shader"
                                 style="display: none; position: absolute; inset: 0px; z-index: 10;">
                            </div>
                            <div class="jsgrid-load-panel"
                                 style="display: none; position: absolute; top: 50%; left: 50%; z-index: 1000;">
                                Идет загрузка...
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Column -->
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
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
<script>
    let expDates = {$links_stat|json_encode};
</script>
{literal}
    <script>

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('generate_button').addEventListener('click', function () {
                generateLink();
            });

            //timer

            let countdowns = document.querySelectorAll('[id^="countdown-"]');
            countdowns.forEach(countdown => {
                let linkId = countdown.id.replace('countdown-', '');
                updateCountdown(linkId);
                setInterval(function () {
                    updateCountdown(linkId);
                }, 1000);
            });
        });

        function generateLink() {
            fetch('/ajax/links_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=generateLink',
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    updateTable(data);
                    expDates.push({
                        id: data.id,
                        expiration_date: data.expiration_date,
                    });
                    clearInterval(window['interval_' + data.id]);

                    window['interval_' + data.id] = setInterval(function () {
                        updateCountdown(data.id);
                    }, 1000);
                    updateCountdown(data.id);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function updateTable(data) {
            let tableBody = document.querySelector('.jsgrid-table tbody');

            let row = document.createElement('tr');
            row.className = 'jsgrid-row js-promo-row';
            row.id = 'row-' + data.id;

            let idCell = document.createElement('td');
            idCell.textContent = data.id;
            row.appendChild(idCell);

            let linkCell = document.createElement('td');
            linkCell.textContent = data.link;
            row.appendChild(linkCell);

            let createdAtCell = document.createElement('td');
            createdAtCell.textContent = data.created_at;
            row.appendChild(createdAtCell);

            let clicksCountCell = document.createElement('td');
            clicksCountCell.textContent = data.clicks_count;
            row.appendChild(clicksCountCell);

            let applicationsCountCell = document.createElement('td');
            applicationsCountCell.textContent = data.applications_count;
            row.appendChild(applicationsCountCell);

            let loansCountCell = document.createElement('td');
            loansCountCell.textContent = data.loans_count;
            row.appendChild(loansCountCell);

            let countdownCell = document.createElement('td');
            countdownCell.id = 'countdown-' + data.id;
            updateCountdown(data.id);
            row.appendChild(countdownCell);

            tableBody.appendChild(row);

        }

        function updateCountdown(linkId) {
            let linkStat = expDates.find(function (item) {
                return item.id === linkId;
            });

            if (!linkStat) {
                return;
            }

            let expirationDate = new Date(linkStat.expiration_date).getTime();
            let now = new Date().getTime();

            // Рассчитываем оставшееся время действия ссылки
            let remaining = expirationDate - now;

            if (remaining < 0) {
                document.getElementById('countdown-' + linkId).innerHTML = 'Истек срок действия ссылки';
            } else {
                let days = Math.floor(remaining / (1000 * 60 * 60 * 24));
                let hours = Math.floor((remaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((remaining % (1000 * 60)) / 1000);

                let countdownString = '';

                if (days > 0) {
                    countdownString += days + 'д ';
                }

                if (hours > 0 || (days === 0 && hours === 0)) {
                    countdownString += hours + ':';
                }

                if (minutes > 0 || (days === 0 && hours === 0 && minutes === 0)) {
                    countdownString += minutes + ':';
                }

                countdownString += seconds + '';

                document.getElementById('countdown-' + linkId).innerHTML = countdownString;
            }
        }

    </script>
{/literal}