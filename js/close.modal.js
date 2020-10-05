/**
 * @file
 * Javascript for vincent theme.
 */
(function ($) {

  Drupal.behaviors.close = {
    attach: function (context) {
      jQuery('.payyed-modal-close').once('auto-close-modal').on('click', function() {
        setTimeout(function(){
          jQuery('.ui-dialog-titlebar-close').click();
        });
      });

      jQuery('a.allfilters').on('click', function () {
        jQuery('.transaction-types').toggle();
      });
    }
  };
})(jQuery);
