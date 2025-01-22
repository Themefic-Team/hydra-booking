<?php
namespace HydraBooking\FdDashboard\Shortcode;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Namespace
use HydraBooking\Admin\Controller\Enqueue;

/**
 * Signup Class
 * 
 * @author Sydur Rahman
 */
class Login {

    /**
     * Constructor
     * 
     * @author Sydur Rahman 
     */
    public function __construct() { 
       
        // Add Shortcode
        add_shortcode( 'hydra_login_form', array( $this, 'hydra_login_form_shortcode' ) );

        // Sign In section 
        add_action( 'wp_ajax_nopriv_tfhb_sign_in', array( $this, 'tfhb_sign_in_callback' ) );
 
    }

    /**
     * Hydra Retistration From
     * 
     * @author Sydur Rahman
     * 
     */

     public function hydra_login_form_shortcode() {
       
         // Enqueue Login Script
		if ( ! wp_script_is( 'tfhb-app-login', 'enqueued' ) ) {
			wp_enqueue_script( 'tfhb-app-login' );
		}
        
		// Start Buffer
		ob_start(); 

        if( is_user_logged_in() ) {
            ?>
            <div class="tfhb-frontend-from">
                <div class="tfhb-frontend-from__title">
                    <h3><?php echo esc_html(__('You are already logged in', 'hydra-booking')) ?></h3>
                    <!-- go to dashboard button -->

                    <a href="#">Go to dashboard</a>
                    
                </div>
        <?php 
            return ob_get_clean();
        }  ?>

        <div class="tfhb-frontend-from">
            <div class="tfhb-frontend-from__title">
                <h3><?php echo esc_html(__('Welcome back', 'hydra-booking')) ?></h3>
                <p><?php echo  esc_html(__('Please enter your details.', 'hydra-booking')) ?></p>
            </div>
            <form action="" id="tfhb-login-from">
                <?php wp_nonce_field( 'tfhb_check_login_nonce', 'tfhb_login_nonce' ); ?>
                <div class="tfhb-frontend-from__field-wrap">
                 

                    <div class="tfhb-frontend-from__field-item">
                        <label for="tfhb_login_user"><?php echo  esc_html(__('Username or Email', 'hydra-booking')) ?></label> 
                        <div class="tfhb-frontend-from__field-item__inner">
                            <span>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.99992 10.8333C12.3011 10.8333 14.1666 8.96785 14.1666 6.66667C14.1666 4.36548 12.3011 2.5 9.99992 2.5C7.69873 2.5 5.83325 4.36548 5.83325 6.66667C5.83325 8.96785 7.69873 10.8333 9.99992 10.8333Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16.6666 17.4987C16.6666 15.7306 15.9642 14.0349 14.714 12.7847C13.4637 11.5344 11.768 10.832 9.99992 10.832C8.23181 10.832 6.53612 11.5344 5.28587 12.7847C4.03563 14.0349 3.33325 15.7306 3.33325 17.4987" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input type="text" name="tfhb_login_user" id="tfhb_login_user" placeholder="Enter Username or Email">
                        </div>
                    </div>
 

                    <div class="tfhb-frontend-from__field-item">
                        <label for="tfhb_password"><?php echo  esc_html(__('Password', 'hydra-booking')) ?></label> 
                        <div class="tfhb-frontend-from__field-item__inner">
                            <span>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_6411_9196)">
                                    <path d="M1.66675 15.0019V17.5019C1.66675 18.0019 2.00008 18.3353 2.50008 18.3353H5.83341V15.8353H8.33341V13.3353H10.0001L11.1667 12.1686C12.3249 12.572 13.5858 12.5705 14.743 12.1642C15.9002 11.7579 16.8853 10.971 17.537 9.93203C18.1888 8.8931 18.4688 7.66373 18.331 6.44504C18.1933 5.22634 17.646 4.09047 16.7788 3.22323C15.9115 2.356 14.7757 1.80874 13.557 1.671C12.3383 1.53325 11.1089 1.81317 10.07 2.46496C9.03105 3.11675 8.24407 4.10182 7.83779 5.25902C7.4315 6.41623 7.42996 7.67706 7.83341 8.83526L1.66675 15.0019Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M13.7499 6.66536C13.98 6.66536 14.1666 6.47882 14.1666 6.2487C14.1666 6.01858 13.98 5.83203 13.7499 5.83203C13.5198 5.83203 13.3333 6.01858 13.3333 6.2487C13.3333 6.47882 13.5198 6.66536 13.7499 6.66536Z" fill="#273F2B" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_6411_9196">
                                    <rect width="20" height="20" fill="white"/>
                                    </clipPath>
                                </defs>
                            </svg>
                            </span>
                            <input type="password" name="tfhb_password" id="tfhb_password" placeholder="Type your password">
                        </div>
                    </div>
                    <div class="tfhb-frontend-from__field-item tfhb-frontend-from__field-item--right">
                         <p><a href="#"><?php echo  esc_html(__('Forget Passwords?', 'hydra-booking')) ?></a></p>
                    </div>
  
                    <div class="tfhb-frontend-from__field-item">
                        <button type="submit">
                            Login
                            <span>
                                <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_6411_13565)">
                                        <path d="M7.5 4.16797L13.3333 10.0013L7.5 15.8346" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_6411_13565">
                                        <rect width="20" height="20" fill="white" transform="translate(0.5)"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </span>
                        </button>
                    </div>

                    <div class="tfhb-frontend-from__field-item tfhb-frontend-from__field-item--center">
                         <p><?php  echo  esc_html(__('Need an account ?', 'hydra-booking')) ?>  <a href="#"><?php esc_html(__('Sign ups', 'hydra-booking')) ?> </a></p>
                    </div>
                   
                </div>
            </form>
        </div>
        <?php  
        return ob_get_clean();
     }

     /**
      *  Sign In Callback
      * 
      * @return void
      * @author Sydur Rahman
      */
      public function tfhb_sign_in_callback(){

        $response = [
            'success' => false,
        ];

        $required_fields = array( 'tfhb_login_user', 'tfhb_password' );
        // Check nonce security
        if ( ! isset( $_POST['tfhb_login_nonce'] ) || ! wp_verify_nonce( $_POST['tfhb_login_nonce'], 'tfhb_check_login_nonce' ) ) {
            $response['message'] = esc_html__( 'Sorry, your nonce did not verify.', 'tourfic' );
        } else {

            foreach ( $required_fields as $required_field ) {
                if ( $required_field === 'tfhb_login_user' && empty( $_POST[ $required_field ] ) ) {
                    $response['fieldErrors'][ $required_field] = esc_html__( 'Username or email is required.', 'tourfic' );
                } elseif ( $required_field === 'tfhb_password' && empty( $_POST[ $required_field ] ) ) {
                    $response['fieldErrors'][ $required_field] = esc_html__( 'Password is required.', 'tourfic' );
                }
            }
        }

        if ( ! $response['fieldErrors'] ) {
            // Get data from form
            $username = sanitize_text_field( $_POST['tfhb_login_user'] );
            $password = sanitize_text_field( $_POST['tfhb_password'] );

            // Set them in an array
            $credential = array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => true,
            );

            require_once( ABSPATH . 'wp-load.php' );
            // Sending data for login
            $user = wp_signon( $credential, true );

            // Validation
            if ( is_wp_error( $user ) ) {
                $response['message'] = $user->get_error_message();
            } else {
                $response['message'] = esc_html__( 'Successfully logged in.', 'tourfic' );
                $response['success'] = true;

                $response['redirect_url'] = get_permalink( 130 );

                // Set the authentication cookies
                wp_set_auth_cookie( $user->ID, true );
            }
        }

        wp_send_json( $response );

        die();

      }

 

}