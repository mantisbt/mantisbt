<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: login_anon.php,v 1.15 2005-02-12 20:01:05 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
 /* login_anon.php logs a user in anonymously without having to enter a username
 * or password.
 *
 * Depends on two global configuration variables:
 * allow_anonymous_login - bool which must be true to allow anonymous login.
 * anonymous_account - name of account to login with.
 *
 * TODO:
 * Check how manage account is impacted.
 * Might be extended to allow redirects for bug links etc.
 */
	require_once( 'core.php' );

	print_header_redirect( 'login.php?username=' . config_get( 'anonymous_account' ) . '&amp;perm_login=false' );
?>
