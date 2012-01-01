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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	form_security_validate('manage_user_delete');

	auth_reauthenticate();
	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$f_user_id	= gpc_get_int( 'user_id' );

	$t_user = user_get_row( $f_user_id );

	# Ensure that the account to be deleted is of equal or lower access to the
	# current user.
	access_ensure_global_level( $t_user['access_level'] );

	# check that we are not deleting the last administrator account
	$t_admin_threshold = config_get_global( 'admin_site_threshold' );
	if ( user_is_administrator( $f_user_id ) &&
	     user_count_level( $t_admin_threshold ) <= 1 ) {
		trigger_error( ERROR_USER_CHANGE_LAST_ADMIN, ERROR );
	}

	# If an administrator is trying to delete their own account, use
	# account_delete.php instead as it is handles logging out and redirection
	# of users who have just deleted their own accounts.
	if ( auth_get_current_user_id() == $f_user_id ) {
		form_security_purge( 'manage_user_delete' );
		print_header_redirect( 'account_delete.php?account_delete_token=' . form_security_token( 'account_delete' ), true, false );
	}

	helper_ensure_confirmed( lang_get( 'delete_account_sure_msg' ) .
		'<br />' . lang_get( 'username' ) . ': ' . $t_user['username'],
		lang_get( 'delete_account_button' ) );

	user_delete( $f_user_id );

	form_security_purge('manage_user_delete');

	html_page_top( null, 'manage_user_page.php' );
?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( 'manage_user_page.php', lang_get( 'proceed' ) );
?>
</div>

<?php
	html_page_bottom();
