<form id="complaint" class="complaint-container">
    <input type="hidden" name="complaint_csrf" value="{$complaint_csrf|escape}" />
    <div style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">
        <label for="complaint_hp">Не заполняйте это поле</label>
        <input id="complaint_hp" name="complaint_hp" type="text" value="" tabindex="-1" autocomplete="off" />
    </div>
    <div class="btns">
        <div class="header">
            <h4>Приемная финансового омбудсмена по правам заемщиков МФО</h4>
            <p>мы рассмотрим Вашу жалобу в кратчайшие сроки!</p>
        </div>
        <div>
            <p>В нашем маркетплейсе финансовых продуктов работает приемная по правам заемщиков:
                омбудсмен лично рассмотрит Ваше обращение и проконтролирует соблюдение МФО Ваших прав. </p>
            <p>Мы обещаем, что
                Ваша жалоба попадет на контроль к руководству компании и будет оперативно решена с соблюдением всех прав
                заемщика, отраженных в федеральных законах, стандартах и предписаниях Банка России и СРО, а также иных
                нормативных документов. </p>
            <p>О результатах рассмотрения Вашего обращения Вы будете уведомлены путем
                направления ответа на Вашу электронную почту, с которой Вы направляли обращение.</p>
        </div>
    </div>

    <div class="content-complaint">
        <div class="input-control">
            <label for="complaint_name">ФИО<span class="required">*</span></label>
            <input name="complaint_name"
                   class="complaint_name"
                   type="text"
                   required
                   placeholder="Иванов Иван Иванович"
                   pattern="^[А-ЯЁа-яё]+\s[А-ЯЁа-яё]+\s[А-ЯЁа-яё]+$"
                   title="Введите фамилию, имя и отчество разделенные пробелами"
                   value="{if $user->lastname && $user->firstname}{$user->lastname} {$user->firstname} {$user->patronymic}{else}{/if}" />
        </div>
        <div class="complaint-content-grid">
            <div class="input-control">
                <label for="complaint_phone">Номер телефона<span class="required">*</span></label>
                <input name="complaint_phone"
                       type="tel"
                       required
                       placeholder="+7 (900) 000-00-00"
                       pattern="{literal}\+\d\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}{/literal}"
                       title="Введите корректный номер телефона"
                       value="{substr($user->phone_mobile, 1)}" />
            </div>
            <div class="input-control">
                <label for="complaint_email">E-mail<span class="required">*</span></label>
                <input name="complaint_email"
                       type="email"
                       required
                       placeholder="example@mail.com"
                       pattern=".+@.+\..+"
                       title="Введите корректную электронную почту"
                       value="{$user->email}" />
            </div>
            <div class="input-control">
                <label for="complaint_birth">Дата рождения<span class="required">*</span></label>
                <input name="complaint_birth"
                       type="date"
                       required
                       placeholder="дд.мм.гггг"
                       title="Введите дату рождения "
                       value="{$user->birth|date_format:'%Y-%m-%d'}"
                       min="1920-01-01"
                       max="{$eighteen_years_birthdate|date_format:'%Y-%m-%d'}"/>
            </div>
            <div class="input-control">
                <label for="complaint_topic">Тема обращения<span class="required">*</span></label>
                <select name="complaint_topic" class="complaint_topic" required>
                    <option value="" disabled selected>Выберите тему обращения</option>
                    {foreach $complaint_topics as $topic}
                        <option value="{$topic.id}" data-yandex-goal-id="{$topic.yandex_goal_id}">{$topic.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="input-control">
            <label for="complaint_text">Текст обращения<span class="required">*</span></label>
            <textarea name="complaint_text"
                      placeholder="Введите текст обращения"
                      class="complaint_text form-control"
                      required
                      maxlength="300"
                      rows="10"></textarea>
            <span id="count_message"></span>
        </div>

        <p class="info-text">Необходимо верно заполнить данные о Вашем ФИО, номере телефона и адресе электронной
            почты, от этого зависит скорость получения ответа и результат рассмотрения Вашего обращения.</p>
        <br>
        <p class="info-text">Вы можете загружать изображения (JPG, JPEG, PNG) и PDF файлы размером до 20 МБ.
            Максимум: 5 файлов.</p>
        <br>
        <input name="complaint_file[]" id="complaint_file_input" multiple type="file" style="display: none"
            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" />

        <ul id="complaint_file_list" style="display: none;"></ul>
    </div>

    <div id="smart-captcha-complaint-container" class="smart-captcha" data-sitekey="{$config->smart_captcha_client_key}"></div>

    <div class="form-check form-check__label">
        <input
            class="form-check-input"
            type="checkbox"
            id="agree"
            name="agree"
            onchange="document.getElementById('btn-send').disabled = !document.getElementById('agree').checked"
        >
        <label class="form-check-label agree-label" for="agree">
            Отправляя обращение я даю
            <a href="/files/docs/soglasie_na_obrabotku_personalnyh_dannyh_complaint.pdf" target="_blank">
                согласие на обработку персональных данных
            </a>
        </label>
    </div>

    <div class="footer">
        <button type="submit" class="btn-send" id="btn-send" disabled>ОТПРАВИТЬ ОБРАЩЕНИЕ</button>
        <button id="add_complaint_file" class="button" type="button"><i class="bi bi-paperclip"></i></button>
    </div>
    <div class="complaint_loader">
        <progress id="uploadProgressBar" value="50" max="100" data-label="50%"></progress>
    </div>
</form>

<form id="modal_complaint_sended" class="mfp-hide modal_complaint_sended_modal white-popup-modal">
    <div class="modal-close-btn" onclick="$.magnificPopup.close();">
        <img alt="Закрыть" src="/design/{$settings->theme}/img/user_credit_doctor/close.png" />
    </div>
    <div class="modal-content" id="complaint_sended_message">
        Обращение отправлено.
    </div>
</form>