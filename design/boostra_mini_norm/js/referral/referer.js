$(document).ready(function () {
    (function ($) {
        const root = document.getElementById('ref-px-banner');
        if (!root) return;

        const $btn = $('.js-ref-copy');
        if (!$btn.length) return;

        const url = root.getAttribute('data-link') || '';

        const $icon = $('.ref-linkpill__icon');
        const $check = $('.ref-check');

        const ua = (navigator.userAgent || '').toLowerCase();
        const isMobile = /android|iphone|ipad|ipod|mobile/.test(ua) || (window.matchMedia && matchMedia('(pointer:coarse)').matches);

        function feedback(ok) {
            $btn.css({ transition: 'background .15s ease', background: ok ? '#2DBD6E' : '#D93025' });
            setTimeout(function () { $btn.css({ background: '#168BFF' }); }, 900);
        }

        function blink() {
            $icon.hide();
            $check.show();
            setTimeout(function () { $check.hide(); $icon.show(); }, 1000);
        }

        // Копирование без Clipboard API
        function copyExecCommand(value) {
            const ta = document.createElement('textarea');
            ta.value = value;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            ta.setSelectionRange(0, 99999);
            let ok = false;
            try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
            document.body.removeChild(ta);
            return ok;
        }

        // Фолбэк — превращаем кнопку в ссылку
        function turnIntoLink() {
            if (!$btn.length) return;
            const a = document.createElement('a');
            a.href = (/^https?:\/\//i.test(url)) ? url : ('//' + url.replace(/^\/\//, ''));
            a.target = '_blank';
            a.rel = 'noopener';
            a.className = $btn[0].className.replace('js-ref-copy', '').trim();
            a.innerHTML = $btn.html();
            $btn.replaceWith(a);
        }

        // Десктоп: сразу копируем (без разрешений), при фейле — превращаем в ссылку
        if (!isMobile) {
            $btn.on('click', function () {
                if (!url) return;
                blink();
                const ok = copyExecCommand(url);
                feedback(ok);
                if (!ok) turnIntoLink();
            });
            return;
        }

        // Мобила: сперва share, затем копирование, затем ссылка
        $btn.on('click', function () {
            if (!url) return;
            blink();

            if (navigator.share) {
                const shareUrl = /^https?:\/\//i.test(url) ? url : 'https://' + url; // только для share
                navigator.share({ title: 'Возьмите скидку', text: 'Моя реферальная ссылка', url: shareUrl })
                    .then(function () { feedback(true); })
                    .catch(function () {
                        const ok = copyExecCommand(url);
                        feedback(ok);
                        if (!ok) turnIntoLink();
                    });
                return;
            }

            const ok = copyExecCommand(url);
            feedback(ok);
            if (!ok) turnIntoLink();
        });

    })(jQuery);
});
