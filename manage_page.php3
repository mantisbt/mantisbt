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

	if ( !access_level_check_equal( "administrator" ) ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}

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

	### Get the user data in $f_sort order
    $query = "SELECT *
    		FROM $g_mantis_user_table
			ORDER BY '$f_sort' $f_dir";

    $result = db_query($query);
	$user_count = db_num_rows( $result );
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
	[ <a href="<? echo $g_manage_create_user_page ?>"><? echo $s_create_new_account_link ?></a> ]
	[ <a href="<? echo $g_manage_category_page ?>"><? echo $s_manage_categories_link ?></a> ]
	[ <a href="<? echo $g_manage_product_versions_page ?>"><? echo $s_manage_product_versions_link ?></a> ]
	[ <a href="<? echo $g_documentation_page ?>"><? echo $s_documentation_link ?></a> ]
</div>

<p>
<div align=center>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table cols=8 width=100%>
	<tr>
		<td colspan=8 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_manage_accounts_title ?></b>
		</td>
	</tr>
	<tr align=center bgcolor=<?echo $g_category_title_color2 ?>>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=username&f_dir=<? echo $f_dir?>"><? echo $s_username ?></a>
			</b>
		</td>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=email&f_dir=<? echo $f_dir?>"><? echo $s_email ?></a>
			</b>
		</td>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=access_level&f_dir=<? echo $f_dir?>"><? echo $s_access_level ?></a>
			</b>
		</td>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=enabled&f_dir=<? echo $f_dir?>"><? echo $s_enabled ?></a>
			</b>
		</td>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=protected&f_dir=<? echo $f_dir?>"><? echo $s_p ?></a>
			</b>
		</td>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=date_created&f_dir=<? echo $f_dir?>"><? echo $s_date_created ?></a>
			</b>
		</td>
		<td>
			<b>
				<a href="<? echo $g_manage_page ?>?f_sort=last_visit&f_dir=<? echo $f_dir?>"><? echo $s_last_visit ?></a>
			</b>
		</td>
		<td>
		</td>
	</tr>
<?
	for ($i=0;$i<$user_count;$i++) {
		### prefix user data with u_
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, "u" );

		$u_date_created  = date( "m-d H:i", sql_to_unix_time( $u_date_created ) );
		$u_last_visit    = date( "m-d H:i", sql_to_unix_time( $u_last_visit ) );

		### alternate row colors
		if ( $i % 2 == 1) {
			$bgcolor=$g_primary_color_dark;
		}
		else {
			$bgcolor=$g_primary_color_light;
		}
?>
	<tr bgcolor=<? echo $bgcolor ?>>
		<td>
			<? echo $u_username ?>
		</td>
		<td>
			<a href="mailto:<? echo $u_email ?>"><? echo $u_email ?></a>
		</td>
		<td align=center>
			<? echo $u_access_level ?>
		</td>
		<td align=center>
			<? echo $u_enabled ?>
		</td>
		<td align=center>
			<?
				if ( $u_protected=="on" ) {
					echo "x";
				}
			?>
		</td>
		<td align=center>
			<? echo $u_date_created ?>
		</td>
		<td align=center>
			<? echo $u_last_visit ?>
		</td>
		<td align=center>
			<a href="<? echo $g_manage_user_page ?>?f_id=<? echo $u_id ?>"><? echo $s_edit_user_link ?></a>
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

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>