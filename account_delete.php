<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_delete.php,v 1.21 2002-09-22 09:35:06 jfitzell Exp $
	# --------------------------------------------------------

	# CALLERS
	#	This page is called from:
	#	- account_delete_page.php

	# EXPECTED BEHAVIOUR
	#	- Delete the currently logged in user account
	#	- Logout the current user
	#	- Redirect to the page specified in the logout_redirect_page config option

	# RESTRICTIONS & PERMISSIONS
	#	- User must be authenticated
	#	- allow_account_delete config option must be enabled

	require_once( 'core.php' );

	#============ Variables ============
	# (none)

	#============ Permissions ============
	login_cookie_check();

	if ( OFF == config_get( 'allow_account_delete' ) ) {
		print_header_redirect( 'account_page.php' );
	}
?>
<?php

	user_delete( auth_get_current_user_id() );

	auth_logout();

	$t_redirect = config_get( 'logout_redirect_page' );

	print_meta_redirect( $t_redirect );
	
	print_page_top1();

?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
