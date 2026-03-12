function initialize() {
    const startButton = document.querySelector("#startButton");
    const modalContainer = document.querySelector("#modalContainer");
    const closeButton = document.querySelector("#closeButton");
    const nextButtons = document.querySelectorAll(".next-button");
    const questionBlocks = document.querySelectorAll(".question-block");
    const choicesRadios = document.querySelectorAll('input[type="radio"]');
    const modalTitle = document.querySelector("#modalTitle");

    let currentQuestionIndex = 0;

    const isAnyRadioSelected = () => {
        const currentBlockRadios = questionBlocks[currentQuestionIndex].querySelectorAll('input[type="radio"]');
        return Array.from(currentBlockRadios).some(radio => radio.checked);
    };

    const updateNextButton = () => {
        nextButtons.forEach(button => {
            button.disabled = !isAnyRadioSelected();
        });
    };

    choicesRadios.forEach(radio => {
        radio.addEventListener("change", updateNextButton);
    });

    const showQuestion = (index) => {
        questionBlocks.forEach((block, idx) => {
            block.style.display = idx === index ? "block" : "none";
        });
    };

    const hideTitleOnLastTwoQuestions = () => {
        modalTitle.style.display = (currentQuestionIndex >= questionBlocks.length - 2) ? "none" : "block";
    };

    const resetForm = () => {
        choicesRadios.forEach(radio => {
            radio.checked = false;
        });
        currentQuestionIndex = 0;
        showQuestion(currentQuestionIndex);
        updateNextButton();
        hideTitleOnLastTwoQuestions();
    };

    const nextQuestion = () => {
        if (!isAnyRadioSelected() && currentQuestionIndex < questionBlocks.length - 2) {
            return;
        }
        currentQuestionIndex++;
        if (currentQuestionIndex < questionBlocks.length) {
            showQuestion(currentQuestionIndex);
            hideTitleOnLastTwoQuestions();
        } else {
            resetForm();
        }
    };

    nextButtons.forEach(button => {
        button.addEventListener("click", nextQuestion);
    });

    if (startButton !== null) {
        startButton.addEventListener("click", () => {
            resetForm();
            if (modalContainer !== null) {
                modalContainer.style.display = "block";
            } else {
                console.warn("Element with selector '#modalContainer' not found");
            }
        });
    } else {
        console.warn("Element with selector '#startButton' not found");
    }

    if (closeButton !== null) {
        closeButton.addEventListener("click", () => {
            if (modalContainer !== null) {
                modalContainer.style.display = "none";
                currentQuestionIndex = 0;
            } else {
                console.warn("Element with selector '#modalContainer' not found");
            }
        });
    } else {
        console.warn("Element with selector '#closeButton' not found");
    }

    const checkboxes = document.querySelectorAll('#choose_tv_medical, #choose_insure');
    checkboxes.forEach(checkbox => {
        selectFirstTariff(checkbox);
    });

    function selectFirstTariff(checkbox) {
        let tariffSelector;
        if (checkbox.id === "choose_tv_medical" || checkbox.id === "fake_choose_tv_medical") {
            tariffSelector = 'input[name="tv_medical_id"]';
        } else if (checkbox.id === "choose_insure" || checkbox.id === "fake_choose_insure") {
            tariffSelector = 'input[name="multipolis_id"]';
        } else {
            return;
        }

        const firstTariff = document.querySelector(tariffSelector + ':not(:disabled)');
        if (firstTariff) {
            firstTariff.checked = true;
        }
    }

    window.prolongationRefreshAmount = function (number) {
        $.ajax({
            url: '/ajax/loan.php',
            method: 'GET',
            data: {
                action: 'prolongation_amount'
            },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    $('#prolongation_amount').text(data.amount);
                    $("#prolongation_confirm_form [name='amount']").val(data.amount);
                    $('.payment_button[data-number="' + number + '"] .payment_button__amount, #amount_text').text(data.amount);
                } else {
                    console.error('Error fetching prolongation amount:', data.error);
                }
            },
            error: function () {
                console.error('Failed to fetch prolongation amount.');
            }
        });
    };

    window.changeProlongationDay = function (number, day) {
        const acceptElement = document.getElementById('prolongation_accept')

        $.ajax({
            url: '/ajax/loan.php',
            method: 'GET',
            data: {
                action: 'prolongation_day',
                prolongation_day: day,
            },
            dataType: 'json',
            beforeSend: function () {
                acceptElement.disabled = true
            },
            success: function (data) {
                if (data.success) {
                    $('#prolongation_amount').text(data.amount)
                    $('#prolongation_confirm_form [name=\'amount\']').val(data.amount)
                    $('.payment_button[data-number="' + number + '"] .payment_button__amount, #amount_text').text(data.amount)
                } else {
                    console.error('Error fetching prolongation amount by day:', data.error)
                }
            },
            error: function () {
                console.error('Failed to fetch prolongation amount by day.')
            },
            complete: function () {
                acceptElement.disabled = false
            }
        })
    }

    // Ищем элемент number - он может быть в основном DOM или внутри #ajax_prolongation__content
    let elementNumber = document.querySelector("#prolongation_confirm_form [name='number']");
    if (!elementNumber) {
        const ajaxContent = document.getElementById('ajax_prolongation__content');
        if (ajaxContent) {
            elementNumber = ajaxContent.querySelector("#prolongation_confirm_form [name='number']");
        }
    }
    
    if (elementNumber) {
        const number = elementNumber.value;
        prolongationRefreshAmount(number);
    } else {
        // Элемент может быть еще не загружен через AJAX, это нормально
        // console.warn("Element '#prolongation_confirm_form [name=\"number\"]' not found");
    }

    class ProlongationApp {
        constructor() {
            this.choose_tv_medical_counter = window.choose_tv_medical_counter;
            this.choose_insure_counter = window.choose_insure_counter;
            this.chooseTvMedicalDescriptionViewed = false;
            this.chooseInsureDescriptionViewed = false;
            this.smsSent = 0;
            this.smsTimer = null;

            this.init();
            this.initSendRepeat();
            this.initOpenDocument();
            this.initProlongationAccept();
            this.initProlongationSmsCancel();
            this.initProlongationSmsConfirm();
            this.initSmsCodeKeyup();
            this.initProlongationCancel();
            this.initSmsModalClose();
        }

        init() {
            this.addEventListener('#btn-modal-telemed', this.openTelemedModal);
            this.addEventListener('#btn-modal-multipolis', this.openMultipolisModal);
            this.addEventListener('#startButton', this.openQuizModal);
            this.addEventListener('#nextButton', this.openTelemedModal);
            this.addEventListener('#btn-prolongation-multipolis', this.openConsultModal);
            this.addEventListener('.js-prolongation-open-modal', this.getDocuments);
            this.addEventListener('.btn-close-prolongation', this.openDocumentModal);
            this.addEventListener('.btn-close-multi', this.openDocumentModal);
            this.addEventListener('.btn-close-telemed', this.openDocumentModal);
            this.addEventListener('.button-telemed-inverse', this.openDocumentModal);
            this.addEventListener('.button-multipolis-inverse', this.openDocumentModal);
            this.addEventListener('.btn-close-modal', this.closeModal)

            const prolongationDay = document.querySelector('#prolongationDay')
            if (prolongationDay) {
                prolongationDay.value = 16
                document.getElementById('prolongationDay').addEventListener('change', this.updateProlongationDay)
            }
            
            document.querySelectorAll('#choose_insure, #fake_choose_insure').forEach(element => {
                element.addEventListener('change', (e) => {
                    this.handleCheckboxChange(
                        e.target,
                        this.openMultipolisModal,
                        this.openMultipolisModalCancel,
                        'chooseInsureDescriptionViewed',
                        '[name="multipolis_id"]',
                        true
                    );
                });
            });

            document.querySelectorAll('#choose_tv_medical, #fake_choose_tv_medical').forEach(element => {
                element.addEventListener('change', (e) => {
                    this.handleCheckboxChange(
                        e.target,
                        this.openTelemedModal,
                        this.openTelemedModalCancel,
                        'chooseTvMedicalDescriptionViewed',
                        '[name="tv_medical_id"]',
                        true
                    );
                });
            });

            document.querySelectorAll('[name="tv_medical_id"]').forEach(element => {
                element.addEventListener('change', (e) => {
                    this.handleIdChange(e, 'tv_medical');
                });
            });

            document.querySelectorAll('[name="multipolis_id"]').forEach(element => {
                element.addEventListener('change', (e) => {
                    this.handleIdChange(e, 'multipolis');
                });
            });
        }

        addEventListener(selector, modalOpenFunction) {
            const elements = document.querySelectorAll(selector);
            if (elements) {
                elements.forEach(element => {
                    element.addEventListener('click', (event) => {
                        modalOpenFunction.call(this, event);
                    });
                });
            }
        }

        handleCheckboxChange(checkboxId, modalOpenFunction, modalCancelFunction, descriptionViewedKey, tariffSelector, userInitiated = false) {
            const counterKey = checkboxId.name + '_counter';
            const previousChecked = checkboxId.getAttribute('data-previous-checked') === 'true';

            checkboxId.setAttribute('data-previous-checked', checkboxId.checked);

            if (userInitiated) {
                const tariffs = document.querySelectorAll(tariffSelector);
                tariffs.forEach(tariff => {
                    tariff.disabled = !checkboxId.checked;
                });
            }

            let relatedRadios;
            if (checkboxId.id === 'choose_insure') {
                relatedRadios = document.querySelectorAll('input[name="multipolis_id"]');
            } else if (checkboxId.id === 'choose_tv_medical') {
                relatedRadios = document.querySelectorAll('input[name="tv_medical_id"]');
            }

            let number = null;
            relatedRadios.forEach(radio => {
                if (radio.checked) {
                    number = radio.getAttribute('data-number');
                }
            });

            if (this[counterKey] === 2) {
                modalOpenFunction.call(this);
                this[descriptionViewedKey] = true;
                const tariffs = document.querySelectorAll(tariffSelector);
                tariffs.forEach(tariff => {
                    tariff.disabled = false;
                });
                this[counterKey]--;
                checkboxId.checked = true;
            } else if (this[counterKey] === 1 && this[descriptionViewedKey]) {
                modalCancelFunction.call(this);
                const tariffs = document.querySelectorAll(tariffSelector);
                tariffs.forEach(tariff => {
                    tariff.disabled = true;
                });
                this[descriptionViewedKey] = false;
                checkboxId.checked = false;
                window.prolongationRefreshAmount(number);
                this[counterKey] = 6;
            } else if (this[counterKey] > 2) {
                this[counterKey]--;
                checkboxId.checked = true;
                window.prolongationRefreshAmount(number);
            }

            if (userInitiated && previousChecked !== checkboxId.checked) {
                if (number !== null && number !== 'undefined') {
                    window.prolongationRefreshAmount(number);
                } else {
                    console.warn('Number is null or undefined:', number);
                }
            }
        }

        handleIdChange(event, type) {
            const element = event.target;
            const amount = element.getAttribute('data-amount');
            const number = element.getAttribute('data-number');
            const id = element.value;

            const idElement = document.querySelector('#prolongation_sms_block [name=' + type + '_id]');
            const amountElement = document.querySelector('#prolongation_sms_block [name=' + type + '_amount]');
            const amountTextElement = document.querySelector('.' + type + '__amount_text');
            const typeElement = document.querySelector('#prolongation_sms_block [name=' + type + ']');

            if (idElement) {
                idElement.value = id;
            } else {
                console.warn(`Element #prolongation_sms_block [name='${type}_id'] not found`);
            }

            if (amountElement) {
                amountElement.value = amount;
            } else {
                console.warn(`Element #prolongation_sms_block [name='${type}_amount'] not found`);
            }

            if (amountTextElement) {
                amountTextElement.textContent = amount;
            } else {
                console.warn(`Element .${type}__amount_text not found`);
            }

            if (typeElement && typeElement.value) {
                window.prolongationRefreshAmount(number);
            } else {
                console.warn(`Element #prolongation_sms_block [name='${type}'] not found or has no value`);
            }
        }

        openTelemedModal() {
            this.openModal({
                items: {src: '#modal-telemed'},
                type: 'inline',
                showCloseBtn: true,
                modal: true,
                callbacks: {
                    close: () => this.openInfoModal()
                }
            });
        }

        openMultipolisModal() {
            this.openModal({
                items: {src: '#modal-multipolis'},
                type: 'inline',
                showCloseBtn: false,
                modal: true,
                callbacks: {
                    close: () => this.openInfoModal()
                }
            });
        }

        openMultipolisModalCancel() {
            this.openModal({
                items: {src: '#modalContainerMultiCancel'},
                type: 'inline',
                showCloseBtn: false,
                modal: true,
                callbacks: {
                    close: () => this.openInfoModal()
                }
            });
        }

        openTelemedModalCancel() {
            this.openModal({
                items: {src: '#modalContainerTelemedCancel'},
                type: 'inline',
                showCloseBtn: false,
                modal: true,
                callbacks: {
                    close: () => this.openInfoModal()
                }
            });
        }


        openQuizModal() {
            this.openModal({
                items: {src: '#modalContainer'},
                type: 'inline',
                showCloseBtn: false,
                modal: true,
            });
        }

        openConsultModal() {
            this.openModal({
                items: {src: '#modalContainerMulti'},
                type: 'inline',
                showCloseBtn: false,
                modal: true,
            });
        }

        openModal(config) {
            $.magnificPopup.open(config);
        }

        openSmsModal() {
            this.openModal({
                items: {src: '#prolongation_sms_block'},
                type: 'inline',
                showCloseBtn: false,
                modal: true,
            });
        }

        openInfoModal() {
            this.openModal({
                items: {src: '#prolongation_block'},
                type: 'inline',
                showCloseBtn: false,
                modal: true,
            });
        }

        openDocumentModal() {
            this.openModal({
                items: {src: '#document_wrapper'},
                type: 'inline',
                showCloseBtn: false,
                modal: true,
            });
        }

        closeDocument() {
            $.magnificPopup.close();
        }

        closeModal () {
            this.openDocumentModal()
            this.closeDocument()
        }

        async getDocuments(event) {

            const documentsElement = document.getElementById('prolongation_documents');

            if (documentsElement) {
                documentsElement.innerHTML = '';

                let listItem = document.createElement('li');
                const span = document.createElement('span');
                span.textContent = 'Заявление о пролонгации договора микрозайма';
                span.classList.add('js-open-document');
                span.style.cursor = 'pointer';
                listItem.appendChild(span);
                documentsElement.appendChild(listItem);
            }
            /*
                Задача решалась на противоречивых данных. Ниже закомментирован код загрузки списка документов из 1С.
                По словам 1С-ников, метод нерабочий, ничего полезного не делает. Решил не убирать, а просто закомментировать.
                Выше захардкодил отображение исключительно заявления о пролонгации.
             */
            /*
            const triggerElement = event.currentTarget;
            const number = triggerElement.getAttribute('data-number');
            const url = `ajax/prolongation.php?action=get_documents&number=${number}`;

            try {
                triggerElement.classList.add('loading');
                document.body.classList.add('loading');

                const response = await fetch(url, {
                    method: 'GET'
                });

                const result = await response.json();

                triggerElement.classList.remove('loading');
                document.body.classList.remove('loading');

                if (result.errors && result.errors.length > 0) {
                    alert(result.errors.join(', '));
                    return;
                }

                try {
                    const documentsElement = document.getElementById('prolongation_documents');

                    documentsElement.innerHTML = '';

                    if (result.documents && Array.isArray(result.documents)) {
                        for (let item of result.documents) {
                            let listItem = document.createElement('li');
                            const span = document.createElement('span');
                            span.textContent = item.name;
                            span.classList.add('js-prolongation-open-modal', 'js-open-document');
                            span.style.cursor = 'pointer';
                            span.setAttribute('data-file', item.file);
                            listItem.appendChild(span);
                            documentsElement.appendChild(listItem);

                            let documentFrame = document.getElementById('document_frame');
                            if (documentFrame) {
                                documentFrame.src = item.file;
                            } else {
                                console.warn("Element with ID 'document_frame' is not found.");
                            }
                        }
                    }
                } catch (e) {}

                this.openInfoModal();

            } catch (error) {
                console.warn("An error occurred:", error);
            }
            */
            this.openInfoModal();
        }


        async fetchWithErrorHandling(url, options) {
            const response = await fetch(url, options);
            if (!response.ok) {
                console.warn(`HTTP error! status: ${response.status}, url: ${url}`);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        }

        async sendSms() {
            try {
                const phone = document.querySelector('#user_phone').value;
                const response = await fetch('ajax/sms.php?action=send&phone=' + encodeURIComponent(phone), {
                    method: 'GET',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                });

                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }

                const data = await response.json();

                if (data.error) {
                    if (data.error === 'sms_time') {
                        this.setTimer(data.time_left);
                    } else {
                        console.log(data);
                    }
                } else {
                    this.setTimer(data.time_left);
                    this.smsSent = 1;

                    if (data.developer_code) {
                        const smsCodeInput = document.querySelector('#sms_code');
                        smsCodeInput.value = data.developer_code;
                        smsCodeInput.dispatchEvent(new Event('change'));
                    }
                }

            } catch (error) {
                console.warn("An error occurred:", error);
            }
        }

        async checkSms() {
            try {
                const phoneElement = document.querySelector('#user_phone');
                const smsCodeElement = document.querySelector('#sms_code');

                if (!phoneElement || !smsCodeElement) {
                    console.warn("Required elements not found");
                    return;
                }

                const phone = phoneElement.value;
                const code = smsCodeElement.value;

                const url = `ajax/sms.php?action=check&phone=${encodeURIComponent(phone)}&code=${encodeURIComponent(code)}`;

                const response = await fetch(url);
                const data = await response.json();
                if (data.success) {
                    if (localStorage.prolongation_link == 'true') {
                        $("#prolongation_confirm_form [name='prolongation_link']").val('true')
                    }
                    this.approve();
                } else {
                    document.querySelector('#prolongation_sms').classList.add('error');
                    document.querySelector('#prolongation_sms .error-info').textContent = data.error || 'Код не совпадает';
                }
            } catch (error) {
                console.warn("An error occurred:", error);
            }
        }

        initProlongationAccept() {
            const acceptElement = document.getElementById('prolongation_accept');
            if (acceptElement) {
                acceptElement.addEventListener('click', () => {
                    const statusInputElement = document.getElementById('status_input');
                    const acceptInfoElement = document.getElementById('accept_info');
                    const cancelInfoElement = document.getElementById('cancel_info');

                    if (statusInputElement) {
                        statusInputElement.value = 1;
                    } else {
                        console.warn('status_input not found!');
                    }

                    if (acceptInfoElement) {
                        acceptInfoElement.style.display = 'block';
                    } else {
                        console.warn('accept_info not found!');
                    }

                    if (cancelInfoElement) {
                        cancelInfoElement.style.display = 'none';
                    } else {
                        console.warn('cancel_info not found!');
                    }

                    if (!this.smsSent) {
                        this.sendSms();
                    }

                    this.openSmsModal();
                });
            } else {
                console.warn('prolongation_accept not found!');
            }
        }

        initProlongationCancel() {
            document.addEventListener('click', (e) => {
                if (e.target.id === 'prolongation_cancel') {
                    const statusInput = document.getElementById('status_input');
                    const acceptInfo = document.getElementById('accept_info');
                    const cancelInfo = document.getElementById('cancel_info');

                    if (statusInput) {
                        statusInput.value = 0;
                    } else {
                        console.warn('status_input not found!');
                    }

                    if (acceptInfo) {
                        acceptInfo.style.display = 'none';
                    } else {
                        console.warn('accept_info not found!');
                    }

                    if (cancelInfo) {
                        cancelInfo.style.display = 'block';
                    } else {
                        console.warn('cancel_info not found!');
                    }

                    this.closeDocument();
                }
            });
        }

        initProlongationSmsCancel() {
            const element = document.getElementById('prolongation_sms_cancel');
            if (element) {
                element.addEventListener('click', () => {
                    this.openInfoModal();
                });
            } else {
                console.warn('Element with ID prolongation_sms_cancel not found');
            }
        }


        initProlongationSmsConfirm() {
            const confirmElement = document.getElementById('prolongation_sms_confirm');
            if (confirmElement) {
                confirmElement.addEventListener('click', () => {
                    const smsCodeInput = document.getElementById('sms_code');
                    if (smsCodeInput && smsCodeInput.value == '') {
                        const prolongationSms = document.getElementById('prolongation_sms');
                        const errorInfoElement = document.querySelector('#prolongation_sms .error-info');
                        if (prolongationSms && errorInfoElement) {
                            prolongationSms.classList.add('error');
                            errorInfoElement.innerHTML = 'Введите код из СМС';
                        } else {
                            console.warn('Error elements not found!');
                        }
                    } else {
                        const prolongationSms = document.getElementById('prolongation_sms');
                        if (prolongationSms) {
                            prolongationSms.classList.remove('error');
                            this.checkSms();
                        } else {
                            console.warn('prolongation_sms not found!');
                        }
                    }
                });
            } else {
                console.warn('prolongation_sms_confirm not found!');
            }
        }

        initSmsModalClose() {
            const closeBtn = document.querySelector('#sms_close_btn');
            if (!closeBtn) return; closeBtn.addEventListener('click', () => {
                $.magnificPopup.close();
                document.querySelector('#prolongation_sms_block').classList.add('hidden');
                document.querySelector('#prolongation_block').classList.remove('hidden');
            });
        }

        initSmsCodeKeyup() {
            const smsCode = document.getElementById('sms_code');
            if (smsCode) {
                smsCode.addEventListener('keyup', () => {
                    if (smsCode.value.length === 4) {
                        this.checkSms();
                    }
                });
            } else {
                console.warn("Element with id 'sms_code' not found");
            }
        }


        async setTimer(seconds) {
            clearInterval(this.smsTimer);
            while (seconds > 0) {
                document.getElementById('repeat_sms').innerHTML = '<span>Повторно отправить код можно через ' + seconds + ' сек</span>';
                await new Promise(resolve => setTimeout(resolve, 1000));
                seconds--;
            }
            document.getElementById('repeat_sms').innerHTML = '<a class="js-send-repeat" href="#">Отправить код еще раз</a>';
        }


        initSendRepeat() {
            document.addEventListener('click', (e) => {
                if (e.target && e.target.classList.contains('js-send-repeat')) {
                    e.preventDefault();
                    this.sendSms();
                }
            });
        }

        initOpenDocument() {
            document.addEventListener('click', (e) => {
                const element = e.target;
                const classList = element.classList;

                if (classList.contains('js-open-document') ||
                    classList.contains('js-close-document') ||
                    classList.contains('js-accept-document')) {
                    e.preventDefault();
                    if (classList.contains('js-open-document')) {
                        this.openDocumentModal();
                    } else if (classList.contains('js-close-document')) {
                        this.closeDocument();
                    } else if (classList.contains('js-accept-document')) {
                        this.acceptDocument();
                    }
                }
            });
        }

        acceptDocument() {
            const statusInputElement = document.querySelector('#status_input');
            const acceptInfoElement = document.querySelector('#accept_info');
            const cancelInfoElement = document.querySelector('#cancel_info');

            if (statusInputElement !== null) {
                statusInputElement.value = 1;
            } else {
                console.warn("Element with ID 'status_input' not found");
            }

            if (acceptInfoElement !== null) {
                acceptInfoElement.style.display = 'block';
            } else {
                console.warn("Element with ID 'accept_info' not found");
            }

            if (cancelInfoElement !== null) {
                cancelInfoElement.style.display = 'none';
            } else {
                console.warn("Element with ID 'cancel_info' not found");
            }

            if (!this.smsSent) {
                this.sendSms();
            }

            this.openSmsModal();
        }

        approve() {
            document.querySelector('#prolongation_confirm_form').submit();
        }

        updateProlongationDay (e) {
            const element = e.target
            $('#prolongation_confirm_form [name=\'prolongation_day\']').val(element.value)
            const number = element.getAttribute('data-number')

            changeProlongationDay(number, element.value)
        };

    }

    new ProlongationApp();
}
