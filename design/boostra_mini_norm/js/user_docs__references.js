$('#link-references').on('click', function( event ){
    $('#references_wrapper').toggleClass('--hide');
});

// Функции для работы с модальным окном о проданном договоре
function showSoldLoanModal(loanNumber, buyer, buyerPhone) {
    const modal = document.getElementById('soldLoanModal');
    const messageEl = document.getElementById('soldLoanMessage');
    const contactEl = document.getElementById('soldLoanContact');

    messageEl.textContent = 'Договор займа № ' + loanNumber + ' был продан ' + buyer + '.';

    if (buyerPhone) {
        contactEl.innerHTML = '<strong>Для получения справки обратитесь по телефону:</strong>' +
            '<a href="tel:' + buyerPhone + '">' + buyerPhone + '</a>';
    } else {
        contactEl.innerHTML = '<strong>Для получения справки обратитесь к ' + buyer + '</strong>';
    }

    modal.style.display = 'block';
}

function closeSoldLoanModal() {
    document.getElementById('soldLoanModal').style.display = 'none';
}

// Закрытие модального окна при клике вне его
window.onclick = function(event) {
    const modal = document.getElementById('soldLoanModal');
    if (event.target === modal) {
        closeSoldLoanModal();
    }
}

// Закрытие по кнопке X
document.querySelector('.sold-loan-modal-close').onclick = closeSoldLoanModal;

// Закрытие по Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeSoldLoanModal();
    }
});

function convertBase64toBlob(content, contentType) {
    contentType = contentType || '';
    var sliceSize = 512;
    var byteCharacters = window.atob(content);
    var byteArrays = [];

    for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
        var slice = byteCharacters.slice(offset, offset + sliceSize);
        var byteNumbers = new Array(slice.length);

        for (var i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i);
        }

        var byteArray = new Uint8Array(byteNumbers);
        byteArrays.push(byteArray);
    }

    var blob = new Blob(byteArrays, {
        type: contentType
    });
    return blob;
}

$('.download-reference').on('click', function (e) {
    e.preventDefault();
    let loanID = $(this).attr('data-loan-id');
    let referenceType = $(this).attr('data-reference-type');

    $.ajax({
        url: "/ajax/get_references.php?loanID="+loanID+"&referenceType="+referenceType,
        dataType: 'json',
        method : 'GET',
        success: function (resp) {
            if (resp.success) {
                blob = convertBase64toBlob(resp.return, 'application/pdf');
                var blobURL = URL.createObjectURL(blob);
                window.open(blobURL);
                return;
            }

            // Проверка на проданный договор - показываем модальное окно
            if (resp.is_sold) {
                showSoldLoanModal(resp.loan_number, resp.buyer, resp.buyer_phone);
                return;
            }

            $(e.target).replaceWith("<div class='alert alert-danger alert-reference'>В данный момент справка не может быть сформирована, просим обратиться с запросом справки по адресу электронной почты info@boostra.ru, в письме обязательно нужно указать ФИО, дату рождения, номер договора и описание необходимой справки</div>");

        },
        error: function(jqXHR, textStatus, errorThrown) {
            $(e.target).replaceWith("<div class='alert alert-danger alert-reference' >В данный момент справка не может быть сформирована, просим обратиться с запросом справки по адресу электронной почты info@boostra.ru, в письме обязательно нужно указать ФИО, дату рождения, номер договора и описание необходимой справки</div>");
        },
    });
});
