<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	### CONFIGURATION VARIABLES                                             ###
	###########################################################################

	#--------------------
	# database variables
	$g_hostname        = "localhost";    # set this
	$g_port            = 3306;           # set this if not default
	$g_db_username     = "root";         # set this
	$g_db_password     = "";             # set this
	$g_database_name   = "bugtracker";   # set this
	#--------------------

	#--------------------
	# file path variables
	$g_path            = "/mantis/";   # requires trailing /
	#--------------------

	#--------------------
	# extensions for php 3 and php 4
	$g_php             = ".php3";        # set this to php for php4
	#--------------------
	                                     # or whatever your webserver needs

	#--------------------
	$g_cookie_prefix     = "MANTIS";     # set this to a unique identifier
	                                     # this allows you to have multiple
	#--------------------
	                                     # installations on one site.

	#--------------------
	$g_db_table_prefix   = "mantis";     # set to name of tables
                                         # this allows you to have multiple
                                         # installations on one site.
	#--------------------

	#--------------------
	# email variables
	$g_administrator_email  = "administrator@mydomain.com";   # set this
	$g_webmaster_email      = "webmaster@mydomain.com";       # set this
	#--------------------

	#--------------------
	# sitewide titles
	$g_window_title         = "Mantis";     # browser window title
	$g_page_title           = "Mantis";     # title in html page
	#--------------------

	#--------------------
	# toggling advanced interfaces      # 1 to enable - 0 to disable
	$g_show_advanced_report      = 1;
	$g_show_advanced_update      = 1;

	$g_show_version              = 0;

	# requires PHP4
	# 0 = disabled; 1 = admin only; 2 = anyone
	$g_show_source               = 0;
	#--------------------

	# core file variables
	$g_core_API_file             = "core_API.php";
	$g_meta_include_file         = "meta_inc.php";
	$g_menu_include_file         = "menu_inc.php";

	#--------------------
	# bugnote includes
	$g_bugnote_include_file      = "bugnote_inc.php";
	$g_bugnote_add_include_file  = "bugnote_add_inc.php";
	#--------------------

	#--------------------
	# css
	$g_css_include_file     = "css_inc.php";
	#--------------------

	#--------------------
	# database table names
	$g_mantis_bug_table            = $g_db_table_prefix."_bug_table";
	$g_mantis_bug_text_table       = $g_db_table_prefix."_bug_text_table";
	$g_mantis_bugnote_table        = $g_db_table_prefix."_bugnote_table";
	$g_mantis_bugnote_text_table   = $g_db_table_prefix."_bugnote_text_table";
	$g_mantis_news_table           = $g_db_table_prefix."_news_table";
	$g_mantis_user_table           = $g_db_table_prefix."_user_table";
	$g_mantis_user_profile_table   = $g_db_table_prefix."_user_profile_table";
	$g_mantis_user_pref_table      = $g_db_table_prefix."_user_pref_table";
	#--------------------

	#--------------------
	# page names
	$g_index                       = "index".$g_php;
	$g_main_page                   = "main_page".$g_php;

	# bug view/update
	$g_view_bug_all_page           = "view_bug_all_page".$g_php;
	$g_view_bug_page               = "view_bug_page".$g_php;
	$g_view_bug_advanced_page      = "view_bug_advanced_page".$g_php;

	$g_bug_delete_page             = "bug_delete_page".$g_php;
	$g_bug_delete                  = "bug_delete".$g_php;
	$g_bug_update_page             = "bug_update_page".$g_php;
	$g_bug_update_advanced_page    = "bug_update_advanced_page".$g_php;
	$g_bug_update                  = "bug_update".$g_php;

	$g_bug_reopen                  = "bug_reopen".$g_php;

	# vote
	$g_bug_vote_add                = "bug_vote_add".$g_php;

	# bugnote
	$g_bugnote_add_page            = "bugnote_add_page".$g_php;
	$g_bugnote_add                 = "bugnote_add".$g_php;
	$g_bugnote_delete              = "bugnote_delete".$g_php;

	# report bug
	$g_report_bug_page             = "report_bug_page".$g_php;
	$g_report_bug_advanced_page    = "report_bug_advanced_page".$g_php;
	$g_report_add                  = "report_add".$g_php;

	# summary
	$g_summary_page                = "summary_page".$g_php;

	# account
	$g_account_page                = "account_page".$g_php;
	$g_account_update              = "account_update".$g_php;
	$g_account_delete_page         = "account_delete_page".$g_php;
	$g_account_delete              = "account_delete".$g_php;

	$g_account_profile_manage_page    = "account_profile_manage_page".$g_php;
	$g_account_profile_add            = "account_profile_add".$g_php;
	$g_account_profile_edit_page      = "account_profile_edit_page".$g_php;
	$g_account_profile_update         = "account_profile_update".$g_php;
	$g_account_profile_delete         = "account_profile_delete".$g_php;
	$g_account_profile_make_default   = "account_profile_make_default".$g_php;

	$g_account_prefs_page             = "account_prefs_page".$g_php;
	$g_account_prefs_update           = "account_prefs_update".$g_php;

	# site management
	$g_manage_page                 = "manage_page".$g_php;
	$g_manage_create_new_user      = "manage_create_new_user".$g_php;
	$g_manage_create_user_page     = "manage_create_user_page".$g_php;

	$g_manage_user_page            = "manage_user_page".$g_php;
	$g_manage_user_update          = "manage_user_update".$g_php;
	$g_manage_user_reset           = "manage_user_reset".$g_php;
	$g_manage_user_delete_page     = "manage_user_delete_page".$g_php;
	$g_manage_user_delete          = "manage_user_delete".$g_php;

	$g_documentation_page          = "documentation_page".$g_php;

	# category management
	$g_manage_category_page        = "manage_category_page".$g_php;
	$g_manage_category_update      = "manage_category_update".$g_php;

	$g_manage_product_versions_page   = "manage_product_versions_page".$g_php;
	$g_manage_product_versions_update = "manage_product_versions_update".$g_php;

	# news
	$g_news_menu_page              = "news_menu_page".$g_php;
	$g_news_edit_page              = "news_edit_page".$g_php;
	$g_news_add                    = "news_add".$g_php;
	$g_news_update                 = "news_update".$g_php;
	$g_news_delete_page            = "news_delete_page".$g_php;
	$g_news_delete                 = "news_delete".$g_php;

	# login
	$g_login                       = "login".$g_php;
	$g_login_page                  = "login_page".$g_php;
	$g_login_error_page            = "login_error_page".$g_php;
	$g_login_success_page          = "index".$g_php;
	$g_logout_page                 = "logout_page".$g_php;

	# debug only
	$g_show_source_page            = "show_source_page".$g_php;

	# errors
	$g_mysql_error_page            = "mysql_error_page".$g_php;
	#--------------------

	#--------------------
	# cookies
	# cookie names
	$g_string_cookie            = $g_cookie_prefix."_STRING_COOKIE";
	$g_last_access_cookie       = $g_cookie_prefix."_LAST_ACCESS_COOKIE";

	# cookie values
	$g_string_cookie_val        = $HTTP_COOKIE_VARS[$g_string_cookie];
	$g_last_access_cookie_val   = $HTTP_COOKIE_VARS[$g_last_access_cookie];
	#--------------------

	#--------------------
	# time for coookie to live in seconds
	$g_time_length              = 30000000;     # 1 year
	# time to delay between page redirects
	$g_wait_time                = 1;            # in seconds
	#--------------------

	#--------------------
	# defaults for viewing preferences
	$g_default_limit_view       = 50;
	$g_default_show_changed     = 1;
	$g_default_hide_resolved    = "";  # set to on to enable
	$g_default_advanced_report  = "";  # set to on to enable
	$g_default_advanced_view    = "";  # set to on to enable
	#--------------------

	#--------------------
	# date lengths to bount bugs by
	# folows the english required by strtotime()
	$g_date_partitions = array("1 day","3 days","1 week","1 month","1 year");
	#--------------------

	#--------------------
	# html table appearance variables
	$g_primary_table_tags        = "";
	#--------------------

	#--------------------
	# color values
	$g_white_color           = "#ffffff";    # white

	$g_primary_color_dark    = "#d8d8d8";    # gray
	$g_primary_color_light   = "#e8e8e8";    # light gray
	$g_primary_border_color  = "#aaaaaa";    # dark gray
	$g_category_title_color	 = "#c8c8e8";    # bluish
	$g_category_title_color2 = "#c0c0c8";    # gray bluish

	$g_table_title_color     = "#ffffff";    # white

	$g_required_field_color  = "#aa0000";    # redish

	$g_new_color             = "#ffa0a0";    # red
	$g_feedback_color        = "#ff50a8";    # purple
	$g_acknowledged_color    = "#ffd850";    # orange
	$g_confirmed_color       = "#ffffb0";    # yellow
	$g_assigned_color        = "#c8c8ff";    # blue
	$g_resolved_color        = "#ffffff";    # not used in default
	#--------------------

	#--------------------
	# news
	$g_news_view_limit      = 5;
	#--------------------

	#--------------------
	#version
	$g_mantis_version       = "0.11.0";
	#--------------------
?>
