<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This page allows the user to edit his/her profile
 * Changes get POSTed to account_prof_update.php
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses profile_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'profile_api.php' );
require_api( 'string_api.php' );

if( !config_get( 'enable_profiles' ) ) {
	trigger_error( ERROR_ACCESS_DENIED, ERROR );
}

auth_ensure_user_authenticated();

current_user_ensure_unprotected();

$f_profile_id	= gpc_get_int( 'profile_id' );

if( profile_is_global( $f_profile_id ) ) {
	access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );

	$t_row = profile_get_row( ALL_USERS, $f_profile_id );
} else {
	$t_row = profile_get_row( auth_get_current_user_id(), $f_profile_id );
}

extract( $t_row, EXTR_PREFIX_ALL, 'v' );

html_page_top();

if( profile_is_global( $f_profile_id ) ) {
	print_manage_menu();
}
?>

<?php # Edit Profile Form BEGIN ?>
<br />
<div class="form-container">

<h2><?php echo lang_get( 'edit_profile_title' ) ?></h2>
<div class="right">
	<?php
		if( !profile_is_global( $f_profile_id ) ) {
			print_account_menu();
		}
	?>
</div>

<form method="post" action="account_prof_update.php">

<fieldset>
	<?php echo form_security_field( 'profile_update' )?>

	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="profile_id" value="<?php echo $v_id ?>" />

	<div class="field-container">
		<label for="platform" class="required">
			<span><?php echo lang_get( 'platform' ) ?></span>
		</label>
		<span class="input">
			<input type="text" name="platform" size="32" maxlength="32" 
				value="<?php echo string_attribute( $v_platform ) ?>" />
		</span>
		<span class="label-style"></span>
	</div>

	<div class="field-container">
		<label for="os" class="required">
			<span><?php echo lang_get( 'os' ) ?></span>
		</label>
		<span class="input">
			<input type="text" name="os" size="32" maxlength="32" 
				value="<?php echo string_attribute( $v_os ) ?>" />
		</span>
		<span class="label-style"></span>
	</div>

	<div class="field-container">
		<label for="os_version" class="required">
			<span><?php echo lang_get( 'os_version' ) ?></span>
		</label>
		<span class="input">
			<input type="text" name="os_build" size="32" maxlength="32" 
				value="<?php echo string_attribute( $v_os_build ) ?>" />
		</span>
		<span class="label-style"></span>
	</div>

	<div class="field-container">
		<label for="description" class="required">
			<span><?php echo lang_get( 'additional_description' ) ?></span>
		</label>
		<span class="textarea">
			<textarea name="description" cols="60" rows="8"><?php 
				echo string_textarea( $v_description );
			?></textarea>
		</span>
		<span class="label-style"></span>
	</div>

	<div class="submit-button">
		<input type="submit" class="button" value="<?php echo lang_get( 'update_profile_button' ) ?>" />
	</div>

</fieldset>
</form>
</div>
<?php
html_page_bottom();
