<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.47 $
	# $Author: jfitzell $
	# $Date: 2003-02-17 01:50:09 $
	#
	# $Id: view_all_bug_page.php,v 1.47 2003-02-17 01:50:09 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'filter_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_page_number		= gpc_get_int( 'page_number', 1 );

	# check to see if the cookie does not exist
	if ( !filter_is_cookie_valid() ) {
		print_header_redirect( 'view_all_set.php?type=0' );
	}

	$t_bug_count = null;
	$t_page_count = null;

	$rows = filter_get_bug_rows( &$f_page_number, null, &$t_page_count, &$t_bug_count );

	compress_enable();

	print_page_top1();

	if ( current_user_get_pref( 'refresh_delay' ) > 0 ) {
		print_meta_redirect( 'view_all_bug_page.php?page_number='.$f_page_number, current_user_get_pref( 'refresh_delay' )*60 );
	}

	print_page_top2();

	include( $g_view_all_include_file );

	print_page_bot1( __FILE__ );
?>
