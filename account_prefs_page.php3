<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### This page allows the user to set his/her preferences
	### Update is POSTed to acount_prefs_update.php3
	### Reset is POSTed to acount_prefs_reset.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### grab the user id
	$u_id = get_current_user_field( "id " );

	### Grab the data
    $query = "SELECT *
    		FROM $g_mantis_user_pref_table
			WHERE user_id='$u_id'";
    $result = db_query($query);

    ## OOPS, No entry in the database yet.  Lets make one
    if ( db_num_rows( $result )==0 ) {

		### Create row # @@@@@ Add the rest of the fields
	    $query = "INSERT
	    		INTO $g_mantis_user_pref_table
	    		(id, user_id, advanced_report, advanced_view, language)
	    		VALUES
	    		(null, '$u_id',
	    		'$g_default_advanced_report', '$g_default_advanced_view', 'english')";
	    $result = db_query($query);

		### Rerun select query
	    $query = "SELECT *
	    		FROM $g_mantis_user_pref_table
				WHERE user_id='$u_id'";
	    $result = db_query($query);
    }

    ### prefix data with u_
	$row = db_fetch_array($result);
	extract( $row, EXTR_PREFIX_ALL, "u" );
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

<? print_account_menu( $g_account_prefs_page ) ?>

<? ### Account Preferences Form BEGIN ?>
<p>
<div align="center">
<table width="50%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%" cols="2">
	<form method="post" action="<? echo $g_account_prefs_update ?>">
	<tr>
		<td colspan="2" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_default_account_preferences_title ?></b>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_default_project ?>
		</td>
		<td>
			<select name="f_project_id">
			<option value="0000000">
			<? print_project_option_list( $u_default_project ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_advanced_report ?>
		</td>
		<td>
			<input type="checkbox" name="f_advanced_report" <? if ( $u_advanced_report==1 ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_advanced_view ?>
		</td>
		<td>
			<input type="checkbox" name="f_advanced_view" <? if ( $u_advanced_view=="1" ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_advanced_update ?>
		</td>
		<td>
			<input type="checkbox" name="f_advanced_update" <? if ( $u_advanced_update=="1" ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_refresh_delay ?>
		</td>
		<td>
			<input type="text" name="f_refresh_delay" size="4" maxlength="4" value="<? echo $u_refresh_delay ?>">
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_redirect_delay ?>
		</td>
		<td>
			<input type="text" name="f_redirect_delay" size="1" maxlength="1" value="<? echo $u_redirect_delay ?>">
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_email_on_new ?>
		</td>
		<td>
			<input type="checkbox" name="f_email_on_new" <? if ( $u_email_on_new=="1" ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_email_on_assigned ?>
		</td>
		<td>
			<input type="checkbox" name="f_email_on_assigned" <? if ( $u_email_on_assigned=="1" ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_email_on_feedback ?>
		</td>
		<td>
			<input type="checkbox" name="f_email_on_feedback" <? if ( $u_email_on_feedback=="1" ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_email_on_resolved ?>
		</td>
		<td>
			<input type="checkbox" name="f_email_on_resolved" <? if ( $u_email_on_resolved=="1" ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_email_on_closed ?>
		</td>
		<td>
			<input type="checkbox" name="f_email_on_closed" <? if ( $u_email_on_closed=="1" ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_email_on_reopened ?>
		</td>
		<td>
			<input type="checkbox" name="f_email_on_reopened" <? if ( $u_email_on_reopened=="1" ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_email_on_bugnote_added ?>
		</td>
		<td>
			<input type="checkbox" name="f_email_on_bugnote" <? if ( $u_email_on_bugnote=="1" ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_email_on_status_change ?>
		</td>
		<td>
			<input type="checkbox" name="f_email_on_status" <? if ( $u_email_on_status=="1" ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_email_on_priority_change ?>
		</td>
		<td>
			<input type="checkbox" name="f_email_on_priority" <? if ( $u_email_on_priority=="1" ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_language ?>
		</td>
		<td>
			<select name=f_language>
				<? print_language_option_list( $u_language ) ?>
			</select>
		</td>
	</tr>
	<tr align="center">
		<td>
			<input type="submit" value="<? echo $s_update_prefs_button ?>">
		</td>
		</form>
		<form method="post" action="<? echo $g_account_prefs_reset ?>">
			<input type="hidden" name="f_id" value="<? echo $u_id ?>">
		<td>
			<input type="submit" value="<? echo $s_reset_prefs_button ?>">
		</td>
		</form>
	</tr>
	</form>
	</table>
	</td>
</tr>
</table>
</div>
<? ### Account Preferences Form END ?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>