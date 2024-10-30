<style type='text/css'>
    body div.lf_single_dot_container{
        width: <?php echo $post_dots_size; ?>px;
        height: <?php echo $post_dots_size; ?>px;
        border-radius: 50%;
    }
    div.lf_single_dot_container .pulsating-circle {
        border-style: solid;
        border-width: 2px;
    }
    div.lf_single_dot_container .pulsating-circle {
        border-color: <?php echo isset($post_dots_color) && $post_dots_color != '' ? $post_dots_color : '#ffffff'; ?>;
    }
</style>