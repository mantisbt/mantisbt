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

	### Get user information and prefix with u_
	$query = "SELECT id
		FROM $g_mantis_user_table
		WHERE cookie_string='$g_string_cookie_val'";
    $result = db_mysql_query($query);
    $u_id = mysql_result( $result, 0 );
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
[ <a href="<? echo $g_report_bug_page ?>">Simple Report</a> ]
</div>

<p>
<div align=center>
<table width=75% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100%>
	<form method=post action="<? echo $g_report_add ?>">
	<tr>
		<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
			<b>Enter Report Details</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td valign=top width=25%>
			Category:<br>
			<font color=<? echo $g_required_field_color ?> size=-1>[*required*]</font>
		</td>
		<td>
			<select name=f_category>
				<option value="" selected>Select Category
				<? print_categories() ?>
			</select>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Reproducibility:<br>
			<font color=<? echo $g_required_field_color ?> size=-1>[*required*]</font>
		</td>
		<td>
			<select name=f_reproducibility>
				<option value="" selected>Select Reproducebility
				<option value="always">always
				<option value="sometimes">sometimes
				<option value="random">random
				<option value="have not tried">have not tried
				<option value="unable to duplicate">unable to duplicate
			</select>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			Severity:<br>
			<font color=<? echo $g_required_field_color ?> size=-1>[*required*]</font>
		<td>
			<select name=f_severity>
				<option value="" selected>Select Severity
				<option value="block">block
				<option value="crash">crash
				<option value="major">major
				<option value="minor">minor
				<option value="tweak">tweak
				<option value="text">text
				<option value="trivial">trivial
				<option value="feature">feature
			</select>
		</td>
	</tr>
	<tr height=10 bgcolor=<? echo $g_white_color ?>>
		<td colspan=2>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Select Profile:
		</td>
		<td>
			<select name=f_id>
				<option value="">
			<?
				### Get profiles
				$query = "SELECT id, platform, os, os_build, default_profile
					FROM $g_mantis_user_profile_table
					WHERE user_id='$u_id'
					ORDER BY id DESC";
			    $result = db_mysql_query( $query );
			    $profile_count = mysql_num_rows( $result );

				for ($i=0;$i<$profile_count;$i++) {
					### prefix data with v_
					$row = mysql_fetch_array( $result );
					extract( $row, EXTR_PREFIX_ALL, "v" );
					$v_platform	= string_unsafe( $v_platform );
					$v_os		= string_unsafe( $v_os );
					$v_os_build	= string_unsafe( $v_os_build );

					if ( $v_default_profile=="on" ) {
						PRINT "<option value=\"$v_id\" SELECTED>$v_platform $v_os $v_os_build";
					}
					else {
						PRINT "<option value=\"$v_id\">$v_platform $v_os $v_os_build";
					}
				}

			?>
			</select>
		</td>
	</tr>
	<tr height=10 bgcolor=<? echo $g_white_color ?>>
		<td colspan=2>
		<b>OR</b> Fill in
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Platform:
		</td>
		<td>
			<input type=text name=f_platform size=32 maxlength=32>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			OS:
		</td>
		<td>
			<input type=text name=f_os size=32 maxlength=32>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dlight ?>>
		<td>
			OS Build:
		</td>
		<td>
			<input type=text name=f_osbuild size=16 maxlength=16>
		</td>
	</tr>
	<tr height=10 bgcolor=<? echo $g_white_color ?>>
		<td colspan=2>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			Product Version
		</td>
		<td>
			<input type=text name=f_version size=16 maxlength=16>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Product Build
		</td>
		<td>
			<input type=text name=f_build size=4 maxlength=4>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			Summary:<br>
			<font color=<? echo $g_required_field_color ?> size=-1>[*required*]</font>
		</td>
		<td>
			<input type=text name=f_summary size=80 maxlength=128>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Description:<br>
			<font color=<? echo $g_required_field_color ?> size=-1>[*required*]</font>
		</td>
		<td>
			<textarea name=f_description cols=60 rows=5></textarea>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			Steps to Reproduce:
		</td>
		<td>
			<textarea name=f_steps_to_reproduce cols=60 rows=5></textarea>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Additional Information:
		</td>
		<td>
			<textarea name=f_additional_info cols=60 rows=5></textarea>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2>
			<input type=submit value="  Submit Report  ">
		</td>
	</tr>
	</form>
	</table>
	</td>
</tr>
</table>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>
