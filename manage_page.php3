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
		$f_sort = "username";
	}

	### basically we toggle between ASC and DESC if the user clicks the
	### same sort order
	if ( isset( $f_dir ) ) {
		if ( $f_dir=="ASC" ) {
			$f_dir = "DESC";
		}
		else {
			$f_dir = "ASC";
		}
	}
	else {
		$f_dir = "ASC";
	}

	if ( !isset( $f_hide ) ) {
		$f_hide = 0;
	} else if ( $f_hide=="on" ) {
		$f_hide = 1;
	}
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

<? print_manage_menu( $g_manage_page ) ?>

<p>
<div align="center">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td colspan="8" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_new_accounts_title ?> (<? echo $s_1_week_title ?>)</b>
		</td>
	</tr>
	<tr>
		<td bgcolor="<? echo $g_primary_color_dark ?>">
<?
	### Get the user data in $f_sort order
	$days_old = 7;
	$query = "SELECT *
		FROM $g_mantis_user_table
		WHERE TO_DAYS(NOW()) - TO_DAYS(date_created) <= '$days_old'
		ORDER BY date_created DESC";
//		WHERE UNIX_TIMESTAMP(date_created)>$one_week_ago
	$result = db_query( $query );
	$new_user_count = db_num_rows( $result );
	for ($i=0;$i<$new_user_count;$i++) {
		$row = db_fetch_array( $result );
		$t_username = $row["username"];

		if ( $i+1 < $new_user_count ) {
			echo $t_username." : ";
		} else {
			echo $t_username;
		}
	}
?>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<p>
<div align="center">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td colspan="8" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_never_logged_in_title ?></b> <? print_bracket_link( $g_manage_prune, "Prune Accounts" ) ?>
		</td>
	</tr>
	<tr>
		<td bgcolor="<? echo $g_primary_color_dark ?>">
<?
	### Get the user data in $f_sort order
	$query = "SELECT *
		FROM $g_mantis_user_table
		WHERE login_count=0
		ORDER BY date_created";
	$result = db_query( $query );
	$user_count = db_num_rows( $result );
	for ($i=0;$i<$user_count;$i++) {
		$row = db_fetch_array( $result );
		$t_username = $row["username"];

		if ( $i+1 < $user_count ) {
			echo $t_username." : ";
		} else {
			echo $t_username;
		}
	}
?>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<p>
<div align="center">
<form method="post" action="<? echo $g_manage_page ?>?f_hide=<?$f_hide ?>">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr align="right">
		<td bgcolor="<? echo $g_table_title_color ?>">
			<input type=checkbox name=f_hide <? if ( $f_hide==1 ) echo "CHECKED" ?>> Hide Inactive
			<input type=submit value="<? echo $s_filter_button ?>">
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</form>
</div>

<p>
<div align="center">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td colspan="8" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_manage_accounts_title ?></b>
		</td>
	</tr>
	<tr align="center" bgcolor="<? echo $g_category_title_color2 ?>">
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_username, "username", $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "username" ) ?>
		</td>
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_email, "email", $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "email" ) ?>
		</td>
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_access_level, "access_level", $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "access_level" ) ?>
		</td>
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_enabled, "enabled", $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "enabled" ) ?>
		</td>
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_p, "protected", $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "protected" ) ?>
		</td>
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_date_created, "date_created", $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "date_created" ) ?>
		</td>
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_last_visit, "last_visit", $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "last_visit" ) ?>
		</td>
		<td>
		</td>
	</tr>
<?
	### Get the user data in $f_sort order
	if ( $f_hide==0 ) {
		$query = "SELECT *,  UNIX_TIMESTAMP(date_created) as date_created
				FROM $g_mantis_user_table
				ORDER BY '$f_sort' $f_dir";
	} else {
		$query = "SELECT *,  UNIX_TIMESTAMP(date_created) as date_created
				FROM $g_mantis_user_table
				WHERE (TO_DAYS(NOW()) - TO_DAYS(last_visit) < '$days_old')
				ORDER BY '$f_sort' $f_dir";
	}

    $result = db_query($query);
	$user_count = db_num_rows( $result );
	for ($i=0;$i<$user_count;$i++) {
		### prefix user data with u_
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, "u" );

		$u_date_created  = date( $g_normal_date_format, $u_date_created );
		$u_last_visit    = date( $g_normal_date_format, sql_to_unix_time( $u_last_visit ) );

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
		<td align="center">
			<? echo get_enum_element( $g_access_levels_enum_string, $u_access_level ) ?>
		</td>
		<td align="center">
			<? echo trans_bool( $u_enabled ) ?>
		</td>
		<td align="center">
			<? echo trans_bool( $u_protected ) ?>
		</td>
		<td align="center">
			<? echo $u_date_created ?>
		</td>
		<td align="center">
			<? echo $u_last_visit ?>
		</td>
		<td align="center">
			<? print_bracket_link( $g_manage_user_page."?f_id=".$u_id, $s_edit_user_link ) ?>
		</td>
	</tr>
<?
	}  ### end for
?>
	</table>
	</td>
</tr>
</table>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>