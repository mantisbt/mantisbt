<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: news.php,v 1.1 2004-02-10 11:37:43 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path . 'news_api.php' );
	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'news_inc.php' );
?>
<?php
	$f_news_id = gpc_get_int( 'news_id', null );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />

<?php
	if ( $f_news_id !== null ) {
		if ( news_is_private( $f_news_id ) ) {
			access_ensure_project_level(	config_get( 'private_news_threshold' ), 
							news_get_field( $f_news_id, 'project_id' ) );
		} else {
			access_ensure_project_level( VIEWER );
		}

		print_news_string_by_news_id( $f_news_id );
	}
?>

<?php html_page_bottom1( __FILE__ ) ?>
