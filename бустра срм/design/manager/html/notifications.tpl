{literal}
    <style>
        .notification-modal {
            display: none;
            position: fixed;
            top: 2%;
            right: 5%;
            z-index: 1050;
            width: 350px;
            max-width: 90%;
        }

        .modal-notification-badge {
            background: #dc3545;
            color: #ffffff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
        }

        .notification-modal-dialog {
            max-width: 100%;
            margin: 0;
        }

        .notification-modal-content {
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            overflow: hidden;
        }

        .notification-modal-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #343a40;
        }

        .notification-close {
            background: none;
            border: none;
            font-size: 18px;
            line-height: 1;
            color: #6c757d;
        }

        .notification-close:hover {
            color: #000;
            cursor: pointer;
        }

        .notification-modal-body {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px 15px;
        }

        .notification-modal-body ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .notification-modal-body ul li {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s;
        }

        .notification-modal-body ul li:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .notification-title {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }

        .notification-description {
            font-size: 14px;
            color: #6c757d;
            line-height: 1.4;
        }

        .notification-description a {
            color: #007bff;
            text-decoration: none;
        }

        .notification-description a:hover {
            text-decoration: underline;
        }

        .notification-modal-footer {
            padding: 10px;
            text-align: right;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .notification-btn {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            padding: 5px 10px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
        }

        .notification-btn:hover {
            background-color: #0056b3;
        }

        .reds_status[data-read="0"] {
            position: absolute;
            top: 12px;
            left: -5px;
            width: 10px;
            height: 10px;
            background: #28a745;
            border-radius: 50%;
            z-index: 1;
        }

        .notification-btn-clear {
            background-color: #dc3545;
            color: #ffffff;
            border: none;
            padding: 5px 10px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }

        .notification-btn-clear:hover {
            background-color: #bd2130;
        }
    </style>
{/literal}
<div class="notification-modal" id="notification-modal">
    <div class="notification-modal-dialog">
        <div class="notification-modal-content">
            <div class="notification-modal-header">
                <h5 class="notification-modal-title">Уведомления</h5>
                <span class="modal-notification-badge" id="modal-notification-badge">0</span>
                <button type="button" class="notification-close" data-dismiss="notification-modal">&times;</button>
            </div>
            <div class="notification-modal-body" id="notification-modal-content">
                <div id="no-notifications-message" style="display: none; text-align: center; color: #6c757d;">
                    Нет уведомлений
                </div>
                <ul id="notification_block_ul"></ul>
            </div>
            <div class="notification-modal-footer">
                <button type="button" class="notification-btn-clear" id="clear-notifications-btn">Очистить</button>
            </div>
        </div>
    </div>
</div>
{literal}
    <script>
        $(document).ready(function () {
            const NOTIFICATIONS_URL = '/ajax/notifications.php';
            const notificationModal = $('#notification-modal');
            const notificationList = $('#notification_block_ul');
            const noNotificationsMessage = $('#no-notifications-message');
            const modalNotificationBadge = $('#modal-notification-badge');
            const notificationBadge = $('#notification-badge');

            // Получение уведомлений
            function fetchNotifications() {
                $.post(NOTIFICATIONS_URL, { action: 'get' })
                    .done(function (data) {
                        notificationList.empty();

                        if (data.status !== 'success' || !data.data || data.data.length === 0) {
                            noNotificationsMessage.show();
                            updateNotificationCount(0); // Обновляем счётчик, если уведомлений нет
                            return;
                        }

                        noNotificationsMessage.hide();
                        data.data.forEach((item) => addNotification(item));
                        updateNotificationCount(); // Обновляем счётчик на основе новых данных
                    })
                    .fail(() => {
                        noNotificationsMessage.text('Ошибка загрузки').show();
                        updateNotificationCount(0); // Обновляем счётчик как 0 в случае ошибки
                    });
            }

            // Добавление уведомления
            function addNotification(item) {
                const messageWithLink = item.message.replace(
                    /(https?:\/\/[^\s]+)/g,
                    '<a href="$1" target="_blank">$1</a>'
                );

                notificationList.append(`
                    <li class="notification-item" data-id="${item.id}">
                        <div class="notification-title">${item.is_read === "0" ? '<span class="unread">•</span> ' : ''}${item.subject}</div>
                        <div class="notification-description">${messageWithLink}</div>
                    </li>
                `);
            }

            // Обновление счётчика непрочитанных уведомлений
            function updateNotificationCount(forceCount = null) {
                let count = forceCount !== null ? forceCount : $('.notification-item .unread').length;

                if (count > 0) {
                    modalNotificationBadge.text(count).show();
                    notificationBadge.text(count).show();
                } else {
                    modalNotificationBadge.hide();
                    notificationBadge.hide();
                }
            }

            // Пометка уведомлений как прочитанных
            function markAsRead(notificationIds = []) {
                $.post(NOTIFICATIONS_URL, { action: 'mark_read', notification_ids: notificationIds })
                    .done((resp) => {
                        if (resp.status === 'success') {
                            if (notificationIds.length === 0) {
                                // Если отмечаем все, очищаем список
                                $('.notification-item .unread').removeClass('unread');
                                notificationList.empty();
                                noNotificationsMessage.show();
                                updateNotificationCount(0);
                            } else {
                                // Если отмечаем только выбранные
                                notificationIds.forEach((id) => {
                                    $(`.notification-item[data-id="${id}"] .unread`).remove();
                                });
                                updateNotificationCount();
                            }
                        }
                    });
            }

            $('.nav-link-notification').on('click', (e) => {
                e.preventDefault();

                if (notificationModal.is(':visible')) {
                    notificationModal.fadeOut(300);
                } else {
                    notificationModal.fadeIn(300);
                }
            });

            // Закрытие окна при клике вне него
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.notification-modal-content').length && !$(e.target).closest('.nav-link-notification').length) {
                    notificationModal.fadeOut(300);
                }
            });

            // Событие: Очистить все уведомления
            $('#clear-notifications-btn').on('click', (e) => {
                e.preventDefault();
                markAsRead([]);
            });

            // Событие: Отметить конкретное уведомление как прочитанное
            $(document).on('click', '.notification-item', function () {
                const id = $(this).data('id');
                markAsRead([id]);
            });

            // Первичная загрузка уведомлений
            fetchNotifications();
            setInterval(fetchNotifications, 60000); // Каждую минуту
        });
    </script>
{/literal}
