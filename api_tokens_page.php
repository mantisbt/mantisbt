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
 * @uses database_api.php
 * @uses html_api.php
 */

require_once( 'core.php' );
require_api( 'api_token_api.php' );
require_api( 'authentication_api.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'html_api.php' );

auth_ensure_user_authenticated();
auth_reauthenticate();

current_user_ensure_unprotected();

html_page_top( lang_get( 'api_tokens_link' ) );
?>

<div id="api-token-create-div" class="form-container">
	<form id="account-create-api-token-form" method="post" action="api_token_create.php">
		<fieldset>
			<legend><span><?php echo lang_get( 'api_token_create_form_title' ); ?></span></legend>
			<?php print_account_menu( 'api_tokens_page.php' ) ?>
			<?php echo form_security_field( 'create_api_token_form' ); ?>

			<div class="field-container">
				<label for="token_name"><span><?php echo lang_get( 'api_token_name' ) ?></span></label>
				<span class="input"><input id="token_name" type="text" name="token_name" size="64" maxlength="<?php echo DB_FIELD_SIZE_API_TOKEN_NAME; ?>" /></span>
				<span class="label-style"></span>
			</div>

			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'api_token_create_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>
<?php
$t_user_id = auth_get_current_user_id();
$t_tokens = api_token_get_all( $t_user_id );
$t_date_format = config_get( 'normal_date_format' );

if ( count( $t_tokens ) > 0 ) {
?>
	<div id="api-tokens-list-div" class="form-container">
	<fieldset>
		<legend><span><?php echo lang_get( 'api_tokens_title' ); ?></span></legend>
	</fieldset>

	<table>
		<thead>
			<tr class="row-category">
				<th><?php echo lang_get( 'api_token_name' ); ?></th>
				<th><?php echo lang_get( 'date_created' ); ?></th>
				<th><?php echo lang_get( 'last_used' ); ?></th>
				<th><?php echo lang_get( 'actions' ); ?></th>
			</tr>
		</thead>
		<tbody>
	<?php foreach( $t_tokens as $t_token ) {
			extract( $t_token, EXTR_PREFIX_ALL, 'u' );

			$u_date_created  = date( $t_date_format, $u_date_created );

			if( api_token_is_used( $t_token ) ) {
				$u_date_used = date( $t_date_format, $u_date_used );
			} else {
				$u_date_used = lang_get( 'api_token_never_used' );
			}
		?>
			<tr>
				<td><?php echo string_display_line( $u_name ) ?></td>
				<td><?php echo string_display_line( $u_date_created ) ?></td>
				<td><?php echo string_display_line( $u_date_used ) ?></td>
				<td>
					<form id="revoke-api-token-form" method="post" action="api_token_revoke.php">
						<?php echo form_security_field( 'revoke_api_token_form' ); ?>
						<fieldset>
							<input id="token_id" type="hidden" name="token_id" value="<?php echo $u_id ; ?>" />
							<input id="token_name" type="hidden" name="token_name" value="<?php echo string_attribute( $u_name ); ?>" />
							<input type="submit" class="button" value="<?php echo lang_get( 'api_token_revoke_button' ) ?>" />
						</fieldset>
					</form>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
<?php
}

html_page_bottom();
