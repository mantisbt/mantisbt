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
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses api_token_api.php
 * @uses authentication_api.php
 * @uses current_user_api.php
 * @uses html_api.php
 */

require_once( 'core.php' );
require_api( 'api_token_api.php' );
require_api( 'authentication_api.php' );
require_api( 'current_user_api.php' );
require_api( 'html_api.php' );

auth_ensure_user_authenticated();
auth_reauthenticate();

current_user_ensure_unprotected();

html_page_top( lang_get( 'api_tokens_link' ) );
?>

<div id="account-create-api-token-div" class="form-container">
	<form id="account-create-api-token-form" method="post" action="api_token_create.php">
		<fieldset>
			<legend><span><?php echo lang_get( 'create_api_token_form_title' ); ?></span></legend>
<?php echo form_security_field( 'create_api_token_form' ); ?>

<div class="field-container">
	<label for="token_name"><span><?php echo lang_get( 'token_name' ) ?></span></label>
	<span class="input"><input id="token_name" type="text" name="token_name" size="64" maxlength="<?php echo api_token_name_max_length(); ?>" /></span>
	<span class="label-style"></span>
</div>

<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'create_api_token_button' ) ?>" /></span>
</fieldset>
</form>
</div>

<?php
html_page_bottom();

