<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# CONFIGURATION VARIABLES
	###########################################################################

	# In general a value of 0 means the feature is disabled and 1 means the
	# feature is enabled.  Any other cases will have an explanation.

	# Look in configuration.html for more detailed comments.

	#error_reporting(E_ALL ^ E_NOTICE);
	error_reporting(E_ALL);

	################################
	# Mantis Database Settings
	################################

	# --- database variables ---------

	# set these values to match your setup
	$g_hostname      = "localhost";
	$g_port          = 3306;         # 3306 is default
	$g_db_username   = "root";
	$g_db_password   = "";
	$g_database_name = "bugtracker";

	############################
	# Mantis Path Settings
	############################

	# --- path variables --------------

	# path to your installation as seen from the web browser
	# requires trailing /
	$g_path          = "http://192.168.7.1/mantis/";

	# path to your images directory (for icons)
	# requires trailing /
	$g_icon_path     = $g_path."images/";

	# absolute path to your installation.  *NO* symlinks allowed
	# requires trailing /
	$g_absolute_path = "/usr/local/www/data/mantis/";

	#############################
	# Web Server
	#############################

	# --- using MS IIS ----------------
	# set to ON if you use IIS
	$g_use_iis = OFF;

	#############################
	# Mantis Version String
	#############################

	# --- version variables -----------
	$g_mantis_version = "0.17.0";
	$g_show_version   = ON;

	#############################
	# Mantis Email Settings
	#############################

	# --- email variables -------------
	$g_administrator_email  = "administrator@nowhere";
	$g_webmaster_email      = "webmaster@nowhere";

	# the "From: " field in emails
	$g_from_email           = "noreply@300baud.org";

	# the "To: " address all emails are sent.  This can be a mailing list or archive address.
	# Actual users are emailed via the bcc: fields
	$g_to_email             = "nobody@300baud.org";

	# the return address for bounced mail
	$g_return_path_email    = "admin@300baud.org";

	# allow users to signup for their own accounts
	$g_allow_signup              = ON;

	# allow email notification
	$g_enable_email_notification = ON;

	# notify developers and higher when a new bug comes in
	# only if their preference is also set
	$g_notify_developers_on_new  = ON;

	# set to 0 to disable email check
	$g_validate_email            = ON;
	$g_check_mx_record           = ON;

	# This disables the automatic generation of mailto: links
	$g_hide_user_email           = OFF;

	# Set to 0 to remove X-Priority header
	$g_use_x_priority            = ON;

	# Set to 0 as on Windows systems, as long as php-mail-function has its
	# bcc-bug (~PHP 4.0.6)
	$g_use_bcc                   = ON;

	# phpMailer instead of standard mail() function (REQUIRES PHP 4.x.x)
	# Get the phpMailer-package from http://phpmailer.sourceforge.net
	# The installation is very simple you only need 2 plain text php-files
	#  class.smtp.php
	#  class.phpmailer.php

	# Copy these files to your php-include-dir i.e. "c:\php\includes" or
	# "/usr/lib/php/includes"
	# and add this path to the "include_path"-entry  in the php.ini file.
	# The installation is described in the readme and there is also a simple
	# example.
	# PhpMailer comes with a detailed documentation in phpdoc format.

	$g_use_phpMailer = OFF;

	# select the method to mail by:
	# 0 - mail()
	# 1 - sendmail
	# 2 - SMTP
	$g_phpMailer_method = 0;

	# This option allows you to use a remote SMTP host.  Must use the phpMailer script
	# Name of smtp host, needed for phpMailer, taken from php.ini
	$g_smtp_host     = "localhost";

	################################
	# Mantis Language Settings
	################################

	# --- language settings -----------

	$g_default_language     = "english";

	# list the choices that the users are allowed to choose
	$g_language_choices_arr = array( "english", "chinese_traditional", "danish", "dutch", "french", "french2", "german", "italian", "korean", "norwegian", "polish", "portuguese_brazilian", "portuguese_standard", "russian", "spanish", "swedish", "turkish" );

	###############################
	# Mantis Display Settings
	###############################

	# --- sitewide variables ----------
	$g_window_title = "Mantis";     # browser window title
	$g_page_title   = "Mantis";     # title at top of html page

	# --- advanced views --------------
	# BOTH, SIMPLE_ONLY, ADVANCED_ONLY
	$g_show_report = BOTH;
	$g_show_update = BOTH;
	$g_show_view   = BOTH;

	# --- display source code ---------
	# display a link at the bottom of the page to show the PHP source
	# WARNING: Potential security hazard.  Only turn this on when you really
	# need it (for debugging)
	# ON = admin only; OFF = disabled
	$g_show_source = OFF;

	# --- footer menu -----------------
	# Footer Menu
	$g_show_footer_menu = OFF;

	# --- footer menu -----------------
	# show the project name in the page title
	# 0 : no project name
	# 1 : project name and any additional
	# 2 : only project name
	$g_show_project_in_title = ON;

	# --- show assigned to names ------
	# This is in the view all pages
	$g_show_assigned_names = ON;

	# --- show priority as icon ---
	# OFF: Shows priority as icon in view all bugs page
	# ON:  Shows priority as text in view all bugs page
	$g_show_priority_text = OFF;

	############################
	# Mantis JPGRAPH Addon
	############################

	# --- jpgraph settings --- #
	# Initial Version from Duncan Lisset
	#
	# To use the Jpgraph addon you need the JPGRAPH package from
	# http://www.aditus.nu/jpgraph/index.php
	# You can place the package whereever you want, but you have
	# to set the var in jpgraph.php eg.
	# (DEFINE("DIR_BASE","/www/mantisbt/jpgraph/");)

	$g_use_jpgraph = OFF;
	$g_jpgraph_path = "./jpgraph/";   # dont forget the ending slash!

	############################
	# Mantis Time Settings
	############################

	# --- time varaibles --------------

	# time for 'permanent' cookie to live in seconds (1 year)
	$g_cookie_time_length = 30000000;

	# time to delay between page redirects (in seconds)
	$g_wait_time          = 2;

	# minutes to wait before document is stale (in minutes)
	$g_content_expire     = 0;

	############################
	# Mantis Date Settings
	############################

	# --- date format settings --------
	# date format strings (default is 'US' formatting)
	# go to http://www.php.net/manual/en/function.date.php
	# for detailed instructions on date formatting
	$g_short_date_format    = "m-d";
	$g_normal_date_format   = "m-d H:i";
	$g_complete_date_format = "m-d-y H:i T";

	############################
	# Mantis News Settings
	############################

	# --- Limit News Items ------------
	# limit by entry count or date
	# BY_LIMIT - entry limit
	# BY_DATE - by date
	$g_news_limit_method    = BY_LIMIT;

	# limit by last X entries
	$g_news_view_limit      = 7;

	# limit by days
	$g_news_view_limit_days = 30;

	##################################
	# Mantis Default Preferences
	##################################

	# --- signup default ---------------
	# look in constant_inc.php for values
	$g_default_new_account_access_level = REPORTER;

	# --- viewing defaults ------------
	# site defaults for viewing preferences
	$g_default_limit_view         = 50;
	$g_default_show_changed       = 6;

	# make sure people aren't refreshing too often
	$g_min_refresh_delay          = 10;    # in minutes

	# --- account pref defaults -------
	# BOTH, SIMPLE_ONLY, ADVANCED_ONLY
	$g_default_advanced_report    = BOTH;
	$g_default_advanced_view      = BOTH;
	$g_default_advanced_update    = BOTH;
	$g_default_refresh_delay      = 30;    # in minutes
	$g_default_redirect_delay     = 2;     # in seconds
	$g_default_email_on_new       = ON;
	$g_default_email_on_assigned  = ON;
	$g_default_email_on_feedback  = ON;
	$g_default_email_on_resolved  = ON;
	$g_default_email_on_closed    = ON;
	$g_default_email_on_reopened  = ON;
	$g_default_email_on_bugnote   = ON;
	$g_default_email_on_status    = 0; # @@@ Unused
	$g_default_email_on_priority  = 0; # @@@ Unused
	# default_language - is set to site language

	###############################
	# Mantis Summary Settings
	###############################

	# how many reporters to show
	# this is useful when there are hundreds of reporters
	$g_reporter_summary_limit = 10;

	# default space padding (increase when bug count goes over 100,000)
	$g_summary_pad            = 5;

	# --- summary date displays -------
	# date lengths to count bugs by (in days)
	$g_date_partitions = array( 1, 2, 3, 7, 30, 60, 90, 180, 365);

	###############################
	# Mantis Bugnote Settings
	###############################

	# --- bugnote settings ------------
	# bugnote ordering
	# change to ASC or DESC
	$g_bugnote_order = "ASC";

	###################################
	# Mantis File Upload Settings
	###################################

	# --- file upload settings --------
	# @@@ This should be broken into per project settings and split between bug uploads and project document uploads
	$g_allow_file_upload    = ON;

	# Upload destination: specify actual location in project settings
	# DISK or DATABASE
	$g_file_upload_method   = DISK;

	$g_max_file_size        = 5000000; # 5 MB

	############################
	# Mantis HTML Settings
	############################

	# --- html tags -------------------
	$g_allow_html_tags        = ON;

	# do NOT include href or img tags here
	# do NOT include tags that have parameters (eg. <font face="arial">)
	$g_html_tags              = array("<p>","</p>","<li>","</li>","<ul>","</ul>",
									"<ol>","</ol>","<br />","<br>","<pre>","</pre>",
									"<i>","</i>","<b>","</b>","<u>","</u>");

	$g_allow_href_tags        = ON;

	# --- table tags ------------------
	# this is inserted into the outermost tables ( tags like border, cellspacing, etc)
	$g_primary_table_tags          = "";

	##########################
	# Mantis HR Settings
	##########################

	# --- hr --------------------------
	$g_hr_size  = 1;
	$g_hr_width = 50;

	#############################
	# Mantis LDAP Settings
	#############################

	# look in README.LDAP for details

	# --- using openldap -------------
	$g_ldap_server       = "192.168.192.38";
	$g_ldap_root_dn      = "dc=traffic,dc=redflex,dc=com,dc=au";
	$g_ldap_organisation = "(organizationname=*Traffic)"; # optional
	$g_use_ldap_email    = OFF; # Should we send to the LDAP email address or what MySql tells us

	############################
	# Mantis Misc Settings
	############################

	# --- threshold -------------------
	# access level needed to re-open bugs
	# Look in the constant_inc.php file if you want to set a different value
	$g_reopen_bug_threshold = DEVELOPER;

	# --- quick proceed----------------
	# see fewer confirmation screens between actions
	$g_quick_proceed = ON;

	# --- login method ----------------
	# CRYPT or PLAIN or MD5 or LDAP
	$g_login_method = CRYPT;

	# --- limit reporters -------------
	# Set to 1 if you wish to limit reporters to only viewing bugs that they report.
	$g_limit_reporters = OFF;

	# --- close immediately -----------
	# Allow developers and above to close bugs immediately when resolving bugs
	$g_allow_close_immediately = OFF;

	# --- account delete -----------
	# Allow users to delete their own accounts
	$g_allow_account_delete = OFF;

	##############################
	# Mantis Color Variables
	##############################

	# --- color values ----------------
	# you can change the look and feel by modifying these values

	$g_background_color        = "#ffffff"; # white
	$g_required_color          = "#bb0000"; # red
	$g_table_border_color      = "#000000"; # black
	$g_category_title_color    = "#c8c8e8"; # blue
	$g_primary_color1          = "#d8d8d8"; # dark gray
	$g_primary_color2          = "#e8e8e8"; # light gray
	$g_form_title_color        = "#ffffff"; # white
	$g_spacer_color            = "#ffffff"; # white
	$g_menu_color              = "#e8e8e8"; # light gray

	# --- status color codes ----------
	#
	$g_new_color               = "#ffa0a0"; # red
	$g_feedback_color          = "#ff50a8"; # purple
	$g_acknowledged_color      = "#ffd850"; # orange
	$g_confirmed_color         = "#ffffb0"; # yellow
	$g_assigned_color          = "#c8c8ff"; # blue
	$g_resolved_color          = "#cceedd"; # buish-green
	$g_closed_color            = "#ffffff"; # white

	###############################
	# Mantis Cookie Variables
	###############################

	# --- cookie prefix ---------------
	# set this to a unique identifier.  No spaces.
	$g_cookie_prefix = "MANTIS";

	# --- cookie names ----------------
	$g_string_cookie           = $g_cookie_prefix."_STRING_COOKIE";
	$g_project_cookie          = $g_cookie_prefix."_PROJECT_COOKIE";
	$g_view_all_cookie         = $g_cookie_prefix."_VIEW_ALL_COOKIE";
	$g_manage_cookie           = $g_cookie_prefix."_MANAGE_COOKIE";

	# --- cookie values ---------------
	$g_string_cookie_val           = "";
	$g_project_cookie_val          = "";
	$g_view_all_cookie_val         = "";
	$g_manage_cookie_val           = "";

	if ( isset( $HTTP_COOKIE_VARS[$g_string_cookie] ) ) {
		$g_string_cookie_val         = $HTTP_COOKIE_VARS[$g_string_cookie];
	}
	if ( isset( $HTTP_COOKIE_VARS[$g_project_cookie] ) ) {
		$g_project_cookie_val        = $HTTP_COOKIE_VARS[$g_project_cookie];
	}
	if ( isset( $HTTP_COOKIE_VARS[$g_view_all_cookie] ) ) {
		$g_view_all_cookie_val       = $HTTP_COOKIE_VARS[$g_view_all_cookie];
	}
	if ( isset( $HTTP_COOKIE_VARS[$g_manage_cookie] ) ) {
		$g_manage_cookie_val  = $HTTP_COOKIE_VARS[$g_manage_cookie];
	}

	#######################################
	# Mantis Database Table Variables
	#######################################

	# --- table prefix ----------------
	# if you change this remember to reflect the changes in the database
	$g_db_table_prefix = "mantis";

	# --- table names -----------------
	$g_mantis_bug_file_table          = $g_db_table_prefix."_bug_file_table";
	$g_mantis_bug_table               = $g_db_table_prefix."_bug_table";
	$g_mantis_bug_text_table          = $g_db_table_prefix."_bug_text_table";
	$g_mantis_bugnote_table           = $g_db_table_prefix."_bugnote_table";
	$g_mantis_bugnote_text_table      = $g_db_table_prefix."_bugnote_text_table";
	$g_mantis_news_table              = $g_db_table_prefix."_news_table";
	$g_mantis_project_category_table  = $g_db_table_prefix."_project_category_table";
	$g_mantis_project_file_table      = $g_db_table_prefix."_project_file_table";
	$g_mantis_project_table           = $g_db_table_prefix."_project_table";
	$g_mantis_project_user_list_table = $g_db_table_prefix."_project_user_list_table";
	$g_mantis_project_version_table   = $g_db_table_prefix."_project_version_table";
	$g_mantis_user_table              = $g_db_table_prefix."_user_table";
	$g_mantis_user_profile_table      = $g_db_table_prefix."_user_profile_table";
	$g_mantis_user_pref_table         = $g_db_table_prefix."_user_pref_table";

	###########################
	# Mantis Enum Strings
	###########################

	# --- enum strings ----------------
	$g_access_levels_enum_string      = "10:viewer,25:reporter,40:updater,55:developer,70:manager,90:administrator";
	$g_project_status_enum_string     = "10:development,30:release,50:stable,70:obsolete";
	$g_project_view_state_enum_string = "10:public,50:private";

	$g_priority_enum_string           = "10:none,20:low,30:normal,40:high,50:urgent,60:immediate";
	$g_severity_enum_string           = "10:feature,20:trivial,30:text,40:tweak,50:minor,60:major,70:crash,80:block";
	$g_reproducibility_enum_string    = "10:always,30:sometimes,50:random,70:have not tried,90:unable to duplicate,100:N/A";
	$g_status_enum_string             = "10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed";
	$g_resolution_enum_string         = "10:open,20:fixed,30:reopened,40:unable to duplicate,50:not fixable,60:duplicate,70:not a bug,80:suspended,90:won't fix";
	$g_projection_enum_string         = "10:none,30:tweak,50:minor fix,70:major rework,90:redesign";
	$g_eta_enum_string                = "10:none,20:< 1 day,30:2-3 days,40:< 1 week,50:< 1 month,60:> 1 month";

	#############################
	# Mantis Page Variables
	#############################

	#----------------------------------
	# extensions for php 3 and php 4
	# set this to php for php4 or whatever your webserver needs
	$g_php             = ".php";
	#----------------------------------

	#----------------------------------
	# specifiy your top/bottom include file (logos, banners, etc)
	$g_bottom_include_page            = $g_absolute_path."";
	$g_top_include_page               = $g_absolute_path."";
	# css
	$g_css_include_file               = $g_absolute_path."css_inc.php";
	# meta tags
	$g_meta_include_file              = $g_absolute_path."meta_inc.php";

	#----------------------------------
	# core file variables
	$g_core_API_file                  = $g_absolute_path."core_API.php";
	#----------------------------------

	#----------------------------------
	# misc
	$g_index                          = $g_path."index".$g_php;
	$g_main_page                      = $g_path."main_page".$g_php;
	#----------------------------------

	#----------------------------------
	# account
	$g_account_page                   = $g_path."account_page".$g_php;
	$g_account_update                 = $g_path."account_update".$g_php;
	$g_account_delete_page            = $g_path."account_delete_page".$g_php;
	$g_account_delete                 = $g_path."account_delete".$g_php;
	#----------------------------------

	#----------------------------------
	# account profiles
	$g_account_profile_menu_page      = $g_path."account_prof_menu_page".$g_php;
	$g_account_profile_add            = $g_path."account_prof_add".$g_php;
	$g_account_profile_edit_page      = $g_path."account_prof_edit_page".$g_php;
	$g_account_profile_update         = $g_path."account_prof_update".$g_php;
	$g_account_profile_delete         = $g_path."account_prof_delete".$g_php;
	$g_account_profile_make_default   = $g_path."account_prof_make_default".$g_php;
	#----------------------------------

	#----------------------------------
	# account prefs
	$g_account_prefs_page             = $g_path."account_prefs_page".$g_php;
	$g_account_prefs_update           = $g_path."account_prefs_update".$g_php;
	$g_account_prefs_reset            = $g_path."account_prefs_reset".$g_php;
	#----------------------------------

	#----------------------------------
	# bug
	$g_bug_assign                     = $g_path."bug_assign".$g_php;

	$g_bug_file_add                   = $g_path."bug_file_add".$g_php;

	$g_bug_delete_page                = $g_path."bug_delete_page".$g_php;
	$g_bug_delete                     = $g_path."bug_delete".$g_php;
	$g_bug_update_page                = $g_path."bug_update_page".$g_php;
	$g_bug_update_advanced_page       = $g_path."bug_update_advanced_page".$g_php;
	$g_bug_update                     = $g_path."bug_update".$g_php;

	$g_bug_reopen_page                = $g_path."bug_reopen_page".$g_php;

	$g_bug_close                      = $g_path."bug_close".$g_php;

	$g_bug_resolve_page               = $g_path."bug_resolve_page".$g_php;
	$g_bug_resolve_page2              = $g_path."bug_resolve_page2".$g_php;
	$g_bug_vote_add                   = $g_path."bug_vote_add".$g_php;
	#----------------------------------

	#----------------------------------
	# bugnote
	$g_bugnote_add                    = $g_path."bugnote_add".$g_php;
	$g_bugnote_delete                 = $g_path."bugnote_delete".$g_php;
	$g_bugnote_edit_page              = $g_path."bugnote_edit_page".$g_php;
	$g_bugnote_update                 = $g_path."bugnote_update".$g_php;
	#----------------------------------

	#----------------------------------
	# bugnote includes
	$g_bugnote_include_file           = $g_absolute_path."bugnote_inc.php";
	#----------------------------------

	#----------------------------------
	# login
	$g_login                          = $g_path."login".$g_php;
	$g_login_page                     = $g_path."login_page".$g_php;
	$g_login_error_page               = $g_path."login_error_page".$g_php;
	$g_login_success_page             = $g_path."index".$g_php;
	$g_login_select_proj_page         = $g_path."login_select_proj_page".$g_php;
	$g_logout_page                    = $g_path."logout_page".$g_php;
	$g_logout_redirect_page           = $g_path.".";
	#----------------------------------

	# documentation
	$g_documentation_html             = $g_path."documentation.html";

	#----------------------------------
	# site management
	$g_manage_page                    = $g_path."manage_page".$g_php;
	$g_manage_create_new_user         = $g_path."manage_create_new_user".$g_php;
	$g_manage_create_user_page        = $g_path."manage_create_user_page".$g_php;
	#----------------------------------

	$g_manage_prune                   = $g_path."manage_prune".$g_php;

	#----------------------------------
	$g_manage_user_page               = $g_path."manage_user_page".$g_php;
	$g_manage_user_update             = $g_path."manage_user_update".$g_php;
	$g_manage_user_reset              = $g_path."manage_user_reset".$g_php;
	$g_manage_user_delete_page        = $g_path."manage_user_delete_page".$g_php;
	$g_manage_user_delete             = $g_path."manage_user_delete".$g_php;
	#----------------------------------

	#----------------------------------
	# userland documentation
	$g_documentation_page             = $g_path."documentation_page".$g_php;
	$g_usage_doc_page                 = $g_path."documentation.html";
	$g_site_settings_page             = $g_path."site_settings_page".$g_php;
	$g_site_settings_edit_page        = $g_path."site_settings_edit_page".$g_php;
	$g_site_settings_update           = $g_path."site_settings_update".$g_php;
	#----------------------------------

	#----------------------------------
	$g_set_project                    = $g_path."set_project".$g_php;
	#----------------------------------

	#----------------------------------
	# multiple projects
	$g_proj_doc_add_page              = $g_path."proj_doc_add_page".$g_php;
	$g_proj_doc_add                   = $g_path."proj_doc_add".$g_php;
	$g_proj_doc_delete_page           = $g_path."proj_doc_delete_page".$g_php;
	$g_proj_doc_edit_page             = $g_path."proj_doc_edit_page".$g_php;
	$g_proj_doc_page                  = $g_path."proj_doc_page".$g_php;
	$g_proj_doc_update                = $g_path."proj_doc_update".$g_php;
	#----------------------------------

	#----------------------------------
	# multiple projects
	$g_manage_project_menu_page       = $g_path."manage_proj_menu_page".$g_php;
	$g_manage_project_add             = $g_path."manage_proj_add".$g_php;
	$g_manage_project_edit_page       = $g_path."manage_proj_edit_page".$g_php;
	$g_manage_project_update          = $g_path."manage_proj_update".$g_php;
	$g_manage_project_delete          = $g_path."manage_proj_delete".$g_php;
	$g_manage_project_delete_page     = $g_path."manage_proj_delete_page".$g_php;
	#----------------------------------

	#----------------------------------
	# manage multiple project users
	$g_manage_project_user_menu_page    = $g_path."manage_proj_user_menu_page".$g_php;
	$g_manage_project_user_add          = $g_path."manage_proj_user_add".$g_php;
	$g_manage_project_user_edit_page    = $g_path."manage_proj_user_edit_page".$g_php;
	$g_manage_project_user_delete       = $g_path."manage_proj_user_delete".$g_php;
	$g_manage_project_user_delete_page  = $g_path."manage_proj_user_delete_page".$g_php;
	#----------------------------------

	#----------------------------------
	# project versions
	$g_manage_project_version_add         = $g_path."manage_proj_ver_add".$g_php;
	$g_manage_project_version_update      = $g_path."manage_proj_ver_update".$g_php;
	$g_manage_project_version_edit_page   = $g_path."manage_proj_ver_edit_page".$g_php;
	$g_manage_project_version_delete      = $g_path."manage_proj_ver_delete".$g_php;
	$g_manage_project_version_delete_page = $g_path."manage_proj_ver_del_page".$g_php;
	#----------------------------------

	#----------------------------------
	# project category
	$g_manage_project_category_add         = $g_path."manage_proj_cat_add".$g_php;
	$g_manage_project_category_update      = $g_path."manage_proj_cat_update".$g_php;
	$g_manage_project_category_edit_page   = $g_path."manage_proj_cat_edit_page".$g_php;
	$g_manage_project_category_delete      = $g_path."manage_proj_cat_delete".$g_php;
	$g_manage_project_category_delete_page = $g_path."manage_proj_cat_del_page".$g_php;
	#----------------------------------

	#----------------------------------
	# news
	$g_news_menu_page                 = $g_path."news_menu_page".$g_php;
	$g_news_edit_page                 = $g_path."news_edit_page".$g_php;
	$g_news_add                       = $g_path."news_add".$g_php;
	$g_news_update                    = $g_path."news_update".$g_php;
	$g_news_delete_page               = $g_path."news_delete_page".$g_php;
	$g_news_delete                    = $g_path."news_delete".$g_php;

	$g_news_list_page                 = $g_path."news_list_page".$g_php;
	$g_news_view_page                 = $g_path."news_view_page".$g_php;
	#----------------------------------

	#----------------------------------
	# project documents
	$g_proj_doc_add                   = $g_path."proj_doc_add".$g_php;
	$g_proj_doc_add_page              = $g_path."proj_doc_add_page".$g_php;
	$g_proj_doc_delete                = $g_path."proj_doc_delete".$g_php;
	$g_proj_doc_delete_page           = $g_path."proj_doc_delete_page".$g_php;
	$g_proj_doc_edit_page             = $g_path."proj_doc_edit_page".$g_php;
	$g_proj_doc_page                  = $g_path."proj_doc_page".$g_php;
	$g_proj_doc_update                = $g_path."proj_doc_update".$g_php;
	$g_proj_doc_view_page             = $g_path."proj_doc_view_page".$g_php;
	#----------------------------------

	$g_proj_user_add                  = $g_path."proj_user_add".$g_php;
	$g_proj_user_delete               = $g_path."proj_user_delete".$g_php;
	$g_proj_user_delete_page          = $g_path."proj_user_delete_page".$g_php;
	$g_proj_user_update               = $g_path."proj_user_update".$g_php;
	$g_proj_user_menu_page            = $g_path."proj_user_menu_page".$g_php;

	#----------------------------------
	# report bug
	$g_report_bug_page                = $g_path."report_bug_page".$g_php;
	$g_report_bug_advanced_page       = $g_path."report_bug_advanced_page".$g_php;
	$g_report_add                     = $g_path."report_add".$g_php;
	#----------------------------------

	#----------------------------------
	# debug only
	$g_show_source_page               = $g_path."show_source_page".$g_php;
	#----------------------------------

	#----------------------------------
	#signup
	$g_signup_page                    = $g_path."signup_page".$g_php;
	$g_signup                         = $g_path."signup".$g_php;
	#----------------------------------

	#----------------------------------
	# summary
	$g_summary_page                       = $g_path."summary_page".$g_php;
	$g_summary_jpgraph_function           = $g_absolute_path."summary_graph_functions".$g_php;
	$g_summary_jpgraph_page               = $g_path."summary_jpgraph_page".$g_php;
	$g_summary_jpgraph_cumulative_bydate  = $g_path."summary_graph_cumulative_bydate".$g_php;
	$g_summary_jpgraph_bydeveloper        = $g_path."summary_graph_bydeveloper".$g_php;
	$g_summary_jpgraph_byreporter         = $g_path."summary_graph_byreporter".$g_php;
	$g_summary_jpgraph_byseverity         = $g_path."summary_graph_byseverity".$g_php;
	$g_summary_jpgraph_bystatus           = $g_path."summary_graph_bystatus".$g_php;
	$g_summary_jpgraph_byresolution       = $g_path."summary_graph_byresolution".$g_php;
	$g_summary_jpgraph_bycategory         = $g_path."summary_graph_bycategory".$g_php;
	$g_summary_jpgraph_bypriority         = $g_path."summary_graph_bypriority".$g_php;
	#----------------------------------

	#----------------------------------
	# bug view/update
	$g_view_all_bug_page              = $g_path."view_all_bug_page".$g_php;
	$g_view_all_include_file          = $g_absolute_path."view_all_inc.php";

	$g_view_bug_advanced_page         = $g_path."view_bug_advanced_page".$g_php;
	$g_view_bug_page                  = $g_path."view_bug_page".$g_php;
	$g_view_bug_inc                   = $g_absolute_path."view_bug_inc.php";
	$g_bug_file_upload_inc            = $g_absolute_path."bug_file_upload_inc.php";

	$g_file_download                  = $g_path."file_download".$g_php;

	$g_print_all_bug_page             = $g_path."print_all_bug_page".$g_php;
	#----------------------------------
	#----------------------------------
	# New files
	$g_bug_file_delete                = $g_path."bug_file_delete".$g_php;
	$g_csv_export_inc                 = $g_path."view_csv_export_inc".$g_php;
	$g_view_all_bug_update            = $g_path."view_all_bug_update".$g_php;
?>