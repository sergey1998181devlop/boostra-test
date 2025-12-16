/* Wheel of Fortune (MVP) — jQuery version (refactored)
 * - мгновенный быстрый спин (WAAPI → CSS fallback)
 * - остановка по индексу от бэка
 * - "Поздравляем! 🎉" для любого выигрыша
 */
$(function () {
    // ---------- DOM ----------
    const $root = $('#wheel');
    if (!$root.length) return;

    const $svg     = $root.find('.wheel__svg');
    const svgEl    = $svg[0];
    let   $gRotor  = $svg.find('#wheelRotor');
    let   $gRimDots= $svg.find('#wheelRimDots');
    let   $gSectors= $svg.find('#wheelSectors');
    let   $gLabels = $svg.find('#wheelLabels');
    const $pStroke = $svg.find('#pointerStroke');
    const $pDrop   = $svg.find('#pointerDrop');
    const $hubFill = $svg.find('.wheel__hub-fill');

    const $spinBtn   = $('#wheelSpinBtn');
    const $resultBox = $('#wheelResult');
    const $priceNode = $('#wheelPrice');

    // Проверяем, что группы существуют внутри ротора, иначе создаём
    if (!$gSectors.length)  $gSectors = $(appendSvgGroup('wheelSectors'));
    if (!$gLabels.length)   $gLabels  = $(appendSvgGroup('wheelLabels'));

    // центр viewBox 460×460
    if ($gRotor.length) {
        $gRotor.css({ transformOrigin: '230px 230px', transformBox: 'view-box' });
    }

    // ---------- CONFIG ----------
    const ICON_PATH = '/design/boostra_mini_norm/img/wheel/';
    const CX = 230, CY = 230;          // центр
    const Rin  = 38;                   // внутренний радиус сектора
    const Rout = 212;                  // внешний радиус сектора — встык к ободу
    const Rrim = 220;                  // радиус точек/обода
    const DOTS = 20;
    const ICON_SIZE = 40;

    const sectorsCount = Number($root.data('sectors')) || 10;
    const startOffset  = Number($root.data('startOffset') || $root.attr('data-start-offset') || 0);
    const price        = Number($root.data('price')) || 200;
    const sectorAngle  = 360 / sectorsCount;
    const half         = sectorAngle / 2;

    const spinUrl     = $root.data('spin-url')     || '/wheel/spin';
    const completeUrl = $root.data('complete-url') || '/wheel/complete';

    if ($priceNode.length) $priceNode.text(`${price.toLocaleString('ru-RU')} ₽`);

    const items = [
        {text:'Умножитель',               icon:ICON_PATH+'x2-icon.png'},
        {text:'Скидка <br> 200 ₽',        icon:ICON_PATH+'ruble-icon.png'},
        {text:'Попробуйте ещё <br> раз',  icon:ICON_PATH+'sad-face-icon.png'},
        {text:'Скидка <br> 1000 ₽',       icon:ICON_PATH+'ruble-icon.png'},
        {text:'Попробуйте ещё <br> раз',  icon:ICON_PATH+'sad-face-icon.png'},
        {text:'Скидка <br> 200 ₽',        icon:ICON_PATH+'ruble-icon.png'},
        {text:'Джекпот',                  icon:ICON_PATH+'jackpot-icon.png'},
        {text:'Попробуйте ещё <br> раз',  icon:ICON_PATH+'sad-face-icon.png'},
        {text:'Сюрприз',                  icon:ICON_PATH+'gift-icon.png'},
        {text:'Бонусный спин',            icon:ICON_PATH+'repeat-icon.png'},
    ].slice(0, sectorsCount);

    // ---------- STATE ----------
    let spinning       = false;
    let spinStartedAt  = 0;
    let fastSpinAnim   = null;   // Web Animations API handler
    let pendingBonus   = false;  // если выпал бонус — следующий POST уйдёт с bonus=1
    let lastPrize      = null;   // кеш последнего приза

    // анимация/тайминги
    const FAST_SPIN_PERIOD = 320;    // скорость "быстрой" фазы (меньше — быстрее)
    const MIN_FAST_MS      = 5000;   // минимум крутиться до замедления
    const DECEL_MS         = 8000;   // длительность замедления
    const DECEL_TURNS      = 5;      // доп. обороты при торможении

    // ---------- HELPERS ----------
    const NS    = 'http://www.w3.org/2000/svg';
    const XLINK = 'http://www.w3.org/1999/xlink';
    const toRad = deg => (deg * Math.PI) / 180;

    function appendSvgGroup(id){
        const g = document.createElementNS(NS, 'g');
        g.setAttribute('id', id);
        $gRotor[0].appendChild(g);
        return g;
    }
    function createSvg(tag, attrs = {}){
        const el = document.createElementNS(NS, tag);
        Object.entries(attrs).forEach(([k,v]) => el.setAttribute(k, v));
        return el;
    }
    function polar(cx, cy, r, deg){
        const a = toRad(deg);
        return [cx + r * Math.cos(a), cy + r * Math.sin(a)];
    }
    function wedgePath(a0, a1, rin, rout){
        const delta = ((a1 - a0 + 360) % 360);
        const large = delta > 180 ? 1 : 0;
        const [x0, y0] = polar(CX, CY, rout, a0);
        const [x1, y1] = polar(CX, CY, rout, a1);
        const [x2, y2] = polar(CX, CY, rin,  a1);
        const [x3, y3] = polar(CX, CY, rin,  a0);
        return `M ${x0} ${y0} A ${rout} ${rout} 0 ${large} 1 ${x1} ${y1} L ${x2} ${y2} A ${rin} ${rin} 0 ${large} 0 ${x3} ${y3} Z`;
    }
    function getCurrentAngle(el){
        const tr = getComputedStyle(el).transform;
        if (!tr || tr === 'none') return 0;
        const m2d = tr.match(/matrix\(([^)]+)\)/);
        const m3d = tr.match(/matrix3d\(([^)]+)\)/);
        const vals= (m2d ? m2d[1] : m3d ? m3d[1] : '').split(',').map(parseFloat);
        const a = vals[0], b = vals[1];
        if (isNaN(a) || isNaN(b)) return 0;
        return (Math.atan2(b, a) * 180 / Math.PI + 360) % 360;
    }

    // ---------- BUILD ----------
    function buildSectors(){
        $gSectors.empty();
        $gLabels.empty();

        for (let i = 0; i < sectorsCount; i++){
            const mid = -90 + i * sectorAngle + startOffset;
            const a0  = mid - half;
            const a1  = mid + half;

            // path
            $gSectors[0].appendChild(createSvg('path', {
                d: wedgePath(a0, a1, Rin, Rout),
                class: `wheel__sector ${i % 2 ? 'sector-b' : 'sector-a'}`
            }));

            // label group (перпендикулярно радиусу)
            const grp = createSvg('g', { class:'wheel__label-group', transform:`rotate(${mid + 90} ${CX} ${CY})` });

            // text (с переносом по <br>)
            const item = items[i] || {text:'', icon:''};
            const text = createSvg('text', { class:'wheel__label', x:CX, y: CY - (Rin + (Rout - Rin)*0.78), 'text-anchor':'middle' });
            String(item.text||'').split(/<br\s*\/?>/i).forEach((line, idx) => {
                const tspan = createSvg('tspan', { x: CX });
                if (idx) tspan.setAttribute('dy', 14);
                tspan.textContent = line.trim();
                text.appendChild(tspan);
            });
            grp.appendChild(text);

            // icon
            if (item.icon){
                const iconY = CY - (Rin + (Rout - Rin) * 0.5);
                const img = createSvg('image', {
                    class: 'wheel__icon',
                    x: CX - ICON_SIZE/2,
                    y: iconY - ICON_SIZE/2,
                    width: ICON_SIZE,
                    height: ICON_SIZE
                });
                img.setAttributeNS(XLINK, 'xlink:href', item.icon);
                img.setAttribute('href', item.icon);
                grp.appendChild(img);
            }
            $gLabels[0].appendChild(grp);
        }
    }
    function buildRimDots(){
        $gRimDots.empty();
        for (let i = 0; i < DOTS; i++){
            const ang = -90 + i * (360 / DOTS);
            const [x, y] = polar(CX, CY, Rrim, ang);
            $gRimDots[0].appendChild(createSvg('circle', {
                cx:x, cy:y, r:7, class: (i % 2 ? 'alt' : '')
            }));
        }
    }
    function drawPointer(){
        const mid = -90 + startOffset;
        const a0 = mid - half, a1 = mid + half;
        $pStroke.attr('d', wedgePath(a0, a1, Rin, Rout));

        // капля (стрелка) на 180°
        const tip = CY - Rout - 8;
        const dropTop = tip - 18;
        const w = 18;
        const dropPath = [
            `M ${CX} ${dropTop}`,
            `Q ${CX + w/2} ${dropTop + 10} ${CX + w/2} ${tip}`,
            `L ${CX} ${tip + 10}`,
            `L ${CX - w/2} ${tip}`,
            `Q ${CX - w/2} ${dropTop + 10} ${CX} ${dropTop}`,
            'Z'
        ].join(' ');
        $pDrop.attr('d', dropPath)
            .attr('transform', `rotate(180 ${CX} ${dropTop + 9})`);
    }
    if ($hubFill.length) $hubFill.attr('r', '26');

    // ---------- RESULT UI ----------
    function showResultMessage(prize){
        if (!$resultBox.length) return;
        const t = prize?.type;
        const v = Number(prize?.value || 0);
        let base = '';
        switch (t) {
            case 'bonus':      base = 'Начислен бонусный спин — жмите «Играть» ещё раз!'; break;
            case 'multiplier': base = 'Вы получили ×2 на следующий выигрыш.'; break;
            case 'discount':   base = `Скидка ${v.toLocaleString('ru-RU')} ₽.`; break;
            case 'gift':       base = `Сюрприз!`; break;
            case 'jackpot':    base = 'Джекпот!'; break;
            default:           base = 'Увы, ничего. Попробуйте ещё раз в будущем.'; break;
        }
        $resultBox.text(base).removeClass('is-win');
    }

    // ---------- ANIMATION ----------
    function resetRotorForNewSpin(){
        // стопнем любые предыдущие анимации (WAAPI/CSS)
        if (fastSpinAnim) { try { fastSpinAnim.cancel(); } catch(e){} fastSpinAnim = null; }
        $gRotor.removeClass('fast-spin').css({ animation:'none', transition:'none' });

        // зафиксируем текущий угол
        const cur = getCurrentAngle($gRotor[0]);
        $gRotor.css({ transform: `rotate(${cur}deg)` });

        // reflow + убираем инлайновые свойства (чтобы класс заработал)
        void $gRotor[0].offsetWidth;
        $gRotor[0].style.removeProperty('animation');
        $gRotor[0].style.removeProperty('transition');
    }
    function startFastSpin(){
        // мгновенно запускаем кручение: WAAPI → fallback CSS
        if (fastSpinAnim) { try { fastSpinAnim.cancel(); } catch(e){} fastSpinAnim = null; }
        const cur = getCurrentAngle($gRotor[0]);
        if ($gRotor[0].animate){
            fastSpinAnim = $gRotor[0].animate(
                [{ transform:`rotate(${cur}deg)` }, { transform:`rotate(${cur + 360}deg)` }],
                { duration: FAST_SPIN_PERIOD, iterations: Infinity, easing:'linear' }
            );
        } else {
            $gRotor.css('--wheel-fast-period', FAST_SPIN_PERIOD + 'ms')
                .addClass('fast-spin');
        }
    }
    function finalizeSpin(winIndex, prize){
        if (!spinning) return;
        const elapsed = performance.now() - spinStartedAt;
        const wait = Math.max(0, MIN_FAST_MS - elapsed);

        setTimeout(() => {
            if (!spinning) return;

            // стоп быструю фазу
            if (fastSpinAnim) { try { fastSpinAnim.cancel(); } catch(e){} fastSpinAnim = null; }
            const cur = getCurrentAngle($gRotor[0]);
            $gRotor.removeClass('fast-spin')
                .css({ animation:'none', transition:'none', transform:`rotate(${cur}deg)` });

            // просчитать цель и плавно затормозить
            const targetMid = 360 - (winIndex * sectorAngle);
            const delta     = (targetMid - (cur % 360) + 360) % 360;
            const total     = cur + DECEL_TURNS * 360 + delta;

            requestAnimationFrame(() => {
                $gRotor.css({ transition:`transform ${DECEL_MS}ms cubic-bezier(.05,.9,0,1)`, transform:`rotate(${total}deg)` });
            });

            setTimeout(() => {
                const final = total % 360;
                $gRotor.css({ transition:'none', transform:`rotate(${final}deg)` });
                spinning = false;

                lastPrize = prize || { type:'nothing', value:0 };
                showResultMessage(lastPrize);

                if (lastPrize.type === 'bonus'){
                    pendingBonus = true;
                    $spinBtn.prop('disabled', false).text('Бонусный спин');

                    const spinId = $root.data('spin_id');
                    if (spinId) {
                        $.post(completeUrl, { spin_id: spinId, bonus: 1 });
                    }
                } else {
                    pendingBonus = false;
                    $spinBtn.prop('disabled', true);
                    setTimeout(showMvpModal, 5000); // показать модалку через 5с
                }
            }, DECEL_MS);
        }, wait);
    }

    // ---------- API / FLOW ----------
    function spinOnce(){
        if (spinning) return;
        spinning = true;
        spinStartedAt = performance.now();
        $spinBtn.prop('disabled', true);
        $resultBox.text('');

        resetRotorForNewSpin();
        startFastSpin(); // крутиться сразу

        $.post(spinUrl, { bonus: pendingBonus ? 1 : 0 }, function(res){
            const resp = typeof res === 'string' ? JSON.parse(res) : res;
            if (resp && resp.success){
                $root.data('spin_id', resp.spin_id || '');
                finalizeSpin(resp.index, resp.prize);
            } else {
                $root.data('spin_id', '');
                finalizeSpin(Math.floor(Math.random()*sectorsCount), { type:'nothing', value:0 });
            }
        }).fail(function(){
            finalizeSpin(Math.floor(Math.random()*sectorsCount), { type:'nothing', value:0 });
        });
    }

    function finishAndMark(){
        $.post(completeUrl, {
            spin_id: $root.data('spin_id') || '',
            bonus: pendingBonus ? 1 : 0
        });
        $root.remove();
    }

    function showMvpModal(){
        const $modal = $('#wheel-mvp-modal');
        if (!$modal.length) return;

        $modal.addClass('is-open').show();
        $('body').addClass('wheel-modal-open');

        // по ТЗ для MVP: фиксируем завершение сразу при показе
        finishAndMark();

        const $close = $('#wheel-close-all');
        function doClose(){
            $modal.removeClass('is-open');
            $('body').removeClass('wheel-modal-open');
            $('#wheelSpinBtn').closest('.wheel__actions').remove();
            $modal.off('click.wheelOverlay');
            $(document).off('keydown.wheelEsc');
        }
        $close.one('click', doClose);
        $modal.on('click.wheelOverlay', function(e){ if (e.target === this) doClose(); });
        $(document).on('keydown.wheelEsc', function(e){
            const key = e.key || e.keyCode || e.which;
            if (key === 'Escape' || key === 27) doClose();
        });
    }

    // ---------- INIT ----------
    if ($hubFill.length) $hubFill.attr('r','26'); // лёгкая коррекция хаба
    buildSectors();
    buildRimDots();
    drawPointer();
    $spinBtn.on('click', spinOnce);
});
