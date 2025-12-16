'use strict';
(function ($) {
  $(function () {
    var $input = $('#faq-search-input');
    if (!$input.length || !$.fn.autocomplete) return;
    if ($input.data('faqAutocompleteInit')) return;
    $input.data('faqAutocompleteInit', true);

    var scope = $input.data('scope') || (
      $input.closest('#private').length || location.pathname.indexOf('/user/faq') === 0 || location.pathname.indexOf('/user') === 0
        ? 'user'
        : 'public'
    );
    var serviceUrl = $input.data('serviceUrl') || (scope === 'user' ? '/user/faq?action=search' : '/faq?action=search');

    var $wrap = $input.closest('.faq-search');
    var $notice = $wrap.find('.faq-no-suggest');
    if (!$notice.length) {
      $notice = $('<div class="faq-no-suggest">Ничего не найдено</div>');
      $wrap.append($notice);
    }

    $input.autocomplete({
      serviceUrl: serviceUrl,
      paramName: 'query',
      params: { scope: scope },
      minChars: 3,
      deferRequestBy: 600,
      noCache: true,
      triggerSelectOnValidInput: false,
      onSearchStart: function () { $wrap.addClass('loading'); $notice.hide(); },
      onSearchComplete: function () {
        $wrap.removeClass('loading');
        if (($input.val() || '').length < 3) return $notice.hide();
        var inst = $input.data('autocomplete');
        var list = (inst && Array.isArray(inst.suggestions)) ? inst.suggestions : [];
        if (!list.length) $notice.show(); else $notice.hide();
      },
      onSearchError: function () { $wrap.removeClass('loading'); if (($input.val() || '').length >= 3) $notice.show(); },
      onSelect: function (s) {
        if (s && s.data && s.data.url) window.location.href = s.data.url;
        $notice.hide();
      }
    });

    $input.on('input blur', function(){
      var inst = $input.data('autocomplete');
      if (inst && inst.clearCache) inst.clearCache();
      if (($input.val() || '').length < 3) $notice.hide();
    });
  });
})(jQuery);
