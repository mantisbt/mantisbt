<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( !isset( $f_offset ) ) {
		$f_offset = 0;
	}

	### get news count
	$query = "SELECT COUNT(id)
			FROM $g_mantis_news_table";
	$result = mysql_query( $query );
    $total_news_count = mysql_result( $result, 0 );

	$query = "SELECT *
			FROM $g_mantis_news_table
			ORDER BY id DESC
			LIMIT $f_offset, $g_news_view_limit";
	$result = db_mysql_query( $query );
    $news_count = mysql_num_rows( $result );
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<?
	for ($i=0;$i<$news_count;$i++) {
		$row = mysql_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, "v" );
		$v_headline = string_display( $v_headline );
		$v_body = string_display( $v_body );
		$v_date_posted = date( "m-d H:i", sql_to_unix_time( $v_date_posted ) );

		## grab the username and email of the poster
	    $query = "SELECT username, email
	    		FROM $g_mantis_user_table
	    		WHERE id='$v_poster_id'";
	    $result2 = mysql_query( $query );
	    if ( $result2 ) {
	    	$row = mysql_fetch_array( $result2 );
			$t_poster_name	= $row["username"];
			$t_poster_email	= $row["email"];
		}
?>
<p>
<div align=center>
<table width=75% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_primary_color_dark ?>>
		<b><? echo $v_headline ?></b> -
		<i><? echo $v_date_posted ?></i> -
		<a href="mailto:<? echo $t_poster_email ?>"><? echo $t_poster_name ?></a>
	</td>
</tr>
<tr>
	<td bgcolor=<? echo $g_primary_color_light ?>>
		<br>
		<blockquote>
		<? echo $v_body ?>
		</blockquote>
	</td>
</tr>
</table>
</div>
<?
	}
?>

<?
	$f_offset_next = $f_offset + $g_news_view_limit;
	$f_offset_prev = $f_offset - $g_news_view_limit;

	PRINT "<p>";
	PRINT "<div align=center>";
	if ( $f_offset_prev >= 0) {
		PRINT "[ <a href=\"$g_main_page?f_offset=$f_offset_prev\">newer news</a> ]";
	}
	if ( $news_count==$g_news_view_limit ) {
		PRINT " [ <a href=\"$g_main_page?f_offset=$f_offset_next\">older news</a> ]";
	}
	PRINT "</div>";
?>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>