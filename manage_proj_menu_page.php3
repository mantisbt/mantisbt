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

	if ( !isset( $f_sort ) ) {
		$f_sort = "name";
	}

	# basically we toggle between ASC and DESC if the user clicks the
	# same sort order
	if ( isset( $f_dir ) ) {
		if ( "ASC" == $f_dir ) {
			$f_dir = "DESC";
		} else {
			$f_dir = "ASC";
		}
	}
	else {
		$f_dir = "ASC";
	}

?>
<? print_page_top1() ?>
<? print_page_top2() ?>

<? print_manage_menu( $g_manage_project_menu_page ) ?>

<? # Add Project Form BEGIN ?>
<p>
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" action="<? echo $g_manage_project_add ?>">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_add_project_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<? echo $s_project_name?>
	</td>
	<td width="75%">
		<input type="text" name="f_name" size="64" maxlength="128">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_status ?>
	</td>
	<td>
		<select name="f_status">
		<? print_enum_string_option_list( $s_project_status_enum_string ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_view_status ?>
	</td>
	<td>
		<input type="radio" name="f_view_state" value="10" CHECKED> <? echo $s_public ?>
		<input type="radio" name="f_view_state" value="50"> <? echo $s_private ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_access_threshold ?>
	</td>
	<td>
		<select name="f_access_min">
			<? print_enum_string_option_list( $s_access_levels_enum_string, VIEWER ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_upload_file_path ?>
	</td>
	<td>
		<input type="text" name="f_file_path" size="70" maxlength="250">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_description ?>
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="5" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_add_project_button ?>">
	</td>
</tr>
</form>
</table>
</div>
<? # Add Project Form END ?>

<? # Project Menu Form BEGIN ?>
<p>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<? echo $s_projects_title ?>
	</td>
</tr>
<tr class="row-category">
	<td width="15%">
		<? print_manage_project_sort_link(  $g_manage_project_menu_page, $s_name, "name", $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "name" ) ?>
	</td>
	<td width="10%">
		<? print_manage_project_sort_link(  $g_manage_project_menu_page, $s_status, "status", $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "status" ) ?>
	</td>
	<td width="8%">
		<? print_manage_project_sort_link(  $g_manage_project_menu_page, $s_enabled, "enabled", $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "enabled" ) ?>
	</td>
	<td width="12%">
		<? print_manage_project_sort_link(  $g_manage_project_menu_page, $s_view_status, "view_state", $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "view_state" ) ?>
	</td>
	<td width="40%">
		<? print_manage_project_sort_link(  $g_manage_project_menu_page, $s_description, "description", $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "description" ) ?>
	</td>
	<td width="4%">
		&nbsp;
	</td>
</tr>
<?
	$query = "SELECT *
			FROM $g_mantis_project_table
			ORDER BY '$f_sort' $f_dir";
	$result = db_query( $query );
	$project_count = db_num_rows( $result );
	for ($i=0;$i<$project_count;$i++) {
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "v" );

		$v_name 		= string_display( $v_name );
		$v_description 	= string_display( $v_description );

		# alternate row colors
		$t_bgcolor = alternate_colors( $i, $g_primary_color1, $g_primary_color2 );
?>
<tr bgcolor="<? echo $t_bgcolor ?>">
	<td>
		<? echo $v_name ?>
	</td>
	<td>
		<? echo get_enum_element( $s_project_status_enum_string, $v_status ) ?>
	</td>
	<td>
		<? echo trans_bool( $v_enabled ) ?>
	</td>
	<td>
		<? echo get_enum_element( $s_project_view_state_enum_string, $v_view_state ) ?>
	</td>
	<td align="left">
		<? echo $v_description ?>
	</td>
	<td>
		<? print_bracket_link( $g_manage_project_edit_page."?f_project_id=".$v_id,  $s_edit_link ) ?>
	</td>
</tr>
<?
	}
?>
</table>
<? # Project Menu Form END ?>

<? print_page_bot1( __FILE__ ) ?>