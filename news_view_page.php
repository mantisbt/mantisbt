<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'news_api.php' );
	require_once( $t_core_path.'string_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>
<?php
	$f_news_id = gpc_get_int( 'news_id' );

	$row = news_get_row( $f_news_id );

	extract( $row, EXTR_PREFIX_ALL, 'v' );

	access_ensure_project_level( VIEWER, $v_project_id );
	if ( PRIVATE == $v_view_state ) {
		access_ensure_project_level( config_get( 'private_news_threshold' ), $v_project_id );
	}

	$v_headline 	= string_display( $v_headline );
	$v_body 		= string_display_links( $v_body );
	$v_date_posted 	= date( config_get( 'normal_date_format' ), $v_date_posted );
?>
<br />
<div align="center">
<table class="width75" cellspacing="0">
<tr>
	<td class="news-heading">
		<span class="bold"><?php echo $v_headline ?></span> -
		<span class="italic-small"><?php echo $v_date_posted ?></span> -
		<span class="news-email">
		<?php
			print_user( $v_poster_id );
		?>
		</span>
	</td>
</tr>
<tr>
	<td class="news-body">
		<?php echo $v_body ?>
	</td>
</tr>
</table>
</div>

<br />
<div align="center">
	<?php print_bracket_link( 'news_list_page.php', lang_get( 'back_link' ) ) ?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
