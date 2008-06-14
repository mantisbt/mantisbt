<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
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
	# $Id: account_delete.php,v 1.30.22.1 2007-10-13 22:31:56 giallu Exp $
	# --------------------------------------------------------

	# CALLERS
	#	This page is called from:
	#	- account_page.php

	# EXPECTED BEHAVIOUR
	#	- Delete the currently logged in user account
	#	- Logout the current user
	#	- Redirect to the page specified in the logout_redirect_page config option

	# CALLS
	#	This page conditionally redirects upon completion

	# RESTRICTIONS & PERMISSIONS
	#	- User must be authenticated
	#	- allow_account_delete config option must be enabled

	require_once( 'core.php' );

	#============ Parameters ============
	# (none)

	#============ Permissions ============
	# helper_ensure_post();

	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();

	if ( OFF == config_get( 'allow_account_delete' ) ) {
		print_header_redirect( 'account_page.php' );
	}
?>
<?php
	helper_ensure_confirmed( lang_get( 'confirm_delete_msg' ),
							 lang_get( 'delete_account_button' ) );

	user_delete( auth_get_current_user_id() );

	auth_logout();

	$t_redirect = config_get( 'logout_redirect_page' );

	html_meta_redirect( $t_redirect );

	html_page_top1();

?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
