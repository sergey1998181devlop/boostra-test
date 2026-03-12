var sbpBanksModal = document.querySelector('#sbp_banks_modal');
var overlay = document.querySelector('#sbp_banks_modal_overlay');

function changeSbpBank() {
  openChooseSbpBankModal(true);
}

function openSbpBanksModal() {
  if (!sbpBanksModal) {
    return;
  }

  openModal();
}

function openChooseSbpBankModal(canChangeSelectedBank = false) {
  if (!sbpBanksModal) {
    return;
  }

  const cardIdInput = document.querySelector('input[name="card_id"]');
  const cardTypeInput = document.querySelector('input[name="card_type"]');
  const bankIdInput = document.querySelector('#bank_id');

  if (!cardIdInput || !cardTypeInput) {
    return;
  }

  if (!canChangeSelectedBank && cardTypeInput.value === 'sbp' && (cardIdInput.value && cardIdInput.value !== '0')) {
    return;
  }

  if (!canChangeSelectedBank && bankIdInput && bankIdInput.value && bankIdInput.value !== '0') {
    return;
  }

  openModal();
}

function openModal() {
  overlay.style.display = 'block';
  sbpBanksModal.style.display = 'block';
}

function saveBank(bankId, orderId) {
  if (!sbpBanksModal) {
    return;
  }

  if (!bankId) {
    alert('Некорректный банк');
  }

  if (!orderId) {
    alert('Некорректная заявка');
  }

  $.ajax({
    url: 'ajax/choose_card.php',
    data: {
      action: 'choose_bank',
      bank_id: bankId,
      order_id: orderId,
    },
    success: function (resp) {
      if (resp?.result?.result === 'success') {
        updateInputsValue(bankId);
        showSelectedBankTitle(bankId);
        closeModal();
        hideChooseBankButton();
      } else {
        alert(resp.error || 'Неизвестная ошибка');
      }
    }
  })
}

function saveDefaultBank(bankId) {
  if (!sbpBanksModal) {
    return;
  }

  if (!bankId) {
    alert('Некорректный банк');
  }

  $.ajax({
    url: 'ajax/choose_card.php',
    data: {
      action: 'choose_default_bank',
      bank_id: bankId,
    },
    success: function (resp) {
      if (resp?.result?.result === 'success') {
        updateInputsValue(bankId);
        showSelectedBankTitle(bankId);
        closeModal();
        hideChooseBankButton();

        if (resp?.result?.need_reload) {
          location.reload();
        }
      } else {
        alert(resp.error || 'Неизвестная ошибка');
      }
    }
  })
}

function updateInputsValue(bankId) {
  const cardIdInput = document.querySelector('input[name="card_id"]');
  const cardTypeInput = document.querySelector('input[name="card_type"]');
  const bankIdInput = document.querySelector('#bank_id');

  if (!bankIdInput) {
    return;
  }

  bankIdInput.value = bankId;

  if (cardIdInput) {
    cardIdInput.value = 0;
  }

  if (cardTypeInput) {
    cardTypeInput.value = 'sbp';
  }
}

function closeModal() {
  if (!sbpBanksModal) {
    return;
  }

  overlay.style.display = 'none';
  sbpBanksModal.style.display = 'none';
}

function showSelectedBankTitle(bankId) {
  const banks = document.querySelectorAll('.sbp_bank');

  if (!banks) {
    return;
  }
  const selectedBank = Array.from(banks).find(bank => bank.dataset.bankId == bankId);

  if (!selectedBank) {
    return;
  }

  const span = selectedBank.querySelector('.sbp_bank_title');

  if (!span) {
    return;
  }

  const bankTitle = span.innerText;
  const cardsElement = document.querySelector(".cards");

  if (!cardsElement) {
    return;
  }

  let selectedBankBlock = document.querySelector('.selected_bank_block');

  if (!selectedBankBlock) {
    return;
  }

  let selectedBanElement = document.querySelector('.selected_bank > span');

  if (selectedBanElement) {
    selectedBanElement.innerHTML = `Выбранный банк для выплаты по СБП: ${bankTitle}`;
  } else {
    const selectedBanElement = document.createElement('p');
    selectedBanElement.classList.add('selected_bank');
    selectedBanElement.innerHTML = `<span>Выбранный банк для выплаты по СБП: ${bankTitle}</span>`;
    cardsElement.insertBefore(selectedBanElement, cardsElement.firstChild);
  }
}

function hideChooseBankButton() {
  const button = document.querySelector('.choose_bank')

  if (button) {
    button.style.display = 'none';
  }
}

$(document).ready(function () {
  function addCloseModalHandler() {
    if (!sbpBanksModal) {
      return;
    }

    const closeChooseBankButton = sbpBanksModal.querySelector('.close_choose_bank_button');

    if (!closeChooseBankButton) {
      return;
    }

    closeChooseBankButton.addEventListener('click', closeModal);
  }
  addCloseModalHandler();

  function addAttachSbpHandler() {
    const attachButtons = document.querySelectorAll('.attach_sbp_btn');

    function isIosSafari() {
      let ua = window.navigator.userAgent;

      // Условие вернёт true, если:
      return (
        /iP(ad|hone|od)/.test(ua)    // устройство: iPad, iPhone или iPod
        && /Safari/.test(ua)         // браузер содержит "Safari"
        && !/CriOS/.test(ua)         // исключаем Chrome на iOS (он тоже на WebKit)
        && !/FxiOS/.test(ua)         // исключаем Firefox на iOS
      );
    }

    attachButtons.forEach(btn => {
      btn.addEventListener('click', function (event) {
        event.preventDefault();

        // Проверяем чекбоксы, если они есть на странице
        const needVerifySbp = document.querySelector('.js-need-verify-sbp');
        const needVerifySbpList = document.querySelector('.js-need-verify-sbp-list');

        if (needVerifySbp || needVerifySbpList) {
          const uncheckedSbp = needVerifySbp && !needVerifySbp.checked;
          const uncheckedSbpList = needVerifySbpList && !needVerifySbpList.checked;

          if (uncheckedSbp || uncheckedSbpList) {
            const notCheckedInfo = document.getElementById('not_checked_info_sbp') || document.getElementById('not_checked_info_sbp_list');
            if (notCheckedInfo) {
              notCheckedInfo.style.display = 'block';
            }
            return;
          }

          // Скрываем формы и сообщение об ошибке после проверки
          const docsWrapperSbp = document.querySelector('.docs_wrapper_sbp');
          const docsWrapperSbpList = document.querySelector('.docs_wrapper_sbp_list');
          const notCheckedInfoSbp = document.getElementById('not_checked_info_sbp');
          const notCheckedInfoSbpList = document.getElementById('not_checked_info_sbp_list');

          if (docsWrapperSbp) docsWrapperSbp.style.display = 'none';
          if (docsWrapperSbpList) docsWrapperSbpList.style.display = 'none';
          if (notCheckedInfoSbp) notCheckedInfoSbp.style.display = 'none';
          if (notCheckedInfoSbpList) notCheckedInfoSbpList.style.display = 'none';
        }

        // Получаем значение согласия на рекуррентные платежи
        const recurrentSbp = document.getElementById('recurrent_sbp');
        const recurrentSbpList = document.getElementById('recurrent_sbp_list');
        let recurringConsent = 1; // по умолчанию разрешено

        if (recurrentSbp) {
          recurringConsent = recurrentSbp.checked ? 1 : 0;
        } else if (recurrentSbpList) {
          recurringConsent = recurrentSbpList.checked ? 1 : 0;
        }

        let newWin = null;

        if (isIosSafari()) {
          // в Safari iOS нужно открыть сразу
          newWin = window.open('', '_blank');
        }

        $.ajax({
          url: 'ajax/b2p_payment.php',
          data: {
            action: 'attach_sbp',
            recurring_consent: recurringConsent
          },
          success: function (resp) {
            if (resp.link) {
              if (newWin) {
                // Safari iOS — перекидываем в заранее открытое окно
                newWin.location.href = resp.link;
              } else {
                // другие браузеры — откроют прямо сейчас
                window.open(resp.link, '_blank');
              }
            } else {
              if (newWin) newWin.close();
              alert('Возникла ошибка, повторите попытку')
            }
          },
          error: function () {
            if (newWin) newWin.close();
          }
        });
      });
    });
  }
  addAttachSbpHandler();
})
