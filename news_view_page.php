<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: news_view_page.php,v 1.34 2005-03-21 12:09:37 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'news_api.php' );
	require_once( $t_core_path . 'print_api.php' );
?>
<?php
	$f_news_id = gpc_get_int( 'news_id', null );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />

<?php
	if ( $f_news_id !== null ) {
		$t_project_id = news_get_field( $f_news_id, 'project_id' );
		if ( news_is_private( $f_news_id ) ) {
			access_ensure_project_level(	config_get( 'private_news_threshold' ),
							$t_project_id );
		} else {
			access_ensure_project_level( VIEWER, $t_project_id );
		}

		print_news_string_by_news_id( $f_news_id );
	}
?>

<br />
<div align="center">
	<?php print_bracket_link( 'news_list_page.php', lang_get( 'archives' ) ); ?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
