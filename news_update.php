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

	$f_id			= gpc_get_int( 'f_id' );
	$f_project_id	= gpc_get_int( 'f_project_id' );
	$f_view_state	= gpc_get_int( 'f_view_state' );
	$f_headline		= gpc_get_string( 'f_headline' );
	$f_announcement	= gpc_get_string( 'f_announcement', '' );
	$f_body			= gpc_get_string( 'f_body', '' );

    news_update( $f_id, $f_project_id, $f_view_state, $f_announcement, $f_headline, $f_body );
    $f_headline 	= string_display( $f_headline );
    $f_body 		= string_display( $f_body );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
	<?php echo $s_operation_successful ?><br />
<table class="width75" cellspacing="1">
<tr>
	<td class="news-heading">
		<span class="news-headline"><?php echo $f_headline ?></span>
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
	print_bracket_link( 'news_edit_page.php?f_id='.$f_id.'&amp;f_action=edit', $s_edit_link );
	print_bracket_link( 'news_menu_page.php', $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
