import Configs from './config/config.js';

let config = new Configs();
let checkCallConnected = false;
let tabOpened = false;

function getCurrentSiteId() {
    return localStorage.getItem('selectedSiteId') || 'all';
}

/**
 * Открытие карточки клиента с правильным site_id
 * Централизованная функция для открытия карточки клиента
 *
 * @param {Object} resp - ответ от customer_card.php
 * @param {string} phone - номер телефона клиента
 */
function openClientCard(resp, phone) {
    if (!resp.userId) {
        if (document.location.pathname === '/missings') {
            setTimeout(() => {
                window.open("/tickets/create", "blank");
            }, 300);
        }
        return;
    }

    let checkTabOpened = localStorage.getItem('tabOpened');
    let checkTabTicketsOpened = localStorage.getItem('tabOpenedTickets');

    if (document.location.pathname === '/missings') {
        if (!checkTabTicketsOpened) {
            localStorage.setItem('tabOpenedTickets', 'true');
            setTimeout(() => {
                window.open("/tickets/create?client_id=" + resp.userId, "blank");
            }, 100);
        }
        return;
    }

    if (document.location.pathname === '/ccprolongations_plus' || document.location.pathname === '/ccprolongations') {
        $("input[name='phone']").val(phone);
        $("input[name='phone']").trigger('keyup');
        return;
    }

    if (checkTabOpened) {
        return;
    }

    localStorage.setItem('tabOpened', 'true');

    let clientUrl = "/client/" + resp.userId;
    if (resp.siteId && resp.siteId !== 'boostra') {
        clientUrl += "?site_id=" + resp.siteId;
    }

    setTimeout(() => {
        window.open(clientUrl, "blank");
    }, 200);
}

/**
 * Проверяем наличие специального заголовка жалобы в КО и выводим алерт оператору
 *
 * @param {Object} call - объект звонка из VoximplantKit
 */
function checkComplaintAndAlert(call) {
    if (call?.settings?.extraHeaders && call.settings.extraHeaders['X-Complaint-CB']) {
        Swal.fire({
            title: 'ВНИМАНИЕ!',
            text: 'У клиента зафиксировано упоминание жалобы в КО (ЦБ) в разговоре с роботом!',
            type: 'warning',
            backdrop: 'rgba(129,125,125,0.6)',
            confirmButtonText: 'Понятно',
            allowOutsideClick: false,
            allowEscapeKey: false
        });
    }
}

$(document).ready(function () {

    window.addEventListener('beforeunload', function (e) {
        if (checkCallConnected) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    window.VoximplantKitSettings = {
        // Визуальные настройки
        visualSettings: {
            drawMode: "full",
            side: "right",
            cssSelector: "#vox-kit-softphone"
        },
        // Настройки авторизации
        authSettings:
            {
                // Авторизация password
                api_url: config.api_url,
                domain: config.domain,
                email: config.email,
                type: config.type,
            }
    };


    const settings = window.VoximplantKitSettings;
    if (settings && typeof window.VoximplantKit === "object") {
        window.VoximplantKit.init(settings);
    }

    $(document).on("click", ".logout-div", function () {
        window.VoximplantKit.App.logout();
    })

    let userPhone = null;

    /**
     * Входящий звонок - сохраняем номер клиента, ждём DataUpdated для получения phone_a
     */
    window.VoximplantKit.Call.on("IncomingCall", call => {
        checkCallConnected = true;

        let phone = null;
        if (call?.settings?.extraHeaders['X-Kit-Number']) {
            phone = call.settings.extraHeaders['X-Kit-Number'];
        } else if (call?.settings?.number) {
            let number = call.settings.number;
            phone = number.split(":");
            phone = phone[1].split("@");
            phone = phone[0];
        }

        userPhone = phone;

        checkComplaintAndAlert(call);

        addManager(userPhone);
    })


    /**
     * Обновление данных звонка - здесь получаем phone_a и определяем site_id
     */
    let count = 0;
    window.VoximplantKit.Call.on("DataUpdated", (call) => {
        checkCallConnected = true;
        count++;

        if (checkCallConnected && count === 1) {
            let phone = call.VARIABLES.phone_num ? call.VARIABLES.phone_num : call.CALL.destination;

            // Для входящих звонков: phone_a = клиент (звонящий), phone_b = горячая линия (куда звонят)
            // Определяем телефон клиента из phone_a, если не получен из headers
            if (userPhone == null) {
                userPhone = call.CALL.phone_a;
            }

            // Горячая линия - это номер, на который позвонили (phone_b)
            let hotlinePhone = call.CALL.phone_b;

            $.ajax({
                url: 'ajax/customer_card.php',
                data: {
                    phone: userPhone,
                    phone_a: hotlinePhone,
                    site_id: 'all'
                },
                success: function (resp) {
                    if (resp.detectedSiteId) {
                        let currentSiteId = getCurrentSiteId();
                        if (currentSiteId !== resp.detectedSiteId) {
                            localStorage.setItem('selectedSiteId', resp.detectedSiteId);
                        }
                    }
                    openClientCard(resp, userPhone);
                }
            });

            $.ajax({
                url: 'ajax/vox_ticket_create.php',
                data: {
                    data: {
                        'client_name': phone,
                        'message': "Новый звонок",
                        'call_id': call.CALL.id,
                        'subject': 'Voximplant',
                        'phone_a': call.CALL.phone_a,
                        'phone_b': call.CALL.phone_b,
                        'vox_call_id': call.CALL.id,
                        'result_code': call.CALL.result_code,
                    }
                },
                success: function (response) {
                }
            });
        }
    })

    window.VoximplantKit.Call.on("CallConnected", call => {
        checkCallConnected = true;
    })

    window.VoximplantKit.Call.on("CallFailed", e => {
        checkCallConnected = false;
        count = 0;
    })

    window.VoximplantKit.Call.on("CallDisconnected", call => {
        checkCallConnected = false;
        count = 0
        let checkTabOpened = localStorage.getItem('tabOpened')
        let checkTabTicketsOpened = localStorage.getItem('tabOpenedTickets')
        if (checkTabOpened) {
            localStorage.removeItem('tabOpened')
        }
        if (checkTabTicketsOpened) {
            localStorage.removeItem('tabOpenedTickets')
        }
    })
})

window.VoximplantKit.App.on("ConnectionFailed", (e) => {
    checkCallConnected = false;
})


function callConnected(e, link = null) {
    if (!checkCallConnected) {
        return false
    }
    e.preventDefault();
    e.stopPropagation()
    if (!checkCallConnected) {
        Swal.fire({
            title: 'При перезагрузке страницы, Ваш звонок прервется',
            text: "Вы хотите открыть страницу в новой вкладке?",
            type: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Отменить',
            confirmButtonText: 'Да, открыть'
        }).then((result) => {
            if (result.value) {
                let a = document.createElement('a');
                a.target = '_blank';
                a.href = link ?? '';
                a.click();
            }
        })
    } else {
        let a = document.createElement('a');
        a.target = '_blank';
        a.href = link ?? '';
        a.click();
    }

}

$(document).on('click', '#sidebarnav>li>a, .breadcrumb>li>a, .js-order-row>td>a', function (e) {
    let link = $(this).attr('href');

    callConnected(e, link)
})

$(document).on('click', '.js-voximplant-call', function () {
    let _phone = $(this).data('phone');
    let _userId = $(this).data('user');
    let manager_id = $(".manager-id").val();
    let _agent_phone = config.phone;
    let _phoneLength = _phone.toString().length;
    let listing_type = $(this).data('listing_type');

    if (_phoneLength !== 11 && _phoneLength !== 18) {
        Swal.fire('Ошибка!', 'Неправильный номер.', 'error');
        return;
    }

    if (_phoneLength === 11) {
        _phone = formatRussianPhoneNumber(_phone);
    }

    if (listing_type === 'missings') {
        $.ajax({
            url: 'ajax/assigned_missing_manager.php',
            data: {
                action: 'check',
                user_id: _userId,
                manager_id: manager_id
            },
            success: function (resp) {
                if (!resp.success || resp.allowed === false) {
                    Swal.fire('Ошибка', resp.error || 'Вы не можете звонить этому клиенту', 'error');
                    return;
                }

                callClient(_phone, _agent_phone);
                if (document.location.pathname !== '/ccprolongations_plus' || document.location.pathname == '/ccprolongations') {
                    addManager(_phone, listing_type);
                }
            },
            error: function () {
                Swal.fire('Ошибка', 'Сервер не отвечает при проверке менеджера', 'error');
            }
        });
    } else {
        callClient(_phone, _agent_phone);
    }
});

function callClient(phone, agent_phone) {
    window.VoximplantKit.Widget.maximize();

    $.ajax({
        url: 'ajax/get_vox_parameter.php',
        success: function (response) {
            if (response.parameter) {
                window.VoximplantKit.App.call(phone);
            } else {
                window.VoximplantKit.App.call(phone, agent_phone);
            }
        }
    });
}


function addManager(phone, listing_type = null) {
    let manager_id = $(".manager-id").val();

    $.ajax({
        url: 'ajax/update_responsible_manager.php',
        data: {
            data: {
                manager_id,
                phone,
                listing_type
            }
        },
        success: function (response) {
            if (response.success) {
                let jsManagerNameClass = ' .js-missing-manager-name';
                $('.manager' + response.user).find(jsManagerNameClass).html(response.manager.name);
            }
        }
    });
}

function formatRussianPhoneNumber(phoneNumberString) {
    return ('' + phoneNumberString).replace(/\D/g, '');
}

window.VoximplantKit.App.on("ConnectionEstablished", (e) => {
    $(".logout-div").remove()
    $('.sp-header__title-container').after(
        `<div class="logout-div" style="border: 1px solid white; border-radius: 3px">
                    <button style="color: white">Выйти</button>
                </div>`
    )
})

$(document).on("click", ".sp-button", function () {
    $(".logout-div").remove()
    $('.sp-header__title-container').after(
        `<div class="logout-div" style="border: 1px solid white; border-radius: 3px">
                    <button style="color: white">Выйти</button>
                </div>`
    )
})
$(document).on("click", ".el-form>button", function () {
    var checkInterval = setInterval(function () {
        if ($(".sp-header__title-container").length === 1) {
            clearInterval(checkInterval)
            checkInterval = null
            $(".logout-div").remove()
            $('.sp-header__title-container').after(
                `<div class="logout-div" style="border: 1px solid white; border-radius: 3px">
                    <button style="color: white">Выйти</button>
                </div>`
            )

        }
    }, 2000)
})


window.VoximplantKit.App.on("AuthCompleted", (e) => {
})

window.VoximplantKit.App.on("StatusUpdated", (e) => {
})

window.VoximplantKit.Call.on("OutgoingCall", call => {
})

