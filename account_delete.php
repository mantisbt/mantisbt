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
 * CALLERS
 *	This page is called from:
 *	- account_page.php
 *
 * EXPECTED BEHAVIOUR
 *	- Delete the currently logged in user account
 *	- Logout the current user
 *	- Redirect to the page specified in the logout_redirect_page config option
 *
 * CALLS
 *	This page conditionally redirects upon completion
 *
 * RESTRICTIONS & PERMISSIONS
 *	- User must be authenticated
 *	- allow_account_delete config option must be enabled
 * @todo review form security tokens for this page
 * @todo should page_top1 be before meta redirect?
 *
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
 /**
  * MantisBT Core API's
  */
require_once( 'core.php' );

auth_ensure_user_authenticated();

current_user_ensure_unprotected();

if ( OFF == config_get( 'allow_account_delete' ) ) {
	print_header_redirect( 'account_page.php' );
}

helper_ensure_confirmed( lang_get( 'confirm_delete_msg' ),
						 lang_get( 'delete_account_button' ) );

user_delete( auth_get_current_user_id() );

auth_logout();

html_meta_redirect( config_get( 'logout_redirect_page' ) );

html_page_top1();

?>

<br />
<div align="center">
<?php
echo lang_get( 'operation_successful' ) . '<br />';
print_bracket_link( $t_redirect, lang_get( 'proceed' ) );
?>
</div>

<?php
	html_page_bottom( __FILE__ );
