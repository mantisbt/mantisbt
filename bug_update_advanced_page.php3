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
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
[ <a href="<? echo $g_view_bug_yadvanced_page ?>?f_id=<? echo $f_id ?>">Back</a> ]
</div>

<?
    $query = "SELECT *
    		FROM $g_mantis_user_table
			WHERE cookie_string='$g_string_cookie_val'";
    $result = db_mysql_query($query);
	$row = mysql_fetch_array($result);
	if ( $row ) {
		extract( $row, EXTR_PREFIX_ALL, "u" );
	}

    $query = "SELECT *
    		FROM $g_mantis_bug_table
    		WHERE id='$f_id'";
    $result = db_mysql_query( $query );
	$row_count = mysql_num_rows( $result );

	$row = mysql_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v" );

    $query = "SELECT username, email
    		FROM $g_mantis_user_table
    		WHERE id='$v_handler_id'";
    $result = db_mysql_query( $query );
    if ( $result ) {
   		$row = mysql_fetch_array( $result );
		$t_handler_name		= $row["username"];
		$t_handler_email	= $row["email"];
	}

    $query = "SELECT username, email
    		FROM $g_mantis_user_table
    		WHERE id='$v_reporter_id'";
    $result = db_mysql_query( $query );
    if ( $result ) {
   		$row = mysql_fetch_array( $result );
		$t2_handler_name		= $row["username"];
		$t2_handler_email	= $row["email"];
	}

    $query = "SELECT *
    		FROM $g_mantis_bug_text_table
    		WHERE id='$v_bug_text_id'";
    $result = db_mysql_query( $query );
	$row_count = mysql_num_rows( $result );

	$row = mysql_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v2" );

	$v_summary = string_unsafe( $v_summary );
	$v2_description = string_unsafe( $v2_description );
	$v2_steps_to_reproduce = string_unsafe( $v2_steps_to_reproduce );
	$v2_additional_information = string_unsafe( $v2_additional_information );
	$v_date_submitted = date( "m-d H:i", sql_to_unix_time( $v_date_submitted ) );
	$v_last_updated = date( "m-d H:i", sql_to_unix_time( $v_last_updated ) );
?>

<p>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<form method=post action="<? echo $g_bug_update ?>">
<input type=hidden name=f_id value="<? echo $v_id ?>">
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table cols=6 width=100% bgcolor=<? echo $g_white_color ?>>
	<tr>
		<td colspan=6 bgcolor=<? echo $g_table_title_color ?>>
			<b>Viewing Bug Details</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_category_title_color ?> align=center>
		<td width=15%>
			<b>ID</b>
		</td>
		<td width=20%>
			<b>Category</b>
		</td>
		<td width=15%>
			<b>Severity</b>
		</td>
		<td width=20%>
			<b>Reproducibility</b>
		</td>
		<td width=15%>
			<b>Date Submitted</b>
		</td>
		<td width=15%>
			<b>Last Update</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?> align=center>
		<td>
			<? echo $v_id ?>
		</td>
		<td>
			<select name=f_category>
				<? print_categories( $v_category ) ?>
			</select>
		</td>
		<td>
			<select name=f_severity>
				<option value="block" <? if ( $v_severity=="block" ) echo "SELECTED" ?>>block
				<option value="crash" <? if ( $v_severity=="crash" ) echo "SELECTED" ?>>crash
				<option value="major" <? if ( $v_severity=="major" ) echo "SELECTED" ?>>major
				<option value="minor" <? if ( $v_severity=="minor" ) echo "SELECTED" ?>>minor
				<option value="tweak" <? if ( $v_severity=="tweak" ) echo "SELECTED" ?>>tweak
				<option value="text" <? if ( $v_severity=="text" ) echo "SELECTED" ?>>text
				<option value="trivial" <? if ( $v_severity=="trivial" ) echo "SELECTED" ?>>trivial
				<option value="feature" <? if ( $v_severity=="feature" ) echo "SELECTED" ?>>feature
			</select>
		</td>
		<td>
			<select name=f_reproducibility>
				<option value="always" <? if ( $v_reproducibility=="always" ) echo "SELECTED" ?>>always
				<option value="sometimes" <? if ( $v_reproducibility=="sometimes" ) echo "SELECTED" ?>>sometimes
				<option value="random" <? if ( $v_reproducibility=="random" ) echo "SELECTED" ?>>random
				<option value="have not tried" <? if ( $v_reproducibility=="have not tried" ) echo "SELECTED" ?>>have not tried
				<option value="unable to duplicate" <? if ( $v_reproducibility=="unable to duplicate" ) echo "SELECTED" ?>>unable to duplicate
			</select>
		</td>
		<td>
			<? echo $v_date_submitted ?>
		</td>
		<td>
			<? echo $v_last_updated ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Reporter</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=5>
			<?
				if ( isset( $t2_handler_name ) ) {
					echo "<a href=\"mailto:$t2_handler_email\">".$t2_handler_name."</a>";
				}
				else {
					echo "user no longer exists";
				}
			?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Assigned To</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=5>
			<select name=f_handler_id>
				<option value="">
			<?
			    $query = "SELECT id, username
			    		FROM $g_mantis_user_table
			    		WHERE access_level='administrator' OR access_level='developer'";
			    $user_result = mysql_query( $query );
			    $user_count = mysql_num_rows( $user_result );
			    for ($i=0;$i<$user_count;$i++) {
			    	$row = mysql_fetch_array( $user_result );
			    	$t_handler_name	= $row["username"];
			    	$t_handler_id	= $row["id"];

			    	if ( $t_handler_id==$v_handler_id ) {
						echo "<option value=\"$t_handler_id\" SELECTED>".$t_handler_name;
					}
					else {
						echo "<option value=\"$t_handler_id\">".$t_handler_name;
					}
				}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Priority</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<select name=f_priority>
				<option value="immediate" <? if ( $v_priority=="immediate" ) echo "SELECTED" ?>>immediate
				<option value="urgent" <? if ( $v_priority=="urgent" ) echo "SELECTED" ?>>urgent
				<option value="high" <? if ( $v_priority=="high" ) echo "SELECTED" ?>>high
				<option value="normal" <? if ( $v_priority=="normal" ) echo "SELECTED" ?>>normal
				<option value="low" <? if ( $v_priority=="low" ) echo "SELECTED" ?>>low
				<option value="none" <? if ( $v_priority=="none" ) echo "SELECTED" ?>>none
			</select>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=2>

		</td>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Platform</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<? echo $v_platform ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Status</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?>>
			<select name=f_status>
				<option value="new" <? if ( $v_status=="new" ) echo "SELECTED" ?>>new
				<option value="acknowledged" <? if ( $v_status=="acknowledged" ) echo "SELECTED" ?>>acknowledged
				<option value="confirmed" <? if ( $v_status=="confirmed" ) echo "SELECTED" ?>>confirmed
				<option value="assigned" <? if ( $v_status=="assigned" ) echo "SELECTED" ?>>assigned
				<option value="resolved" <? if ( $v_status=="resolved" ) echo "SELECTED" ?>>resolved
			</select>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=2>

		</td>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>OS</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?>>
			<? echo $v_os ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Projection</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<select name=f_projection>
				<option value="redesign" <? if ( $v_projection=="redesign" ) echo "SELECTED" ?>>redesign
				<option value="major rework" <? if ( $v_projection=="major rework" ) echo "SELECTED" ?>>major rework
				<option value="minor fix" <? if ( $v_projection=="minor fix" ) echo "SELECTED" ?>>minor fix
				<option value="tweak" <? if ( $v_projection=="tweak" ) echo "SELECTED" ?>>tweak
			</select>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=2>

		</td>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>OS version</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<? echo $v_os_build ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>ETA</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?>>
			<select name=f_eta>
				<option value="none" <? if ( $v_eta=="none" ) echo "SELECTED" ?>>none
				<option value="< 1 day" <? if ( $v_eta=="< 1 day" ) echo "SELECTED" ?>>< 1 day
				<option value="2-3 days" <? if ( $v_eta=="2-3 days" ) echo "SELECTED" ?>>2-3 days
				<option value="< 1 week" <? if ( $v_eta=="< 1 week" ) echo "SELECTED" ?>>< 1 week
				<option value="< 1 month" <? if ( $v_eta=="< 1 month" ) echo "SELECTED" ?>>< 1 month
				<option value="> 1 month" <? if ( $v_eta=="> 1 month" ) echo "SELECTED" ?>>> 1 month
			</select>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=2>

		</td>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Product Version</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?>>
			<? echo $v_version ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Resolution</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<select name=f_resolution>
				<option value="open" <? if ( $v_resolution=="open" ) echo "SELECTED" ?>>open
				<option value="fixed" <? if ( $v_resolution=="fixed" ) echo "SELECTED" ?>>fixed
				<option value="duplicate" <? if ( $v_resolution=="duplicate" ) echo "SELECTED" ?>>duplicate
				<option value="not a bug" <? if ( $v_resolution=="not a bug" ) echo "SELECTED" ?>>not a bug
				<option value="suspended" <? if ( $v_resolution=="suspended" ) echo "SELECTED" ?>>suspended
				<option value="reopened" <? if ( $v_resolution=="reopened" ) echo "SELECTED" ?>>reopened
				<option value="wont fix" <? if ( $v_resolution=="wont fix" ) echo "SELECTED" ?>>wont fix
				<option value="not fixable" <? if ( $v_resolution=="not fixable" ) echo "SELECTED" ?>>not fixable
				<option value="unable to duplicate" <? if ( $v_resolution=="unable to duplicate" ) echo "SELECTED" ?>>unable to duplicate
			</select>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=2>

		</td>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Build</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<? echo $v_build?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Duplicate ID</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?>>
			<select name=f_duplicate_id>
				<option value="0000000">
			<?
			    $query = "SELECT id
			    		FROM $g_mantis_bug_table
			    		ORDER BY id ASC";
			    $user_result = mysql_query( $query );
			    $user_count = mysql_num_rows( $user_result );
			    for ($i=0;$i<$user_count;$i++) {
			    	$row = mysql_fetch_array( $user_result );
			    	$t_duplicate_id	= $row["id"];

			    	if ( $t_duplicate_id==$v_duplicate_id ) {
						echo "<option value=\"$t_duplicate_id\" SELECTED>".$t_duplicate_id;
					}
					else {
						echo "<option value=\"$t_duplicate_id\">".$t_duplicate_id;
					}
				}
			?>
			</select>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=2>

		</td>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Votes</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?>>
			<? echo $v_votes ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Summary</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=5>
			<? echo $v_summary ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Description</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=5>
			<? echo $v2_description ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Steps to<br>Reproduce</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=5>
			<? echo $v2_steps_to_reproduce ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Additional<br>Information</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=5>
			<? echo $v2_additional_information ?>
		</td>
	</tr>
	<tr>
		<td align=center bgcolor=<? echo $g_white_color ?> colspan=6>
			<input type=submit value="Update Information">
		</td>
	</tr>
	</table>
	</td>
</tr>
</form>
</table>


<?
		$id = $v_id;
		$handler = $row["handler"];
		$reproducibility = $row["reproducibility"];
		$severity = $row["severity"];
		$status = $row["status"];
?>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>