<style>
    /* ===== Referral Banner — responsive ===== */
    .ref-px-banner{
        position: relative;
        width: 100%;
        max-width: 100%;
        aspect-ratio: 712 / 350;        /* сохраняем пропорции макета */
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 0 0 1px rgba(0,0,0,.06) inset;
        overflow: hidden;
        font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    }

    /* фоновые пятна (можно оставить как было — не критично) */
    .ref-bg{ position:absolute; pointer-events:none; user-select:none; }
    .ref-bg--left  { left: 0; top: 0; width: 28.1%;  height: auto; }
    .ref-bg--right { right: 0; bottom: 0; width: 30.9%; height: auto; }
    .ref-bg--bot   { left: 0; bottom: 0; width: 20.5%; height: auto; }

    /* иллюстрация с человеком — проценты от 712×350 */
    .ref-art{
        position: absolute;
        left: 3.93%;
        top: 20%;
        width: 39.33%;
        height: auto;
    }

    /* текстовый блок — проценты от 712×350 */
    .ref-copy{
        position: absolute;
        left: 37.64%;
        top: 12.57%;
        right: 3.93%;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .ref-title{
        font-weight: 700;
        font-size: clamp(18px, 4.6vw, 33px);
        line-height: 1.2;
        letter-spacing: .02em;
        color: #2F2F2F;
    }

    .ref-subtitle{
        font-weight: 500;
        font-size: clamp(13px, 2.9vw, 21px);
        line-height: 1.25;
        color: #60646C;
    }
    .ref-subtitle__strong{ font-weight: 700; color: #2F2F2F; }

    /* «пилюля» со ссылкой — ОДНО правило, адаптив + переносы */
    .ref-linkpill{
        display: flex;
        align-items: center;
        gap: 8px;
        box-sizing: border-box;
        width: 100%;
        padding: 12px 14px;
        border-radius: 12px;
        background: #168BFF;
        color: #fff;
        border: 0;
        height: auto;                 /* не фиксируем высоту */
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 1px 0 rgba(0,0,0,.04), 0 2px 8px rgba(22,139,255,.25);
    }

    .ref-linkpill:active{ transform: translateY(1px); }

    .ref-linkpill__icon,
    .ref-check{
        flex: 0 0 auto;
        width: 20px;
        height: 20px;
    }

    .ref-check{ color:#fff; display:none; }

    .ref-linkpill__text{
        min-width: 0;                 /* ключ к сжатию внутри flex */
        flex: 1 1 auto;
        font-weight: 700;
        font-size: clamp(13px, 3.4vw, 20px);
        line-height: 1.25;
        white-space: normal;          /* разрешаем переносы */
        overflow-wrap: anywhere;      /* переносим длинные токены */
        word-break: break-word;
    }

    /* Кнопка копирования */
    .js-ref-copy{
        flex: 0 0 auto;
        padding: 8px 10px;
        border-radius: 9999px;
        font-size: clamp(12px, 3.2vw, 14px);
        line-height: 1;
    }
    
    .ref-rules {
        text-align: right;
        display: inline-block;
        margin-bottom: 5px;
    }

    /* мелкие экраны */
    @media (max-width: 480px){
        .ref-copy{ gap: 12px; }
        .ref-linkpill{ padding: 10px 12px; gap: 6px; }
        .ref-linkpill__icon, .ref-check{ width: 18px; height: 18px; }
    }

    /* очень узкие */
    @media (max-width: 360px){
        .ref-linkpill{ padding: 8px 10px; }
    }

    .ref-px-banner *, .ref-px-banner *::before, .ref-px-banner *::after{
        box-sizing: border-box;
    }

    /* iPhone SE / 360–390px */
    @media (max-width: 390px){
        /* делаем текстовую колонку уже, чтобы «пилюля» влезала */
        .ref-copy{
            left: 44%;          /* было 37.64% */
            right: 3%;
            gap: 12px;
        }
        .ref-title{
            font-size: clamp(16px, 4.4vw, 24px);
            line-height: 1.15;
        }
        .ref-subtitle{
            font-size: clamp(12px, 3.2vw, 14px);
            line-height: 1.25;
        }

        /* «пилюля» – компактнее */
        .ref-linkpill{
            padding: 9px 10px;
            gap: 6px;
            border-radius: 10px;
        }
        .ref-linkpill__icon,
        .ref-check{
            width: 16px; height: 16px;
        }
        .ref-linkpill__text{
            font-size: clamp(12px, 3.4vw, 15px);
        }

        /* ссылку «Правила акции» не даём “выпирать” */
        .ref-rules{ font-size: 12px; }
    }

    /* очень узкие (≤ 340px) — ещё чуть уже текстовая колонка */
    @media (max-width: 340px){
        .ref-copy{ left: 48%; right: 2.5%; gap: 10px; }
        .ref-linkpill{ padding: 8px 9px; }
        .ref-linkpill__text{ font-size: 12px; }
    }

    .ref-px-banner{
        width: min(712px, 100%);   /* не шире 712, но резиново в меньших контейнерах */
        aspect-ratio: 712 / 350;   /* сохраняем высоту как в макете */
    }

    /* на очень широких контейнерах не даём баннеру «распухать» */
    @media (min-width: 1024px){
        .ref-px-banner{ width: 712px; }
    }

    /* ===== Мобильный режим: ломаем абсолют, складываем в столбик ===== */
    @media (max-width: 420px){
        .ref-px-banner{
            aspect-ratio: auto;                 /* высоту считаем по контенту */
            display: grid;
            grid-template-columns: 1fr;
            grid-auto-rows: auto;
            gap: 12px;
            padding: 12px;                      /* внутренний отступ вместо абсолютов */
            max-width: 274px !important;
        }

        /* убираем абсолют у иллюстрации и текста */
        .ref-art,
        .ref-copy{
            position: static !important;
            left: auto; right: auto; top: auto; bottom: auto;
            width: auto;
        }

        /* картинка сверху, не шире 60% блока */
        .ref-art{
            max-width: 60%;
            height: auto;
            justify-self: start;
        }

        /* текстовый блок под картинкой, растягивается на всю ширину */
        .ref-copy{
            display: grid;
            gap: 10px;
        }

        .ref-title{
            font-size: clamp(18px, 5.2vw, 22px);
            line-height: 1.2;
        }
        .ref-subtitle{
            font-size: clamp(12px, 4vw, 14px);
            line-height: 1.3;
        }

        /* «пилюля» теперь гарантированно во всю ширину */
        .ref-linkpill{
            width: 100%;
            padding: 10px 12px;
            gap: 8px;
            border-radius: 10px;
        }
        .ref-linkpill__icon,
        .ref-check{ width: 18px; height: 18px; }
        .ref-linkpill__text{
            min-width: 0;
            font-size: clamp(12px, 4.2vw, 15px);
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        /* ссылка правил — в одну строку и не выпирает */
        .ref-rules{
            justify-self: end;
            font-size: 12px;
            margin: 0;
        }

        /* фоновые пятна оставляем, но прячем, если мешают высоте */
        .ref-bg{ display: none; }
    }
</style>

<!-- NEW: Referral Banner -->
<div class="ref-px-banner" id="ref-px-banner" style="--scale:1"
     data-link="{$referer_url}">
    <!-- фоновые пятна -->
    <img class="ref-bg ref-bg--right"  src="/design/{$settings->theme|escape}/img/referral/Rectangle 759.png" alt="" aria-hidden="true">
    <img class="ref-bg ref-bg--left" src="/design/{$settings->theme|escape}/img/referral/Rectangle 760.png" alt="" aria-hidden="true">
    <img class="ref-bg ref-bg--bot"   src="/design/{$settings->theme|escape}/img/referral/Rectangle 761.png" alt="" aria-hidden="true">

    <!-- иллюстрация с человеком -->
    <img class="ref-art" src="/design/{$settings->theme|escape}/img/referral/Money Management 1.png" alt="" aria-hidden="true">

    <!-- текстовый блок -->
    <div class="ref-copy">
        <div class="ref-title">ВОЗЬМИТЕ СКИДКУ</div>
        <div class="ref-subtitle">
            ОТПРАВЬТЕ ССЫЛКУ ДРУГУ<br>
            ЗА КАЖДОГО ДРУГА СКИДКА<br>
            <span class="ref-subtitle__strong">7000 РУБЛЕЙ</span>
        </div>

        <!-- синяя «пилюля» со ссылкой и иконкой -->
        <button type="button" class="ref-linkpill js-ref-copy" title="Скопировать или поделиться ссылкой">
            <img class="ref-linkpill__icon" src="/design/{$settings->theme|escape}/img/referral/Export.png" alt="" aria-hidden="true">
            <span class="ref-linkpill__text" id="ref-link-text">{$referer_url}</span>
            <span class="ref-check" style="display: none">&#10003;</span>
        </button>

        <a class="ref-rules" href="/referral_discount_rules" target="_blank" rel="noopener">Правила акции</a>
    </div>
</div>

<script src="/design/{$settings->theme|escape}/js/referral/referer.js?v=2" defer></script>