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

	if ( !access_level_check_greater_or_equal( "reporter" ) ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}

	### If deleteing profile redirect to delete script
	if ( $f_action=="delete") {
		header( "Location: $g_account_profile_delete?f_id=$f_id" );
		exit;
	}
	### If Defaulting profile redirect to make default script
	else if ( $f_action=="make default") {
		header( "Location: $g_account_profile_make_default?f_id=$f_id&f_user_id=$f_user_id" );
		exit;
	}

	### Retrieve new item data and prefix with v_
	$query = "SELECT *
		FROM $g_mantis_user_profile_table
		WHERE id='$f_id'";
    $result = db_mysql_query( $query );
	$row = mysql_fetch_array( $result );
	if ( $row ) {
    	extract( $row, EXTR_PREFIX_ALL, "v" );
    }

   	$v_platform = string_edit( $v_platform );
   	$v_os = string_edit( $v_os );
   	$v_os_build = string_edit( $v_os_build );
   	$v_description  = string_edit( $v_description );
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
	<table cols=2 width=100%>
	<form method=post action="<? echo $g_account_profile_update ?>">
		<input type=hidden name=f_id value="<? echo $v_id ?>">
	<tr>
		<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_edit_profile_title ?></b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td width=25%>
			<? echo $s_platform ?>
		</td>
		<td width=75%>
			<input type=text name=f_platform size=32 maxlength=32 value="<? echo $v_platform ?>">
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<? echo $s_operating_system ?>
		</td>
		<td>
			<input type=text name=f_os size=32 maxlength=32 value="<? echo $v_os ?>">
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			<? echo $s_version ?>
		</td>
		<td>
			<input type=text name=f_os_build size=16 maxlength=16 value="<? echo $v_os_build ?>">
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<? echo $s_additional_description ?>
		</td>
		<td>
			<textarea name=f_description cols=60 rows=8><? echo $v_description ?></textarea>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2>
			<input type=submit value="<? echo $s_update_profile_button ?>">
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