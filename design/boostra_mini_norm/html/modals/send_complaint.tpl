<form id="modal_email_feedback" class="mfp-hide white-popup-modal modal-feedback-container">
    <div class="modal-btns">
        <div class="modal-header">
            <h4>Отправка обращения</h4>
        </div>
        <div class="modal-close-btn" onclick="$.magnificPopup.close();">
            <img alt="Закрыть" src="/design/{$settings->theme}/img/user_credit_doctor/close.png" />
        </div>
    </div>

    <div class="modal-content-feedback">
        <div class="input-control">
            <input name="feedback_name"
                   class="feedback_name"
                   type="text"
                   placeholder="ФИО*"
                   pattern="{literal}.+\s.+\s.+{/literal}"
                   title="Введите фамилию, имя и отчество разделенные пробелами"
                   value="{$user->lastname} {$user->firstname} {$user->patronymic}"
            />
        </div>
        <div class="feedback-content-grid">
            <div class="input-control">
                <input name="feedback_phone"
                       type="tel"
                       placeholder="Номер телефона*"
                       pattern="{literal}\+\d\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}{/literal}"
                       title="Введите корректный номер телефона"
                       value="{substr($user->phone_mobile, 1)}"
                />
            </div>
            <div class="input-control">
                <input name="feedback_email"
                       type="email"
                       placeholder="E-mail*"
                       pattern="{literal}.+@.+\..+{/literal}"
                       title="Введите корректную электронную почту"
                       value="{substr($user->email, 1)}"
                />
            </div>
        </div>
        <div class="input-control">
            <select name="feedback_topic" class="feedback_topic">
                <option value="" disabled selected>Тема обращения</option>
                {foreach $complaint_topics as $topic}
                    <option value="{$topic->id}" data-yandex-goal-id="{$topic->yandex_goal_id}">{$topic->name}</option>
                {/foreach}
            </select>
        </div>
        <div class="input-control">
            <textarea name="feedback_text" placeholder="Текст обращения" class="feedback_text form-control" maxlength="300"></textarea>
            <span id="count_message"></span>
        </div>

        <p style="font-size: 0.7rem">Необходимо верно заполнить данные о Вашем ФИО, номере телефона и адресе электронной почты, от этого зависит скорость получения ответа и результат рассмотрения Вашего обращения.</p>
        <br>
        <p style="font-size: 0.7rem">Вы можете загружать изображения (JPG, JPEG, PNG) и PDF файлы размером до 5 МБ.</p>
        <br>
        <input name="feedback_file[]" id="feedback_file_input" multiple type="file" style="display: none" />

        <ul id="feedback_file_list" style="display: none;"></ul>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn-send">Сформировать обращение</button>
        <button id="add_feedback_file" class="button"><i class="bi bi-paperclip"></i></button>
    </div>
</form>

<form id="modal_email_feedback_sended" class="mfp-hide modal_email_feedback_sended_modal">
    <div class="modal-close-btn" onclick="$.magnificPopup.close();">
        <img alt="Закрыть" src="/design/{$settings->theme}/img/user_credit_doctor/close.png" />
    </div>
    <div class="modal-header"><h4>&nbsp;</h4></div>
    <div class="modal-content" id="feedback_sended_message">
        Обращение отправлено.
    </div>
</form>
