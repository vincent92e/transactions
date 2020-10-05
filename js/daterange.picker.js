/**
 * @file
 * Javascript for member_photo module.
 */
(function ($) {

  //Enable tooltip
  Drupal.behaviors.daterange = {
    attach: function (context) {
      jQuery(function () {
        jQuery('input[name="daterange"]').daterangepicker({
          "locale": {
            "format": "DD/MM/YYYY",
          },
        })
      });

      jQuery(document).unbind('click').on('click', 'button.applyBtn', function() {
        setTimeout(function(){
          jQuery('.daterangesubmit').mousedown(); // .click() doesn't work on buttons with ajax attached so use mousedown() instead.
        });
      });
    }
  };

})(jQuery);
