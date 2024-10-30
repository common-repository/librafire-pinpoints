/**
 * Created by LibraFire on 12/28/2015.
 */
jQuery(function ($) {

    var cpOptions = {
        // you can declare a default color here,
        // or in the data-default-color attribute on the input
        defaultColor: '123423',
        // a callback to fire whenever the color changes to a valid color
        change: function (event, ui) {

            var changedColor = ui.color.toString();
            $("#post_dots_color").val( changedColor );

            $(".lf_single_dot_container").stop().animate({
                'background-color': changedColor + " !important"
            });
            $(".pulsating-circle").stop().animate({
                'border-color': changedColor + " !important"
            });

            setTimeout(function () {
                if ($("#lf_single_dot_color").val() != "" && $("#step-4").hasClass('disabled')) {
                    $("#step-4").removeClass('disabled');
                    $("#step-5").removeClass('disabled');
                    $("#step-6").removeClass('disabled');
                }
            });
        },
        // a callback to fire when the input is emptied or an invalid color
        clear: function () {
        },
        // hide the color picker controls on load
        hide: true,
        // show a group of common colors beneath the square
        // or, supply an array of colors to customize further
        palettes: true
    };

    var cpOptionsBg = {
        // you can declare a default color here,
        // or in the data-default-color attribute on the input
        defaultColor: '123423',
        // a callback to fire whenever the color changes to a valid color
        change: function (event, ui) {
            var changedColor = ui.color.toString();
            $("#tinymce").stop().animate({
                'background-color': changedColor + " !important"
            });
            $("#post_dots_color_bg").val( changedColor );
        },
        // a callback to fire when the input is emptied or an invalid color
        clear: function () {
        },
        // hide the color picker controls on load
        hide: true,
        // show a group of common colors beneath the square
        // or, supply an array of colors to customize further
        palettes: true
    };

    if (typeof jQuery.wp === 'object' && typeof jQuery.wp.wpColorPicker === 'function') {

        var $the_colorpicker = jQuery('#lf_single_dot_color').wpColorPicker(cpOptions);
        var $the_bg_colorpicker = jQuery('#dots_bg_color').wpColorPicker(cpOptionsBg);

        var new_color = jQuery("#post_dots_color").val();
        var new_color_bg = jQuery("#post_dots_color_bg").val();

        $the_colorpicker.wpColorPicker('color', new_color);
        $the_bg_colorpicker.wpColorPicker('color', new_color_bg);

        $(".lf_single_dot_container").show();

    }

    else {

        //We use farbtastic if the WordPress color picker widget doesn't exist
        jQuery('#colorpicker_bg').farbtastic('#fffff');

    }
});