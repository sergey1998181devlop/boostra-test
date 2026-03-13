$.fn.countdown = function (duration) {
    var container = $(this[0]).html(format (duration));
    var countdown = setInterval(function () {
        if (--duration) {
            container.html(format (duration));
        } else {
            reset();
        }
    }, 1000);

    function reset () {
        clearInterval(countdown);
    }

    function format (time) {
        return leadZero(parseInt(time / 60)) + ":" + leadZero(parseInt(time % 60));
    }

    function leadZero (s) {
        if (s < 10)
            return '0' + s;
        return s;
    }
};