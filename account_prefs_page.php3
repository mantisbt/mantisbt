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

	### grab the user id
    $u_id = get_current_user_id();

	### Grab the data
    $query = "SELECT *
    		FROM $g_mantis_user_pref_table
			WHERE user_id='$u_id'";
    $result = db_mysql_query($query);

    ## OOPS, No entry in the database yet.  Lets make one
    if ( mysql_num_rows( $result )==0 ) {

		### Create row
	    $query = "INSERT
	    		INTO $g_mantis_user_pref_table
	    		(id, user_id, advanced_report, advanced_view)
	    		VALUES
	    		(null, '$u_id',
	    		'$g_default_advanced_report', '$g_default_advanced_view')";
	    $result = db_mysql_query($query);

		### Rerun select query
	    $query = "SELECT *
	    		FROM $g_mantis_user_pref_table
				WHERE user_id='$u_id'";
	    $result = db_mysql_query($query);
    }

    ### prefix data with u_
	$row = mysql_fetch_array($result);
	extract( $row, EXTR_PREFIX_ALL, "u" );
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<table width=50% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100% cols=2>
	<form method=post action="<? echo $g_account_prefs_update ?>">
		<input type=hidden name=f_action value="update">
		<input type=hidden name=f_id value="<? echo $u_id ?>">
		<input type=hidden name=f_user_id value="<? echo $u_user_id ?>">
	<tr>
		<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_default_account_preferences ?></b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<? echo $s_advanced_report ?>
		</td>
		<td>
			<input type=checkbox name=f_advanced_report size=4 maxlength=4 <? if ( $u_advanced_report=="on" ) echo "CHECKED"?>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			<? echo $s_advanced_report ?>
		</td>
		<td>
			<input type=checkbox name=f_advanced_view size=4 maxlength=4 <? if ( $u_advanced_view=="on" ) echo "CHECKED"?>
		</td>
	</tr>
	<tr align=center>
		<td>
			<input type=submit value="<? echo $s_update_prefs ?>">
		</td>
		</form>
		<form method=post action="<? echo $g_account_prefs_update ?>">
			<input type=hidden name=f_action value="reset">
			<input type=hidden name=f_id value="<? echo $u_id ?>">
		<td>
			<input type=submit value="<? echo $s_reset_prefs ?>">
		</td>
		</form>
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