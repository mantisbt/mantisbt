<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( !access_level_check_equal( "administrator" ) ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
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
<?
	if ( !isset( $f_sort ) ) {
		$f_sort = "username";
	}

    $query = "SELECT *
    		FROM $g_mantis_user_table
			ORDER BY '$f_sort' ASC";

    $result = db_mysql_query($query);
	$user_count = mysql_num_rows($result);
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
	[ <a href="<? echo $g_manage_create_user_page ?>">Create New Account</a> ]
	[ <a href="<? echo $g_manage_category_page ?>">Manage Categories</a> ]
</div>

<p>
<div align=center>
<table bgcolor=<? echo $g_primary_border_color ?> width=100%>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100%>
	<tr>
		<td>
			<b>Manage Accounts</b>
		</td>
	</tr>
	<tr align=center bgcolor=<?echo $g_category_title_color2 ?>>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=username">username</a>
			</b>
		</td>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=email">email</a>
			</b>
		</td>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=access_level">access level</a>
			</b>
		</td>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=enabled">enabled</a>
			</b>
		</td>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>">p</a>
			</b>
		</td>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=date_created">date created</a>
			</b>
		</td>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=last_visit">last visit</a>
			</b>
		</td>
		<td>
		</td>
	</tr>
<?
	for ($i=0;$i<$user_count;$i++) {
		$row = mysql_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, "v" );
		$v_date_created  = date( "m-d H:i", sql_to_unix_time( $v_date_created ) );
		$v_last_visit    = date( "m-d H:i", sql_to_unix_time( $v_last_visit ) );
		if ( $i % 2 == 1) {
			$bgcolor=$g_primary_color_dark;
		}
		else {
			$bgcolor=$g_primary_color_light;
		}
?>
	<tr bgcolor=<? echo $bgcolor ?>>
		<td>
			<? echo $v_username ?>
		</td>
		<td>
			<a href="mailto:<? echo $v_email ?>"><? echo $v_email ?></a>
		</td>
		<td align=center>
			<?
				echo $v_access_level;
			?>
		</td>
		<td align=center>
			<? echo $v_enabled ?>
		</td>
		<td align=center>
			<?
				if ( $v_protected=="on" ) {
					echo "x";
				}
			?>
		</td>
		<td align=center>
			<? echo $v_date_created ?>
		</td>
		<td align=center>
			<? echo $v_last_visit ?>
		</td>
		<td align=center>
			<a href="<? echo $g_manage_user_page ?>?f_id=<? echo $v_id ?>">edit user</a>
		</td>
	</tr>
<?
	}
?>
	</table>
	</td>
</tr>
</table>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>