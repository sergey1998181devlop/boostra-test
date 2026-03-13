<div class="complain-wgt_wrapper">
    <div class="complain-wgt__inner">
        <div class="complain-wgt__head">
            <span class="complain-wgt__mob-hid-content">Приемная Boostra по правам заемщика</span>
            <div class="complain-wgt__close"></div>
        </div>
        <div class="complain-wgt__body">
            <form class="custom-form complain-wgt__form MultiFile-intercepted">
                <div class="complain-wgt__form-body">
                    <div class="complain-wgt__info-block">
                        <p class="complain-wgt__txt">
                            <span class="complain-wgt__desc-hid-content">В нашей компании работает виртуальная приемная по правам заёмщика. Boostra лично рассмотрит вашу жалобу в течение 3-х рабочих дней и проконтролирует соблюдение Ваших прав.</span>
                            <span class="complain-wgt__mob-hid-content">В нашей компании работает виртуальная приемная по правам заёмщика. Boostra лично рассмотрит вашу жалобу и проконтролирует соблюдение ваших прав. Мы обещаем, что ваш запрос попадет на контроль к руководству компании и будет решен с соблюдением всех прав заемщика, отраженных в федеральных законах, стандартах и предписаниях СРО и ЦБ, а также внутренних нормативах нашей компании.</span>
                        </p>
                        <div class="complain-wgt__steps">
                            <ul class="styled-list-down">
                                <li>Пожалуйста, заполните форму</li>
                                <li>Boostra рассмотрит вашу жалобу совместно с юридическим отделом и отделом поддержки клиентов</li>
                                <li>На вашу электронную почту будет предоставлен ответ <span class="txt-selected">в течение 3-х рабочих дней</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="complain-wgt__fields-block">
                        <div class="coplain-wgt__fields">
                            <div class="custom-form__field-wrap wg_files_wrapper custom-form__field-wrap_pt-sm">
                                <div class="custom-form__field custom-form__field_required custom-form__field_not-empty custom-form__field_not-valid">
                                    <span class="custom-form__label-float">Имя*</span>
                                    <input type="text" name="user_name" class="custom-form__input">
                                </div>
                            </div>
                            <div class="custom-form__field-wrap">
                                <div class="custom-form__field custom-form__field_required custom-form__field_is-email custom-form__field_not-valid">
                                    <span class="custom-form__label-float">Адрес эл. почты*</span>
                                    <input type="email" name="user_email" class="custom-form__input">
                                </div>
                            </div>
                            <div class="custom-form__field-wrap">
                                <div class="custom-form__field">
                                    <span class="custom-form__label-float">Номер договора займа (при наличии)</span>
                                    <input type="text" name="user_contract" class="custom-form__input">
                                </div>
                            </div>
                            <div class="custom-form__field-wrap">
                                <div class="custom-form__field custom-form__field_required custom-form__field_not-empty custom-form__field_limited custom-form__field_not-valid">
                                    <span class="custom-form__label-float">Сообщение*</span>
                                    <textarea name="user_message" cols="35" rows="4" class="custom-form__input custom-form__textarea" data-minlength="10" data-maxlength="10000"></textarea>
                                </div>
                            </div>
                            <div class="custom-form__field-wrap custom-form__field-wrap_pt-sm">
                                <div class="custom-form__field">
                                    <div class="custom-file-load">
                                        <div class="custom-file-load__files-list"></div>
                                        <div class="MultiFile-wrap" id="file-input">
                                            <input type="file" name="user_files[]" id="file-input" class="MultiFile-applied" maxlength="4" data-maxsize="6024" accept=".doc,.docx,.pdf,.txt,jpeg,.jpg,.png,.zip" multiple="" value="">
                                        </div>
                                        <div class="custom-file-load__load-info">Максимум 4 файла размером до 5 мб</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="complain-wgt__submit-wrap">
                            <div class="custom-form__field">
                                <input type="submit" value="Отправить" class="btn-prime custom-form__submit complain-wgt__submit">
                                <span class="custom-form__loader"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="complain-wgt__form-footer">
                    <div class="complain-wgt__note">
                        Нажимая кнопку, я даю <a href="/preview/soglasie_obrabotka" target="_blanket">согласие</a> на обработку моих персональных данных
                    </div>
                </div>
            </form>
        </div>
    </div>
    <button class="complain-wgt__btn-open complain-modal-btn"><span>Пожаловаться Boostra</span></button>
</div>
<script>
    {literal}
        $(".complain-wgt_wrapper .complain-wgt__btn-open").on('click', function () {
            $(".complain-wgt_wrapper").addClass('open');
        });

        $(".complain-wgt_wrapper .complain-wgt__close").on('click', function () {
            $(".complain-wgt_wrapper").removeClass('open');
        });

        $(".custom-form__input").on('focus', function () {
            $(this).closest('.custom-form__field').addClass('in_focus');
        });

        $(".custom-form__input").on('blur', function () {
            $(this).closest('.custom-form__field').removeClass('in_focus');
        });

        $(".custom-form__input").on('change', function () {
            let text = $(this).val();

            if (text) {
                $(this).closest('.custom-form__field').addClass('in_filled');
            } else {
                $(this).closest('.custom-form__field').removeClass('in_filled');
            }
        });

        $('.complain-wgt_wrapper #file-input').change(function() {
            let files = this.files;
            if (files) {
                $.each(files, function (index, val) {
                    let name = val.name,
                        key = val.lastModified;
                    if (Object.keys(FormFiles).length < 4) {
                        FormFiles[key] = val;
                        $(".custom-file-load__files-list").append('<div class="MultiFile-label" id="user_file_' + key + '"><a class="MultiFile-remove" onclick="removeFile(' + key + ');" href="javascript:void(0)"></a> <span><span class="MultiFile-label" title="Выбран файл: ' + name + '"><span class="MultiFile-title">' + name + '</span></span></span></div>');
                    } else if (Object.keys(FormFiles).length === 4) {
                        alert('Превышен лимит файлов');
                    }
                })
            }
        });

        function removeFile(key) {
            delete FormFiles[key];
            $("#user_file_" + key).remove();
        }

        $(".complain-wgt__form").on('submit', function (e) {
            e.preventDefault();
            let form_array = $(this).serializeArray(),
                form = new FormData();
            for (const field of form_array) {
                form.append(field.name, field.value);
            }

            $.each(FormFiles, function( key, value ) {
                form.append('user_files[]', value);
            });

            $('.custom-form__field').removeClass('in_error');
            $('.custom-form__field .error-text').remove();

            $.ajax({
                url: 'ajax/complain.php',
                type: 'POST',
                data: form,
                async : false,
                contentType: false,
                processData: false,
                dataType: "JSON",
                beforeSend: function () {
                    $(".complain-wgt__form").addClass('loading');
                },
                success: function (resp) {
                    if (resp.errors) {
                        $.each(resp.errors, function( key, value ) {
                            let element = $('.complain-wgt__form [name^="' + key + '"]').closest('.custom-form__field');
                            $(element).addClass('in_error');
                            $(element).prepend('<span class="error-text">' + value + '</span>');
                        });
                    } else if(resp.success) {
                        $(".complain-wgt__form-body").empty().html(resp.message);
                        $(".complain-wgt_wrapper").addClass("wg_form_completed");
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                    alert(error);
                    console.log(error);
                },
            }).done(function () {
                $(".complain-wgt__form").removeClass('loading');
            });
        });

        $(document).ready(function(){
            let $footer_element = $('footer');
            let bottom_padding = window.screen.width < 769 ? 0 : 55;
            $(window).scroll(function() {
                let scroll = $(window).scrollTop() + $(window).height();
                let offset = $footer_element.offset().top;

                if (scroll > offset) {
                    $(".complain-wgt_wrapper").addClass('in_view_footer');
                    document.body.style.setProperty('--complain_bottom_btn', $footer_element.innerHeight() + bottom_padding + 'px');

                } else if($(".complain-wgt_wrapper").hasClass('in_view_footer')) {
                    $(".complain-wgt_wrapper").removeClass('in_view_footer');
                    document.body.style.removeProperty('--complain_bottom_btn');
                }
            });
        });

    {/literal}
</script>