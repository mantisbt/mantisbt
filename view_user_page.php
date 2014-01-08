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
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'current_user_api.php' );

	auth_ensure_user_authenticated();

	# extracts the user information for the currently logged in user
	# and prefixes it with u_
	$f_user_id = gpc_get_int( 'id', auth_get_current_user_id() );
	$row = user_get_row( $f_user_id );

	extract( $row, EXTR_PREFIX_ALL, 'u' );

	$t_can_manage = access_has_global_level( config_get( 'manage_user_threshold' ) ) &&
		access_has_global_level( $u_access_level );
	$t_can_see_realname = access_has_project_level( config_get( 'show_user_realname_threshold' ) );
	$t_can_see_email = access_has_project_level( config_get( 'show_user_email_threshold' ) );

	# In case we're using LDAP to get the email address... this will pull out
	#  that version instead of the one in the DB
	$u_email = user_get_email( $u_id );
	$u_realname = user_get_realname( $u_id );

	html_page_top();
?>

<br />
<div align="center">
<table class="width75" cellspacing="1">

	<!-- Headings -->
	<tr>
		<td class="form-title">
			<?php echo lang_get( 'view_account_title' ) ?>
		</td>
	</tr>

	<!-- Username -->
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category" width="25%">
			<?php echo lang_get( 'username' ) ?>
		</td>
		<td width="75%">
			<?php echo string_display_line( $u_username ) ?>
		</td>
	</tr>

	<!-- Email -->
	<tr <?php echo helper_alternate_class() ?>>
		<td class="category">
			<?php echo lang_get( 'email' ) ?>
		</td>
		<td>
			<?php
				if ( ! ( $t_can_manage || $t_can_see_email ) ) {
					print error_string(ERROR_ACCESS_DENIED);
				} else {
					if ( !is_blank( $u_email ) ) {
						print_email_link( $u_email, $u_email );
					} else {
						echo " - ";
					}
				}
			?>
		</td>
	</tr>

	<!-- Realname -->
	<tr <?php echo helper_alternate_class() ?> valign="top">
		<td class="category">
			<?php echo lang_get( 'realname' ) ?>
		</td>
		<td>
			<?php
				if ( ! ( $t_can_manage || $t_can_see_realname ) ) {
					print error_string(ERROR_ACCESS_DENIED);
				} else {
					echo string_display_line( $u_realname );
				}
			?>
		</td>
	</tr>

	<?php if ( $t_can_manage ) { ?>
	<tr>
		<td colspan="2" class="center">
			<?php print_bracket_link( 'manage_user_edit_page.php?user_id=' . $f_user_id, lang_get( 'manage_user' ) ); ?>
		</td>
	</tr>
	<?php } ?>
</table>
</div>

<br />

<?php
	html_page_bottom();
