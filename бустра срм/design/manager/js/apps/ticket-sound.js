function getSoundConfig() {
    if (!document.body) {
        return {};
    }
    try {
        let configStr = document.body.dataset.ticketSoundConfig || '{}';
        if (configStr.startsWith('"') && configStr.endsWith('"')) {
            configStr = JSON.parse(configStr);
        }
        return JSON.parse(configStr);

    } catch (e) {
        console.warn('Ошибка парсинга конфигурации звукового уведомления:', e);
        return {};
    }
}

const soundConfig = getSoundConfig();

const TIME_REMIND_IN_MINUTE = Number(soundConfig.remind_interval_min) || 15;
const TIME_CHECK_NEW_TICKETS_IN_SEC = Number(soundConfig.check_interval_sec) || 10;

// Загружаем список известных тикетов из localStorage
let knownTickets = JSON.parse(localStorage.getItem('knownTickets') || '{}');

function saveKnownTickets() {
    localStorage.setItem('knownTickets', JSON.stringify(knownTickets));
}

function checkTickets() {
    $.getJSON('/ajax/tickets.php?action=get_new_tickets', function (data) {

        let tickets = Array.isArray(data.tickets) ? data.tickets : [];
        let newTickets = 0;
        tickets.forEach(ticket => {
            // Если тикета нет в локальном списке — это новый
            if (!knownTickets[ticket.id]) {
                knownTickets[ticket.id] = {firstNotifiedAt: Date.now(), notified: true};
                newTickets++;
                scheduleReminder(ticket);
            }
        });
        //Есть новые тикеты.
        if (newTickets) {
            saveKnownTickets();
            playSound();
        }
    });
}

let soundPlayed = false;

function playSound() {
    if (soundPlayed) return; // если недавно играло — выходим

    const sound = document.getElementById('ticketSound');
    sound.currentTime = 0;

    sound.play()
        .then(() => {
            soundPlayed = true;
            // разрешаем повтор через 1 сек
            setTimeout(() => soundPlayed = false, 1000);
        })
        .catch(() => console.warn('Autoplay blocked'));
}

function scheduleReminder(ticket) {
    setTimeout(() => {
        checkTicketStillNew(ticket.id);
    }, TIME_REMIND_IN_MINUTE * 60 * 1000);
}

function checkTicketStillNew(ticketId) {
    console.log('checkTicketStillNew ' + ticketId);
    $.getJSON('/ajax/tickets.php?action=get_new_tickets', {id: ticketId}, function (data) {
        const ticket = Array.isArray(data.tickets) ? data.tickets[0] : null;

        if (ticket) {
            playSound();
            scheduleReminder(ticket);
        } else {
            // Если статус изменился — можно удалить из localStorage
            delete knownTickets[ticketId];
            saveKnownTickets();
        }
    });
}

// Проверяем новые тикеты каждые N секунд
setInterval(checkTickets, TIME_CHECK_NEW_TICKETS_IN_SEC * 1000);
checkTickets();

// При закрытии страницы сохраняем данные
window.addEventListener('beforeunload', saveKnownTickets);