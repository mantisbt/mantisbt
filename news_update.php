<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: news_update.php,v 1.32 2005-03-21 12:09:37 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'news_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'print_api.php' );
?>
<?php
	$f_news_id		= gpc_get_int( 'news_id' );
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_view_state	= gpc_get_int( 'view_state' );
	$f_headline		= gpc_get_string( 'headline' );
	$f_announcement	= gpc_get_string( 'announcement', '' );
	$f_body			= gpc_get_string( 'body', '' );

	$row = news_get_row( $f_news_id );

	# Check both the old project and the new project
	access_ensure_project_level( config_get( 'manage_news_threshold' ), $row['project_id'] );
	access_ensure_project_level( config_get( 'manage_news_threshold' ), $f_project_id );

	news_update( $f_news_id, $f_project_id, $f_view_state, $f_announcement, $f_headline, $f_body );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<div align="center">
	<?php echo lang_get( 'operation_successful' ) ?><br />
<?php
	print_bracket_link( 'news_edit_page.php?news_id='.$f_news_id.'&amp;action=edit', lang_get( 'edit_link' ) );
	print_bracket_link( 'news_menu_page.php', lang_get( 'proceed' ) );

	echo '<br /><br />';
	print_news_entry( $f_headline, $f_body, $row['poster_id'], $f_view_state, $f_announcement, $row['date_posted'] );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
