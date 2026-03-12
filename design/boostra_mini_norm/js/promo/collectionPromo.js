document.addEventListener('DOMContentLoaded', function () {

    setTimeout(() => {
        $.magnificPopup.open({
            items: {
                src: '#collectionPromo'
            },
            type: 'inline',
            modal: true,
            showCloseBtn: false,
            closeOnBgClick: false,
            enableEscapeKey: false,
            callbacks: {
                open: function() {
                    setTimeout(function() {
                        $('#collectionPromo .modal_title .close-modal').show();
                    }, 15000)
                },
            }
        });
    }, 500)
    // Находим кнопку по id
    let button = document.getElementById('collection_promo_pay_button');

    // Проверяем, что кнопка существует
    if (button) {
        // Добавляем обработчик события click
        button.addEventListener('click', function () {
            console.log('Кнопка нажата (чистый JS)');
            document.querySelector('.full_payment_button').click();
        });
    }
});