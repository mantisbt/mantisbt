<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	
	require_once( $g_core_path . 'news_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	check_access( config_get( 'manage_project_threshold' ) );

	$f_project_id	= gpc_get_int( 'project_id' );
	$f_poster_id	= gpc_get_int( 'poster_id' );
	$f_view_state	= gpc_get_int( 'view_state' );
	$f_headline		= gpc_get_string( 'headline' );
	$f_announcement	= gpc_get_string( 'announcement', '' );
	$f_body			= gpc_get_string( 'body', '' );

	news_create( $f_project_id, $f_poster_id, $f_view_state, $f_announcement, $f_headline, $f_body );
    $f_headline = string_display( $f_headline );
    $f_body 	= string_display( $f_body );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
	<?php echo $s_operation_successful ?><br />
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
<br />
<?php
	print_bracket_link( 'news_menu_page.php', $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
