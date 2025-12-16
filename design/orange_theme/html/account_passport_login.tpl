{* Страница входа пользователя *}

{* Канонический адрес страницы *}
{$canonical="/account/login" scope=parent}

{$meta_title = "Добро пожаловать в личный кабинет Boostra" scope=parent}

{$login_scripts = true scope=parent}

{$body_class = "gray" scope=parent}

<style>
    .flex-center {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #send-passport button[disabled] {
        cursor: no-drop;
        opacity: .5;
    }
</style>

<section id="login">
	<div>
		<div id="worksheet">
            <div id="steps">
                <h3>Вход в личный кабинет заёмщика</h3>
                <form method="post" id="send-passport">
                    <div class="flex-center">
                        <label>
                            <span>Авторизация производится с помощью<br/> Вашего паспорта, который Вы указывали<br/> при получении займа</span>
                        </label>
                        <label>
                            <img src="design/{$settings->theme|escape}/img/passport.png" alt="паспорт РФ" width="240">
                        </label>
                    </div>
                    <fieldset style="display: block">
                        <div class="flex-center">
                            <label class="w-50">
                                <input required="" id="passport" type="text" name="passport" value="" />
                                <span class="floating-label">Серия номер паспорта</span>
                            </label>
                            <label class="w-50">
                                <input required="" type="text" name="birthday" value="" />
                                <span class="floating-label">Дата рождения</span>
                            </label>
                        </div>
                    </fieldset>
                    <fieldset style="display: block">
                        <button type="submit" class="big button">Войти</button>
                    </fieldset>
                </form>
            </div>
		</div>
	</div>
</section>
<script type="text/javascript">
    $(document).ready(function (){
        $("input[name='passport']").inputmask("9999 999999");
        $("input[name='birthday']").inputmask("99.99.9999");
    });

    $('#send-passport').on('submit', function (e){
        e.preventDefault();
        let formData = $(this).serialize();
        $('.alert').remove();

        $.ajax({
            url: '{$url_login}',
            method: 'POST',
            data: formData,
            beforeSend: function () {
              $('#send-passport button').prop('disabled', true);
            },
            success: function (json) {
                if (json['errors']) {
                    let html = '<div class="alert alert-danger">При отправке формы произошла ошибка! <br/><b>' + json['errors'].join('<br/>') + '</b></div>';
                    $('#send-passport').prepend(html);
                } else {
                    if (json['result']) {
                        window.location = json['result'];
                    }
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                alert(error);
                console.log(error);
            },
        }).done(function () {
            $('#send-passport button').prop('disabled', false);
        });
    });
</script>

