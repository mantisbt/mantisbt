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
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );

form_security_validate( 'account_delete' );

auth_ensure_user_authenticated();

helper_ensure_confirmed( lang_get( 'confirm_delete_msg' ),
						 lang_get( 'delete_account_button' ) );

$t_user_id = auth_get_current_user_id();

$t_data = array(
	'query' => array( 'id' => $t_user_id )
);

$t_command = new UserDeleteCommand( $t_data );
$t_command->execute();

form_security_purge( 'account_delete' );

auth_logout();

layout_page_header();

layout_page_begin();
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
<?php
echo lang_get( 'account_removed_msg' ) . '<br />';
print_link_button( config_get_global( 'logout_redirect_page' ), lang_get( 'proceed' ));
?>
</div>

<?php
layout_page_end();
