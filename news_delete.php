<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: news_delete.php,v 1.22 2005-02-12 20:01:06 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'news_api.php' );
?>
<?php
	$f_news_id = gpc_get_int( 'news_id' );

	$row = news_get_row( $f_news_id );

	# This check is to allow deleting of news items that were left orphan due to bug #3723
	if ( project_exists( $row['project_id'] ) ) {
		access_ensure_project_level( config_get( 'manage_news_threshold' ), $row['project_id'] );
	}

	helper_ensure_confirmed( lang_get( 'delete_news_sure_msg' ),
							 lang_get( 'delete_news_item_button' ) );

	news_delete( $f_news_id );

	$t_redirect_url = 'news_menu_page.php';
	print_header_redirect( $t_redirect_url );
?>
