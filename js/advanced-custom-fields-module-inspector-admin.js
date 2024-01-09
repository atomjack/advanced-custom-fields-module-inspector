(function ($) {
    class ACFModuleInspector {
        constructor() {
            this.bindEvents();
        }

        bindEvents() {
            $('#inspect-button').on('click', this.refresh.bind(this));
            $('.refresh-cache').on('click', this.refresh.bind(this, true));
            
            $('body').on(
                'click',
                '.advanced-custom-fields-module-inspector-results ul.groups > li > h3',
                function (e) {
                    // if($(this).find('i').hasClass('plus'))
                    //   $(this).find('i').removeClass('plus').addClass('minus');
                    // else
                    //   $(this).find('i').removeClass('minus').addClass('plus');
                    var $el = $(this);
                    $(this)
                        .parent()
                        .find('ul.fields')
                        .slideToggle(function () {
                            afterSlide($el);
                        });
                }
            );
    
            $('body').on(
                'click',
                '.advanced-custom-fields-module-inspector-results ul.groups > li > ul.fields li h4',
                function (e) {
                    // if($(this).find('i').hasClass('plus'))
                    //   $(this).find('i').removeClass('plus').addClass('minus');
                    // else
                    //   $(this).find('i').removeClass('minus').addClass('plus');
                    var $el = $(this);
                    $(this)
                        .parent()
                        .find('ul.modules')
                        .slideToggle(function () {
                            afterSlide($el);
                        });
                }
            );
    
            $('body').on(
                'click',
                '.advanced-custom-fields-module-inspector-results ul.groups > li > ul.fields li > ul.modules h5:not(.empty)',
                function (e) {
                    var $el = $(this);
                    $(this)
                        .parent()
                        .find('ul.urls')
                        .slideToggle(function () {
                            afterSlide($el);
                        });
                }
            );
        }

        refresh(e) {
            const self = this;
            e.preventDefault();
            const force = $(e.currentTarget).hasClass('refresh-cache') ? 1 : 0;
            $('.advanced-custom-fields-module-inspector-results').empty();
            var $button = $(e.currentTarget);
            $button.data('old-title', $button.val());
            $button.val('Running');
            $button.attr('disabled', 'disabled');
            $.ajax({
                url: ACF_Module_Inspector.ajaxUrl,
                type: 'POST',
                dataType: 'html',
                data: {
                    action: 'acf_module_inspector_inspect',
                    force: force
                },
                success: function (html) {
                    $button.removeAttr('disabled');
                    $button.val($button.data('old-title'));
                    $(
                        '.advanced-custom-fields-module-inspector-results'
                    ).hide();
                    $('.advanced-custom-fields-module-inspector-results')
                        .html(html)
                        .slideDown();
                    $('.refresh-cache').on('click', self.refresh.bind(self));
                },
            });
        }
    }

    $(function () {
        new ACFModuleInspector();
    });

    function afterSlide($el) {
        if (!$el.find('i').hasClass('minus')) $el.find('i').addClass('minus');
        else $el.find('i').removeClass('minus');
    }
})(jQuery);
