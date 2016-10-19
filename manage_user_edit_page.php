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
 * User Edit Page
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
 * @uses error_api.php
 * @uses form_api.php
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
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

auth_reauthenticate();

access_ensure_global_level( config_get( 'manage_user_threshold' ) );

$f_username = gpc_get_string( 'username', '' );

if( is_blank( $f_username ) ) {
	$t_user_id = gpc_get_int( 'user_id' );
} else {
	$t_user_id = user_get_id_by_name( $f_username );
	if( $t_user_id === false ) {
		# If we can't find the user by name, attempt to find by email.
		$t_user_id = user_get_id_by_email( $f_username );
		if( $t_user_id === false ) {
			# If we can't find the user by email, attempt to find by realname.
			$t_user_id = user_get_id_by_realname( $f_username );
			if( $t_user_id === false ) {
				error_parameters( $f_username );
				trigger_error( ERROR_USER_BY_NAME_NOT_FOUND, ERROR );
			}
		}
	}
}

$t_user = user_get_row( $t_user_id );

# Ensure that the account to be updated is of equal or lower access to the
# current user.
access_ensure_global_level( $t_user['access_level'] );

$t_ldap = ( LDAP == config_get( 'login_method' ) );

layout_page_header();

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_user_page.php' );
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

<!-- USER INFO -->
<div id="edit-user-div" class="form-container">
	<form id="edit-user-form" method="post" action="manage_user_update.php">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-user"></i>
					<?php echo lang_get('edit_user_title') ?>
				</h4>
			</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="form-container">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php echo form_security_field( 'manage_user_update' ) ?>
			<!-- Title -->
			<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />

			<!-- Username -->
			<tr>
				<td class="category">
					<?php echo lang_get( 'username_label' ) ?>
				</td>
				<td>
					<input id="edit-username" type="text" class="input-sm" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" name="username" value="<?php echo string_attribute( $t_user['username'] ) ?>" />
				</td>
			</tr>

			<!-- Realname -->
			<tr><?php
			if( $t_ldap && ON == config_get( 'use_ldap_realname' ) ) {
				# With LDAP
				echo '<td class="category">' . lang_get( 'realname_label' ) . '</td>';
				echo '<td>';
				echo string_display_line( user_get_realname( $t_user_id ) );
				echo '</td>';
			} else {
				# Without LDAP ?>
				<td class="category"><?php echo lang_get( 'realname_label' ) ?></td>
				<td><input id="edit-realname" type="text" class="input-sm" size="32" maxlength="<?php echo DB_FIELD_SIZE_REALNAME;?>" name="realname" value="<?php echo string_attribute( $t_user['realname'] ) ?>" /></td><?php
			}
		?>
			</tr>
			<!-- Email -->
			<tr><?php
			if( $t_ldap && ON == config_get( 'use_ldap_email' ) ) {
				# With LDAP
				echo '<td class="category">' . lang_get( 'email_label' ) . '</td>';
				echo '<td>' . string_display_line( user_get_email( $t_user_id ) ) . '</td>';
			} else {
				# Without LDAP
				echo '<td class="category">' . lang_get( 'email_label' ) . '</td>';
				echo '<td>';
				print_email_input( 'email', $t_user['email'] );
				echo '</td>';
			} ?>
			</tr>
			<!-- Access Level -->
			<tr>
				<td class="category">
					<?php echo lang_get( 'access_level_label' ) ?>
				</td>
				<td>
					<select id="edit-access-level" name="access_level" class="input-sm"><?php
						$t_access_level = $t_user['access_level'];
						if( !MantisEnum::hasValue( config_get( 'access_levels_enum_string' ), $t_access_level ) ) {
							$t_access_level = config_get( 'default_new_account_access_level' );
						}
						print_project_access_levels_option_list( (int)$t_access_level ); ?>
					</select>
				</td>
			</tr>
			<!-- Enabled Checkbox -->
			<tr>
				<td class="category">
					<?php echo lang_get( 'enabled_label' ) ?>
				</td>
				<td>
					<label>
						<input type="checkbox" class="ace" id="edit-enabled" name="enabled" <?php check_checked( (int)$t_user['enabled'], ON ); ?>>
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<!-- Protected Checkbox -->
			<tr>
				<td class="category">
					<?php echo lang_get( 'protected_label' ) ?>
				</td>
				<td>
					<label>
						<input type="checkbox" class="ace" id="edit-protected" name="protected" <?php check_checked( (int)$t_user['protected'], ON ); ?>>
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<?php
			if( config_get( 'enable_email_notification' ) == ON ) { ?>
				<tr>
					<td class="category"> <?php echo lang_get( 'notify_user' ) ?> </td>
					<td>
						<label>
							<input type="checkbox" class="ace" id="send-email" name="send_email_notification" checked="checked" ?>
							<span class="lbl"></span>
						</label>
					</td>
				</tr>
			<?php } ?>

			<?php event_signal( 'EVENT_MANAGE_USER_UPDATE_FORM', array( $t_user['id'] ) ); ?>

			<!-- Submit Button -->
		</fieldset>
		</table>
		</div>
		</div>
		</div>

		<div class="widget-toolbox padding-8 clearfix">
			<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'update_user_button' ) ?>" />
		</div>
		</div>
		</div>
	</form>
</div>
<div class="space-10"></div>
<?php
# User action buttons: RESET/UNLOCK and DELETE

$t_reset = $t_user['id'] != auth_get_current_user_id()
	&& helper_call_custom_function( 'auth_can_change_password', array() )
	&& user_is_enabled( $t_user['id'] )
	&& !user_is_protected( $t_user['id'] );
$t_unlock = OFF != config_get( 'max_failed_login_count' ) && $t_user['failed_login_count'] > 0;
$t_delete = !( ( user_is_administrator( $t_user_id ) && ( user_count_level( config_get_global( 'admin_site_threshold' ) ) <= 1 ) ) );
$t_impersonate = auth_can_impersonate( $t_user['id'] );

if( $t_reset || $t_unlock || $t_delete || $t_impersonate ) {
?>
<div id="manage-user-actions-div" class="col-md-6 col-xs-12 no-padding">
<div class="space-8"></div>
<div class="btn-group">

<!-- Reset/Unlock Button -->
<?php if( $t_reset || $t_unlock ) { ?>
	<form id="manage-user-reset-form" method="post" action="manage_user_reset.php" class="pull-left">
		<fieldset>
			<?php echo form_security_field( 'manage_user_reset' ) ?>
			<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
<?php	if( $t_reset ) { ?>
			<span><input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'reset_password_button' ) ?>" /></span>
<?php	} else { ?>
			<span><input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'account_unlock_button' ) ?>" /></span>
<?php	} ?>
		</fieldset>
	</form>
<?php } ?>

<!-- Delete Button -->
<?php if( $t_delete ) { ?>
	<form id="manage-user-delete-form" method="post" action="manage_user_delete.php" class="pull-left">
		<fieldset>
			<?php echo form_security_field( 'manage_user_delete' ) ?>
			<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
			<span><input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'delete_user_button' ) ?>" /></span>
		</fieldset>
	</form>
<?php } ?>

<!-- Impersonate Button -->
<?php if( $t_impersonate ) { ?>
	<form id="manage-user-impersonate-form" method="post" action="manage_user_impersonate.php" class="pull-left">
		<fieldset>
			<?php echo form_security_field( 'manage_user_impersonate' ) ?>
			<input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
			<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'impersonate_user_button' ) ?>" />
		</fieldset>
	</form>
<?php } ?>

</div>
</div>
<?php } ?>

<?php if( $t_reset ) { ?>
<div class="col-md-6 col-xs-12 no-padding">
<div class="space-4"></div>
<div class="alert alert-info">
	<i class="fa fa-info-circle"></i>
<?php
	if( ( ON == config_get( 'send_reset_password' ) ) && ( ON == config_get( 'enable_email_notification' ) ) ) {
		echo lang_get( 'reset_password_msg' );
	} else {
		echo lang_get( 'reset_password_msg2' );
	}
?>
</div>
</div>
<?php } ?>

<?php event_signal( 'EVENT_MANAGE_USER_PAGE', array( $t_user_id ) ); ?>

<div class="clearfix"></div>

<!-- PROJECT ACCESS (if permissions allow) and user is not ADMINISTRATOR -->
<?php if( access_has_global_level( config_get( 'manage_user_threshold' ) ) &&
	!user_is_administrator( $t_user_id ) ) {
?>
<div class="space-10"></div>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
<h4 class="widget-title lighter">
<i class="ace-icon fa fa-user"></i>
<?php echo lang_get('add_user_title') ?>
</h4>
</div>

<div class="widget-body">
<div class="widget-main no-padding">
<div class="form-container">
<div class="table-responsive">
	<table class="table table-bordered table-condensed table-striped">
        <tr>
            <td class="category">
                <?php echo lang_get( 'assigned_projects_label' ) ?>
            </td>
            <td><?php print_project_user_list( $t_user['id'] ) ?></td>
        </tr>
        <form id="manage-user-project-add-form" method="post" action="manage_user_proj_add.php">
        <fieldset>
            <?php echo form_security_field( 'manage_user_proj_add' ) ?>
            <input type="hidden" name="user_id" value="<?php echo $t_user['id'] ?>" />
        <tr>
            <td class="category">
                <?php echo lang_get( 'unassigned_projects_label' ) ?>
            </td>
            <td>
                <select id="add-user-project-id" name="project_id[]" class="input-sm" multiple="multiple" size="5">
                    <?php print_project_user_list_option_list2( $t_user['id'] ) ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="category">
                <?php echo lang_get( 'access_level_label' ) ?>
            </td>
            <td>
                <select id="add-user-project-access" name="access_level" class="input-sm">
                    <?php print_project_access_levels_option_list( (int)config_get( 'default_new_account_access_level' ) ) ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo lang_get( 'add_user_button' ) ?>" />
            </td>
        </tr>
        </fieldset>
        </form>
	</table>
</div>
</div>
</div>
</div>
</div>

<?php
} # End of PROJECT ACCESS conditional section
echo '</div>';

define( 'ACCOUNT_PREFS_INC_ALLOW', true );
include( dirname( __FILE__ ) . '/account_prefs_inc.php' );
edit_account_prefs( $t_user['id'], false, false, 'manage_user_edit_page.php?user_id=' . $t_user_id );

layout_page_end();
