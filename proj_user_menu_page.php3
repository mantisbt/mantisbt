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

	# these pages are invalid for the "All Project" selection
	if ( "0000000" == $g_project_cookie_val ) {
		print_header_redirect( $g_login_select_proj_page );
	}

	if ( !isset( $f_sort ) ) {
		$f_sort = "username";
	}

	### basically we toggle between ASC and DESC if the user clicks the
	### same sort order
	if ( isset( $f_dir ) ) {
		if ( $f_dir=="ASC" ) {
			$f_dir = "DESC";
		} else {
			$f_dir = "ASC";
		}
	} else {
		$f_dir = "ASC";
	}

	$query = "SELECT access_min
			FROM $g_mantis_project_table
			WHERE id='$g_project_cookie_val'";
	$result = db_query( $query );
	$t_access_min_val = db_result( $result, 0, 0 );
	$t_access_min = get_enum_element( $s_access_levels_enum_string, $t_access_min_val );
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
	<? echo $s_automatic_access_level ?>: <span class="bold"><? echo $t_access_min ?></span>
</div>

<p>
<div align="center">
	<? echo  $s_create_user_message ?>
</div>

<p>
<div align="center">
<table class="width75" cellspacing="0">
<form method="post" action="<? echo $g_proj_user_add ?>">
<tr>
	<td class="form-title" colspan="5">
		<? echo $s_add_user_title ?>
	</td>
</tr>
<tr class="row-1">
	<td>
		<? echo $s_username ?>
	</td>
	<td>
		<input type="text" name="f_username" size="32" maxlength="32">
	</td>
	<td>
		<? echo $s_access_level ?>
	</td>
	<td>
		<select name="f_access_level">
			<? ### No administrator choice ?>
			<? print_project_user_option_list( REPORTER ) ?>
		</select>
	</td>
	<td>
		<input type="submit" value="<? echo $s_add_user_button ?>">
	</td>
</tr>
</form>
</table>
</div>

<p>
<div align="center">
<table class="width75" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
		<? echo $s_manage_accounts_title ?>
	</td>
</tr>
<tr class="row-category">
	<td>
		<? print_manage_user_sort_link( $g_proj_user_menu_page, $s_username, "username", $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "username" ) ?>
	</td>
	<td>
		<? print_manage_user_sort_link( $g_proj_user_menu_page, $s_email, "email", $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "email" ) ?>
	</td>
	<td>
		<? print_manage_user_sort_link( $g_proj_user_menu_page, $s_access_level, "access_level", $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "access_level" ) ?>
	</td>
	<td>
		&nbsp;
	</td>
</tr>
<?
	### Get the user data in $f_sort order
    $query = "SELECT *
    		FROM $g_mantis_project_user_list_table
    		WHERE project_id='$g_project_cookie_val'
			ORDER BY '$f_sort' $f_dir";

    $result = db_query($query);
	$user_count = db_num_rows( $result );
	for ($i=0;$i<$user_count;$i++) {
		### prefix user data with u_
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, "u" );

		$query2 = "SELECT username, email
				FROM $g_mantis_user_table
				WHERE id='$u_user_id'";
		$result2 = db_query( $query2 );
		$row2 = db_fetch_array( $result2 );
		extract( $row2, EXTR_PREFIX_ALL, "u" );

		### alternate row colors
		$t_bgcolor = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );
?>
<tr bgcolor="<? echo $t_bgcolor ?>">
	<td>
		<? echo $u_username ?>
	</td>
	<td>
		<? print_email_link( $u_email, $u_email ) ?>
	</td>
	<td>
		<? echo get_enum_element( $s_access_levels_enum_string, $u_access_level ) ?>
	</td>
	<td>
		<? print_bracket_link( $g_proj_user_delete_page."?f_user_id=".$u_user_id, $s_remove_link ) ?>
	</td>
</tr>
<?
	}  ### end for
?>
</table>
</div>

<p>
<div align="center">
<table class="width75" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
		<? echo $s_automatic_access ?>: (<? echo $t_access_min ?>)
	</td>
</tr>
<tr class="row-category">
	<td>
		<? print_manage_user_sort_link( $g_proj_user_menu_page, $s_username, "username", $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "username" ) ?>
	</td>
	<td>
		<? print_manage_user_sort_link( $g_proj_user_menu_page, $s_email, "email", $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "email" ) ?>
	</td>
	<td>
		<? print_manage_user_sort_link( $g_proj_user_menu_page, $s_access_level, "access_level", $f_dir ) ?>
		<? print_sort_icon( $f_dir, $f_sort, "access_level" ) ?>
	</td>
	<td>
		&nbsp;
	</td>
</tr>
<?
	### Get the user data in $f_sort order
    $query = "SELECT DISTINCT u.id
				FROM $g_mantis_user_table u, $g_mantis_project_user_list_table ul
				WHERE u.access_level>='$t_access_min_val' AND
					u.id<>ul.user_id";
    $result = db_query( $query );
	$user_count = db_num_rows( $result );
	for ($i=0;$i<$user_count;$i++) {
		### prefix user data with u_
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, "u" );

		$query2 = "SELECT username, email, access_level
				FROM $g_mantis_user_table
				WHERE id='$u_id'";
		$result2 = db_query( $query2 );
		$row2 = db_fetch_array( $result2 );
		extract( $row2, EXTR_PREFIX_ALL, "u" );

		### alternate row colors
		$t_bgcolor = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );
?>
<tr bgcolor="<? echo $t_bgcolor ?>">
	<td>
		<? echo $u_username ?>
	</td>
	<td>
		<? print_email_link( $u_email, $u_email ) ?>
	</td>
	<td>
		<? echo get_enum_element( $s_access_levels_enum_string, $u_access_level ) ?>
	</td>
	<td>
		<? print_bracket_link( $g_proj_user_delete_page."?f_user_id=".$u_id, $s_remove_link ) ?>
	</td>
</tr>
<?
	}  ### end for
?>
</table>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>