<?php
	require_once( '../core.php' );

	# Load all the table names for use by the upgrade statements
	$t_bug_file_table				= config_get( 'mantis_bug_file_table' );
	$t_bug_history_table			= config_get( 'mantis_bug_history_table' );
	$t_bug_monitor_table			= config_get( 'mantis_bug_monitor_table' );
	$t_bug_relationship_table		= config_get( 'mantis_bug_relationship_table' );
	$t_bug_table					= config_get( 'mantis_bug_table' );
	$t_bug_text_table				= config_get( 'mantis_bug_text_table' );
	$t_bugnote_table				= config_get( 'mantis_bugnote_table' );
	$t_bugnote_text_table			= config_get( 'mantis_bugnote_text_table' );
	$t_news_table					= config_get( 'mantis_news_table' );
	$t_project_category_table		= config_get( 'mantis_project_category_table' );
	$t_project_file_table			= config_get( 'mantis_project_file_table' );
	$t_project_table				= config_get( 'mantis_project_table' );
	$t_project_user_list_table		= config_get( 'mantis_project_user_list_table' );
	$t_project_version_table		= config_get( 'mantis_project_version_table' );
	$t_user_table					= config_get( 'mantis_user_table' );
	$t_user_profile_table			= config_get( 'mantis_user_profile_table' );
	$t_user_pref_table				= config_get( 'mantis_user_pref_table' );
	$t_user_print_pref_table		= config_get( 'mantis_user_print_pref_table' );
	$t_custom_field_project_table	= config_get( 'mantis_custom_field_project_table' );
	$t_custom_field_table      		= config_get( 'mantis_custom_field_table' );
	$t_custom_field_string_table	= config_get( 'mantis_custom_field_string_table' );
	$t_upgrade_table				= config_get( 'mantis_upgrade_table' );
	$t_filters_table				= config_get( 'mantis_filters_table' );
?>