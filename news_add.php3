<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	### Add news
    $result 	= news_add_query( $f_project_id, $f_poster_id, $f_headline, $f_body );
    $f_headline = string_display( $f_headline );
    $f_body 	= string_display( $f_body );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>

<? print_menu( $g_menu_include_file ) ?>

<p>
<div align="center">
<?
	if ( $result ) {			### SUCCESS
		PRINT "$s_news_added_msg<p>";
?>
<table width="75%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_primary_color_dark ?>">
		<b><? echo $f_headline ?></b>
	</td>
</tr>
<tr>
	<td bgcolor="<? echo $g_primary_color_light ?>">
		<br>
		<blockquote>
			<? echo $f_body ?>
		</blockquote>
	</td>
</tr>
</table>
<p>
<?
	} else {					### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_news_menu_page, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>