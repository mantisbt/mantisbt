<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( !isset( $f_type ) ) {
		print_header_redirect( $g_view_all_bug_page );
	}

	if ( ON == $g_hide_closed_default ) {
		$g_hide_closed_default = "on";
	} else {
		$g_hide_closed_default = "";
	}

	if ( !isset( $f_hide_closed ) ) {
		$f_hide_closed = "";
	}

	# show bugs per page
	if ( !isset( $f_per_page ) || ( $f_per_page < 0 ) ) {
		$f_per_page = $g_default_limit_view;
	}

	# sort direction
	if ( !isset( $f_sort ) ) {
		$f_sort = "last_updated";
	}
	if ( !isset( $f_dir ) ) {
		$f_dir = "DESC";
	}

	# date values
	if ( !isset( $f_start_month ) ) {
		$f_start_month = date( "m" );
	}
	if ( !isset( $f_end_month ) ) {
		$f_end_month = date( "m" );
	}
	if ( !isset( $f_start_day ) ) {
		$f_start_day = 1;
	}
	if ( !isset( $f_end_day ) ) {
		$f_end_day = date( "d" );
	}
	if ( !isset( $f_start_year ) ) {
		$f_start_year = date( "Y" );
	}
	if ( !isset( $f_end_year ) ) {
		$f_end_year = date( "Y" );
	}

/*   array contents
     --------------
	 0: version
	 1: $f_show_category
	 2: $f_show_severity
	 3: $f_show_status
	 4: $f_per_page
	 5: $f_highlight_changed
	 6: $f_hide_closed
	 7: $f_user_id
	 8: $f_assign_id
	 9: $f_sort
	10: $f_dir
	11: $f_start_month
	12: $f_start_day
	13: $f_start_year
	14: $f_end_month
	15: $f_end_day
	16: $f_end_year
*/
	# Set new filter values.  These are stored in a cookie
	$t_setting_arr	= explode( "#", $g_view_all_cookie_val );
	switch ( $f_type ) {
		# New cookie
		case "0":
				$t_settings_string = "$g_cookie_version".
									"#any#any".
									"#any#$f_per_page".
									"#$g_default_show_changed#$g_hide_closed_default".
									"#any#any".
									"#last_updated#DESC".
									"#$f_start_month#$f_start_day".
									"#$f_start_year#$f_end_month".
									"#$f_end_day#$f_end_year";
				break;
		# Update filters
		case "1":
				$t_settings_string = "$g_cookie_version".
									"#$f_show_category#$f_show_severity".
									"#$f_show_status#$f_per_page".
									"#$f_highlight_changed#$f_hide_closed".
									"#$f_user_id#$f_assign_id".
									"#$f_sort#$f_dir".
									"#$f_start_month#$f_start_day".
									"#$f_start_year#$f_end_month".
									"#$f_end_day#$f_end_year";
				break;
		# Set the sort order and direction
		case "2":
				$t_setting_arr = explode( "#", $g_view_all_cookie_val );
				$t_settings_string = "$t_setting_arr[0]".
									"#$t_setting_arr[1]#$t_setting_arr[2]".
									"#$t_setting_arr[3]#$t_setting_arr[4]".
									"#$t_setting_arr[5]#$t_setting_arr[6]".
									"#$t_setting_arr[7]#$t_setting_arr[8]".
									"#$f_sort#$f_dir".
									"#$t_setting_arr[11]#$t_setting_arr[12]".
									"#$t_setting_arr[13]#$t_setting_arr[14]".
									"#$t_setting_arr[15]#$t_setting_arr[16]";
				break;
		# does nothing. catch all case
		default:
				$t_setting_arr = explode( "#", $g_view_all_cookie_val );
				$t_settings_string = "$t_setting_arr[0]".
									"#$t_setting_arr[1]#$t_setting_arr[2]".
									"#$t_setting_arr[3]#$t_setting_arr[4]".
									"#$t_setting_arr[5]#$t_setting_arr[6]".
									"#$t_setting_arr[7]#$t_setting_arr[8]".
									"#$t_setting_arr[9]#$t_setting_arr[10]".
									"#$t_setting_arr[11]#$t_setting_arr[12]".
									"#$t_setting_arr[13]#$t_setting_arr[14]".
									"#$t_setting_arr[15]#$t_setting_arr[16]";
	}

	# set cookie values
	setcookie( $g_view_all_cookie, $t_settings_string, time()+$g_cookie_time_length, $g_cookie_path );

	# redirect to print_all or view_all page
	if ( isset( $f_print ) ) {
		$t_redirect_url = $g_print_all_bug_page;
	} else {
		$t_redirect_url = $g_view_all_bug_page;
	}

	# pass on search term(s)
	if ( isset( $f_search ) ) {
		$f_search = urlencode( $f_search );
		print_header_redirect( $t_redirect_url."?f_search=".$f_search );
	} else {
		print_header_redirect( $t_redirect_url );
	}
?>