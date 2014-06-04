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
 * Lost Password Requests
 *
 * @package MantisBT
 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses email_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'email_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'lost_pwd' );

# lost password feature disabled or reset password via email disabled -> stop here!
if( OFF == config_get( 'lost_password_feature' ) ||
	OFF == config_get( 'send_reset_password' ) ||
	OFF == config_get( 'enable_email_notification' ) ) {
	trigger_error( ERROR_LOST_PASSWORD_NOT_ENABLED, ERROR );
}

# force logout on the current user if already authenticated
if( auth_is_user_authenticated() ) {
	auth_logout();
}

$f_username = gpc_get_string('username');
$f_email = gpc_get_string('email');

email_ensure_valid( $f_email );

$t_user_table = db_get_table( 'user' );

/** @todo Consider moving this query to user_api.php */
$t_query = 'SELECT id FROM ' . $t_user_table . ' WHERE username = ' . db_param() . ' AND email = ' . db_param() . ' AND enabled=' . db_param();
$t_result = db_query_bound( $t_query, array( $f_username, $f_email, true ) );

if( 0 == db_num_rows( $t_result ) ) {
	trigger_error( ERROR_LOST_PASSWORD_NOT_MATCHING_DATA, ERROR );
}

if( is_blank( $f_email ) ) {
	trigger_error( ERROR_LOST_PASSWORD_NO_EMAIL_SPECIFIED, ERROR );
}

$row = db_fetch_array( $t_result );
$t_user_id = $row['id'];

if( user_is_protected( $t_user_id ) ) {
	trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
}

if( !user_is_lost_password_request_allowed( $t_user_id ) ) {
	trigger_error( ERROR_LOST_PASSWORD_MAX_IN_PROGRESS_ATTEMPTS_REACHED, ERROR );
}

$t_confirm_hash = auth_generate_confirm_hash( $t_user_id );
email_send_confirm_hash_url( $t_user_id, $t_confirm_hash );

user_increment_lost_password_in_progress_count( $t_user_id );

form_security_purge( 'lost_pwd' );

$t_redirect_url = 'login_page.php';

html_page_top();
?>

<br />
<div>
<table class="width50" cellspacing="1">
<tr>
	<td class="center">
		<strong><?php echo lang_get( 'lost_password_done_title' ) ?></strong>
	</td>
</tr>
<tr>
	<td>
		<br/>
		<?php echo lang_get( 'reset_request_in_progress_msg' ) ?>
		<br/><br/>
	</td>
</tr>
</table>
<br />
<?php print_bracket_link( 'login_page.php', lang_get( 'proceed' ) ); ?>
</div>

<?php
html_page_bottom1a( __FILE__ );
