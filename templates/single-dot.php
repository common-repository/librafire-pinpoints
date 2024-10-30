<?php

/*
 * File Name: Single Dot
 * Plugin: LF-PinPoints
 * Author: LibraFire
 * Created: 2015-27-01
 */

echo '<div class="lf_single_dot_container" id="' . $single_dot->dot_id . '" style="left: '.$coordinates[0].'%; top: '.$coordinates[1].'%">';
    echo '<div class="relative-elem full-size">';
        echo '<span class="pulsating-circle"></span>';
    echo '</div>';
    echo '<input type="hidden" name="lf_custom_dot_pointer[]" class="lf_single_dot" value="' . $single_dot->position . '" />';
    echo '<div class="dot_controlls">';
        echo '<span data-parent="#' . $single_dot->dot_id . '" class="remove_dot">x</span>';
        echo '<span data-parent="#' . $single_dot->dot_id . '" class="minimize_dot">_</span>';
        wp_editor( $single_dot->captionText, 'caption_text_' . $single_dot->dot_id, array(
            'wpautop'       => true,
            'media_buttons' => false,
            'textarea_name' => 'meta_caption_text[' . $single_dot->dot_id . ']',
            'textarea_rows' => 10,
            'teeny'         => true
        ) );
    echo '</div>';
echo '</div>';

?>