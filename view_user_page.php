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
 * View User Page
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

auth_ensure_user_authenticated();

# extracts the user information for the currently logged in user
# and prefixes it with u_
$f_user_id = gpc_get_int( 'id', auth_get_current_user_id() );
$t_row = user_get_row( $f_user_id );

extract( $t_row, EXTR_PREFIX_ALL, 'u' );

$t_can_manage = access_has_global_level( config_get( 'manage_user_threshold' ) ) &&
	access_has_global_level( $u_access_level );
$t_can_see_realname = access_has_project_level( config_get( 'show_user_realname_threshold' ) );
$t_can_see_email = access_has_project_level( config_get( 'show_user_email_threshold' ) );

# In case we're using LDAP to get the email address... this will pull out
#  that version instead of the one in the DB
$u_email = user_get_email( $u_id );
$u_realname = user_get_realname( $u_id );

html_page_top();
?>

<div class="section-container">
	<h2><?php echo lang_get( 'view_account_title' ) ?></h2>
	<div class="field-container">
		<span class="display-label"><span><?php echo lang_get( 'username' ) ?></span></span>
		<span class="display-value"><span><?php echo string_display_line( $u_username ) ?></span></span>
		<span class="label-style"></span>
	</div>
	<div class="field-container">
		<span class="display-label"><span><?php echo lang_get( 'email' ) ?></span></span>
		<span class="display-value"><span>
			<?php
				if( ! ( $t_can_manage || $t_can_see_email ) ) {
					print error_string( ERROR_ACCESS_DENIED );
				} else {
					if( !is_blank( $u_email ) ) {
						print_email_link( $u_email, $u_email );
					} else {
						echo ' - ';
					}
				} ?>
		</span></span>
		<span class="label-style"></span>
	</div>
	<div class="field-container">
		<span class="display-label"><span><?php echo lang_get( 'realname' ) ?></span></span>
		<span class="display-value"><span><?php
			if( ! ( $t_can_manage || $t_can_see_realname ) ) {
				print error_string( ERROR_ACCESS_DENIED );
			} else {
				echo string_display_line( $u_realname );
			} ?>
		</span></span>
		<span class="label-style"></span>
	</div>
	<span class="section-links">
	<?php if( $t_can_manage ) { ?>
		<form id="manage-user-form" method="get" action="manage_user_edit_page.php" class="action-button">
			<fieldset>
				<input type="hidden" name="user_id" value="<?php echo $f_user_id ?>" />
				<span><input type="submit" class="button" value="<?php echo lang_get( 'manage_user' ) ?>" /></span>
			</fieldset>
		</form>
	<?php } ?>
	<?php if( auth_can_impersonate( $f_user_id ) ) { ?>
		<form id="manage-user-impersonate-form" method="post" action="manage_user_impersonate.php" class="action-button">
			<fieldset>
				<?php echo form_security_field( 'manage_user_impersonate' ) ?>
				<input type="hidden" name="user_id" value="<?php echo $f_user_id ?>" />
				<span><input type="submit" class="button" value="<?php echo lang_get( 'impersonate_user_button' ) ?>" /></span>
			</fieldset>
		</form>
	<?php } ?>
	</span>
</div><?php

html_page_bottom();
