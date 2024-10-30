<?php

if (!defined('ABSPATH')) exit;

class LF_PinPoints
{

    /**
     * The single instance of LF_PinPoints.
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;

    /**
     * Settings class object
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $settings = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * Constructor function.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function __construct($file = '', $version = '1.0.1')
    {
        $this->_version = $version;
        $this->_token = 'LF_PinPoints';

        // Load plugin environment variables
        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));

        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        register_activation_hook($this->file, array($this, 'install'));
        register_deactivation_hook($this->file, array($this, 'uninstall'));

        // Load frontend JS & CSS
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'), 10);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 10);

        // Load admin JS & CSS
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_styles'), 10, 1);

        // Load API for generic admin functions
        if (is_admin()) {
            $this->admin = new LF_PinPoints_API();
        }

        add_action('admin_init', array($this, 'librafire_pinpoints_admin_init'));
        add_action('admin_notices', array($this, 'librafire_pinpoints_admin_notices'));
        // Handle localisation
        $this->load_plugin_textdomain();
        add_action('init', array($this, 'load_localisation'), 0);
    } // End __construct ()

    function librafire_pinpoints_admin_notices()
    {
        if ($notices = get_option('librafire_pinpoints_deferred_admin_notices')) {
            foreach ($notices as $notice) {
                if( $notice['type'] == 'activated' )
                    echo "<div class='notice-info notice activated-lf is-dismissible'><p>{$notice['message']}</p></div>";
                else
                    echo "<div class='updated notice updated-lf is-dismissible'><p>{$notice['message']}</p></div>";
            }
            delete_option('librafire_pinpoints_deferred_admin_notices');
        }
    }
    public function librafire_pinpoints_admin_init()
    {
        $current_version = $this->_version;
        $version = get_option('librafire_pinpoints_version');
        if ($version != $current_version) {
            // Do whatever upgrades needed here.
            update_option('librafire_pinpoints_version', $current_version);
            $notices = get_option('librafire_pinpoints_deferred_admin_notices', array());
            $notices[] = array('message' => "LibraFire PinPoints: Upgraded version $version to $current_version.", 'type' => 'update');
            update_option('librafire_pinpoints_deferred_admin_notices', $notices);
        }
    }
    /**
     * Wrapper function to register a new post type
     * @param  string $post_type Post type name
     * @param  string $plural Post type item plural name
     * @param  string $single Post type item single name
     * @param  string $description Description of post type
     * @return object              Post type class object
     */
    public function register_post_type($post_type = '', $plural = '', $single = '', $description = '', $options = array())
    {

        if (!$post_type || !$plural || !$single) return;

        $post_type = new LF_PinPoints_Post_Type($post_type, $plural, $single, $description, $options);

        return $post_type;
    }

    /**
     * Load plugin textdomain
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function load_plugin_textdomain () {
        $domain = 'lf-pinpoints';

        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

        load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
    } // End load_plugin_textdomain ()

    /**
     * Wrapper function to register a new taxonomy
     * @param  string $taxonomy Taxonomy name
     * @param  string $plural Taxonomy single name
     * @param  string $single Taxonomy plural name
     * @param  array $post_types Post types to which this taxonomy applies
     * @return object             Taxonomy class object
     */
    public function register_taxonomy($taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array())
    {

        if (!$taxonomy || !$plural || !$single) return;

        $taxonomy = new LF_PinPoints_Taxonomy($taxonomy, $plural, $single, $post_types, $taxonomy_args);

        return $taxonomy;
    }

    /**
     * Load frontend CSS.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function enqueue_styles()
    {
        wp_register_style($this->_token . '-frontend', esc_url($this->assets_url) . 'css/frontend.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-frontend');
    } // End enqueue_styles ()

    /**
     * Load frontend Javascript.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function enqueue_scripts()
    {
        wp_register_script($this->_token . '-frontend', esc_url($this->assets_url) . 'js/frontend' . $this->script_suffix . '.js', array('jquery'), $this->_version);
        wp_enqueue_script($this->_token . '-frontend');

    } // End enqueue_scripts ()

    /**
     * Load admin CSS.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function admin_enqueue_styles($hook = '')
    {
        wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/admin.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-admin');

        wp_register_style($this->_token . '-multiselect', esc_url($this->assets_url) . 'css/jquery.multiselect.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-multiselect');
    } // End admin_enqueue_styles ()

    /**
     * Load admin Javascript.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function admin_enqueue_scripts($hook = '')
    {

        wp_register_script($this->_token . '-admin', esc_url($this->assets_url) . 'js/admin' . $this->script_suffix . '.js', array('jquery'), $this->_version, true);
        wp_enqueue_script($this->_token . '-admin');

        wp_register_script($this->_token . '-colorpicker', esc_url($this->assets_url) . 'js/colorPickerInit.js', array('jquery'), $this->_version);
        wp_enqueue_script($this->_token . '-colorpicker');

        wp_register_script($this->_token . '-multiselect', esc_url($this->assets_url) . 'js/jquery.multiselect.js', array('jquery'), $this->_version);
        wp_enqueue_script($this->_token . '-multiselect');

        wp_enqueue_script('jquery-ui-core');

        wp_enqueue_script('jquery-ui-draggable');

        wp_enqueue_style('wp-color-picker');

        wp_enqueue_script('wp-color-picker');

        wp_enqueue_style('wp-pointer');

        wp_enqueue_script('wp-pointer');

    } // End admin_enqueue_scripts ()

    /**
     * Load plugin localisation
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function load_localisation()
    {
        load_plugin_textdomain('lf-pinpoints', false, dirname(plugin_basename($this->file)) . '/lang/');
    } // End load_localisation ()

    /**
     * Main LF_PinPoints Instance
     *
     * Ensures only one instance of LF_PinPoints is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see LF_PinPoints()
     * @return Main LF_PinPoints instance
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    } // End instance ()

    /**
     * Main LF_PinPoints messages handler
     *
     * Ensures only one instance of LF_PinPoints is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see LF_PinPoints()
     * @return Main LF_PinPoints messages handler
     */
    public static function message($type)
    {
        if ($type == 'activated') {
            return "<div class='lf-activation-message'>
                    <h3>Well done! Plugin installed successfully!</h3>
                    <div class='lf-activation-message-description'>
                        Now head over <a href='" . admin_url("options-general.php?page=LF_PinPoints_settings") . "'>to options page</a> to setup some defaults.
                    </div>
                </div>";
        }
    } // End instance ()

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    } // End __clone ()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    } // End __wakeup ()

    /**
     * Installation. Runs on activation.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function install()
    {
        $this->_log_version_number();

        $notices = get_option('librafire_pinpoints_deferred_admin_notices', array());

        $notices[] = array('message' => $this->message('activated'), 'type' => 'activated');



        update_option('librafire_pinpoints_deferred_admin_notices', $notices);
    } // End install ()

    /**
     * Installation. Runs on deactivating.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    function uninstall()
    {
        delete_option('librafire_pinpoints_version');
        delete_option('librafire_pinpoints_deferred_admin_notices');
    }
    /**
     * Log the plugin version number.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    private function _log_version_number()
    {
        update_option($this->_token . '_version', $this->_version);
    } // End _log_version_number ()

}
