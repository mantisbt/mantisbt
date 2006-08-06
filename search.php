<?php
       # Mantis - a php based bugtracking system
       # Copyright (C) 2000 - 2002 Kenzaburo Ito - kenito@300baud.org
       # Copyright (C) 2002 - 2006 Mantis Team - mantisbt-dev@lists.sourceforge.net
       # This program is distributed under the terms and conditions of the GPL
       # See the README and LICENSE files for details

       # --------------------------------------------------------
       # $Revision: 1.1 $
       # $Author: vboctor $
       # $Date: 2006-08-06 06:08:50 $
       #
       # $Id: search.php,v 1.1 2006-08-06 06:08:50 vboctor Exp $
       # --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'compress_api.php' );
	require_once( $t_core_path . 'filter_api.php' );

	auth_ensure_user_authenticated();

	$f_page_number = gpc_get_int( 'page_number', 1 );
	$f_search = gpc_get_string( 'search' );

	$t_per_page = null;
	$t_bug_count = null;
	$t_page_count = null;

	$my_filter = filter_get_default();
	$my_filter['search'] = $f_search;

	$rows = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, $my_filter, null, null, true );
	if ( $rows === false ) {
		print_header_redirect( 'view_all_set.php?type=0' );
	}

	$t_bugslist = array();
	for ( $i = 0; $i < sizeof( $rows ); $i++ ) {
		array_push( $t_bugslist, $rows[$i]['id'] );
	}

	gpc_set_cookie( config_get( 'bug_list_cookie' ), implode( ',', $t_bugslist ) );

	compress_enable();

	html_page_top1( lang_get( 'view_bugs_link' ) );

	if ( current_user_get_pref( 'refresh_delay' ) > 0 ) {
		html_meta_redirect( 'view_all_bug_page.php?page_number='.$f_page_number, current_user_get_pref( 'refresh_delay' )*60 );
	}

	html_page_top2();

	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'view_all_inc.php' );

	html_page_bottom1( __FILE__ );
?>