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

	if ( !isset( $f_hide_closed ) ) {
		$f_hide_closed = "";
	}

	if ( isset( $f_save )) {
		### Save preferences
		$t_settings_string = $f_show_category."#".
							$f_show_severity."#".
							$f_show_status."#".
							$f_limit_view."#".
							$f_show_changed."#".
							$f_hide_closed;
		setcookie( $g_view_unassigned_cookie, $t_settings_string, time()+$g_cookie_time_length );
	}
	else if ( strlen($g_view_unassigned_cookie_val)>6 ) {
		### Load preferences
		$t_setting_arr 		= explode( "#", $g_view_assigned_cookie_val );
		$f_show_category 	= $t_setting_arr[0];
		$f_show_severity 	= $t_setting_arr[1];
		$f_show_status 		= $t_setting_arr[2];
		$f_limit_view 		= $t_setting_arr[3];
		$f_show_changed 	= $t_setting_arr[4];
		$f_hide_closed 	= $t_setting_arr[5];
	}

	if ( !isset( $f_limit_view ) ) {
		$f_limit_view = $g_default_limit_view;
	}

	if ( !isset( $f_show_changed ) ) {
		$f_show_changed = $g_default_show_changed;
	}

	if ( !isset( $f_show_category ) ) {
		$f_show_category = "any";
	}

	if ( !isset( $f_show_severity ) ) {
		$f_show_severity = "any";
	}

	if ( !isset( $f_show_status ) ) {
		$f_show_status = "any";
	}

	if ( !isset( $f_offset ) ) {
		$f_offset = 0;
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
		$f_dir = "DESC";
	}

	### build our query string based on our viewing criteria
	$query = "SELECT * FROM $g_mantis_bug_table";

	$t_where_clause = " WHERE project_id='$g_project_cookie_val' AND
							handler_id<=0";
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

	$query = $query.$t_where_clause;

	if ( !isset( $f_sort ) ) {
			$f_sort="last_updated";
	}
	$query = $query." ORDER BY '$f_sort' $f_dir";
	if ( isset( $f_limit_view ) ) {
		$query = $query." LIMIT $f_offset, $f_limit_view";
	}

	### perform query
    $result = db_query( $query );
	$row_count = db_num_rows( $result );

	$link_page = $g_view_all_unassign_bug_page;
	$page_type = "unassigned";
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

<? print_view_all_bugs_menu( $g_view_all_unassign_bug_page ) ?>

<? include( $g_view_all_include_file ) ?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>