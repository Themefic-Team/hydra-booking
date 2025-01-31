<?php
namespace HydraBooking\FdDashboard;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Namespace
use HydraBooking\Admin\Controller\Enqueue;
// use Shortcode\Signup;
use HydraBooking\FdDashboard\Shortcode\Signup;
use HydraBooking\FdDashboard\Shortcode\Login;

/**
 * Frontend Dashboard Class
 * 
 * @author Sydur Rahman
 */
class FrontendDashboard {

    /**
     * Constructor
     * 
     * @author Sydur Rahman 
     */
    public function __construct() { 

        // Define Constants
        $this->define_constants();

        // Load Shortcode
        new Signup();

        // Load Shortcode
        new Login();

        // Load Frontend Dashboard 
        add_filter( 'theme_page_templates', array( $this, 'set_page_template' ), 10, 4 );

        // Load Frontend Dashboard
        add_filter( 'page_template', array( $this, 'load_page_templates' ) );

        // Create Page
        $this->tfhb_create_page();

        // add post state
		add_filter( 'display_post_states', array( $this, 'tfhb_add_post_state' ), 10, 2 );

        // query vars
        add_filter( 'query_vars', array( $this, 'tfhb_single_query_vars' ) );

        // Rewrite
        add_rewrite_rule(
			'^meeting/([0-9]+)/?$',
			'index.php?hydra-booking=email-verification&tfhb_verification=$matches[1]',
			'top'
		);
        

        // Page Template
        add_filter( 'template_include', array( $this, 'tfhb_single_page_template' ) );

        // ajax forgot password ;
        add_action( 'wp_ajax_nopriv_tfhb_forgot_password', array( $this, 'tfhb_forgot_password_callback' ) );

        // reset password  
        add_action( 'wp_ajax_nopriv_tfhb_reset_password', array( $this, 'tfhb_reset_password_callback' ) );
 
       
    }

    /**
     * Define Constants
     * 
     * @return void
     * @since 1.0.0
     * @author Sydur Rahman
     */

    public function define_constants() {
        define( 'TFHB_FD_DASHBOARD_PATH', plugin_dir_path( __FILE__ ) ); 
        define( 'TFHB_FD_DASHBOARD_URL', plugin_dir_url( __FILE__ ) );
        define( 'TFHB_FD_DASHBOARD_TEMPLATE_PATH', TFHB_FD_DASHBOARD_PATH . 'Template/' );

    }

     /**
     * query vars
     * 
     * @return void
     * @since 1.0.0
     * @author Sydur Rahman
     */
    public function tfhb_single_query_vars( $query_vars ) {
        $query_vars[] = 'tfhb_verification';
        return $query_vars;
    }

    /**
     * Page Template
     * 
     * @return void
     * @since 1.0.0
     * @author Sydur Rahman
     */

    public function tfhb_single_page_template( $template ) {

        if ( get_query_var( 'hydra-booking' ) === 'email-verification' && get_query_var( 'tfhb_verification' ) ) {
            $template = TFHB_FD_DASHBOARD_TEMPLATE_PATH . 'email-verification.php';
        }
        if ( get_query_var( 'hydra-booking' ) === 'forgot-password' ) { 
            
            if ( ! wp_script_is( 'tfhb-app-login', 'enqueued' ) ) {
                wp_enqueue_script( 'tfhb-app-login' );
            }
            $template = TFHB_FD_DASHBOARD_TEMPLATE_PATH . 'forgot-password.php';
        }

        return $template;
    }

     /**
     * Forgot Passowrd
     * 
     * @return void
     * @since 1.0.0
     * @author Sydur Rahman
     */

    public function tfhb_forgot_password_callback(){
        
        $response = [
            'success' => false,
        ];

        $required_fields = array( 'tfhb_forgot_user');
        // Check nonce security
        if ( ! isset( $_POST['tfhb_forgot_nonce'] ) || ! wp_verify_nonce( $_POST['tfhb_forgot_nonce'], 'tfhb_check_forgot_nonce' ) ) {
            $response['message'] = esc_html__( 'Sorry, your nonce did not verify.', 'tourfic' );
        } else {

            foreach ( $required_fields as $required_field ) {
                if ( $required_field === 'tfhb_forgot_user' && empty( $_POST[ $required_field ] ) ) {
                    $response['fieldErrors'][ $required_field] = esc_html__( 'Username or email is required.', 'hydra-booking' );
                }
            }
        }

        // Check user exist using username or email or not
        if ( empty( $response['fieldErrors'] ) ) {
            $user = get_user_by( 'login', sanitize_text_field( $_POST['tfhb_forgot_user'] ) );
            if ( ! $user ) {
                $response['fieldErrors'][ 'tfhb_forgot_user' ] = esc_html__( 'User not found.', 'hydra-booking' );
            } else { 

                // if user found then send email with reset password link
                $user_id = $user->ID;
                $user_email = $user->user_email;

                $reset_password_key = get_password_reset_key( $user );
                $string = array( 'email' =>  $user_email, 'code' => $reset_password_key );

                $link = get_site_url() . '/?hydra-booking=forgot-password&tfhb_verification=' . base64_encode( json_encode( $string ) );

                $subject = esc_html__( 'Password Reset Link', 'hydra-booking' );

                $message = esc_html__( 'Hi ' . $user->display_name . ',', 'hydra-booking' ) . "\r\n\r\n";

                $message .= esc_html__( 'You have requested to reset your password.', 'hydra-booking' ) . "\r\n\r\n";

                $message .= esc_html__( 'Please click the link below to reset your password:', 'hydra-booking' ) . "\r\n\r\n";

                $message .= '<a href="' . $link . '">' . $link . '</a>' . "\r\n\r\n";

                $message .= esc_html__( 'If you did not request a password reset, please ignore this email.', 'hydra-booking' ) . "\r\n\r\n";

                $message .= esc_html__( 'Thanks,', 'hydra-booking' ) . "\r\n\r\n";

                $message .= get_bloginfo( 'name' );

                $headers = array(
                    'From' => get_bloginfo( 'admin_email' ),
                    'Reply-To' => $user_email,
                );

                if ( wp_mail( $user_email, $subject, $message, $headers ) ) {
                    $response['success'] = true;
                    $response['message'] = esc_html__( 'Password reset link sent to your email.', 'hydra-booking' );
                }


            }
        }

        

        wp_send_json( $response );

        die();
    }

     /**
     * Reset Passowrd
     * 
     * @return void
     * @since 1.0.0
     * @author Sydur Rahman
     */

     public function tfhb_reset_password_callback(){
         
        $response = [
            'success' => false,
        ]; 
        $required_fields = array( 'tfhb_password', 'tfhb_confirm_password' );
        $field = [];
        foreach ( $_POST as $key => $value ) {
            $field[ $key ] = sanitize_text_field( $value );
        } 
        
        $data = base64_decode($field['code']);
        $data = json_decode($data, true); 

        $frontend_dashboard_settings = get_option('_tfhb_frontend_dashboard_settings');
        $settings = !empty($frontend_dashboard_settings) ? $frontend_dashboard_settings : array();
        $login_page_id =  isset($settings['login']['login_page']) && !empty($settings['login']['login_page']) ? $settings['login']['login_page'] :  get_option( 'tfhb_login_page_id' );
 

        if ( ! isset( $field['tfhb_reset_password_nonce'] ) || ! wp_verify_nonce( $field['tfhb_reset_password_nonce'], 'tfhb_check_reset_password_nonce' ) ) {
            $response['message'] = esc_html__( 'Sorry, your nonce did not verify.', 'hydra-booking' );
        } else {
            foreach ( $required_fields as $required_field ) {
                if ( $required_field === 'tfhb_password' ) {
                    if ( empty( $field[ $required_field ] ) ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Please enter your password', 'hydra-booking' );
                    } elseif ( ! preg_match( '@[A-Z]@', $field['tfhb_password'] ) ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Password must be include at least one uppercase letter', 'hydra-booking' );
                    } elseif ( ! preg_match( '@[0-9]@', $field['tfhb_password'] ) ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Password must be include at least one number', 'hydra-booking' );
                    } elseif ( ! preg_match( '@[^\w]@', $field['tfhb_password'] ) ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Password must be include at least one special character', 'hydra-booking' );
                    } elseif ( strlen( $field['tfhb_password'] ) < 8 ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Password must be at least 8 characters', 'hydra-booking' );
                    }
                } elseif ( $required_field === 'tfhb_confirm_password' ) {
                    if ( empty( $field[ $required_field ] ) ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Please re-enter your password', 'hydra-booking' );
                    } elseif ( $field['tfhb_password'] !== $field['tfhb_confirm_password'] ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Passwords doesn\'t match', 'hydra-booking' );
                    }
                } elseif ( empty( $field[ $required_field ] ) ) {
                    $response['fieldErrors'][ $required_field] = esc_html__( 'The field is required', 'hydra-booking' );
                }
            }
        }


        // Change password
        if ( empty( $response['fieldErrors'] ) ) {
            $user = get_user_by( 'email', $data['email'] );
            if ( $user ) {
                
                reset_password( $user, $field['tfhb_password'] );
                $response['success'] = true;
                $response['message'] = esc_html__( 'Password changed successfully. you can <a href="' . get_permalink( $login_page_id ) . '">login here</a>', 'hydra-booking' );
            } else {
                $response['message'] = esc_html__( 'User not found', 'hydra-booking' );
            }
        }


        wp_send_json( $response );

        die();
     }

    /**
     * Load Frontend Dashboard
     * 
     * @return void
     * @author Sydur Rahman
     */
    public function set_page_template($template){
        $template['tfhb-frontend-dashboard.php'] = 'Hydra - Dashbaord';
        $template['tfhb-frontend-signup.php'] = 'Hydra - Registration';
        $template['tfhb-frontend-login.php'] = 'Hydra - Login';
        return $template;
    }

    /**
     * Load Frontend Dashboard
     * 
     * @return void
     * @author Sydur Rahman
     */

    public function load_page_templates($template){ 
        $page_template = get_page_template_slug();
        // load frontend dashboard template
        if ( $page_template == 'tfhb-frontend-dashboard.php' ) {
            if( is_user_logged_in() ) {
                new Enqueue();

                $template = TFHB_FD_DASHBOARD_TEMPLATE_PATH . 'frontend-dashboard.php';
            }else{
                $frontend_dashboard_settings = get_option('_tfhb_frontend_dashboard_settings');
                $settings = !empty($frontend_dashboard_settings) ? $frontend_dashboard_settings : array();
                $login_page_id =  isset($settings['login']['login_page']) && !empty($settings['login']['login_page']) ? $settings['login']['login_page'] :  get_option( 'tfhb_login_page_id' );
                $get_login_page_url = get_permalink( $login_page_id );
                // Redirect to the login page if a URL exists
                if (!empty($get_login_page_url)) {
                    wp_redirect($get_login_page_url);
                    exit; // Prevent further script execution after redirection
                } else {
                    // Handle the case where the login page URL is not set
                    wp_die('Login page URL not found. Please configure the settings.');
                }

            }
            // if
        }
        if ( $page_template == 'tfhb-frontend-signup.php' ) { 
            $template = TFHB_FD_DASHBOARD_TEMPLATE_PATH . 'frontend-signup.php';
        }
        if ( $page_template == 'tfhb-frontend-login.php' ) { 

            
            $template = TFHB_FD_DASHBOARD_TEMPLATE_PATH . 'frontend-login.php';
        }
        return $template;
    }

    /**
     * Create page
     * 
     * @return void
     * @author Sydur Rahman
     */
    public function tfhb_create_page(){
        
        $pages = array( 
			'login'              => array(
				'name'    => _x( 'hydra-login', 'Page slug', 'tourfic' ),
				'title'   => _x( 'Hydra Login', 'Page title', 'tourfic' ),
				'template' => 'tfhb-frontend-login.php',
				'content' => '',
				'pro'     => false,
			), 
			'register'           => array(
				'name'    => _x( 'hydra-register', 'Page slug', 'tourfic' ),
				'title'   => _x( 'Hydra Register', 'Page title', 'tourfic' ),
				'template' => 'tfhb-frontend-signup.php',
				'content' => '',
				'pro'     => false,
			), 
			'dashboard'          => array(
				'name'    => _x( 'hydra-dashboard', 'Page slug', 'tourfic' ),
				'title'   => _x( 'Hydra Dashboard', 'Page title', 'tourfic' ),
				'template' => 'tfhb-frontend-dashboard.php',
				'content' => '',
				'pro'     => false,
			), 
		);

        foreach ( $pages as $key => $page ) {
			if ($page['pro'] == true ) {
				continue;
			}
			$this->create_page( esc_sql( $page['name'] ), 'tfhb_' . $key . '_page_id', $page['title'], $page['content'], ! empty( $page['parent'] ) ? $page['parent'] : '', $page['template'] );
		}
        
    }

    /**
	 * Create a page and store the ID in an option.
	 *
	 * @param mixed $slug Slug for the new page
	 * @param string $option Option name to store the page's ID
	 * @param string $page_title (default: '') Title for the new page
	 * @param string $page_content (default: '') Content for the new page
	 * @param int $post_parent (default: 0) Parent for the new page
	 *
	 * @return int page ID
	 */
	private function create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0, $template ) {
		global $wpdb;

		$option_value = get_option( $option ); 

		if ( $option_value > 0 && get_post( $option_value ) ) {
			return - 1;
		}

		$page_found = null;

		if ( $slug ) {
			$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s LIMIT 1;", $slug ) );
		}
        
		if ( $page_found ) {
			if ( ! $option ) {
				return $page_found;
			}
			update_option( $option, $page_found );

			return $page_found;
		}

		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => 1,
			'post_name'      => $slug,
			'post_title'     => $page_title,
			'post_content'   => $page_content,
			'post_parent'    => $post_parent,
			'comment_status' => 'closed',
		);
		$page_id   = wp_insert_post( $page_data );

		update_option( $option, $page_id );
		update_post_meta( $page_id, '_wp_page_template', $template );

		return $page_id;
	}

    /**
     * tfhb_add_post_state
     * 
     * @author Sydur
     * 
     */
    public function tfhb_add_post_state($post_states, $post){
        if ( $post->ID == get_option( 'tfhb_login_page_id' ) ||
		     $post->ID == get_option( 'tfhb_register_page_id' ) || 
		     $post->ID == get_option( 'tfhb_dashboard_page_id' ) 
		) {
            $template = get_post_meta($post->ID, '_wp_page_template');
            // tfhb_print_r($template);
            $template_string = '';
 
            
			$post_states[] = '<span class="tfhb-post-states">' . esc_html(__('Hydra Booking', 'hydra-booking')) . '</span>';
		}

		return $post_states;
    }


    /**
     * Forgot Password
     * 
     * @author Sydur
     */

     public function tfhb_forgot_password(){

     }

}