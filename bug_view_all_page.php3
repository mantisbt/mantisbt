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

	$f_hide_resolved = $g_hide_resolved_val;
	if ( isset( $f_dir ) ) {
		if ( $f_dir=="ASC" ) {
			$f_dir = "DESC";
		}
		else {
			$f_dir = "ASC";
		}
	}
	else {
		$f_dir = "DESC";
	}
?>
<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
[ <a href="<? echo $g_view_prefs_page ?>">Viewing Preferences</a> ]
</div>

<p>
<table bgcolor=<? echo $g_primary_border_color ?> width=100%>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100% cols=9>
	<tr>
		<td colspan=9>
			<b>Viewing Bugs</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_category_title_color2 ?> align=center>
		<td width=8%>
			<b>
				<a href="<? echo $g_bug_view_all_page ?>?f_sort=id&f_dir=<? echo $f_dir?>">ID</a>
			</b>
		</td>
		<td width=3%>
			<b>
				#
			</b>
		</td>
		<td width=12%>
			<b>
				<a href="<? echo $g_bug_view_all_page ?>?f_sort=category&f_dir=<? echo $f_dir?>">Category</a>
			</b>
		</td>
		<td width=10%>
			<b>
				<a href="<? echo $g_bug_view_all_page ?>?f_sort=severity&f_dir=<? echo $f_dir?>">Severity</a>
			</b>
		</td>
		<td width=10%>
			<b>
				<a href="<? echo $g_bug_view_all_page ?>?f_sort=status&f_dir=<? echo $f_dir?>">Status</a>
			</b>
		</td>
		<td width=12%>
			<b>
				<a href="<? echo $g_bug_view_all_page ?>?f_sort=last_updated&f_dir=<? echo $f_dir?>">Updated</a>
			</b>
		</td>
		<td width=45%>
			<b>
				<a href="<? echo $g_bug_view_all_page ?>?f_sort=summary&f_dir=<? echo $f_dir?>">Summary</a>
			</b>
		</td>
	</tr>
	<tr height=5>
		<td bgcolor=<? echo $g_white_color ?> colspan=9>
		</td>
	</tr>
	<?
		### $g_view_limit_cookie
		$query = "SELECT * FROM $g_mantis_bug_table";
		if ( $g_hide_resolved_val=="on"  ) {
			$query = $query." WHERE status<>'resolved'";
		}
		if ( !isset( $f_sort ) ) {
				$f_sort="id";
		}
		$query = $query." ORDER BY '$f_sort' $f_dir";
		if ( isset( $g_view_limit_val ) ) {
			$query = $query." LIMIT $g_view_limit_val";
		}


	    $result = db_mysql_query( $query );
		$row_count = mysql_num_rows( $result );

		for($i=0; $i < $row_count; $i++) {
			$row = mysql_fetch_array($result);
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$v_summary = string_unsafe( $v_summary );
			$lastupdated = date( "m-d", sql_to_unix_time( $v_last_updated ) );

			if ($i % 2== 0) {
				$status_color=$g_primary_color_light;
			}
			else {
				$status_color=$g_primary_color_dark;
			}

			if ( $v_status=="new" ) {
				$status_color=$g_new_color;
			}
			if ( $v_status=="acknowledged" ) {
				$status_color=$g_acknowledged_color;
			}
			if ( $v_status=="confirmed" ) {
				$status_color=$g_confirmed_color;
			}
			if ( $v_status=="assigned" ) {
				$status_color=$g_assigned_color;
			}

			$query2 = "SELECT COUNT(id)
						FROM $g_mantis_bugnote_table
						WHERE bug_id ='$v_id'";
			$result2 = db_mysql_query( $query2 );
			$bugnote_count = mysql_result( $result2, 0 );
	?>
	<tr bgcolor=<? echo $status_color ?> align=center>
		<td>
			<a href="<? echo $g_bug_view_page ?>?f_id=<? echo $v_id ?>"><? echo $v_id ?></a>
		</td>
		<td>
			<? if ($bugnote_count > 0) echo $bugnote_count ?>
		</td>
		<td>
			<? echo $v_category ?>
		</td>
		<td>
			<?
				if ( $v_status=="resolved" ) {
					echo $v_severity;
				}
				else {
					if ( ( $v_severity=="major" ) ||
						 ( $v_severity=="crash" ) ||
						 ( $v_severity=="block" ) ) {
						PRINT "<b>";
						echo $v_severity;
						PRINT "</b>";
					}
					else {
						echo $v_severity;
					}
				}
			?>
		</td>
		<td>
			<? echo $v_status ?>
		</td>
		<td>
			<?
				if ( isset( $g_last_access_cookie_val ) ) {
					if ( $v_last_updated >
						$g_last_access_cookie_val ) {
						PRINT "<b>";
						echo $lastupdated;
						PRINT "</b>";
					}
					else {
						echo $lastupdated;
					}
				}
				else {
					echo $lastupdated;
				}
			?>
		</td>
		<td>
			<? echo $v_summary ?>
		</td>
	</tr>
	<?
		}
	?>
	</table>
	</td>
</tr>
</table>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>