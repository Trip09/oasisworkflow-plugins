<?php
/*
 * Settings class for Teams License settings
 *
 * @copyright   Copyright (c) 2015, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * OW_Teams_License_Settings Class
 *
 * @since 2.0
 */

class OW_Teams_License_Settings {
	
	// oasis workflow license key option name
	protected $ow_teams_license_key_option_name = "oasiswf_teams_license_key";
	
	// oasis workflow license status option name
	protected $ow_teams_license_status_option_name = "oasiswf_teams_license_status";
	
	/*
	 * Set things up.
	 *
	 * @since 2.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		
		add_action( 'owf_add_license_settings', array( $this, 'add_license_settings' ) );
	
	}
	
	// White list our options using the Settings API
	public function init_settings() {
		register_setting('ow-settings-license', $this->ow_teams_license_key_option_name, array($this, 'validate_ow_teams_license_key'));
	}	
	
	/*
	 * sanitize and validate the input (if required)
	 *
	 * @since 2.0
	 */
	public function validate_ow_teams_license_key( $license_key ) {
		$license_key = sanitize_text_field( $license_key );
		$ow_license_service = new OW_License_Service();
	
		if( isset( $_POST['oasiswf_teams_license_deactivate'] ) ) { // user is trying to deactivate the license
			$status = $ow_license_service->deactivate_license( $license_key,
					$this->ow_teams_license_status_option_name, OWFTEAMS_PRODUCT_NAME);
	
			if ( $status == OW_License_Service::FAILED ) {
				add_settings_error(
						'ow-settings-license',
						'ow-settings-teams-license-ow-license-key',
						"Oasis Workflow Teams " . __( "License cannot be deactivated. Either the license key is invalid or the licensing server cannot be reached." , "owfteams" ),
						'error'
				);
				// since there was an error, revert to the license key to the value from the DB
				$license_key = trim( get_option( $this->ow_teams_license_key_option_name ) );
	
			} else { // looks like we have a successful de-activation, so let's clear the license key
				$license_key = "";
			}
		} else if ( ! empty ($license_key ) ) { // user is trying to activate the license
			$status = $ow_license_service->activate_license( $license_key,
					$this->ow_teams_license_status_option_name, OWFTEAMS_PRODUCT_NAME);
	
			if ( $status == OW_License_Service::INVALID ) {
				add_settings_error(
						'ow-settings-license',
						'ow-settings-teams-license-ow-license-key',
						"Oasis Workflow Teams " . __( "License cannot be activated. Either the license key is invalid or your activation limit is reached." , "owfteams" ),
						'error'
				);
			}
		}
	
		return $license_key;
	}
	
	/*
	 * generate the page
	 *
	 * @since 2.0
	 */
	public function add_license_settings() {
		$ow_teams_license_key = get_option( $this->ow_teams_license_key_option_name );
		$ow_teams_license_status = get_option( $this->ow_teams_license_status_option_name );
		?>
		<div class="select-info full-width">
			<div class="left quarter-width">
				<label class="settings-title" for="oasis_workflow_teams_license_key"><?php echo("Oasis Workflow Teams ");?><?php _e('license key'); ?>:</label>
			</div>
			<div class="left three-fourth-width">
				<input type="text" class="regular-text" name="<?php echo $this->ow_teams_license_key_option_name; ?>" value="<?php echo $ow_teams_license_key; ?>" />
	         <?php if( $ow_teams_license_status !== false && $ow_teams_license_status == 'valid' ) { ?>
					<input type="submit" class="button-secondary" name="oasiswf_teams_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
				<?php }	?>
			</div>
			<br class="clear">
		</div>
	<?php 		
	}	
}

$ow_teams_license_settings = new OW_Teams_License_Settings();
?>