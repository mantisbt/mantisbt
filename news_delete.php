<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( MANAGER );

	$f_news_id = gpc_get_int( 'f_news_id' );

	helper_ensure_confirmed( lang_get( 'delete_news_sure_msg' ),
							 lang_get( 'delete_news_item_button' ) );

    news_delete( $f_news_id );

    $t_redirect_url = 'news_menu_page.php';
	print_header_redirect( $t_redirect_url );
?>
