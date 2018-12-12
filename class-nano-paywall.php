<?php
/**
 * Plugin Name: Nano Paywall
 * Plugin URI: https://nanoble.org
 * Description: Require payment in Nano to view content using BrainBlocks payment service
 * Version: 0.1
 * Author: Zach Hyatt
 * Author URI: https://nanoble.org
 * Text Domain: nano_paywall
 * License: GPL2
 */

 class nanoPaywall 
 {
    public $plugin_name ="nano-paywall";
    public $txt_domain ="nano_paywall";
    public $version = '0.2b';
    private $np_general_settings;

    public function  __construct()
    {
        add_action( 'init', array( $this, 'register_script' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_shortcode( 'nano-paywall', array( $this, 'shortcode_nano_paywall' ) );
        add_action( 'wp_ajax_nopriv_get_content', array( $this, 'get_content' ) );
        add_action( 'wp_ajax_get_content', array( $this, 'get_content' ) );
        add_action( 'init', array( $this, 'np_start_session' ), 1);

        if ( is_admin() ) {
            //admin menu
            add_action( 'admin_init', array( $this, 'register_settings' ) );
            add_action( 'admin_menu', array( $this, 'setup_admin_menus' ) );
        }

        $this->np_general_settings = get_option( 'np_general_settings' );
        if( ! $this->np_general_settings ) {
            //set initial default values
            $this->np_general_settings = array(
                'np_destination_account_id'       => 'xrb_397p6t19ajqnm6b9psgdhnpughk4moojm5ehero8srqzxexogofxnyz9myzj',
                'np_amount'        => '0.001',
                'np_description'   => 'Get access to this great content with a small Nano payment',
                'np_info'          => 'The purchased content will only be available during your browsing session.  If you have any issues please contact the site administrator.'
            );
        }
    }

    public function np_start_session()
    {
        if ( ! session_id() ) {
            session_start();
        }
    }

    public function setup_admin_menus() {

        add_menu_page( 
            'Nano Paywall', 
            'Nano Paywall', 
            'manage_options', 
            'np-settings-page', 
            array( $this, 'include_settings_page'),
            'dashicons-lock',
            30
        );
    }

    public function include_settings_page() {
        require_once( __DIR__.'/views/np-settings-page.php' );
    }

    public function register_settings() {

        register_setting( 'np_general_settings', 'np_general_settings', array( $this, 'validate_settings' ) );

        //START: General Settings
        add_settings_section(
            'np-settings-page-explain-section', // ID used to identify this section and with which to register options
            __( "Shortcode Usage", 'np_settings' ), // Title to be displayed on the administration page
            array( $this, 'settings_explanation_callback' ), // Callback used to render the description of the section
            'np-settings-page-payment' // Page on which to add this section of options
        );

        add_settings_section(
            'np-settings-page-payment-section', // ID used to identify this section and with which to register options
            __( "Default Settings", 'np_settings' ), // Title to be displayed on the administration page
            array( $this, 'settings_section_empty_callback' ), // Callback used to render the description of the section
            'np-settings-page-payment' // Page on which to add this section of options
        );

        add_settings_field(
            'np_destination_account_id', // ID used to identify the field throughout the theme
            __( "Destination Nano account address", 'np_settings' ), // The label to the left of the option interface element
            $this->get_text_field_callback( 'np_destination_account_id','The public Nano account address that payments will be forwarded to once received by BrainBlocks service.', 'np_general_settings'  ), // Callback used to render the field
            'np-settings-page-payment', // The page on which this option will be displayed
            'np-settings-page-payment-section' // The name of the section to which this field belongs
        );

        add_settings_field(
            'np_amount', // ID used to identify the field throughout the theme
            __( "Amount (Nano)", 'np_settings' ), // The label to the left of the option interface element
            $this->get_text_field_callback( 'np_amount','The default amount of Nano to charge for accessing content if not set shortcode.', 'np_general_settings'  ), // Callback used to render the field
            'np-settings-page-payment', // The page on which this option will be displayed
            'np-settings-page-payment-section' // The name of the section to which this field belongs
        );

        add_settings_field(
            'np_description', // ID used to identify the field throughout the theme
            __( "Description", 'np_settings' ), // The label to the left of the option interface element
            $this->get_text_field_callback( 'np_description','The default description that will appear above payment button if not set in shortcode.', 'np_general_settings'  ), // Callback used to render the field
            'np-settings-page-payment', // The page on which this option will be displayed
            'np-settings-page-payment-section' // The name of the section to which this field belongs
        );

        add_settings_field(
            'np_info', // ID used to identify the field throughout the theme
            __( "Information details", 'np_settings' ), // The label to the left of the option interface element
            $this->get_text_field_callback( 'np_info','The default content that appears when clicking the info button if not set in shortcode.', 'np_general_settings'  ), // Callback used to render the field
            'np-settings-page-payment', // The page on which this option will be displayed
            'np-settings-page-payment-section' // The name of the section to which this field belongs
        );
        //END: General Settings
    }

    private function get_text_field_callback( $key, $description, $settings_name ) {
        return function () use ( $key, $description, $settings_name ) {
            $val = '';
            if ( ! empty( $this->{$settings_name} ) && isset ( $this->{$settings_name}[ $key ] ) ) {
                $val = $this->{$settings_name}[ $key ];
            }

            echo '<input type="text" id="' . $key . '" name="' . $settings_name . '[' . $key . ']" value="' . str_replace('"', '&#34;', $val) . '" style="width:550px;" />';
            echo '<br><label for=' . $key . ' style="display: block; width:550px;">' . $description . '</label>';
        };
    }

    /*
    private function validate_nano_address() {

        $val = '';
        if ( ! empty( $this->{$settings_name} ) && isset ( $this->{$settings_name}[ $key ] ) ) {
            $val = $this->{$settings_name}[ $key ];
        }

        $address_pieces = explode( '_', $val );
        if( count( $address_pieces ) != 2 ) {
            //error
        }

        if( ( strpos( $val, 'xrb_' ) == -1 || strpos( $val, 'nano_' ) ) && strlen( $address_pieces[1] ) ) { 
            //error
        }
    }
    */

    public function validate_settings( $input ) {
        return $input;
    }

    public function settings_explanation_callback( $arg ) {

            echo '<p>The following default settings will be used on all shortcodes which do not have the override parameters detailed below.</p>
            <h4>Shortcode Usage</h4>
            <p>Any content you wish to not show up until an amount of Nano is paid should be wrapped in the following shortcode, which will use the default values:</p>
            <p class="np-indent"><code>[nano_paywall] Content here [/nano_paywall]</code></p>
            <p>Overriding parameters:</p>
            <p class="np-indent"><code>[nano_paywall address="xrb_397p6t19ajqnm6b9psgdhnpughk4moojm5ehero8srqzxexogofxnyz9myzj"]</code> - overwrite the Nano account address</p>
            <p class="np-indent"><code>[nano_paywall amount="0.1"]</code> - overwrite the Amount (Nano)</p>
            <p class="np-indent"><code>[nano_paywall description="This is a description of the content behind the paywall"]</code> - overwrite the Description</p>
            <p class="np-indent"><code>[nano_paywall info="This disclaimer shows up when clicking on the info button"]</code> - overwrite the Information details</p>
            <br />
            ';

    }

    public function settings_section_empty_callback() {
        //intentionally empty
    }

    public function register_script()
    {
        wp_register_script( 'nano-paywall-js', plugins_url( $this->plugin_name .'/js/nano-paywall.js', dirname(__FILE__) ), array('jquery'), filemtime( plugin_dir_path( __FILE__ ) . '/js/nano-paywall.js'), true);
        
        wp_register_script( 'brainblocks-min-js', 'https://brainblocks.io/brainblocks.min.js', array('jquery'), filemtime( plugin_dir_path( __FILE__ ) . '/js/nano-paywall.js'), true);
    }

    public function enqueue_styles()
    {
        wp_enqueue_style('nano-paywall-styles', plugins_url( $this->plugin_name . '/css/nano-paywall.css', dirname(__FILE__) ), array(), filemtime( plugin_dir_path( __FILE__ ) . '/css/nano-paywall.css'));
    }

    public function admin_enqueue_styles()
    {
        wp_enqueue_style('nano-paywall-admin-styles', plugins_url( $this->plugin_name . '/css/nano-paywall-admin.css', dirname(__FILE__) ), array(), filemtime( plugin_dir_path( __FILE__ ) . '/css/nano-paywall-admin.css'));
    }

    public function load_admin_script()
    {

    }

    public function get_content() {

        if ( ! check_ajax_referer( 'nano-paywall-nonce', 'security' ) ) {
            json_encode( array( 'success' => 0, 'error' => 'Invalid security token sent.' ) );
            wp_die();
        }

        $amount = $_POST['amount'];
        $bb_token = $_POST['token'];
        $address = $_POST['address'];

        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, 'https://brainblocks.io/api/session/' . $bb_token . '/verify'); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        $output = curl_exec($ch);
        curl_close($ch);

        $result = json_decode( $output, true );

        if( $result['fulfilled'] == true && $result['amount'] == $amount && $result['destination'] == $address ) {
            $url     = wp_get_referer();
            $post_id = url_to_postid( $url );

            $content = $this->get_paid_for_content( $post_id );

            //save completion in session
            $_SESSION['nano-paywall-'.strval( $post_id )] = 1;

            echo json_encode( array( 'success' => 1, 'content' => $content ));
            wp_die();
        } else {
            echo json_encode( array( 'success' => 0 ));
            wp_die();
        }
    }

    public function get_paid_for_content( $post_id ) {
        $content_post = get_post( $post_id );

        //remove nano-paywall shortcodes and content outside those tags
        $content = str_replace('[/nano-paywall]', '', $content_post->post_content);
        $temp_content = substr($content, strpos($content, '[nano-paywall')+13);

        return substr( $temp_content, strpos( $temp_content, ']')+1 );
    }

    /*
    *   @param nano_amount string
    *   @return string
    */
    public function convert_nano_amount_str_to_rai_amount_str( $nano_amount ) {

        $amount_pieces = explode( '.', $nano_amount );
        if( count( $amount_pieces ) == 1 ) {
            return $nano_amount . '000000';
        } else {
            $pre_dec = $amount_pieces[0];
            $post_dec = $amount_pieces[1];
            $len_post_dec = strlen( $post_dec );

            if( $len_post_dec == 6 ){
                
                return $pre_dec.$post_dec;

            } elseif( $len_post_dec < 6 ) {

                $add_zeros = 6 - $len_post_dec;
                return str_pad($pre_dec.$post_dec, strlen($pre_dec.$post_dec)+$add_zeros, "0", STR_PAD_RIGHT);

            } else {

                return $pre_dec.substr_replace( $post_dec, '.', 6, 0);

            }
        }
    }

    public function shortcode_nano_paywall( $atts, $content )
    {

        //check if already paid
        global $post;
        
        if( ! empty( $_SESSION['nano-paywall-'.strval( $post->ID )] ) ) {
            $content = $this->get_paid_for_content( $post->ID );

            return '<div class="np-container">
                    <div class="np-content">
                        ' . $content . '
                    </div>
                </div>';
        }

        $args = shortcode_atts( array(
            'address'       => ( isset( $this->np_general_settings['np_destination_account_id'] ) ? $this->np_general_settings['np_destination_account_id'] : '0' ),
            'amount'        => ( isset( $this->np_general_settings['np_amount'] ) ? $this->np_general_settings['np_amount'] : '0' ),
            'description'   => ( isset( $this->np_general_settings['np_description'] ) ? $this->np_general_settings['np_description'] : '' ),
            'info'          => ( isset( $this->np_general_settings['np_info'] ) ? $this->np_general_settings['np_info'] : '' )
        ), $atts, 'nano-paywall' );


        wp_localize_script( 'nano-paywall-js', 'nano_paywall_object',
            array( 
                'ajaxurl'       => admin_url( 'admin-ajax.php' ),
                'address'       => $args['address'],
                'amount'        => $this->convert_nano_amount_str_to_rai_amount_str( $args['amount'] ),
                'security'      => wp_create_nonce('nano-paywall-nonce')
            ) 
        );

        wp_enqueue_script('brainblocks-min-js');
        wp_enqueue_script('nano-paywall-js');
        
        $html = '<div id="np-container" class="np-container">
                    <div class="np-overlay">
                        <div class="np-overlay-content">
                            ' . $args['description'] . '<a class="np-info-button"></a>
                        </div>
                        <div class="np-info" id="np-info">' . $args['info'] . '</div>
                        <div id="nano-button"></div>
                        <div class="blur"></div>
                        <div class="np-backdrop">
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        </div>
                    </div>
                    <div class="np-content">
                    </div>
                    <div class="modal"></div>
                </div>';
        
        return $html;
    }

}

$nano_paywall = new nanoPaywall();
 
?>