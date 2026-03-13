{if isset($abBannerType)}
    {if $abBannerType == 'positiveDecision'}
        <div class="abBanner">
            <p>
                Займ до <strong>30 000₽</strong> уже одобрен!<br>
                Не покидайте форму, чтобы не потерять прогресс.
            </p>
        </div>
    {else}
        <div class="abBanner">
            <p>
                Заявка обрабатывается.<br>
                Пожалуйста, не закрывайте форму — это может повлиять на результат.
            </p>
        </div>
    {/if}

    <style>
        .abBanner {
            background-color: #f6f9ff;
            border: 2px solid #0070f3;
            border-radius: 8px;
            padding: 16px 24px;
            max-width: 480px;
            font-family: sans-serif;
            font-size: 16px;
            line-height: 1.4;
            color: #1a1a1a;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin: 20px auto;
        }
        .banner strong {
            color: #0070f3;
        }
    </style>
{/if}