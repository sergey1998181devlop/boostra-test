<form id="complaint" class="complaint-container">
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
                       placeholder="+7 (900) 000-00-00"
                       pattern="{literal}\+\d\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}{/literal}"
                       title="Введите корректный номер телефона"
                       value="{substr($user->phone_mobile, 1)}" />
            </div>
            <div class="input-control">
                <label for="complaint_email">E-mail<span class="required">*</span></label>
                <input name="complaint_email"
                       type="email"
                       placeholder="example@mail.com"
                       pattern=".+@.+\..+"
                       title="Введите корректную электронную почту"
                       value="{substr($user->email, 1)}" />
            </div>
            <div class="input-control">
                <label for="complaint_email">Дата рождения</label>
                <input name="complaint_birth"
                       type="date"
                       placeholder="дд.мм.гггг"
                       title="Введите дату рождения "
                       value="{$user->birth|date_format:'%Y-%m-%d'}"
                       max="{$eighteen_years_birthdate|date_format:'%Y-%m-%d'}"/>
            </div>
            <div class="input-control">
                <label for="complaint_topic">Тема обращения</label>
                <select name="complaint_topic" class="complaint_topic">
                    <option value="" disabled selected>Выберите тему обращения</option>
                    {foreach $complaint_topics as $topic}
                        <option value="{$topic.id}" data-yandex-goal-id="{$topic.yandex_goal_id}">{$topic.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="input-control">
            <label for="complaint_text">Текст обращения</label>
            <textarea name="complaint_text"
                      placeholder="Введите текст обращения"
                      class="complaint_text form-control"
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

    <div class="footer">
        <button type="submit" class="btn-send">ОТПРАВИТЬ ОБРАЩЕНИЕ</button>
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