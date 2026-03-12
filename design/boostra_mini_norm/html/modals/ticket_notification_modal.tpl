{literal}
    <style>
        #notification-pause, #notification-resolved {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .notification-modal {
            position: fixed;
            top: 20px;
            left: 20px;
            background: white;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            padding: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .notification-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 6px;
            position: relative;
            text-align: center;
        }

        .notification-content h2 {
            font-size: 1.3em;
            margin-bottom: 10px;
        }

        .notification-content p {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
        }

        .notification-close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #333;
        }

        .notification-close:hover {
            color: #000;
        }

        .close-button {
            display: block;
            margin: 0 auto;
            padding: 10px 20px;
            width: 50%;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 16px;
        }

        .close-button:hover {
            background-color: #0056b3;
        }

        .close-button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.6);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 5px;
            vertical-align: middle;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 576px) {
            .notification-modal {
                width: calc(100% - 20px);
                max-width: 90%;
                left: 10px;
                top: 10px;
                padding: 12px;
            }

            .notification-content h2 {
                font-size: 1.1em;
            }

            .notification-content p {
                font-size: 14px;
            }

            .close-button {
                font-size: 14px;
                width: 60%;
                padding: 8px 16px;
            }

            .notification-close {
                font-size: 20px;
            }
        }

        @media (max-width: 400px) {
            .notification-modal {
                max-width: 95%;
                left: 5px;
                top: 5px;
            }

            .notification-content {
                padding: 15px;
            }
        }
    </style>
{/literal}

<div id="notification-{$type}">
    <div class="notification-modal">
        <div class="notification-content">
            <span class="notification-close">&times;</span>
            <h2>
                {if $type == 'resolved'}
                    Ваше обращение урегулировано
                {else}
                    Не удалось дозвониться до вас
                {/if}
            </h2>
            <p>
                {if $type == 'resolved'}
                    Если у вас остались дополнительные вопросы, позвоните нам <a href="tel:+74951804205">+74951804205</a> (Отдел по работе с претензиями).
                {else}
                    Мы не смогли с вами связаться по вашему номеру. Пожалуйста, перезвоните нам <a href="tel:+74951804205">+74951804205</a> (Отдел по работе с претензиями), мы поможем в решении вашего вопроса.
                {/if}
            </p>
            <button class="close-button" data-user_id="{$user_id}" data-type="{$type}">OK</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notification = document.getElementById('notification-{$type}');
        const closeButton = notification.querySelector('.close-button');
        const closeIcon = notification.querySelector('.notification-close');
        const sessionStorage = window.sessionStorage;
        const notificationType = '{$type}';
        const storageKey = 'notificationClosed_' + notificationType;

        function showNotification() {
            if (!sessionStorage.getItem(storageKey)) {
                notification.style.display = 'block';
            }
        }

        function hideNotification() {
            notification.style.display = 'none';
            sessionStorage.setItem(storageKey, 'true');
        }

        function disableNotification() {
            const userId = closeButton.getAttribute('data-user_id');
            const type = closeButton.getAttribute('data-type');
            closeButton.disabled = true;
            const originalHTML = closeButton.innerHTML;
            closeButton.innerHTML = '<span class="spinner"></span> Отправка...';

            $.ajax({
                url: 'ajax/ticket_notification.php',
                method: 'POST',
                dataType: 'json',
                timeout: 5000,
                data: {
                    user_id: userId,
                    type: type
                },
                success: function (response) {
                    if (response.success) {
                        hideNotification();
                    } else {
                        errorDiv.textContent = 'Ошибка: ' + response.message;
                        errorDiv.style.display = 'block';
                    }
                },
                error: function (xhr, status, error) {
                    errorDiv.textContent = 'Ошибка связи с сервером. Попробуйте позже.';
                    errorDiv.style.display = 'block';
                    console.error('Error disabling notification:', status, error);
                },
                complete: function () {
                    closeButton.disabled = false;
                    closeButton.innerHTML = originalHTML;
                }
            });
        }

        closeButton.addEventListener('click', disableNotification);
        closeIcon.addEventListener('click', hideNotification);

        window.addEventListener('click', (event) => {
            if (event.target === notification) {
                hideNotification();
            }
        });

        showNotification();
    });
</script>