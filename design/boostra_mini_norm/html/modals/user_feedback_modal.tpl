{literal}
    <style>
        #feedbackModal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .user-feedback-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            padding: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .user-feedback-modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 6px;
            position: relative;
            margin: 0 auto;
            align-items: center;
            justify-content: center;
        }

        .user-feedback-modal-content h2 {
            font-size: 1.3em;
        }

        .user-feedback-modal-content h2,
        .user-feedback-modal-content .reason-question {
            text-align: center;
        }

        #feedbackModal .close {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 24px;
            cursor: pointer;
        }

        .rating-label {
            font-size: 18px;
            margin: 10px 0;
            text-align: center;
        }

        .stars {
            text-align: center;
            margin-bottom: 15px;
        }

        .star {
            font-size: 30px;
            cursor: pointer;
            color: #ccc;
            transition: color 0.2s;
            margin: 0 3px;
        }

        .star:hover,
        .star.active {
            color: #f8ce0b;
        }

        #submitRating:hover {
            background-color: #0056b3;
        }

        #submitRating {
            display: block;
            margin: 20px auto 0 auto;
            padding: 10px 10px;
            width: 55%;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 16px;
        }

        #submitRating:hover {
            background-color: #0056b3;
        }

        .reason-question {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .reason-options input[type="radio"],
        .reason-options input[type="checkbox"] {
            display: none;
        }

        .reason-options {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .reason-options.positive label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 85%;
            padding: 8px 0;
            border: 1px solid #28a745;
            border-radius: 20px;
            background-color: #fff;
            color: #28a745;
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
            text-align: center;
            font-size: 16px;
        }

        .reason-options.positive label:hover {
            background-color: #28a745;
            color: #fff;
        }

        .reason-options.positive label:has(input:checked) {
            background-color: #28a745;
            color: #fff;
        }

        .reason-options.negative label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 85%;
            padding: 8px 0;
            border: 1px solid #dc3545;
            border-radius: 20px;
            background-color: #fff;
            color: #dc3545;
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
            text-align: center;
            font-size: 16px;
        }

        .reason-options.negative label:hover {
            background-color: #dc3545;
            color: #fff;
        }

        .reason-options.negative label:has(input:checked) {
            background-color: #dc3545;
            color: #fff;
        }

        .reason-options.custom {
            flex-direction: column;
            align-items: center;
            margin-top: 10px;
        }

        .reason-options.custom label {
            font-size: 16px;
        }

        .custom-reason-option {
            background-color: #f0f0f0;
            color: #6c757d;
            border: 1px solid #6c757d;
            padding: 8px 0;
            border-radius: 20px;
            width: 85%;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .custom-reason-option:hover {
            background-color: #e2e6ea;
        }

        .custom-reason-option.checked {
            background-color: #e2e6ea;
            color: #6c757d;
        }

        .custom-reason-input {
            display: none;
            margin-top: 14px;
            width: 85%;
            font-size: 16px;
        }

        .feedback-step {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        #feedbackError {
            display: none;
            color: red;
            margin-top: 24px;
            font-size: 18px;
            text-align: center;
        }

        @media (max-width: 576px) {
            #feedbackModal .modal {
                width: 80%;
                padding: 15px;
            }

            .rating-label {
                font-size: 16px;
            }

            .star {
                font-size: 24px;
            }

            .user-feedback-modal {
                width: 80%;
            }

            .user-feedback-modal-content h2 {
                font-size: 2.0em;
            }

            .reason-question {
                font-size: 14px;
            }

            #submitRating {
                font-size: 14px;
            }

            .reason-options.positive label,
            .reason-options.negative label,
            .reason-options.custom label {
                font-size: 14px;
                width: 90%;
            }

            .custom-reason-input {
                margin-top: 10px;
                font-size: 14px;
            }

            #feedbackError {
                font-size: 14px;
            }
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.6);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 5px;
            vertical-align: middle;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .feedback-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .contact-input {
            width: 85%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
    </style>
{/literal}

<div id="feedbackModal">
    <div class="user-feedback-modal fade" id="transcribeModal" tabindex="-1" role="dialog"
         aria-labelledby="transcribeModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg user-feedback-modal-content" role="document">
            <span id="closeModal" class="close">&times;</span>

            <h2>Оцените наш сервис</h2>

            <div id="ratingLabel" class="rating-label">Отлично</div>

            <div class="stars" id="starRating">
                <span data-value="1" class="star">&#9733;</span>
                <span data-value="2" class="star">&#9733;</span>
                <span data-value="3" class="star">&#9733;</span>
                <span data-value="4" class="star">&#9733;</span>
                <span data-value="5" class="star">&#9733;</span>
            </div>

            <div class="reason-question">Что вам понравилось?</div>

            <!-- Положительные варианты (отображаются при оценке 4-5) -->
            <div class="reason-options positive">
                <label>
                    <input type="radio" name="reason_option" value="Понятный интерфейс">
                    Понятный интерфейс
                </label>
                <label>
                    <input type="radio" name="reason_option" value="Удобные способы погашения">
                    Удобные способы погашения
                </label>
                <label>
                    <input type="radio" name="reason_option" value="Качественная поддержка">
                    Качественная поддержка
                </label>
                <label>
                    <input type="radio" name="reason_option" value="Посоветую другу">
                    Посоветую другу
                </label>
            </div>

            <!-- Негативные варианты (отображаются при оценке 1-3) -->
            <div class="reason-options negative" style="display: none;">
                <!-- Шаг 1: Спрашиваем нужны ли обратная связь -->
                <div class="feedback-step step-need-feedback">
                    <div class="reason-question support-question">Требуется ли Вам обратная связь по Вашему вопросу?</div>
                    <label>
                        <input type="radio" name="need_feedback" value="yes" class="need-feedback-option">
                        Да
                    </label>
                    <label>
                        <input type="radio" name="need_feedback" value="no" class="need-feedback-option">
                        Нет
                    </label>
                </div>

                <!-- Шаг 2.1: Если да, то спрашиваем как связаться -->
                <div class="feedback-step step-contact-method" style="display: none;">
                    <div class="reason-question support-question">Как с вами связаться?</div>
                    <label>
                        <input type="radio" name="contact_method" value="phone" class="contact-method-option">
                        Телефон
                    </label>
                    <label>
                        <input type="radio" name="contact_method" value="email" class="contact-method-option">
                        Email
                    </label>
                </div>

                <!-- Шаг 2.1.1: Если обратная связь по телефону, то спрашиваем номер телефона и просим выбрать причину недовольства из списка 4+1 -->
                <div class="feedback-step step-phone-input" style="display: none;">
                    <div class="reason-question">Введите номер телефона</div>
                    <input type="tel" id="phoneInput" class="contact-input" placeholder="+7 (___) ___-__-__">
                </div>

                <!-- Step 2.1.2: Если обратная связь по email, спрашиваем email и просим описать причину недовольства в текстовом поле -->
                <div class="feedback-step step-email-input" style="display: none;">
                    <div class="reason-question">Введите email и комментарий</div>
                    <input type="email" id="emailInput" class="contact-input" placeholder="example@mail.ru">
                    <textarea id="emailCommentInput" class="contact-input"
                              placeholder="Опишите подробнее что не понравилось" rows="4"></textarea>
                </div>

                <!-- Шаг 2.2: Если обратная связь не нужна или после ввода телефона -->
                <div class="feedback-step step-negative-reasons" style="display: none;">
                    <div class="reason-question">Что вам не понравилось?</div>
                    <label>
                        <input type="radio" name="reason_option" value="Частые напоминания о платеже">
                        Частые напоминания о платеже
                    </label>
                    <label>
                        <input type="radio" name="reason_option" value="Работа службы взыскания">
                        Работа службы взыскания
                    </label>
                    <label>
                        <input type="radio" name="reason_option" value="Работа сайта">
                        Работа сайта
                    </label>
                    <label>
                        <input type="radio" name="reason_option" value="Работа службы поддержки">
                        Работа службы поддержки
                    </label>
                </div>
            </div>

            <!-- Отдельный блок для собственного варианта -->
            <div class="reason-options custom">
                <label class="custom-reason-option">
                    <input type="checkbox" id="customReasonCheckbox" name="custom_reason_option" value="custom">
                    Нет подходящего варианта
                </label>
                <input type="text" id="customReasonInput" class="custom-reason-input"
                       placeholder="Введите свой вариант">
            </div>

            <button id="submitRating" data-user_id="{$user_id}" data-order_id="{$order_id}">Отправить</button>

            <!-- Блок для вывода сообщения об ошибке -->
            <div id="feedbackError"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('feedbackModal');
        const closeModalBtn = document.getElementById('closeModal');
        const stars = document.querySelectorAll('#starRating .star');
        const ratingLabel = document.getElementById('ratingLabel');
        const submit = $('#submitRating');
        const feedbackError = document.getElementById('feedbackError');
        const sessionStorage = window.sessionStorage;

        const reasonOptions = document.querySelectorAll('input[name="reason_option"]');
        const reasonQuestion = document.querySelector('.reason-question');

        const positiveContainer = document.querySelector('.reason-options.positive');
        const negativeContainer = document.querySelector('.reason-options.negative');

        const customCheckbox = document.getElementById('customReasonCheckbox');
        const customInput = document.getElementById('customReasonInput');
        const customLabel = document.querySelector('.custom-reason-option');
        const customReasonContainer = document.querySelector('.reason-options.custom');

        const initialRating = 5;
        let currentRating = initialRating;
        let currentStep = 'need-feedback';
        const needFeedbackStep = document.querySelector('.step-need-feedback');
        const contactMethodStep = document.querySelector('.step-contact-method');
        const phoneInputStep = document.querySelector('.step-phone-input');
        const emailInputStep = document.querySelector('.step-email-input');
        const negativeReasonsStep = document.querySelector('.step-negative-reasons');
        const needFeedbackOptions = document.querySelectorAll('.need-feedback-option');
        const contactMethodOptions = document.querySelectorAll('.contact-method-option');
        const phoneInput = document.getElementById('phoneInput');
        const emailInput = document.getElementById('emailInput');
        const emailCommentInput = document.getElementById('emailCommentInput');

        function updateStars(rating) {
            stars.forEach(star => {
                const value = parseInt(star.getAttribute('data-value'), 10);
                star.classList.toggle('active', value <= rating);
            });
        }

        function updateRatingLabel(rating) {
            switch (rating) {
                case 1:
                    ratingLabel.textContent = 'Очень плохо';
                    break;
                case 2:
                    ratingLabel.textContent = 'Плохо';
                    break;
                case 3:
                    ratingLabel.textContent = 'Средне';
                    break;
                case 4:
                    ratingLabel.textContent = 'Хорошо';
                    break;
                case 5:
                    ratingLabel.textContent = 'Отлично';
                    break;
                default:
                    ratingLabel.textContent = 'Оцените нас';
            }
        }

        function updateReasonOptionsDisplay(rating) {
            if (rating <= 3) {
                // Reset to the first step of negative flow
                resetNegativeFlow();

                // Show negative container with first step
                negativeContainer.style.display = 'flex';
                positiveContainer.style.display = 'none';

                // For negative ratings, initially hide custom reasons until later steps
                customReasonContainer.style.display = 'none';

                reasonQuestion.style.display = 'none'; // Hide the main reason question

                submit.hide(); // Hide submit button initially for negative flow
            } else {
                negativeContainer.style.display = 'none';
                positiveContainer.style.display = 'flex';
                reasonQuestion.style.display = 'block';
                reasonQuestion.textContent = "Что вам понравилось?";

                // For positive ratings (4-5), immediately show custom reasons
                customReasonContainer.style.display = 'flex';

                submit.show(); // Show submit button for positive flow
            }

            // Reset all other form elements
            reasonOptions.forEach(option => option.checked = false);
            customCheckbox.checked = false;
            customInput.style.display = 'none';
            customInput.value = '';
            customLabel.classList.remove('checked');

            feedbackError.innerText = "";
            feedbackError.style.display = 'none';
        }

        function resetNegativeFlow() {
            currentStep = 'need-feedback';

            // Hide all steps except the first one
            needFeedbackStep.style.display = 'flex';
            contactMethodStep.style.display = 'none';
            phoneInputStep.style.display = 'none';
            emailInputStep.style.display = 'none';
            negativeReasonsStep.style.display = 'none';
            customReasonContainer.style.display = 'none'; // Hide custom container initially

            // Reset all inputs
            needFeedbackOptions.forEach(option => option.checked = false);
            contactMethodOptions.forEach(option => option.checked = false);
            phoneInput.value = '';
            emailInput.value = '';
            emailCommentInput.value = '';
        }

        function resetFeedbackModal() {
            currentRating = initialRating;
            updateStars(currentRating);
            updateRatingLabel(currentRating);
            updateReasonOptionsDisplay(currentRating);
        }

        resetFeedbackModal();

        stars.forEach((star, index) => {
            star.addEventListener('mouseover', () => {
                stars.forEach((s, i) => {
                    if (i <= index) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });

            star.addEventListener('mouseout', () => {
                stars.forEach(s => s.classList.remove('active'));
                updateStars(currentRating);
            });

            star.addEventListener('click', () => {
                currentRating = index + 1;
                updateStars(currentRating);
                updateRatingLabel(currentRating);
                updateReasonOptionsDisplay(currentRating);
            });
        });

        needFeedbackOptions.forEach(option => {
            option.addEventListener('change', function() {
                if (this.value === 'yes') {
                    // Show contact method options
                    needFeedbackStep.style.display = 'none';
                    contactMethodStep.style.display = 'flex';
                    negativeReasonsStep.style.display = 'none';
                    customReasonContainer.style.display = 'none'; // Hide custom container
                    currentStep = 'contact-method';
                    submit.hide(); // Keep submit button hidden
                } else {
                    // Show negative reasons directly
                    needFeedbackStep.style.display = 'none';
                    contactMethodStep.style.display = 'none';
                    negativeReasonsStep.style.display = 'flex';
                    customReasonContainer.style.display = 'flex'; // Show custom container
                    currentStep = 'negative-reasons';
                    submit.show(); // Show submit button when we're at final step
                }
            });
        });

        contactMethodOptions.forEach(option => {
            option.addEventListener('change', function() {
                contactMethodStep.style.display = 'none';

                if (this.value === 'phone') {
                    // Show phone input and will show reasons after
                    phoneInputStep.style.display = 'flex';
                    emailInputStep.style.display = 'none';
                    negativeReasonsStep.style.display = 'flex';
                    customReasonContainer.style.display = 'flex'; // Show custom container
                    currentStep = 'phone-and-reasons';
                    submit.show(); // Keep submit button hidden until phone is chosen
                } else {
                    // Show email input and don't show reasons after
                    phoneInputStep.style.display = 'none';
                    emailInputStep.style.display = 'flex';
                    customReasonContainer.style.display = 'none'; // Hide custom container
                    currentStep = 'email-input';
                    submit.show(); // Show submit button for email input
                }
            });
        });

        reasonOptions.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.checked) {
                    customCheckbox.checked = false;
                    customInput.style.display = 'none';
                    customInput.value = '';
                    customLabel.classList.remove('checked');

                    feedbackError.innerText = '';
                    feedbackError.style.display = 'none';
                }
            });
        });

        customCheckbox.addEventListener('change', function () {
            if (this.checked) {
                reasonOptions.forEach(radio => radio.checked = false);

                customInput.style.display = 'block';
                customLabel.classList.add('checked');

                feedbackError.innerText = '';
                feedbackError.style.display = 'none';
            } else {
                customInput.style.display = 'none';
                customInput.value = '';
                customLabel.classList.remove('checked');
            }
        });

        closeModalBtn.addEventListener('click', () => {
            if (!modal) {
                return;
            }

            modal.style.display = 'none';
            sessionStorage.setItem('feedbackClosed', 'true');
        });

        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
                sessionStorage.setItem('feedbackClosed', 'true');
            }
        });

        function showFeedbackModal() {
            if (!modal) {
                return;
            }
            modal.style.display = 'block';
        }

        function hideFeedbackModal() {
            if (!modal) {
                return;
            }
            modal.style.display = 'none';
        }

        function checkFeedback() {
            $.ajax({
                url: 'ajax/user_feedback.php?action=check',
                method: 'POST',
                dataType: 'json',
                data: {
                    user_id: submit.data('user_id'),
                    order_id: submit.data('order_id')
                },
                success: function (response) {
                    if (response.success && response.show_modal && !sessionStorage.getItem('feedbackClosed')) {
                        showFeedbackModal();
                    }
                },
                error: function () {
                    console.error('Ошибка проверки отзыва');
                }
            });
        }

        checkFeedback();

        submit.click(function (e) {
            e.preventDefault();

            // Validate custom input first to have >10 symbols
            if (customCheckbox.checked) {
                if (!customInput.value.trim() || customInput.value.trim().length < 10) {
                    feedbackError.innerText = 'Пожалуйста, введите свой вариант (более 10 символов)';
                    feedbackError.style.display = 'block';
                    return;
                }
            }

            let reason = '';
            let contactMethod = '';
            let contactPhone = '';
            let contactEmail = '';
            let emailComment = '';
            let needsFeedback = '';

            // Check the current rating
            if (currentRating <= 3) {
                // Negative flow - check what step we're on
                const selectedNeedFeedback = document.querySelector('input[name="need_feedback"]:checked');

                if (!selectedNeedFeedback) {
                    feedbackError.innerText = "Пожалуйста, выберите нужна ли вам обратная связь.";
                    feedbackError.style.display = 'block';
                    return;
                }

                needsFeedback = selectedNeedFeedback.value;

                if (needsFeedback === 'yes') {
                    const selectedContactMethod = document.querySelector('input[name="contact_method"]:checked');

                    if (!selectedContactMethod) {
                        feedbackError.innerText = "Пожалуйста, выберите способ связи";
                        feedbackError.style.display = 'block';
                        return;
                    }

                    contactMethod = selectedContactMethod.value;

                    if (contactMethod === 'phone') {
                        if (!phoneInput.value.trim()) {
                            feedbackError.innerText = "Пожалуйста, введите номер телефона";
                            feedbackError.style.display = 'block';
                            return;
                        }
                        contactPhone = phoneInput.value.trim();

                        // Also validate that they selected a reason
                        const selectedReason = document.querySelector('input[name="reason_option"]:checked');
                        if (!selectedReason && !customCheckbox.checked) {
                            feedbackError.innerText = "Пожалуйста, выберите причину";
                            feedbackError.style.display = 'block';
                            return;
                        }

                        // Get the reason - either from the selected radio or from custom input
                        if (customCheckbox.checked) {
                            reason = customInput.value.trim();
                        } else {
                            reason = selectedReason.value;
                        }
                    } else if (contactMethod === 'email') {
                        if (!emailInput.value.trim()) {
                            feedbackError.innerText = "Пожалуйста, введите email";
                            feedbackError.style.display = 'block';
                            return;
                        }
                        contactEmail = emailInput.value.trim();

                        if (!emailCommentInput.value.trim() || emailCommentInput.value.trim().length < 50) {
                            feedbackError.innerText = "Пожалуйста, добавьте комментарий (более 50 символов)";
                            feedbackError.style.display = 'block';
                            return;
                        }
                        emailComment = emailCommentInput.value.trim();

                    }
                } else {
                    // If they don't need feedback, check if they selected a reason
                    const selectedReason = document.querySelector('input[name="reason_option"]:checked');

                    if (!selectedReason && !customCheckbox.checked) {
                        feedbackError.innerText = "Пожалуйста, выберите причину";
                        feedbackError.style.display = 'block';
                        return;
                    }

                    reason = selectedReason ? selectedReason.value : '';
                }
            } else {
                // Positive flow - original logic
                if (customCheckbox.checked) {
                    if (customInput.value.trim() === '') {
                        feedbackError.innerText = 'Пожалуйста, введите свой вариант';
                        feedbackError.style.display = 'block';
                        return;
                    }

                    reason = customInput.value.trim();
                } else {
                    const selectedRadio = document.querySelector('input[name="reason_option"]:checked');

                    if (!selectedRadio) {
                        feedbackError.innerText = "Пожалуйста, выберите вариант";
                        feedbackError.style.display = 'block';
                        return;
                    }

                    reason = selectedRadio.value;
                }
            }

            feedbackError.innerText = '';
            feedbackError.style.display = 'none';

            const $btn = $(this);
            $btn.prop('disabled', true);
            const originalHTML = $btn.html();
            $btn.html('<span class="spinner"></span> Отправка...');

            const user_id = $btn.data('user_id');
            const order_id = $btn.data('order_id');

            // Send all the collected data
            $.ajax({
                url: 'ajax/user_feedback.php?action=create',
                method: 'POST',
                dataType: 'json',
                data: {
                    user_id: user_id,
                    order_id: order_id,
                    rate: currentRating,
                    reason: reason,
                    needs_feedback: needsFeedback,
                    contact_method: contactMethod,
                    phone: contactPhone,
                    email: contactEmail,
                    email_comment: emailComment
                },
                success: function (response) {
                    if (response.success) {
                        sessionStorage.setItem('feedbackClosed', 'true');
                        hideFeedbackModal();
                        resetFeedbackModal();
                    } else {
                        feedbackError.innerText = 'Ошибка: ' + response.message;
                        feedbackError.style.display = 'block';
                    }
                },
                error: function () {
                    feedbackError.innerText = 'Ошибка отправки данных';
                    feedbackError.style.display = 'block';
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    $btn.html(originalHTML);
                }
            });
        });
    });
</script>
