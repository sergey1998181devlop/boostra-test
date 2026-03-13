function showChangePhoneModal() {
    const formChangePhone = document.querySelector('#change_phone_form');
    const formPassport = document.querySelector('#personal_data');
    const formPassportFieldSet = formPassport.querySelector('#steps fieldset');
    const button = document.querySelector('#change_phone #login-button');

    function changePhone(event) {
        event.preventDefault();

        const formDataChangePhone = new FormData(formChangePhone);
        let phone = '';
        for (let pair1 of formDataChangePhone.entries()) {
            phone += pair1[1];
        }

        if (phone.length !== 12) {
            return;
        }

        const phoneInput = document.createElement('input');
        phoneInput.type = 'hidden';
        phoneInput.name = 'phone';
        phoneInput.value = phone;
        formPassportFieldSet.appendChild(phoneInput);

        formPassport.submit()
    }

    function handleInput(e) {
        if (e.target.value) {
            if (e.target.nextElementSibling && !e.target.nextElementSibling.hasAttribute('readonly')) {
                e.target.nextElementSibling.focus();
            }
        }
    }

    function handleFocus(e) {
        if (e.target.value) {
            e.target.select();
        }
    }

    function handleKeyDown(e) {
        if (e.key === 'Backspace' && e.target.previousElementSibling && !e.target.hasAttribute('readonly')) {

            if (!e.target.value && !e.target.previousElementSibling.hasAttribute('readonly')) {
                e.target.previousElementSibling.focus();
            }

            e.target.value = '';
        } else if (e.key === 'ArrowLeft' && e.target.previousElementSibling && !e.target.hasAttribute('readonly') && !e.target.previousElementSibling.hasAttribute('readonly')) {
            e.target.previousElementSibling.focus();
        } else if (e.key === 'ArrowRight' && e.target.nextElementSibling && !e.target.hasAttribute('readonly') && !e.target.nextElementSibling.hasAttribute('readonly')) {
            e.target.nextElementSibling.focus();
        }
    }

    setTimeout(() => {
        $.magnificPopup.open({
              items: {
                  src: '#change_phone',
                  type: 'inline'
              },
              closeBtnInside: true,
              closeOnBgClick: true,
          }
        )
    }, 1000)

    button.addEventListener('click', changePhone);

    formChangePhone.addEventListener('input', handleInput);
    formChangePhone.addEventListener('focusin', handleFocus);
    formChangePhone.addEventListener('keydown', handleKeyDown);
}

showChangePhoneModal();