<link rel="stylesheet" href="design/{$settings->theme|escape}/css/wheel.css?v={time()}" />

{* ===== Wheel of Fortune (MVP) ===== *}
<div id="wheel"
     class="wheel"
     data-spin-url="/wheel/spin"
     data-csrf="{$csrf_token|escape}"
     data-price="100"
     data-sectors="10">

    <div class="wheel__stage">
        <!-- ВНИМАНИЕ: Вся геометрия рассчитана под viewBox 0 0 460 460 -->
        <svg class="wheel__svg" viewBox="0 0 460 460" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
            <!-- Серый ободок -->
            <circle cx="230" cy="230" r="220" class="wheel__rim"/>
            <circle cx="230" cy="230" r="206" class="wheel__rim-inner"/>
            <!-- Цветные точки на ободе -->
            <g id="wheelRimDots" class="wheel__rim-dots"></g>

            <!-- Вращаемая часть -->
            <g id="wheelRotor" class="wheel__rotor" transform="rotate(0 230 230)">
                <g id="wheelSectors"></g>
                <g id="wheelLabels"></g>
            </g>

            <!-- Центральный хаб -->
            <circle cx="230" cy="230" r="38" class="wheel__hub"/>
            <circle cx="230" cy="230" r="28" class="wheel__hub-fill"/>

            <!-- Синяя «обводка-сектор» + капля-стрелка -->
            <g id="wheelPointer" class="wheel__pointer">
                <path id="pointerStroke" class="wheel__pointer-stroke" d=""/>
                <path id="pointerDrop" class="wheel__pointer-drop" d=""/>
            </g>
        </svg>

        <div class="wheel__actions">
            <button id="wheelSpinBtn" class="wheel__btn">Играть</button>
        </div>

        <div class="wheel__head">
            <div class="wheel__title">Колесо фортуны</div>
            <div class="wheel__note">Стоимость одного хода <b id="wheelPrice">100 ₽</b></div>
        </div>

        <div id="wheelResult" class="wheel__result" aria-live="polite"></div>
    </div>
</div>

{* MVP-модалка "обкатки механики" *}
<div id="wheel-mvp-modal" class="wheel-modal" style="display:none">
    <div class="wheel-modal__box">
        <div class="wheel-modal__title">Сейчас мы <br> обкатываем <br> новую механику</div>
        <div class="wheel-modal__text">
            Вы — один из первых участников!<br/>
            Скоро колесо заработает в полную силу 🚀
        </div>
        <button id="wheel-close-all" type="button" class="button button-inverse">Назад к договору</button>
    </div>
</div>
{* ===== /Wheel of Fortune ===== *}

<script src="design/{$settings->theme|escape}/js/wheel.js?v={time()}"></script>
