{$canonical="user/tickets" scope=parent}

{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}

<div class="ticket-create-modal-header">
    <h3 class="ticket-create-modal-title">Новое обращение</h3>
    <button type="button" class="ticket-create-modal-close" onclick="closeCreateModal()">
        &times;
    </button>
</div>
<form method="POST" action="/user/tickets?action=createTicket" enctype="multipart/form-data">
    <div class="ticket-create-modal-body">
        <div class="ticket-create-form-row">
            <div class="ticket-create-form-group ticket-create-form-col">
                <label for="fullname" class="ticket-create-form-label">ФИО*</label>
                <input id="fullname"
                       name="fullname"
                       class="ticket-create-form-input"
                       type="text"
                       placeholder="Иванов Иван Иванович"
                       pattern="^[А-ЯЁа-яё]+\s[А-ЯЁа-яё]+\s[А-ЯЁа-яё]+$"
                       title="Введите фамилию, имя и отчество, разделенные пробелами (только русские буквы)"
                       value="{if $user->lastname && $user->firstname}{$user->lastname} {$user->firstname} {$user->patronymic}{else}{/if}"
                       readonly
                />
            </div>
            <div class="ticket-create-form-group ticket-create-form-col">
                <label for="phone_mobile" class="ticket-create-form-label">Номер телефона*</label>
                <input id="phone_mobile"
                       name="phone_mobile"
                       class="ticket-create-form-input"
                       type="tel"
                       placeholder="+7 (900) 000-00-00"
                       pattern="{literal}\+\d\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}{/literal}"
                       title="Введите корректный номер телефона"
                       value="{substr($user->phone_mobile, 1)}"
                       autocomplete="tel"
                       inputmode="tel"
                       required
                />
            </div>
        </div>
        <div class="ticket-create-form-row">
            <div class="ticket-create-form-group ticket-create-form-col">
                <label for="email" class="ticket-create-form-label">E-mail*</label>
                <input id="email"
                       name="email"
                       autocomplete="email"
                       class="ticket-create-form-input"
                       type="email"
                       placeholder="example@mail.com"
                       pattern=".+@.+\..+"
                       title="Введите корректную электронную почту"
                       value="{$user->email}"
                       required
                />
            </div>
            <div class="ticket-create-form-group ticket-create-form-col">
                <label for="contracts" class="ticket-create-form-label">Номер договора*</label>
                <select id="contracts" name="contracts[]" class="ticket-create-form-select" multiple required>
                    {foreach from=$contracts item=contract}
                        <option value="{$contract->id}">
                            {$contract->number}
                        </option>
                    {/foreach}
                </select>
                <div class="ticket-create-help-text" style="margin-top:5px;color:#888;font-size:13px;">
                    Выберите один или несколько договоров. Не создавайте отдельные обращения для каждого договора — выберите все нужные сразу.
                </div>
            </div>
        </div>
        <div class="ticket-create-form-group">
            <label for="subject" class="ticket-create-form-label">Тема*</label>
            <select id="subject" name="subject" class="ticket-create-form-select" required>
                <option value="Вопросы по задолженности">
                    Вопросы по задолженности
                </option>
                <option value="Технические вопросы">
                    Технические вопросы
                </option>
                <option value="Смена данных в личном кабинете">
                    Смена данных в личном кабинете
                </option>
                <option value="Вопрос по предоставлению документов">
                    Вопрос по предоставлению документов
                </option>
                <option value="Вопрос связанный с пролонгацией договора">
                    Вопрос связанный с пролонгацией договора
                </option>
                <option value="Вопрос связанный с предоставлением кредитных каникул">
                    Вопрос связанный с предоставлением кредитных каникул
                </option>
                <option value="Вопрос по программному обеспечению">
                    Вопрос по программному обеспечению
                </option>
                <option value="Вопрос связанный с оплатами">
                    Вопрос связанный с оплатами
                </option>
                <option value="Отзывы ранее предоставленных согласий">
                    Отзывы ранее предоставленных согласий
                </option>
                <option value="Вопрос по перерасчетам">
                    Вопрос по перерасчетам
                </option>
                <option value="Вопрос связанный с БКИ">
                    Вопрос связанный с БКИ
                </option>
                <option value="Иное">
                    Иное
                </option>
            </select>
        </div>
        <div class="ticket-create-form-group">
            <label for="message" class="ticket-create-form-label">Описание*</label>
            <textarea id="message" name="message" class="ticket-create-form-textarea" rows="4" required></textarea>
        </div>
        <div class="ticket-create-form-group">
            <label for="attachments" class="ticket-create-form-label">Вложения</label>
            <input id="attachments"
                   name="attachments[]"
                   class="ticket-create-form-input"
                   type="file"
                   multiple
                   accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.zip,.rar"
            />
        </div>
    </div>
    <div class="ticket-create-modal-footer">
        <button type="button" class="tickets-btn tickets-btn-secondary" onclick="closeCreateModal()">
            Отменить
        </button>
        <button type="submit" class="tickets-btn tickets-btn-primary">Создать</button>
    </div>
</form>