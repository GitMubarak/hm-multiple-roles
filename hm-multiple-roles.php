<?php
/**
 * Plugin Name:	HM Multiple Roles
 * Plugin URI:	https://wordpress.org/plugins/hm-multiple-roles/
 * Description:	This plugin allows you to select multiple roles for a user
 * Version:		1.0
 * Author:		HM Plugin
 * Author URI:	https://hmplugin.com/
 * License:		GPL-2.0+
 * License URI:	http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined('ABSPATH') ) exit;

define('HMMR_PATH', plugin_dir_path(__FILE__));
define('HMMR_ASSETS', plugins_url('/assets/', __FILE__));
define('HMMR_SLUG', plugin_basename(__FILE__));
define('HMMR_PRFX', 'hmmr_');
define('HMMR_CLS_PRFX', 'cls-hmmr-');
define('HMMR_TXT_DOMAIN', 'hm-multiple-roles');
define('HMMR_VERSION', '1.0');


function hmmr_plugin_init() {
	load_plugin_textdomain( HMMR_TXT_DOMAIN, false, HMMR_PATH . '/languages/' );
}
add_action( 'plugins_loaded', 'hmmr_plugin_init' );


function hmmr_admin_enqueue_scripts( $handle ) {
		
	if ( 'user-edit.php' == $handle || 'user-new.php' == $handle ) {
		wp_enqueue_style(
			'hmmr-admin-style',
			HMMR_ASSETS . 'hmmr-admin-styles.css',
			array(),
			HMMR_VERSION,
			FALSE
		);
		
		wp_enqueue_script( 'jquery' );
		
		wp_enqueue_script(
			'hmmr-admin-script',
			HMMR_ASSETS . 'hmmr-admin-script.js',
			array('jquery'),
			HMMR_VERSION,
			TRUE
		);
	}
}
add_action( 'admin_enqueue_scripts', 'hmmr_admin_enqueue_scripts', 10 );


function hmmr_add_multiple_roles_ui( $user ) {
	
	if ( ! current_user_can( 'edit_user', $user->ID ) ) {
		return;
	}

	$roles = get_editable_roles();

	$user_roles = is_array( $user->roles ) ? array_intersect( array_values( $user->roles ), array_keys( $roles ) ) : array();
	?>
	<div class="hmmr-roles-container">
		<table class="form-table">
			<tr>
				<th><label for="user_roles"><?php esc_html_e('Roles', HMMR_TXT_DOMAIN); ?></label></th>
				<td>
					<?php
						foreach ( $roles as $role_id => $role_data ) : 
						?>
							<label for="user_role_<?php echo esc_attr( $role_id ); ?>">
								<input type="checkbox" id="user_role_<?php esc_attr_e( $role_id ); ?>" value="<?php esc_attr_e( $role_id ); ?>" name="hmmr_user_roles[]" <?php echo ( ! empty( $user_roles ) && in_array( $role_id, $user_roles ) ) ? ' checked="checked"' : ''; ?> />
								<?php esc_html_e( $role_data['name'] ); ?>
							</label>
							<br />
						<?php 
						endforeach; 
					?>
					<?php wp_nonce_field( 'hmmr_set_roles', '_hmmr_roles_nonce' ); ?>
				</td>
			</tr>
		</table>
	</div>
	<?php
}
add_action( 'user_new_form', 'hmmr_add_multiple_roles_ui', 0 );
add_action( 'show_user_profile', 'hmmr_add_multiple_roles_ui', 0 );
add_action( 'edit_user_profile', 'hmmr_add_multiple_roles_ui', 0 );


function hmmr_save_multiple_user_roles( $user_id ) {

	// Not allowed to edit user - bail
	if ( ! current_user_can( 'edit_user', $user_id ) || ! wp_verify_nonce( $_POST['_hmmr_roles_nonce'], 'hmmr_set_roles' ) ) {
		return;
	}
	
	$user = new WP_User( $user_id );

	$roles = get_editable_roles();
	
	if ( ! empty( $_POST['hmmr_user_roles'] ) ) {

		//$new_roles = isset( $_POST['hmmr_user_roles'] ) ? (array) filter_var( $_POST['hmmr_user_roles'], FILTER_SANITIZE_STRING ) : array();
		$new_roles = array_map( 'sanitize_text_field', wp_unslash( $_POST['hmmr_user_roles'] ) );

		// Get rid of any bogus roles
		$new_roles = array_intersect( $new_roles, array_keys( $roles ) );

		$roles_to_remove = array();

		$user_roles = array_intersect( array_values( $user->roles ), array_keys( $roles ) );

		if ( ! $new_roles ) {
			// If there are no roles, delete all of the user's roles
			$roles_to_remove = $user_roles;

		} else {

			$roles_to_remove = array_diff( $user_roles, $new_roles );
		}

		foreach ( $roles_to_remove as $_role ) {

			$user->remove_role( $_role );

		}

		if ( $new_roles ) {

			// Make sure that we don't call $user->add_role() any more than it's necessary
			$_new_roles = array_diff( $new_roles, array_intersect( array_values( $user->roles ), array_keys( $roles ) ) );

			foreach ( $_new_roles as $_role ) {

				$user->add_role( $_role );

			}
		}
	}
}
add_action('personal_options_update', 'hmmr_save_multiple_user_roles');
add_action('edit_user_profile_update', 'hmmr_save_multiple_user_roles');
add_action('user_register', 'hmmr_save_multiple_user_roles');