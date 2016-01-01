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
 * This page allows users to add a new profile which is POSTed to
 * account_prof_add.php
 *
 * Users can also manage their profiles
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
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses profile_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'profile_api.php' );

if( !config_get( 'enable_profiles' ) ) {
	trigger_error( ERROR_ACCESS_DENIED, ERROR );
}

if( isset( $g_global_profiles ) ) {
	$g_global_profiles = true;
} else {
	$g_global_profiles = false;
}

auth_ensure_user_authenticated();

current_user_ensure_unprotected();

if( $g_global_profiles ) {
	access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );
} else {
	access_ensure_global_level( config_get( 'add_profile_threshold' ) );
}

html_page_top( lang_get( 'manage_profiles_link' ) );

if( $g_global_profiles ) {
	print_manage_menu( 'manage_prof_menu_page.php' );
}

if( $g_global_profiles ) {
	$t_user_id = ALL_USERS;
} else {
	$t_user_id = auth_get_current_user_id();
}

# Add Profile Form BEGIN
?>
<div id="account-profile-div" class="form-container">
	<form id="account-profile-form" method="post" action="account_prof_update.php">
		<fieldset class="required">
			<legend><span><?php echo lang_get( 'add_profile_title' ) ?></span></legend>
			<?php  echo form_security_field( 'profile_update' )?>
			<input type="hidden" name="action" value="add" />
			<input type="hidden" name="user_id" value="<?php echo $t_user_id ?>" />
			<?php
			if( !$g_global_profiles ) {
				print_account_menu( 'account_prof_menu_page.php' );
			}
			?>
			<div class="field-container">
				<label for="platform" class="required"><span><?php echo lang_get( 'platform' ) ?></span></label>
				<span class="input"><input id="platform" type="text" name="platform" size="32" maxlength="32" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="os" class="required"><span><?php echo lang_get( 'os' ) ?></span></label>
				<span class="input"><input id="os" type="text" name="os" size="32" maxlength="32" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="os-version" class="required"><span><?php echo lang_get( 'os_version' ) ?></span></label>
				<span class="input"><input id="os-version" type="text" name="os_build" size="16" maxlength="16" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="description"><span><?php echo lang_get( 'additional_description' ) ?></span></label>
				<span class="textarea"><textarea id="description" name="description" cols="80" rows="8"></textarea></span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'add_profile_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>
<?php
	# Add Profile Form END
	# Edit or Delete Profile Form BEGIN

	$t_profiles = profile_get_all_for_user( $t_user_id );
	if( $t_profiles ) {
?>

<div id="account-profile-update-div" class="form-container">
	<form id="account-profile-update-form" method="post" action="account_prof_update.php">
		<fieldset>
			<legend><span><?php echo lang_get( 'edit_or_delete_profiles_title' ) ?></span></legend>
			<?php  echo form_security_field( 'profile_update' )?>
			<div class="field-container">
				<label for="action-edit"><span><?php echo lang_get( 'edit_profile' ) ?></span></label>
				<span class="input"><input id="action-edit" type="radio" name="action" value="edit" /></span>
				<span class="label-style"></span>
			</div>
<?php
	if( !$g_global_profiles ) {
?>
			<div class="field-container">
				<label for="action-default"><span><?php echo lang_get( 'make_default' ) ?></span></label>
				<span class="input"><input id="action-default" type="radio" name="action" value="make_default" /></span>
				<span class="label-style"></span>
			</div>
<?php
	}
?>
			<div class="field-container">
				<label for="action-delete"><span><?php echo lang_get( 'delete_profile' ) ?></span></label>
				<span class="input"><input id="action-delete" type="radio" name="action" value="delete" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="select-profile"><span><?php echo lang_get( 'select_profile' ) ?></span></label>
				<span class="input">
					<select id="select-profile" name="profile_id">
						<?php print_profile_option_list( $t_user_id, '', $t_profiles ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'submit_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>
<?php
} # Edit or Delete Profile Form END

html_page_bottom();
