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

	# set cookie values for hide, sort by, and dir
	if ( isset( $f_save ) ) {
		if ( isset( $f_hide ) ) {
			$f_hide = 1;
		} else {
			$f_hide = 0;
		}
		$t_manage_string = $f_hide.":".$f_sort.":".$f_dir;
		setcookie( $g_manage_cookie, $t_manage_string, time()+$g_cookie_time_length );
	} else if ( !empty( $g_manage_cookie_val ) ) {
		$t_manage_arr = explode( ":", $g_manage_cookie_val );
		$f_hide = $t_manage_arr[0];

		if ( isset( $t_manage_arr[1] ) ) {
			$f_sort = $t_manage_arr[1];
		} else {
			$f_sort = "username";
		}

		if ( isset( $t_manage_arr[2] ) ) {
			$f_dir  = $t_manage_arr[2];
		} else {
			$f_dir = "DESC";
		}
	} else {
		$f_hide = 0;
		$f_sort = "username";
		$f_dir  = "DESC";
	}

	# we toggle between ASC and DESC if the user clicks the same sort order
	if ( $f_dir=="ASC" ) {
		$f_dir = "DESC";
	}
	else {
		$f_dir = "ASC";
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
?>
<p>
<div align="center">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td colspan="8" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_new_accounts_title ?> (<? echo $s_1_week_title ?>)</b> <? echo " [".$new_user_count."]" ?>
		</td>
	</tr>
	<tr>
		<td bgcolor="<? echo $g_primary_color_dark ?>">
<?
	for ($i=0;$i<$new_user_count;$i++) {
		$row = db_fetch_array( $result );
		$t_username = $row["username"];

		echo $t_username." : ";
	}
?>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<?
	### Get the user data in $f_sort order
	$query = "SELECT *
		FROM $g_mantis_user_table
		WHERE login_count=0
		ORDER BY date_created";
	$result = db_query( $query );
	$user_count = db_num_rows( $result );
?>
<p>
<div align="center">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td colspan="8" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_never_logged_in_title ?></b> <? echo " [".$user_count."]" ?> <? print_bracket_link( $g_manage_prune, $s_prune_accounts ) ?>
		</td>
	</tr>
	<tr>
		<td bgcolor="<? echo $g_primary_color_dark ?>">
<?
	for ($i=0;$i<$user_count;$i++) {
		$row = db_fetch_array( $result );
		$t_username = $row["username"];

		echo $t_username." : ";
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
<form method="post" action="<? echo $g_manage_page ?>">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr align="right">
		<td bgcolor="<? echo $g_table_title_color ?>">
			<input type="hidden" name="f_save" value="1">
			<input type=checkbox name=f_hide <? if ( $f_hide==1 ) echo "CHECKED" ?>> <? echo $s_hide_inactive ?>
			<input type=submit value="<? echo $s_filter_button ?>">
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</form>
</div>
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
?>
<p>
<div align="center">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td colspan="8" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_manage_accounts_title ?></b> <? echo "[".$user_count."]" ?>
		</td>
	</tr>
	<tr align="center" bgcolor="<? echo $g_category_title_color2 ?>">
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_username, "username", $f_dir, $f_hide ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "username" ) ?>
		</td>
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_email, "email", $f_dir, $f_hide ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "email" ) ?>
		</td>
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_access_level, "access_level", $f_dir, $f_hide ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "access_level" ) ?>
		</td>
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_enabled, "enabled", $f_dir, $f_hide ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "enabled" ) ?>
		</td>
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_p, "protected", $f_dir, $f_hide ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "protected" ) ?>
		</td>
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_date_created, "date_created", $f_dir, $f_hide ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "date_created" ) ?>
		</td>
		<td>
			<? print_manage_user_sort_link(  $g_manage_page, $s_last_visit, "last_visit", $f_dir, $f_hide ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "last_visit" ) ?>
		</td>
		<td>
		</td>
	</tr>
<?
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
			<? echo get_enum_element( $s_access_levels_enum_string, $u_access_level ) ?>
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