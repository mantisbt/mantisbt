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

	# check to see if the cookie does not exist
	if ( empty( $g_view_all_cookie_val ) ) {
		$t_settings_string = "v1#any#any#any#".$g_default_limit_view."#".
							$g_default_show_changed."#0#any#any#last_updated#DESC";
		setcookie( $g_view_all_cookie, $t_settings_string, time()+$g_cookie_time_length );
		print_header_redirect( $g_view_all_bug_page );
	}

	# Check to see if new cookie is needed
	$t_setting_arr 			= explode( "#", $g_view_all_cookie_val );
	if ( $t_setting_arr[0] != "v1" ) {
		$t_settings_string = "v1#any#any#any#".$g_default_limit_view."#".
							$g_default_show_changed."#0#any#any#last_updated#DESC";
		setcookie( $g_view_all_cookie, $t_settings_string, time()+$g_cookie_time_length );
		print_header_redirect( $g_view_all_bug_page."?f=1" );
	}

	if( !isset( $f_search_text ) ) {
		$f_search_text = false;
	}

	$f_hide_closed = "";
	$f_limit_view = $g_default_limit_view;
	$f_highlight_changed = $g_default_show_changed;
	$f_show_category = "any";
	$f_show_severity = "any";
	$f_show_status = "any";
	$f_user_id = "any";
	$f_assign_id = "any";

	if ( !isset( $f_offset ) ) {
		$f_offset = 0;
	}

	if ( isset( $f_save ) ) {
		if ( $f_save == 1 ) {
			# We came here via the FILTER form button click
			# Save preferences
			$t_settings_string = "v1#".
								$f_show_category."#".
								$f_show_severity."#".
								$f_show_status."#".
								$f_limit_view."#".
								$f_highlight_changed."#".
								$f_hide_closed."#".
								$f_user_id."#".
								$f_assign_id."#".
								$f_sort."#".
								$f_dir;

			setcookie( $g_view_all_cookie, $t_settings_string, time()+$g_cookie_time_length );
		} else if ( $f_save == 2 ) {
			# We came here via clicking a sort link
			# Load pre-existing preferences
			$t_setting_arr 			= explode( "#", $g_view_all_cookie_val );
			$f_show_category 		= $t_setting_arr[1];
			$f_show_severity	 	= $t_setting_arr[2];
			$f_show_status 			= $t_setting_arr[3];
			$f_limit_view 			= $t_setting_arr[4];
			$f_highlight_changed 	= $t_setting_arr[5];
			$f_hide_closed 			= $t_setting_arr[6];
			$f_user_id 				= $t_setting_arr[7];
			$f_assign_id 			= $t_setting_arr[8];

			if ( !isset( $f_sort ) ) {
				$f_sort		 			= $t_setting_arr[9];
			}
			if ( !isset( $f_dir ) ) {
				$f_dir		 			= $t_setting_arr[10];
			}
			# Save new preferences
			$t_settings_string = "v1#".
								$f_show_category."#".
								$f_show_severity."#".
								$f_show_status."#".
								$f_limit_view."#".
								$f_highlight_changed."#".
								$f_hide_closed."#".
								$f_user_id."#".
								$f_assign_id."#".
								$f_sort."#".
								$f_dir;

			setcookie( $g_view_all_cookie, $t_settings_string, time()+$g_cookie_time_length );
		}
	} else {
		# Load preferences
		$t_setting_arr 			= explode( "#", $g_view_all_cookie_val );
		$f_show_category 		= $t_setting_arr[1];
		$f_show_severity	 	= $t_setting_arr[2];
		$f_show_status 			= $t_setting_arr[3];
		$f_limit_view 			= $t_setting_arr[4];
		$f_highlight_changed 	= $t_setting_arr[5];
		$f_hide_closed 			= $t_setting_arr[6];
		$f_user_id 				= $t_setting_arr[7];
		$f_assign_id 			= $t_setting_arr[8];
		$f_sort 				= $t_setting_arr[9];
		$f_dir		 			= $t_setting_arr[10];
	}

	# Build our query string based on our viewing criteria

	$query = "SELECT * FROM $g_mantis_bug_table";

	$t_where_clause = " WHERE project_id='$g_project_cookie_val'";

	if ( $f_user_id != "any" ) {
		$t_where_clause .= " AND reporter_id='$f_user_id'";
	}

	if ( $f_assign_id == "none" ) {
		$t_where_clause .= " AND handler_id=0";
	} else if ( $f_assign_id != "any" ) {
		$t_where_clause .= " AND handler_id='$f_assign_id'";
	}

	$t_clo_val = CLOSED;
	if (( $f_hide_closed=="on"  )&&( $f_show_status!="closed" )) {
		$t_where_clause = $t_where_clause." AND status<>'$t_clo_val'";
	}

	if ( $f_show_category != "any" ) {
		$t_where_clause = $t_where_clause." AND category='$f_show_category'";
	}
	if ( $f_show_severity != "any" ) {
		$t_where_clause = $t_where_clause." AND severity='$f_show_severity'";
	}
	if ( $f_show_status != "any" ) {
		$t_where_clause = $t_where_clause." AND status='$f_show_status'";
	}

	# Simple Text Search - Thnaks to Alan Knowles
	if ($f_search_text) {
		$t_where_clause .= " AND ((summary LIKE '%".addslashes($f_search_text)."%')
							OR (description LIKE '%".addslashes($f_search_text)."%')
							OR (steps_to_reproduce LIKE '%".addslashes($f_search_text)."%')
							OR (additional_information LIKE '%".addslashes($f_search_text)."%')
							OR ($g_mantis_bug_table.id LIKE '%".addslashes($f_search_text)."%'))
							AND $g_mantis_bug_text_table.id = $g_mantis_bug_table.bug_text_id";
		$query = "SELECT $g_mantis_bug_table.*, $g_mantis_bug_text_table.description
				FROM $g_mantis_bug_table, $g_mantis_bug_text_table ".$t_where_clause;
	} else {
		$query = $query.$t_where_clause;
	}

	if ( !isset( $f_sort ) ) {
		$f_sort="last_updated";
	}
	$query = $query." ORDER BY '$f_sort' $f_dir";
	if ( $f_sort != "priority" ) {
		$query = $query.", priority DESC";
	}

	if ( isset( $f_limit_view ) ) {
		$query = $query." LIMIT $f_offset, $f_limit_view";
	}

	# perform query
    $result = db_query( $query );
	$row_count = db_num_rows( $result );

	$link_page = $g_view_all_bug_page;
	$page_type = "all";
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<?
	if ( get_current_user_pref_field( "refresh_delay" ) > 0 ) {
		print_meta_redirect( $PHP_SELF, get_current_user_pref_field( "refresh_delay" )*60 );
	}
?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>

<? print_menu( $g_menu_include_file ) ?>

<? #print_view_all_bugs_menu( $g_view_all_bug_page ) ?>

<? include( $g_view_all_include_file ) ?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>
