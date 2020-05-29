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

/* code to display or not grade information */

function dispGrade(item) {
    grade = item.alt;
    var offsets = item.getBoundingClientRect();
    var bottom = Math.round(window.innerHeight - offsets.top-15) + "px";
    var right = Math.round(offsets.left + 45) + "px";

    var x;
    switch (grade) {
        case 'Easy Access':
            x = document.getElementById("grade-ea");
            break;
        case 'Easy':
            x = document.getElementById("grade-e");
            break;
        case 'Leisurely':
            x = document.getElementById("grade-l");
            break;
        case 'Moderate':
            x = document.getElementById("grade-m");
            break;
        case 'Strenuous':
            x = document.getElementById("grade-s");
            break;
        case 'Technical':
            x = document.getElementById("grade-t");
            break;
        default:
            return;
    }
    if (x != null) {
        x.style.visibility = "visible";
        x.style.bottom = bottom;
        x.style.left = right;
    }
}

function noGrade(item) {
    grade = item.alt;
    x = document.getElementById("grade-ea");
    x.style.visibility = "hidden";
    x = document.getElementById("grade-e");
    x.style.visibility = "hidden";
    x = document.getElementById("grade-l");
    x.style.visibility = "hidden";
    x = document.getElementById("grade-m");
    x.style.visibility = "hidden";
    x = document.getElementById("grade-s");
    x.style.visibility = "hidden";
    x = document.getElementById("grade-t");
    x.style.visibility = "hidden";
}