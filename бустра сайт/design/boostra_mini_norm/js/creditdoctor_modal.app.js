class CreditDoctorModalApp {
    constructor() {
        this.clickCounterDoc = 6;
        this.popupAnswers = {};
        this.lastPopup = 6;
        this.shouldOpenMainModal = true;
        this.checkUtmSourceAndDisableModal();
        this.init();
    }

    resetClickCounter() {
        this.clickCounterDoc = 6;
    }

    init() {
        const creditDoctorButton = document.getElementById('btn-modal-creditdoctor');
        if (creditDoctorButton) {
            creditDoctorButton.addEventListener('click', (e) => {
                e.stopPropagation();
                this.openCreditDoctorModal();
                const creditDoctorCheckbox = document.getElementById('creditdoctor_0');
                if (creditDoctorCheckbox) {
                    creditDoctorCheckbox.checked = true;
                }
            });
        }

        const acceptLink = document.getElementById('accept_link');

        if (acceptLink) {
            acceptLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.openMainModal();
            });
        } else {
//            const openDocumentsLink = document.getElementById('open_accept_documents');
//
//            if (openDocumentsLink) {
//                openDocumentsLink.addEventListener('click', (e) => {
//                    e.preventDefault();
//                    this.openMainModal();
//                });
//            }
        }

        const closeButtons = document.querySelectorAll('.btn-close-doctor, .btn-prolongation-creditdoctor');
        closeButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                $.magnificPopup.close();
            });
        });

        const creditDoctorCheck = document.getElementById('credit_doctor_check');

        if (creditDoctorCheck) {
            creditDoctorCheck.addEventListener('change', (e) => {
                e.stopPropagation();

                if (this.clickCounterDoc > 0) {
                    creditDoctorCheck.checked = true;
                    this.clickCounterDoc--;
                }

                if (this.clickCounterDoc === 1) {
                    this.openQuizModal();
                }

                if (this.clickCounterDoc === 0 && creditDoctorCheck.checked) {
                    this.openCreditDoctorModalCancel();
                    creditDoctorCheck.checked = false;
                    this.resetClickCounter();
                }

                if (this.clickCounterDoc < 1) {
                    creditDoctorCheck.checked = false;
                }

                console.log(this.clickCounterDoc);

                const isUserCreditDoctor = document.querySelector('[name=is_user_credit_doctor]');
                if (isUserCreditDoctor) {
                    isUserCreditDoctor.value = creditDoctorCheck.checked ? 1 : 0;
                }
            });
        }

        const popupGoButtons = document.querySelectorAll('.popup__go');
        popupGoButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                let currentPopup = e.target.closest('.popup');
                this.nextPopup(currentPopup);
            });
        });

        const popupForms = document.querySelectorAll('form.popup');
        popupForms.forEach(form => {
            const nextButton = form.querySelector('.popup__next');
            nextButton.addEventListener('click', (e) => {
                e.preventDefault();
                let popup = e.target.closest('.popup');
                let checkedInput = popup.querySelector('input:checked');

                if (checkedInput) {
                    this.popupAnswers[popup.dataset.popupId] = checkedInput.value;
                }

                this.nextPopup(popup);
            });
        });

        const paymentButton = document.querySelector('.popup-step[data-popup-id="6"] .popup__next');

        if (paymentButton) {
            paymentButton.addEventListener('click', () => {
                this.openMainModal();
                this.resetPopupState();
            });
        }
    }

    checkUtmSourceAndDisableModal() {
        const creditDoctorElement = document.getElementById('creditDoctorData');
        if (!creditDoctorElement) {
            return;
        }
        const utmSource = creditDoctorElement.dataset.utmSource;

        if (utmSource === 'Boostra' || utmSource === '') {
            this.shouldOpenMainModal = false;
        }
    }

    nextPopup(currentPopup) {
        currentPopup.classList.add('d-none');
        let popupId = parseInt(currentPopup.dataset.popupId) + 1;
        let newPopup = document.querySelector(`.popup[data-popup-id="${popupId}"]`);
        if (newPopup) {
            newPopup.classList.remove('d-none');

            if (popupId === 5) {
                setTimeout(() => {
                    this.nextPopup(newPopup);
                }, 3000);
            }
        } else {

        }

    }

    resetPopupState() {
        this.popupAnswers = {};
        document.querySelectorAll('.popup').forEach(popup => {
            popup.classList.add('d-none');
        });
        document.querySelector('.popup[data-popup-id="0"]').classList.remove('d-none');
    }

    openCreditDoctorModal() {
        $.magnificPopup.open({
            items: {
                src: '#modal-creditdoctor'
            },
            type: 'inline',
            showCloseBtn: false,
            modal: true,
        });
    }

    openMainModal() {
        if (this.shouldOpenMainModal) {
            $.magnificPopup.open({
                items: {
                    src: '#accept_order'
                },
                type: 'inline',
                showCloseBtn: false,
                modal: true,
            });
        } else {
            $.magnificPopup.close();
        }
    }

    openQuizModal() {
        $.magnificPopup.open({
            items: {
                src: '.popup__wrapper',
            },
            type: 'inline',
            modal: true,
            callbacks: {
                open: () => {
                    document.querySelector('.popup[data-popup-id="0"]').classList.remove('d-none');
                },
                close: () => {
                    this.resetPopupState();
                }
            }
        });
    }

    openCreditDoctorModalCancel() {
        $.magnificPopup.open({
            items: {
                src: '#modalContainerCreditCancel'
            },
            type: 'inline',
            showCloseBtn: false,
            modal: true,
            callbacks: {
                close: () => {
                    this.openMainModal();
                }
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new CreditDoctorModalApp();
});
