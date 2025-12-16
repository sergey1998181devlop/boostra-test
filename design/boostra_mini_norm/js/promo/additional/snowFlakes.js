(function ($) {
    alert(1)
    $.fn.snowit = function (options) {
        var $flake = $('<div class="lis-flake" />').css({'top': '-50px', 'position': 'absolute'}).html('&#10052;'),
            documentHeight = $('#collectionPromo').height();
            documentWidth = $('#collectionPromo').width(),
            defaults = {
                minSize: 10,
                maxSize: 20,
                total: 25,
                speed: documentHeight / 105,
                flakeColor: "#0997ff"
            },
            options = $.extend({}, defaults, options),
            inStyle = '<style>#collectionPromo { position: relative; }.lis-flake { position: absolute; color:#ff0000;}.lis-flake:nth-child(odd) {-moz-animation:snow1 ' + options.speed + 's linear infinite;-webkit-animation:snow1 ' + options.speed + 's linear infinite;animation:snow1 ' + options.speed + 's linear infinite}.lis-flake:nth-child(even) {-moz-animation:snow2 ' + (options.speed - (options.speed / 8)) + 's linear infinite;-webkit-animation:snow2 ' + (options.speed - (options.speed / 8)) + 's linear infinite;animation:snow2 ' + (options.speed - (options.speed / 8)) + 's linear infinite}@-moz-keyframes snow1{0%{-moz-transform:translate(-250, 0);opacity:1}100%{-moz-transform:translate(250px, ' + documentHeight + 'px);opacity:0}}@-webkit-keyframes snow1{0%{-webkit-transform:translate(-250, 0);opacity:1}100%{-webkit-transform:translate(250px, ' + documentHeight + 'px);opacity:0}}@keyframes snow1{0%{transform:translate(-250, 0);opacity:1}100%{transform:translate(250px, ' + documentHeight + 'px);opacity:0}}@-moz-keyframes snow2{0%{-moz-transform:translate(0, 0);opacity:1}100%{-moz-transform:translate(0, ' + documentHeight + 'px);opacity:0.2}}@-webkit-keyframes snow2{0%{-webkit-transform:translate(0, 0);opacity:1}100%{-webkit-transform:translate(0, ' + documentHeight + 'px);opacity:0.2}}@keyframes snow2{0%{transform:translate(0, 0);opacity:1}100%{transform:translate(0, ' + documentHeight + 'px);opacity:0.2}}</style>';

        var flakes = function () {
            var startPositionLeft = Math.random() * documentWidth - 50,
                startPositionTop = 0 - (Math.random() * documentHeight - 40);
            var startOpacity = 0.8 * Math.random(),
                num = parseInt($('.lis-flake').length) + 1,
                sizeFlake = options.minSize + Math.random() * options.maxSize;

            $flake
                .attr('num', num)
                .clone()
                .appendTo('div#collectionPromo')
                .css(
                    {
                        left: startPositionLeft,
                        top: startPositionTop,
                        opacity: startOpacity,
                        'font-size': sizeFlake,
                        color: options.flakeColor
                    }
                );
        }

        // Apply style to head
        $('head').append(inStyle);

        for (var i = 1; i <= options.total; i++) {
            flakes();
        }
    };
})(jQuery);