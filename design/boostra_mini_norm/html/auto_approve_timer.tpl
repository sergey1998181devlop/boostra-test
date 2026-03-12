{literal}
    <style>
        body {
            height: 100vh;
            overflow: hidden;
        }
        #auto-approve-timer {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(197, 195, 195, 1);
            z-index: 99999;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-flow: column;
            gap: 10px;
        }

        .auto-approve-timer__timer-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 200px;
            height: 200px;
        }

        .auto-approve-timer__timer-container::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border: 5px solid #ff7e5f;
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(255, 126, 95, 0.8);
            animation: pulse 2s infinite ease-in-out;
            z-index: 1;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.9);
                opacity: 0.7;
            }
            50% {
                transform: scale(1.1);
                opacity: 1;
            }
            100% {
                transform: scale(0.9);
                opacity: 0.7;
            }
        }

        .auto-approve-timer__timer-container::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border: 5px solid transparent;
            border-top-color: #ff7e5f;
            border-radius: 50%;
            animation: spin 2s linear infinite;
            z-index: 1;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        #auto-approve-timer .loading-timer-text {
            font-size: 1em;
            font-weight: bold;
            text-align: center;
            margin-top: 15px;
        }

        #auto-approve-timer .loading-timer-text span {
            opacity: 0;
            animation: blink 1.4s infinite;
        }

        #auto-approve-timer .loading-timer-text span:nth-child(1) {
            animation-delay: 0.2s;
        }

        #auto-approve-timer .loading-timer-text span:nth-child(2) {
            animation-delay: 0.4s;
        }

        #auto-approve-timer .loading-timer-text span:nth-child(3) {
            animation-delay: 0.6s;
        }

        @keyframes blink {
            0%, 100% {
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
        }
        #auto-approve-timer__time  {
            font-size: 3em;
            font-weight: bold;
            text-align: center;
            z-index: 2; /* Чтобы текст был поверх круга */
        }
        @keyframes glow {
            from {
                box-shadow: 0 0 20px rgba(255, 126, 95, 0.5);
            }
            to {
                box-shadow: 0 0 40px rgba(255, 126, 95, 0.9);
            }
        }
    </style>
{/literal}

<div id="auto-approve-timer">
    <div class="auto-approve-timer__timer-container">
        <div id="auto-approve-timer__time"></div>
    </div>
    <div class="loading-timer-text">
        Ожидайте, идет оплата<span>.</span><span>.</span><span>.</span>
    </div>
</div>

<script type="text/javascript">
    // Укажите количество секунд для отсчета
    let totalSeconds = {$auto_approve_seconds_task};
    {literal}
    // Функция для обновления таймера
    function updateTimer() {
        const timerElement = document.getElementById('auto-approve-timer__time');

        // Вычисляем минуты и секунды
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;

        // Форматируем вывод, чтобы всегда было два символа
        const formattedMinutes = String(minutes).padStart(2, '0');
        const formattedSeconds = String(seconds).padStart(2, '0');

        // Обновляем текст таймера
        timerElement.textContent = `${formattedMinutes}:${formattedSeconds}`;

        // Уменьшаем количество секунд
        totalSeconds--;

        // Если время вышло, останавливаем таймер
        if (totalSeconds < 0) {
            clearInterval(intervalAutoApprove);
            timerElement.remove();
            location.reload();
        }
    }
    {/literal}

    // Запускаем таймер с интервалом 1 секунда
    let intervalAutoApprove = setInterval(updateTimer, 1000);

    // Инициализируем таймер сразу
    updateTimer();
</script>
