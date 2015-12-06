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
 * This page stores the reported bug
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses api_token_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'api_token_api.php' );
require_api( 'string_api.php' );

form_security_validate( 'create_api_token_form' );

auth_ensure_user_authenticated();
auth_reauthenticate();

$f_token_name = gpc_get_string( 'token_name' );

$t_user_id = auth_get_current_user_id();

user_ensure_unprotected( $t_user_id );

$t_token = api_token_create( $f_token_name, $t_user_id );
$t_disclose_message = lang_get( 'api_token_disclose_message' );
$t_display_once_message = lang_get( 'api_token_displayed_once' );

html_page_top();
?>

<div align="center">
<br /><br />
<?php echo $t_disclose_message ?>
<br />
<span class="important-msg"><?php echo $t_display_once_message ?></span>
<br /><br />
<span class="api-token"><?php echo string_display_line( $t_token ) ?></span>
<br /><br />
<?php print_bracket_link( 'api_tokens_page.php', lang_get( 'api_tokens_link' ) ) ?>
<br />
</div>

<?php
html_page_bottom();

