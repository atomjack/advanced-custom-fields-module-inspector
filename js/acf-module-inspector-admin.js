(function($) {
  $(document).ready(function() {
    $('#inspect-button').on('click', function(e) {
      e.preventDefault();
      $('.acf-module-inspector-results').empty();
      var $button = $(this);
      $button.data("old-title", $button.val());
      $button.val('Running');
      $button.attr('disabled', 'disabled');
      $.ajax({
        url: ACFModuleInspector.ajaxUrl,
        type: 'POST',
        dataType: 'html',
        data: {
          action: 'acf_module_inspector_inspect'
        },
        success: function(html) {
          $button.removeAttr('disabled');
          $button.val($button.data('old-title'));
          $('.acf-module-inspector-results').hide();
          $('.acf-module-inspector-results').html(html).slideDown();
        }
      });
    });
    $('body').on('click', '.acf-module-inspector-results ul.groups > li > h3', function(e) {
      // if($(this).find('i').hasClass('plus'))
      //   $(this).find('i').removeClass('plus').addClass('minus');
      // else
      //   $(this).find('i').removeClass('minus').addClass('plus');
      var $el = $(this);
      $(this).parent().find('ul.fields').slideToggle(function() {
        afterSlide($el);
      });
    });

    $('body').on('click', '.acf-module-inspector-results ul.groups > li > ul.fields li h4', function(e) {
      // if($(this).find('i').hasClass('plus'))
      //   $(this).find('i').removeClass('plus').addClass('minus');
      // else
      //   $(this).find('i').removeClass('minus').addClass('plus');
      var $el = $(this);
      $(this).parent().find('ul.modules').slideToggle(function() { afterSlide($el); });
    });

    $('body').on('click', '.acf-module-inspector-results ul.groups > li > ul.fields li > ul.modules h5:not(.empty)', function(e) {
      var $el = $(this);
      $(this).parent().find('ul.urls').slideToggle(function() { afterSlide($el); });
    });


  });

  function afterSlide($el) {
    console.log("after slide: ", $el);
    if(!$el.find('i').hasClass('minus'))
      $el.find('i').addClass('minus');
    else
      $el.find('i').removeClass('minus');
  }
})(jQuery);
