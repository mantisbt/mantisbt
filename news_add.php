<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: news_add.php,v 1.29 2004-01-11 07:16:07 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'news_api.php' );
?>
<?php
	access_ensure_project_level( config_get( 'manage_news_threshold' ) );

	$f_view_state	= gpc_get_int( 'view_state' );
	$f_headline		= gpc_get_string( 'headline' );
	$f_announcement	= gpc_get_string( 'announcement', '' );
	$f_body			= gpc_get_string( 'body', '' );

	news_create( helper_get_current_project(), auth_get_current_user_id(), $f_view_state, $f_announcement, $f_headline, $f_body );
	$f_headline = string_display_links( $f_headline );
	$f_body	= string_display_links( $f_body );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( 'news_menu_page.php', lang_get( 'proceed' ) );
?>
<br /><br />
<table class="width75" cellspacing="1">
<tr>
	<td class="news-heading">
		<span class="bold"><?php echo $f_headline ?></span>
	</td>
</tr>
<tr>
	<td class="news-body">
		<?php echo $f_body ?>
	</td>
</tr>
</table>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
