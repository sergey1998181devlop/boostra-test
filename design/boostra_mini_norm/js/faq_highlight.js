// Инициализация подсветки и tooltip FAQ
// Использование: initFaqHighlight({enabled: true, delay: 10});

function initFaqHighlight({ enabled, delay }) {
    if (!enabled) return;

    const SHOWN_KEY = 'faq_highlight_shown';
    let tooltipClosed = localStorage.getItem(SHOWN_KEY) === "1";
    if (tooltipClosed) return;

    const DELAY_MINUTES = delay || 10;
    const VISIT_KEY = 'visit_ts';
    const now = Date.now();

    let start = parseInt(localStorage.getItem(VISIT_KEY) || 0, 10);
    if (!start) {
        localStorage.setItem(VISIT_KEY, now.toString());
        start = now;
    }
    const elapsed = (now - start) / 60000;

    let tooltip = null;

    function isMobile() {
        return window.innerWidth <= 768;
    }

    let faqLink = null;
    if (isMobile()) {
        faqLink = document.getElementById('faq-link-mobile');
    } else {
        faqLink = document.getElementById('faq-link');
    }

    function getTargetElement() {
        return isMobile()
            ? document.getElementById('mobile-menu')
            : document.getElementById('faq-link');
    }

    function createTooltip() {
        tooltip = document.createElement('div');
        tooltip.className = 'faq-tooltip';
        tooltip.innerHTML = 'Есть вопросы? Ответы — <a href="/user/faq">здесь</a> <button class="faq-tooltip-close">&times;</button>';
        document.body.appendChild(tooltip);
        tooltip.querySelector('.faq-tooltip-close').onclick = function () {
            hideTooltip();

            tooltipClosed = true;
            if (faqLink) faqLink.classList.remove('faq-highlight');

            localStorage.setItem(SHOWN_KEY, "1");
        };

        if (faqLink) faqLink.addEventListener('click', hideTooltip);
    }

    function positionTooltip() {
        if (!tooltip) return;

        const target = getTargetElement();

        if (!target) return;

        const r = target.getBoundingClientRect();

        if (isMobile()) {
            tooltip.style.left = (r.right + window.scrollX - tooltip.offsetWidth) + 'px';
            tooltip.style.top = (r.bottom + window.scrollY + 24) + 'px';
        } else {
            tooltip.style.left = (r.left + (r.width / 2) + window.scrollX) + 'px';
            tooltip.style.top = (r.bottom + window.scrollY + 8) + 'px';
        }

        tooltip.classList.add('visible');
        if (faqLink) faqLink.classList.add('faq-highlight');
    }

    function showTooltip(isDelay = true) {
        if (tooltipClosed) return;

        if (!tooltip) {
            createTooltip();

            if (isDelay) positionTooltip();

            window.addEventListener('resize', positionTooltip);
            window.addEventListener('load', positionTooltip);
        }
    }

    function hideTooltip() {
        if (tooltip) tooltip.classList.remove('visible');
    }

    const mobileMenuOpenBtn = document.getElementById('mobile-menu-open');
    if (mobileMenuOpenBtn) {
        mobileMenuOpenBtn.addEventListener('click', function() {
            if (!tooltipClosed) {
                setTimeout(function () {
                    hideTooltip();
                }, 100);
            }
        });
    }

    const mobileMenuCloseBtn = document.getElementById('mobile-menu-close');
    if (mobileMenuCloseBtn) {
        mobileMenuCloseBtn.addEventListener('click', function() {
            if (!tooltipClosed) {
                setTimeout(function () {
                    positionTooltip();
                }, 100);
            }
        });
    }

    if (elapsed >= DELAY_MINUTES) {
        showTooltip(false);
    } else {
        setTimeout(showTooltip, (DELAY_MINUTES - elapsed) * 60000);
    }
}