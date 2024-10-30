<?php
/*
 * Plugin Name: LF PinPoints
 * Version: 1.1.6
 * Plugin URI: http://www.librafire.com
 * Description: This is the plugin that allows you to place a how many dots you want in container.
 * Author: LibraFire
 * Author URI: http://www.librafire.com
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: lf-pinpoints
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author LibraFire
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

// Load plugin class files
require_once('includes/class-lf-pinpoints.php');
require_once('includes/class-lf-pinpoints-settings.php');

// Load plugin libraries
require_once('includes/lib/class-lf-pinpoints-admin-api.php');
require_once('includes/lib/class-lf-pinpoints-post-type.php');
require_once('includes/lib/class-lf-pinpoints-taxonomy.php');

/**
 * Returns the main instance of LF_PinPoints to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object LF_PinPoints
 */
function LF_PinPoints()
{
    $instance = LF_PinPoints::instance(__FILE__, '1.0.0');

    if (is_null($instance->settings)) {
        $instance->settings = LF_PinPoints_Settings::instance($instance);
    }

    return $instance;


}

LF_PinPoints();

define('ROOT', plugins_url('', __FILE__));
define('STYLES', ROOT . '/css/');
define('SCRIPTS', ROOT . '/js/');
define('TEMPLATES', plugin_dir_path(__FILE__) . "/templates/");
$_nonce = '';

/*Now we add the meta boxes to the services*/
if (!is_edit_page('new')) {
    add_action('add_meta_boxes', 'add_pinpoints');
}
function add_pinpoints()
{

    $register_on_post_types = array();

    $active_for_post_types = get_settings('lf_register_on_post_type');

    if ($active_for_post_types != '') {
        $register_on_post_types = $active_for_post_types;
    }

    if (empty($register_on_post_types))
        array_push($register_on_post_types, 'post');

    foreach ($register_on_post_types as $registered_post_type) {
        add_meta_box('wpt_pinpoints', __('Pin points'), 'wpt_pinpoints', $registered_post_type, 'normal', 'high');
    }

}

$validShortcode = '';

$shortcodeNames = array(
    'points',
    'pinpoints',
    'lfpoints',
    'custompoints',
    'customlibrafirepoints'
);

foreach ($shortcodeNames as $shortcode) {
    if (!shortcode_exists($shortcode)) {
        global $validShortcode;
        $validShortcode = $shortcode;
        add_shortcode($shortcode, 'generate_points_frontend');
        break;
    }
}

function wpt_pinpoints()
{
    global $post, $validShortcode;

    ?>
    <div class="wpt_pinpoints clearfix">
        <?php

        // Noncename needed to verify where the data originated
        echo '<input type="hidden" name="wpt_pinpoints_noncename" id="wpt_pinpoints_noncename" value="' .
            wp_create_nonce('post_dots_nonce_' . $post->ID) . '" />';

        $dots_json = get_post_meta($post->ID, 'lf_post_dots_' . $post->ID, true);
        $dots_as_json = json_decode($dots_json);

        $post_dots_color = $dots_as_json->dots_color[0]->value;
        $post_dots_bg_color = $dots_as_json->dots_bg_color[0]->value != null || $dots_as_json->dots_bg_color[0]->value != '' ? $dots_as_json->dots_bg_color[0]->value : get_option('lf_caption_text_color');
        $preview_image_src = $dots_as_json->dots_image[0]->value;
        $post_dots_size = $dots_as_json->dots_scale[0]->value != null || $dots_as_json->dots_scale[0]->value != '' ? $dots_as_json->dots_scale[0]->value : get_option('lf_default_dot_size');

        $visible = empty($dots_as_json) ? false : true;

        $disabled = !$visible ? 'disabled' : '';
        $class = !$visible ? 'conditional_show' : '';
        ?>

        <div class="pinpoints-left">
            <div id="step-1" class="updated-lf fade">
                <h2><span class="dashicons dashicons-upload"></span> <?php _e('Background image', 'lf-pinpoints'); ?>
                    <span class="step-1 dashicons dashicons-info pull-right"></span></h2>
                <input id="upload_image_button" class="button button-primary button-large" type="button"
                       value="<?php _e('Set background image', 'lf-pinpoints'); ?>">
            </div>
            <div id="step-2" class="updated-lf fade disabled">
                <h2><span class="dashicons dashicons-plus"></span> <?php _e('Add a new dot', 'lf-pinpoints'); ?></h2>
                <input id="add_more_dots" class="button button-primary button-large" <?php echo $disabled; ?>
                       type="button" value="<?php _e('Add a new dot', 'lf-pinpoints'); ?>">
            </div>
            <div id="step-3" class="updated-lf fade disabled">
                <h2><span
                        class="dashicons dashicons-admin-customizer"></span> <?php _e('Dots color', 'lf-pinpoints'); ?>
                </h2>
                <input id="lf_single_dot_color" name="color_options[color]"
                       data-default-color="<?php echo $post_dots_color; ?>" type="text" value=""/>
            </div>
            <div id="step-4" class="updated-lf fade disabled">
                <h2><span class="dashicons dashicons-marker"></span> <?php _e('Dots size (px)', 'lf-pinpoints'); ?></h2>
                <input id="dots_scale" name="dots_scale" type="number" step="1" min="1"
                       value="<?php echo $post_dots_size == NULL ? 20 : $post_dots_size; ?>"/>
            </div>
            <div id="step-5" class="updated-lf fade disabled">
                <h2><span
                        class="dashicons dashicons-format-image"></span> <?php _e('Caption background color', 'lf-pinpoints'); ?>
                    <span class="step-5 dashicons dashicons-info pull-right"></span></h2>
                <input id="dots_bg_color" name="bg_color_options[color]"
                       data-default-color="<?php echo $post_dots_bg_color; ?>" type="text" value=""/>
            </div>
            <div id="step-6" class="updated-lf fade disabled">
                <h2>
                    <button id="save_post_dots" type="button" class="button button-primary button-large">
                        <span class="dashicons dashicons-yes"></span>
                        <?php _e('Save dots', 'lf-pinpoints'); ?>
                    </button>
                </h2>

            </div>
            <div class="clearfix">
                <div id="message" class="updated-lf-success notice-lf notice-success is-dismissible">
                    <p><?php _e('Points saved. <br /> Place this shortcode in the text editor. <br />Shortcode:', 'lf-pinpoints'); ?>
                        <input type="text" value="[<?php echo $validShortcode; ?>]" readonly
                               onClick="this.select();"></b></p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php _e('Dismiss this notice.', 'lf-pinpoints'); ?></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="pinpoints-right">
            <input type="hidden" name="post_dots_color" id="post_dots_color" value="<?php echo $post_dots_color; ?>"/>
            <input type="hidden" name="post_dots_color_bg" id="post_dots_color_bg"
                   value="<?php echo $post_dots_bg_color; ?>"/>

            <div class='dots-container'>
                <div class='isa_success response-message'><i class='fa fa-check'></i></div>
                <img id='preview_image' class="<?php echo $class; ?>" src="<?php echo $preview_image_src; ?>"/>
                <input class="<?php echo $class; ?>" type='hidden' name='preview_image_src' id='preview_image_src'
                       value="<?php echo $preview_image_src; ?>"/>
                <?php
                if (!empty($dots_as_json)) {
                    foreach ($dots_as_json->dots_json as $single_dot) {
                        // Get a dot coordinates from JSON
                        $coordinates = explode("|", $single_dot->position);
                        // Get a template file
                        require(TEMPLATES . 'single-dot.php');
                    }
                }
                ?>

            </div>
        </div>
    </div>
    <?php
    require(TEMPLATES . 'admin-css-dynamic.php');
}

add_action('registered_post_type', 'modify_post_type_post', 99, 2);

function modify_post_type_post($post_type, $args)
{
    if ('post' != $post_type)
        return;
    $args->register_meta_box_cb = 'add_services_metaboxes';
    $wp_post_types[$post_type] = $args;
}

function generate_points_frontend()
{
    global $post;

    $dots_json = get_post_meta($post->ID, 'lf_post_dots_' . $post->ID, true);
    $dots_as_json = json_decode($dots_json);

    $post_dots_color = $dots_as_json->dots_color[0]->value;
    $post_dots_bg_color = $dots_as_json->dots_bg_color[0]->value;
    $preview_image_src = $dots_as_json->dots_image[0]->value;
    $post_dots_size = $dots_as_json->dots_scale[0]->value;
    $caption_text_size = get_option('lf_caption_text_font_size');
    $caption_background_opacity = get_option('lf_default_dot_caption_opacity');

    $return = "<div class='dots-container'>";
    $return .= "<img id='preview_image' src='" . $preview_image_src . "' />";

    if (!empty($dots_as_json)) {
        foreach ($dots_as_json->dots_json as $dotOptions) {

            $coordinates = explode("|", $dotOptions->position);

            ob_start();
            include(TEMPLATES . '/single-dot-frontend.php');
            $return .= ob_get_contents();
            ob_end_clean();
        }
    }
    $return .= "</div>";

    ob_start();
    include(TEMPLATES . '/frontend-css-dynamic.php');
    $return .= ob_get_contents();
    ob_end_clean();

    return $return;
}

function save_dots_data()
{

    $dots_json = stripslashes($_POST['dots']);
    $post_id = $_POST['post_id'];
    $key = 'lf_post_dots_' . $post_id;

    $valid_nonce = check_ajax_referer('post_dots_nonce_' . $post_id, 'security', true);


    if (get_post_meta($post_id, $key, FALSE)) {
        // If the custom field already has a value
        $response = update_post_meta($post_id, $key, $dots_json);
    } else {
        // If the custom field doesn't have a value
        $response = add_post_meta($post_id, $key, $dots_json);
    }
    if (!$dots_json) delete_post_meta($post_id, $key); // Delete if blank

    die('saved');
}

add_action('wp_ajax_save_dots', 'save_dots_data');


/**
 * is_edit_page
 * function to check if the current page is a post edit page
 *
 * @author Ohad Raz <admin@bainternet.info>
 *
 * @param  string $new_edit what page to check for accepts new - new post page ,edit - edit post page, null for either
 * @return boolean
 */
function is_edit_page($new_edit = null)
{
    global $pagenow;
    //make sure we are on the backend
    if (!is_admin()) return false;


    if ($new_edit == "edit")
        return in_array($pagenow, array('post.php',));
    elseif ($new_edit == "new") //check for new post page
        return in_array($pagenow, array('post-new.php'));
    else //check for either new or edit
        return in_array($pagenow, array('post.php', 'post-new.php'));
}