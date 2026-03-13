/**
 * Обновляет индикатор наличия непрочитанных комментариев оператора
 * (восклицательный знак в меню "Форма обращения" пользователя)
 */
function updateTicketsUnreadCommentsAlert() {
    fetch('/user/tickets?action=hasUnreadOperatorComments')
        .then(response => response.json())
        .then(data => {
            const ids = ['operator-alert', 'mobile-operator-alert'];
            ids.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = data.has_unread ? 'inline-block' : 'none';
            });
        })
        .catch(() => {})
}