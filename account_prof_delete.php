<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_prof_delete.php,v 1.23 2003-02-11 09:08:29 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# The specified profile is deleted and the user is redirected to
	# account_prof_menu_page.php3
?>
<?php 
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'profile_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_profile_id	= gpc_get_int( 'profile_id' );

	profile_delete( auth_get_current_user_id(), $f_profile_id );

	print_header_redirect( 'account_prof_menu_page.php' );
?>
