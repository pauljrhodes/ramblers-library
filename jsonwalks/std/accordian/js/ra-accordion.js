/*------------------------------------------------------------------------
 # JoomShaper Accordion Module by JoomShaper.com
 # ------------------------------------------------------------------------
 # author    JoomShaper http://www.joomshaper.com
 # Copyright (C) 2010 - 2014 JoomShaper.com. All Rights Reserved.
 # @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://www.joomshaper.com
 -------------------------------------------------------------------------*/

!(function ($) {

    $.fn.raAccordion = function (options) {

        var settings = $.extend({
            hidefirst: 0
        }, options);

        return this.each(function () {

            var $items = $(this).find('>div');
            var $handlers = $items.find('.toggler');
            var $panels = $items.find('.ra-accordion-container');

            if (settings.hidefirst === 1)
            {
                $panels.hide().first();
            } else
            {
                $handlers.first().addClass('active');
                $panels.hide().first().slideDown();
            }

            $handlers.on('click', function () {

                if ($(this).hasClass('active'))
                {
                    $(this).removeClass('active');
                    $panels.slideUp();
                } else
                {
                    $handlers.removeClass('active');
                    $panels.slideUp();
                    $(this).addClass('active').parent().find('.ra-accordion-container').slideDown();
                }

                event.preventDefault();
            });

        });
    };

})(jQuery);

