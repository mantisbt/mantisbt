<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: db_table_names_inc.php,v 1.8 2005-07-14 21:38:00 thraxisp Exp $
	# --------------------------------------------------------

	# Load all the table names for use by the upgrade statements
	$t_bug_file_table				= config_get_global( 'mantis_bug_file_table' );
	$t_bug_history_table			= config_get_global( 'mantis_bug_history_table' );
	$t_bug_monitor_table			= config_get_global( 'mantis_bug_monitor_table' );
	$t_bug_relationship_table		= config_get_global( 'mantis_bug_relationship_table' );
	$t_bug_table					= config_get_global( 'mantis_bug_table' );
	$t_bug_text_table				= config_get_global( 'mantis_bug_text_table' );
	$t_bugnote_table				= config_get_global( 'mantis_bugnote_table' );
	$t_bugnote_text_table			= config_get_global( 'mantis_bugnote_text_table' );
	$t_news_table					= config_get_global( 'mantis_news_table' );
	$t_project_category_table		= config_get_global( 'mantis_project_category_table' );
	$t_project_file_table			= config_get_global( 'mantis_project_file_table' );
	$t_project_table				= config_get_global( 'mantis_project_table' );
	$t_project_user_list_table		= config_get_global( 'mantis_project_user_list_table' );
	$t_project_version_table		= config_get_global( 'mantis_project_version_table' );
	$t_user_table					= config_get_global( 'mantis_user_table' );
	$t_user_profile_table			= config_get_global( 'mantis_user_profile_table' );
	$t_user_pref_table				= config_get_global( 'mantis_user_pref_table' );
	$t_user_print_pref_table		= config_get_global( 'mantis_user_print_pref_table' );
	$t_custom_field_project_table	= config_get_global( 'mantis_custom_field_project_table' );
	$t_custom_field_table      		= config_get_global( 'mantis_custom_field_table' );
	$t_custom_field_string_table	= config_get_global( 'mantis_custom_field_string_table' );
	$t_upgrade_table				= config_get_global( 'mantis_upgrade_table' );
	$t_filters_table				= config_get_global( 'mantis_filters_table' );
	$t_tokens_table					= config_get_global( 'mantis_tokens_table' );
	$t_project_hierarchy_table		= config_get_global( 'mantis_project_hierarchy_table' );
	$t_config_table					= config_get_global( 'mantis_config_table' );
?>
