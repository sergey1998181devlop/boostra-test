class UsedeskValidator {
    constructor() {
        // Configuration for widget customizations (extensible)
        // Read from global config (set in index.tpl): operatorAvatar, customIconEnabled
        const customIconEnabled = window.usedeskConfig?.customIconEnabled !== false;
        const operatorAvatar = customIconEnabled
            ? (window.usedeskConfig?.operatorAvatar || 'https://secure.usedesk.ru/images/icons/chat-svg/operator.svg')
            : '';
        this.customizations = {
            customIconEnabled,
            operatorAvatar
            // Future: botAvatar, headerIcon, etc.
        };
        this.observer = null;
        this.init();
    }

    init() {
        window.__widgetInitCallback = () => {
            // Apply custom branding
            this.applyCustomizations();

            // делегированная валидация полей
            document.body.addEventListener('input', this.handleInputEvent.bind(this), true);
            document.body.addEventListener('submit', this.handleSubmitEvent.bind(this), true);

            // перехват кликов по фоновой области
            document.addEventListener('click', this.handleDocumentClick.bind(this), true);
        };

        // Также пробуем применить кастомизацию сразу и через интервал
        setTimeout(() => {
            this.applyCustomizations();
        }, 2000);

        setTimeout(() => {
            this.applyCustomizations();
        }, 5000);
    }

    applyCustomizations() {
        if (!this.customizations.customIconEnabled) {
            return;
        }
        this.replaceAvatars();
        this.observeWidgetChanges();
    }

    replaceAvatars() {
        if (!this.customizations.operatorAvatar) {
            return;
        }

        // Ищем все контейнеры аватаров (это DIV элементы с классами .uw__avatar)
        const avatarContainers = document.querySelectorAll('.uw__avatar, .uw__message-avatar, .uw__operator-avatar');

        if (avatarContainers.length === 0) {
            return;
        }

        avatarContainers.forEach(container => {
            const newBg = `url("${this.customizations.operatorAvatar}")`;

            // Устанавливаем background-image через setProperty с !important
            container.style.setProperty('background-image', newBg, 'important');
            container.style.setProperty('background-size', 'cover', 'important');
            container.style.setProperty('background-position', 'center', 'important');
            container.style.setProperty('background-repeat', 'no-repeat', 'important');

            // Удаляем атрибут href если он есть
            if (container.hasAttribute('href')) {
                container.removeAttribute('href');
            }
        });
    }

    observeWidgetChanges() {
        if (this.observer) return;

        this.observer = new MutationObserver(() => this.replaceAvatars());
        const container = document.querySelector('#usedesk-messenger') || document.body;
        this.observer.observe(container, { childList: true, subtree: true });
    }

    handleInputEvent(event) {
        const input = event.target;

        // валидация email при вводе
        if (input.matches('input[name="email"]') && input.id !== 'email') {
            const rawValue = input.value.trim();
            if (!rawValue) {
                input.style.border = '';
                this.hideError(input);
                return;
            }
            const isEmailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(rawValue);
            input.style.border = isEmailValid ? '' : '1px solid #f00';
            isEmailValid
                ? this.hideError(input)
                : this.showError(input, 'Введите корректный e-mail');
        }

        // маска и валидация телефона при вводе
        if (input.matches('input[name="custom_field_0"]')) {
            const rawValue = input.value;
            const digitsOnly = rawValue.replace(/\D/g, '').slice(0, 11);
            if (digitsOnly !== rawValue) {
                input.value = digitsOnly;
            }
            if (!digitsOnly) {
                input.style.border = '';
                this.hideError(input);
                return;
            }
            const { isValid, message } = this.validatePhoneNumber(digitsOnly);
            input.style.border = isValid ? '' : '1px solid #f00';
            isValid
                ? this.hideError(input)
                : this.showError(input, message);
        }
    }

    handleSubmitEvent(event) {
        const form = event.target;
        if (!form.matches('.uw__callback-form')) return;

        let formIsValid = true;

        // проверка email при submit
        const emailInput = form.querySelector('input[name="email"]');
        if (emailInput) {
            const emailValue = emailInput.value.trim();
            if (emailValue) {
                const isEmailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue);
                if (!isEmailValid) {
                    this.showError(emailInput, 'Введите корректный e-mail');
                    emailInput.style.border = '1px solid #f00';
                    formIsValid = false;
                } else {
                    this.hideError(emailInput);
                    emailInput.style.border = '';
                }
            }
        }

        // проверка телефона при submit
        const phoneInput = form.querySelector('input[name="custom_field_0"]');
        if (phoneInput) {
            const phoneValue = phoneInput.value;
            if (phoneValue) {
                const { isValid, message } = this.validatePhoneNumber(phoneValue);
                if (!isValid) {
                    this.showError(phoneInput, message);
                    phoneInput.style.border = '1px solid #f00';
                    formIsValid = false;
                } else {
                    this.hideError(phoneInput);
                    phoneInput.style.border = '';
                }
            }
        }

        if (!formIsValid) {
            event.preventDefault();
        }
    }

    handleDocumentClick(event) {
        // разрешаем клик по крестику и кнопке открытия, блокируем клик по фону
        const chatFrame = document.querySelector('.uw__frame');
        if (!chatFrame) return;  // чат закрыт

        const clickedInsideFrame = !!event.target.closest('.uw__frame');
        const clickedCloseButton = !!event.target.closest('#uw-close-chat-button, #uw-main-button-close');
        const clickedOpenButton  = !!event.target.closest('#uw-main-button');

        if (!clickedInsideFrame && !clickedCloseButton && !clickedOpenButton) {
            event.stopImmediatePropagation();
            event.preventDefault();
        }
    }

    showError(inputElement, message) {
        let errorBox = inputElement.parentElement.querySelector('.uw__field-validation-box');
        if (!errorBox) {
            errorBox = document.createElement('div');
            errorBox.className = 'uw__field-validation-box';
            errorBox.style.color = '#f00';
            errorBox.style.marginTop = '4px';
            inputElement.parentElement.appendChild(errorBox);
        }
        errorBox.textContent = message;
        errorBox.style.display = 'block';
    }

    hideError(inputElement) {
        const errorBox = inputElement.parentElement.querySelector('.uw__field-validation-box');
        if (errorBox) {
            errorBox.style.display = 'none';
        }
    }

    validatePhoneNumber(phone) {
        if (!/^[78]/.test(phone[0])) {
            return { isValid: false, message: 'Номер должен начинаться с 7 или 8' };
        }

        if (phone.length < 11) {
            return { isValid: false, message: 'Введите полный номер телефона (11 цифр)' };
        }
        return { isValid: true, message: '' };
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new UsedeskValidator();
});


// Также создаем экземпляр сразу, если DOM уже загружен
if (document.readyState !== 'loading') {
    new UsedeskValidator();
}
