<?php
       # Mantis - a php based bugtracking system
       # Copyright (C) 2000 - 2002 Kenzaburo Ito - kenito@300baud.org
       # Copyright (C) 2002 - 2006 Mantis Team - mantisbt-dev@lists.sourceforge.net
       # This program is distributed under the terms and conditions of the GPL
       # See the README and LICENSE files for details

       # --------------------------------------------------------
       # $Revision: 1.3 $
       # $Author: vboctor $
       # $Date: 2007-04-22 08:33:32 $
       #
       # $Id: search.php,v 1.3 2007-04-22 08:33:32 vboctor Exp $
       # --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'compress_api.php' );
	require_once( $t_core_path . 'filter_api.php' );

	auth_ensure_user_authenticated();

	$f_print = gpc_get_bool( 'print' );

	gpc_make_array( FILTER_SEARCH_CATEGORY );
	gpc_make_array( FILTER_SEARCH_SEVERITY_ID );
	gpc_make_array( FILTER_SEARCH_STATUS_ID );
	gpc_make_array( FILTER_SEARCH_REPORTER_ID );
	gpc_make_array( FILTER_SEARCH_HANDLER_ID );
	gpc_make_array( FILTER_SEARCH_PROJECT_ID );
	gpc_make_array( FILTER_SEARCH_RESOLUTION_ID );
	gpc_make_array( FILTER_SEARCH_PRODUCT_BUILD );
	gpc_make_array( FILTER_SEARCH_PRODUCT_VERSION );
	gpc_make_array( FILTER_SEARCH_FIXED_IN_VERSION );
	gpc_make_array( FILTER_SEARCH_TARGET_VERSION );
	gpc_make_array( FILTER_SEARCH_PROFILE );
	gpc_make_array( FILTER_SEARCH_PRIORITY_ID );
	gpc_make_array( FILTER_SEARCH_MONITOR_USER_ID );
	gpc_make_array( FILTER_SEARCH_VIEW_STATE_ID );

	$my_filter = filter_get_default();
	$my_filter[FILTER_PROPERTY_FREE_TEXT] = gpc_get_string( FILTER_SEARCH_FREE_TEXT, '' );
	$my_filter[FILTER_PROPERTY_CATEGORY] = gpc_get_string_array( FILTER_SEARCH_CATEGORY, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_REPORTER_ID] = gpc_get_string_array( FILTER_SEARCH_REPORTER_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_HANDLER_ID] = gpc_get_string_array( FILTER_SEARCH_HANDLER_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_SEVERITY_ID] = gpc_get_string_array( FILTER_SEARCH_SEVERITY_ID, META_FILTER_ANY );

	$my_filter[FILTER_PROPERTY_STATUS_ID] = gpc_get_string_array( FILTER_SEARCH_STATUS_ID, META_FILTER_ANY );

	$my_filter[FILTER_PROPERTY_PROJECT_ID] = gpc_get_string_array( FILTER_SEARCH_PROJECT_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_RESOLUTION_ID] = gpc_get_string_array( FILTER_SEARCH_RESOLUTION_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_PRODUCT_BUILD] = gpc_get_string_array( FILTER_SEARCH_PRODUCT_BUILD, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_FIXED_IN_VERSION] = gpc_get_string_array( FILTER_SEARCH_FIXED_IN_VERSION, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_TARGET_VERSION] = gpc_get_string_array( FILTER_SEARCH_TARGET_VERSION, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_PRIORITY_ID] = gpc_get_string_array( FILTER_SEARCH_PRIORITY_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_MONITOR_USER_ID] = gpc_get_string_array( FILTER_SEARCH_MONITOR_USER_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_PROFILE] = gpc_get_string_array( FILTER_SEARCH_PROFILE, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_VIEW_STATE_ID] = gpc_get_string_array( FILTER_SEARCH_VIEW_STATE_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_PRODUCT_VERSION] = gpc_get_string_array( FILTER_SEARCH_PRODUCT_VERSION, META_FILTER_ANY );

	// Filtering by Date
	$my_filter[FILTER_PROPERTY_FILTER_BY_DATE] = gpc_get_bool( FILTER_SEARCH_FILTER_BY_DATE );
	$my_filter[FILTER_PROPERTY_START_MONTH] = gpc_get_int( FILTER_SEARCH_START_MONTH, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_START_DAY] = gpc_get_int( FILTER_SEARCH_START_DAY, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_START_YEAR] = gpc_get_int( FILTER_SEARCH_START_YEAR, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_END_MONTH] = gpc_get_int( FILTER_SEARCH_END_MONTH, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_END_DAY] = gpc_get_int( FILTER_SEARCH_END_DAY, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_END_YEAR] = gpc_get_int( FILTER_SEARCH_END_YEAR, META_FILTER_ANY );

	$my_filter[FILTER_PROPERTY_NOT_ASSIGNED] = gpc_get_bool( FILTER_SEARCH_NOT_ASSIGNED );

	$my_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE] = gpc_get_int( FILTER_SEARCH_RELATIONSHIP_TYPE, -1 );
	$my_filter[FILTER_PROPERTY_RELATIONSHIP_BUG] = gpc_get_int( FILTER_SEARCH_RELATIONSHIP_BUG, 0 );

	$my_filter[FILTER_PROPERTY_HIDE_STATUS_ID] = gpc_get_int( FILTER_SEARCH_HIDE_STATUS_ID );
	$my_filter[FILTER_PROPERTY_SHOW_STICKY_ISSUES] = gpc_get_bool( FILTER_SEARCH_SHOW_STICKY_ISSUES );

	$my_filter[FILTER_PROPERTY_SORT_FIELD_NAME] = gpc_get_string( FILTER_SEARCH_SORT_FIELD_NAME, '' );
	$my_filter[FILTER_PROPERTY_SORT_DIRECTION] = gpc_get_string( FILTER_SEARCH_SORT_DIRECTION, '' );
	$my_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] = gpc_get_int( FILTER_SEARCH_ISSUES_PER_PAGE, config_get( 'default_limit_view' ) );
	
	$t_highlight_changed = gpc_get_int( FILTER_SEARCH_HIGHLIGHT_CHANGED, -1 );
	if ( $t_highlight_changed != -1 ) {
		$my_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] = $t_highlight_changed;
	}

	# @@@ Handle custom fields.
	#$my_filter['custom_fields'] = $f_custom_fields_data;

	$tc_setting_arr = filter_ensure_valid_filter( $my_filter );

	$t_settings_serialized = serialize( $tc_setting_arr );
	$t_settings_string = config_get( 'cookie_version' ) . '#' . $t_settings_serialized;

	# redirect to print_all or view_all page
	if ( $f_print ) {
		$t_redirect_url = 'print_all_bug_page.php';
	} else {
		$t_redirect_url = 'view_all_bug_page.php';
	}

	$t_token_id = token_add( $t_settings_serialized, TOKEN_FILTER);
	$t_redirect_url = $t_redirect_url . '?filter=' . $t_token_id;
	html_meta_redirect( $t_redirect_url, 0 );
?>
