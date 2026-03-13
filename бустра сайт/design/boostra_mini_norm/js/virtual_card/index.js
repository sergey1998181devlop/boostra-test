(function () {
    const el = document.getElementById('virCardData');
    const jq = window.jQuery;

    setTimeout(function() {
        const script = document.createElement('script');
        const randomVersion = Math.floor(Math.random() * 1000000); // random int

        script.src = `/design/${el.dataset.theme}/js/virtual_card/built/index.js?v=${randomVersion}`;
        script.defer = true;
        script.onload = function() {
            // Restore $ in global scope
            window.$ = jq;
        };
        document.body.appendChild(script);
    }, 100);
})()
