<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2002 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );

	#============ Parameters ============
	# (none)

	#============ Permissions ============
	auth_ensure_user_authenticated();

?>
<?php

	# extracts the user information for the currently logged in user
	# and prefixes it with u_
	$f_user_id = gpc_get_int( 'id', auth_get_current_user_id() );
	$row = user_get_row( $f_user_id );

	extract( $row, EXTR_PREFIX_ALL, 'u' );

	# In case we're using LDAP to get the email address... this will pull out
	#  that version instead of the one in the DB
	$u_email = user_get_email( $u_id, $u_username );

	html_page_top1();
	html_page_top2();
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
	<tr class="row-1">
		<td class="category" width="25%">
			<?php echo lang_get( 'username' ) ?>
		</td>
		<td width="75%">
			<?php echo $u_username ?>
		</td>
	</tr>

	<!-- Email -->
	<tr class="row-1">
		<td class="category">
			<?php echo lang_get( 'email' ) ?>
		</td>
		<td>
			<?php
				if ( !access_has_project_level( config_get( 'show_user_email_threshold' ) ) ) {
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
	<tr class="row-1" valign="top">
		<td class="category">
			<?php echo lang_get( 'realname' ) ?>
		</td>
		<td>
			<?php 
				if ( !access_has_project_level( config_get( 'show_user_realname_threshold' ) ) ) {
					print error_string(ERROR_ACCESS_DENIED);
				} else {
					echo $u_realname;
				}
			?>
		</td>
	</tr>


</table>
</div>

<br />

<?php html_page_bottom1( __FILE__ ) ?>
