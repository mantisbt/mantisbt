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
 * User Create Page
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
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );

auth_reauthenticate();

access_ensure_global_level( config_get( 'manage_user_threshold' ) );

$t_ldap = ( LDAP == config_get_global( 'login_method' ) );

layout_page_header();

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_user_create_page.php' );
?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div id="manage-user-create-div" class="form-container">
	<form id="manage-user-create-form" method="post" action="manage_user_create.php">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-user', 'ace-icon' ); ?>
				<?php echo lang_get( 'create_new_account_title' ) ?>
			</h4>
		</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php echo form_security_field( 'manage_user_create' ) ?>

			<tr>
				<td class="category">
					<?php echo lang_get( 'username' ) ?>
				</td>
				<td>
					<input type="text" id="user-username" name="username" class="input-sm" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" />
				</td>
			</tr><?php
			if( !$t_ldap || config_get_global( 'use_ldap_realname' ) == OFF ) { ?>
			<tr>
				<td class="category">
					<?php echo lang_get( 'realname' ) ?>
				</td>
				<td>
					<input type="text" id="user-realname" name="realname" class="input-sm" size="32" maxlength="<?php echo DB_FIELD_SIZE_REALNAME;?>" />
				</td>
			</tr><?php
			}
			if( !$t_ldap || config_get_global( 'use_ldap_email' ) == OFF ) { ?>
			<tr>
				<td class="category">
					<?php echo lang_get( 'email' ) ?>
				</td>
				<td>
					<?php print_email_input( 'email', '' ) ?>
				</td>
			</tr><?php
			}
			if( OFF == config_get( 'send_reset_password' ) )  { ?>
			<tr>
				<td class="category">
					<?php echo lang_get( 'password' ) ?>
				</td>
				<td>
					<input type="password" id="user-password" name="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" />
				</td>
			</tr>
				<td class="category">
					<?php echo lang_get( 'verify_password' ) ?>
				</td>
				<td>
					<input type="password" id="user-verify-password" name="password_verify" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" />
				</td>
			</tr><?php
			} ?>
			<tr>
				<td class="category">
					<?php echo lang_get( 'access_level' ) ?>
				</td>
				<td>
					<select id="user-access-level" name="access_level" class="input-sm">
						<?php print_project_access_levels_option_list( config_get( 'default_new_account_access_level' ) ) ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'enabled' ) ?>
				</td>
				<td>
					<label>
						<input type="checkbox" class="ace" id="user-enabled" name="enabled" checked="checked">
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'protected' ) ?>
				</td>
				<td>
					<label>
						<input type="checkbox" class="ace" id="user-protected" name="protected">
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			</fieldset>
			</table>
			</div>
			</div>
			</div>

			<?php event_signal( 'EVENT_MANAGE_USER_CREATE_FORM' ) ?>

			<div class="widget-toolbox padding-8 clearfix">
				<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'create_user_button' ) ?>" />
			</div>
		</div>
		</div>
	</form>
</div>

<?php
layout_page_end();
