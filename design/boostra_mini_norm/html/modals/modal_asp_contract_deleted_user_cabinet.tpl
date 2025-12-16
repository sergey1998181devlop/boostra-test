<div id="confirm_remove_account" class="white-popup white-popup-modal mfp-hide">
    <a href="javascript:void(0)" class="modal-close-btn" onclick="$.magnificPopup.close();">
        <img alt="Закрыть" src="/design/{$settings->theme}/img/user_credit_doctor/close.png"/>
    </a>
    <div class="modal-content">
        <ul>
            <li>
                <a href="{$asp_contract_delete_user_link}" target="_blank">Заявление на удаление личного кабинета</a>
            </li>
        </ul>
    </div>
    <div id="modal_asp__sms_block">
        <button id="confirm_remove_account__init">Подписать</button>
        <div id="modal_asp__sms_code_wrapper" style="display: none">
            <div class="input-control">
                <input name='asp_remove_account_code' value=''/>
                <div class="timerOutWrapper"></div>
            </div>
        </div>
    </div>
</div>

{capture_array key="footer_page_scripts"}
{literal}
    <script type="text/javascript">
        const ASPContractDeleteApp = {
            asp_type: '{/literal}{$asp_type_remove_account}{literal}',
            repeatSms: false,
            showTimer: function (time) {
                $(".timerOutWrapper").timerOut({
                    onStart: function () {
                        $(this).show()
                    },
                    onComplete: function () {
                        $(this).hide()
                        if (ASPContractDeleteApp.repeatSms) {
                            $("#confirm_remove_account__init").show().text('Отправить повторно');
                        }
                    },
                    second: time
                });
            },
            sendSms: function () {
                $.ajax({
                    url: 'ajax/sms.php',
                    data: {
                        action: 'send',
                        phone: ASPContractDeleteApp.settings.phone,
                        flag: 'АСП'
                    },
                    beforeSend: function () {
                        $("#confirm_remove_account__init").hide();
                        $("#modal_asp__sms_code_wrapper").show();
                    },
                    success: function(resp){
                        if (!!resp.error)
                        {
                            if (resp.error === 'sms_time') {
                                ASPContractDeleteApp.showTimer(resp['time_left']);
                            } else {
                                console.log(resp);
                            }
                        }
                        else
                        {
                            ASPContractDeleteApp.showTimer(resp['time_left']);
                            ASPContractDeleteApp.repeatSms = true;
                            if (!!resp.developer_code) {
                                $('input[name="asp_remove_account_code"]').val(resp.developer_code);
                            }
                        }
                    }
                });
            },
            checkASPCallback: function (resp) {
                if (resp.success) {
                    $("#modal_asp__sms_block, #confirm_remove_account .modal-close-btn").remove();
                    $("#confirm_remove_account .modal-content")
                        .css('border', 'none')
                        .html("<img src='design/{/literal}{$settings->theme}{literal}/img/svg/checkbox_green.svg' alt='boostra' />")
                        .after("<p>Личный кабинет удален, сейчас будет выполнен автоматический выход.</p>");

                    setTimeout(function () {
                        window.location.href = '/user/logout';
                    }, 3000);
                } else {
                    $("#modal_asp__sms_code_wrapper .input-control").addClass('has-error');
                }
                $("#confirm_remove_account").removeClass('is_loading');
            },
            init: function (settings) {
                this.settings = settings;

                $('input[name="asp_remove_account_code"]').inputmask({
                    mask: "9999",
                    oncomplete: function () {
                        $("#modal_asp__sms_code_wrapper .input-control").removeClass('has-error');
                        $("#confirm_remove_account").addClass('is_loading');
                        checkASPCode(ASPContractDeleteApp.settings.phone, $(this).val(), ASPContractDeleteApp.asp_type, ASPContractDeleteApp.checkASPCallback);
                    }
                });

                this.sendSms();
            },
        };

        $("#confirm_remove_account__init").on('click', function () {
            ASPContractDeleteApp.init({
                phone: '{/literal}{$user->phone_mobile}{literal}',
            })
        });
    </script>
{/literal}
{literal}
    <style type="text/css">
        #confirm_remove_account {
            max-width: 480px;
            padding-top: 40px;
        }
        #confirm_remove_account .modal-close-btn {
            top: 15px;
        }
        #confirm_remove_account .modal-content {
            border: 1px solid gray;
            margin: auto auto 20px;
            padding: 0;
            width: auto;
        }
        #modal_asp__sms_block {
            display: flex;
            flex-flow: column;
            gap: 15px;
            align-items: center;
        }
        #modal_asp__sms_block > * {
            flex: 0 1 auto;
        }
        #confirm_remove_account input {
            width: 50px;
            padding-bottom: 0;
        }
        #confirm_remove_account__init {
            max-width: max-content;
        }
        #confirm_remove_account ul {
            padding-left: 40px;
            padding-right: 40px;
            list-style: none;
        }
        #confirm_remove_account ul li {
            text-decoration: underline;
            margin: 20px 0;
        }
        #confirm_remove_account ul li::before {
            content: '';
            display: inline-block;
            height: 20px;
            width: 20px;
            background: url("design/boostra_mini_norm/img/doc.png") center center / contain no-repeat;
            margin-right: 10px;
            position: relative;
            top: 5px;
            text-decoration: underline;
        }
    </style>
{/literal}
{/capture_array}
