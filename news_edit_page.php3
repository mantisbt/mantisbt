<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( !access_level_check_greater_or_equal( "developer" ) ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}

	### If Deleteing item redirect to delete script
	if ( $f_action=="delete" ) {
		header( "Location: $g_news_delete_page?f_id=$f_id" );
		exit;
	}

	### Retrieve new item data and prefix with v_
	$query = "SELECT *
		FROM $g_mantis_news_table
		WHERE id='$f_id'";
    $result = db_mysql_query( $query );
	$row = mysql_fetch_array( $result );
	if ( $row ) {
    	extract( $row, EXTR_PREFIX_ALL, "v" );
    }

   	$v_headline = string_edit( $v_headline );
   	$v_body = string_edit( $v_body );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<table width=75% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100%>
	<form method=post action="<? echo $g_news_update ?>">
		<input type=hidden name=f_id value="<? echo $v_id ?>">
		<input type=hidden name=f_date_posted value="<? echo $v_date_posted ?>">
	<tr>
		<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_edit_news_title ?></b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td width=25%>
			<? echo $s_headline ?>
		</td>
		<td>
			<input type=text name=f_headline size=64 maxlength=64 value="<? echo $v_headline ?>">
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td width=75%>
			<? echo $s_body ?>
		</td>
		<td>
			<textarea name=f_body cols=60 rows=10><? echo $v_body ?></textarea>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2>
			<input type=submit value="<? echo $s_update_news_button ?>">
		</td>
	</tr>
	</form>
	</table>
	</td>
</tr>
</table>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>