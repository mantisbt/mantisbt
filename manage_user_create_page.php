<?php
# MantisBT - a php based bugtracking system

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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	auth_reauthenticate();

	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$t_ldap = ( LDAP == config_get( 'login_method' ) );

	html_page_top();

	print_manage_menu( 'manage_user_create_page.php' );
?>
<br />
<div align="center">
<form method="post" action="manage_user_create.php">
<?php echo form_security_field( 'manage_user_create' ) ?>
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'create_new_account_title' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category" width="25%">
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="username" size="32" maxlength="<?php echo DB_FIELD_SIZE_USERNAME;?>" />
	</td>
</tr>
<?php
	if ( !$t_ldap || config_get( 'use_ldap_realname' ) == OFF ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'realname' ) ?>
	</td>
	<td>
		<input type="text" name="realname" size="32" maxlength="<?php echo DB_FIELD_SIZE_REALNAME;?>" />
	</td>
</tr>
<?php
	}

	if ( !$t_ldap || config_get( 'use_ldap_email' ) == OFF ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email' ) ?>
	</td>
	<td>
		<?php print_email_input( 'email', '' ) ?>
	</td>
</tr>
<?php
	}

	if ( OFF == config_get( 'send_reset_password' ) )  {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'password' ) ?>
	</td>
	<td>
		<input type="password" name="password" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'verify_password' ) ?>
	</td>
	<td>
		<input type="password" name="password_verify" size="32" maxlength="<?php echo auth_get_password_max_size(); ?>" />
	</td>
</tr>
<?php
	}
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'access_level' ) ?>
	</td>
	<td>
		<select name="access_level">
			<?php print_project_access_levels_option_list( config_get( 'default_new_account_access_level' ) ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'enabled' ) ?>
	</td>
	<td>
		<input type="checkbox" name="enabled" checked="checked" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'protected' ) ?>
	</td>
	<td>
		<input type="checkbox" name="protected" />
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'create_user_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php
	html_page_bottom();
