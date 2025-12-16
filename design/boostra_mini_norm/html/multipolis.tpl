<div id="multipolis_banner" class="additional_service__banner" onclick="openModalMultipolis(); sendMetric('reachGoal','banner_multipolis');">
    <img src="design/{$settings->theme|escape}/img/banners/multipolis_banner.png" alt="Мультиполис">
    <div class="additional_service__banner___text">
        <div>
            <h2>Консьерж сервис</h2>
        </div>
        <div class="additional_service__banner___details">
            <ul>
                <li><i class="orange-checkbox"></i> Финансовая консультация</li>
                <li><i class="orange-checkbox"></i> Юридическая помощь</li>
                <li><i class="orange-checkbox"></i> Консультация психолога</li>
            </ul>
        </div>
        <div class="additional_service__banner___get">
            <button type="button" class="btn">Получить</button>
        </div>
    </div>
</div>

<div style="display:none">
    <div id="question_multipolis" class="white-popup-modal mfp-hide">
        <a class="modal-close-btn" href="javascript:void(0);" onclick="$.magnificPopup.close()"><i class="bi bi-x-circle"></i></a>
        <div class="modal-header">
            <h4>Заявка на консультацию</h4>
        </div>
        <form id="multipolis_form" class="modal-content">
            <input type="hidden" name="multipolis_id" value="{$multipolis->id}" />
            <div class="form-control-border">
                <input name="lastname" placeholder="Фамилия" value="{$user->lastname}" />
            </div>
            <div class="form-control-border">
                <input name="firstname" placeholder="Имя" value="{$user->firstname}" />
            </div>
            <div class="form-control-border">
                <input name="patronymic" placeholder="Отчество" value="{$user->patronymic}" />
            </div>
            <div class="form-control-border">
                <input name="phone_mobile" placeholder="Телефон" value="{$user->phone_mobile|escape}" />
            </div>
            <div class="form-control-border">
                <label for="multipolis_number">Номер полиса</label>
                <input id="multipolis_number" name="multipolis_number" placeholder="Б1-9500" value="{$multipolis->number}" />
            </div>
            <div class="form-control-border text-center">
                <button class="button small-text">Отправить</button>
            </div>
            <div class="lh-1 text-center">
                <p>Свяжитесь прямо сейчас</p>
            </div>
            <div class="lh-1 text-center">
                <a href="https://t.me/BoostraCons_bot" target="_blank" style=" margin-right: 10px;">
                    <img src="design/{$settings->theme|escape}/img/multipolis/tg.png" style=" width: 20px; height: 20px; display: inline-block;" />
                </a>
            </div>
        </form>
    </div>
</div>

{literal}
    <script>
        function openModalMultipolis() {
            $.magnificPopup.open({
                items: {
                    src: '#question_multipolis'
                },
                type: 'inline',
                showCloseBtn: false,
                modal: true,
            });
        }

        $(document).ready(function () {
            $('#multipolis_form').validate({
                messages: {
                    required: "Заполните поле.",
                },
                rules: {
                    "lastname": {
                        required: true
                    },
                    "firstname": {
                        required: true
                    },
                    "patronymic": {
                        required: true
                    },
                    "phone_mobile": {
                        required: true
                    },
                    "multipolis_number": {
                        required: true
                    },
                },
            });
        });

        $('#multipolis_form').on('submit', function (e) {
           e.preventDefault();
            if ($(this).valid()) {
                let data = $(this).serialize();
                $.ajax({
                    url: 'ajax/multipolis.php',
                    data: data,
                    type: 'POST',
                    beforeSend: function () {
                        $('#question_multipolis').addClass('is_loading');
                        $('#multipolis_form input').removeClass('error');
                        $('#multipolis_form label.error').remove();
                    },
                    success: function (resp) {
                        if (resp.errors) {
                            $.each(resp.errors, function( key, value ) {
                                let element = $('#multipolis_form [name^="' + key + '"]');
                                $(element).addClass('error');
                                $(element).after('<label id="' + key + '-error" class="error" for="' + key + '">' + value + '</label>');
                            });
                        } else if(resp.success) {
                            sendMetric('reachGoal', 'oplata_multipolis');
                            $('#question_multipolis .modal-header').empty();
                            $('#multipolis_form').html(resp.message);
                            $('#multipolis_banner').remove();
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                        alert(error);
                        console.log(error);
                    },
                }).done(function () {
                    $('#question_multipolis').removeClass('is_loading');
                });
            }
        });
    </script>

    <style>
        #question_multipolis {
            max-width: 320px;
        }

        #question_multipolis .modal-content {
            flex-flow: column;
        }

        #question_multipolis label {
            font-size: 14px;
        }
    </style>
{/literal}
