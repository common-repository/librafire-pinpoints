<?php

/*
 * File Name: Single Dot Frontend
 * Plugin: LF-PinPoints
 * Author: LibraFire
 * Created: 2015-27-01
 */
?>
<div class="lf_single_dot_container"
     style="
         left: <?php echo $coordinates[0]; ?>%;
         top: <?php echo $coordinates[1]; ?>%;
         background-color: <?php echo $post_dots_color; ?>;">
    <div class="relative-elem full-size">
        <span class="pulsating-circle"></span>
    </div>
    <input type="hidden" name="lf_custom_dot_pointer[]" class="lf_single_dot" value="<?php echo $dotOptions->position; ?>" />
    <?php if( $dotOptions->captionText != '' ) : ?>
        <div class="caption_text">
            <?php echo ($dotOptions->captionText); ?>
        </div>
    <?php endif; ?>
</div>