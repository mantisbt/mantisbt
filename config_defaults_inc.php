<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# CONFIGURATION VARIABLES
	###########################################################################

	# config_defaults_inc.php

	# This file should not be changed. If you want to override any of the values
	# defined here, define them in a file called config_inc.php, which will be loaded
	# after this file.

	# In general a value of 0 means the feature is disabled and 1 means the
	# feature is enabled.  Any other cases will have an explanation.

	# Look in configuration.html for more detailed comments.

	################################
	# Mantis Database Settings
	################################

	# --- database variables ---------

	# set these values to match your setup
	$g_hostname      = 'localhost';
	$g_port          = 3306;         # 3306 is default
	$g_db_username   = 'root';
	$g_db_password   = '';
	$g_database_name = 'bugtracker';

	############################
	# Mantis Path Settings
	############################

	# --- path variables --------------

	# path to your installation as seen from the web browser
	# requires trailing /
	if ( isset( $SERVER_NAME ) && isset ( $PHP_SELF ) ) {
		$g_path = 'http://' . $SERVER_NAME . dirname( $PHP_SELF ) . '/';
	} else { 
		$g_path = 'http://yourhostnamehere/mantis/'; 
	}

	# path to your images directory (for icons)
	# requires trailing /
	$g_icon_path     = $g_path.'images/';

	# absolute path to your installation.  *NO* symlinks allowed (causes problems with file downloads)
	# requires trailing /
	$g_absolute_path = dirname( dirname( __FILE__ ) ) . '/';

	#############################
	# Web Server
	#############################

	# --- using MS IIS ----------------
	# set to ON if you use IIS
	$g_use_iis = OFF;

	#############################
	# Mantis Email Settings
	#############################

	# --- email variables -------------
	$g_administrator_email  = 'administrator@nowhere';
	$g_webmaster_email      = 'webmaster@nowhere';

	# the 'From: ' field in emails
	$g_from_email           = 'noreply@nowhere';

	# the 'To: ' address all emails are sent.  This can be a mailing list or archive address.
	# Actual users are emailed via the bcc: fields
	$g_to_email             = 'nobody@nowhere';

	# the return address for bounced mail
	$g_return_path_email    = 'admin@nowhere';

	# allow users to signup for their own accounts
	$g_allow_signup              = ON;

	# if ON users will be sent their password when reset.
	# if OFF the password will be set to blank.
	$g_send_reset_password       = ON;

	# allow email notification
	$g_enable_email_notification = ON;

	# Associated with each action a list of flags to control who should be notified.
	# The default will be used if the action is not included in $g_notify_flags or
	# if the flag is not included in the specific action definition.
	# The list of actions include: new, assigned, resolved, bugnote, reopened, closed, deleted, feedback
	# In case you need to override the threshold for the 'admin' group in the custom_config_inc.php, use:
	# $g_default_notify_flags['admin'] = OFF;
	$g_default_notify_flags = array('reporter' => ON, 
									'handler'  => ON, 
									'manager'  => ON, 
									'monitor'  => ON,
									'admin'    => ON,
									'bugnotes' => ON,
									'threshold' => DEVELOPER);

	# Following is the definition of the differences between the "new" action and the default.
	# In case you need to override the threshold for the new action in custom_config_inc.php, use: 
	# $g_notify_flags['new']['threshold'] = MANAGER;
	$g_notify_flags['new'] = array(	'bugnotes' => OFF,
									'monitor'  => OFF);

	# set to OFF to disable email check
	$g_validate_email            = ON;
	$g_check_mx_record           = ON;

	# This disables the automatic generation of mailto: links
	# Valid values: NONE, ALL, NO_ANONYMOUS, ADMIN_ONLY
	$g_show_user_email           = NONE;

	# Set to OFF to remove X-Priority header
	$g_use_x_priority            = ON;

	# Set to OFF on Windows systems, as long as php-mail-function has its bcc-bug (~PHP 4.0.6)
	$g_use_bcc                   = ON;

	# some Mail transfer agents (MTAs) don't like bare linefeeds...
	# or they take good input and create barelinefeeds
	# If problems occur when sending mail through your server try turning this OFF
	# more here: http://pobox.com/~djb/docs/smtplf.html
	$g_mail_send_crlf            = OFF;

	# phpMailer instead of standard mail() function (REQUIRES PHP 4.x.x)
	# Get the phpMailer-package from http://phpmailer.sourceforge.net
	# The installation is very simple you only need 2 plain text php-files
	#  class.smtp.php
	#  class.phpmailer.php

	# Copy these files to your php-include-dir i.e. 'c:\php\includes' or
	# '/usr/lib/php/includes'
	# and add this path to the 'include_path'-entry  in the php.ini file.
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
	$g_smtp_host     = 'localhost';

	# --- email separator and padding ------------
	$g_email_separator1     = '=======================================================================';
	$g_email_separator2     = '-----------------------------------------------------------------------';
	$g_email_padding_length = 28;

	#############################
	# Mantis Version String
	#############################

	# --- version variables -----------
	$g_mantis_version = '0.18.0-CVS';
	$g_show_version   = ON;

	################################
	# Mantis Language Settings
	################################

	# --- language settings -----------
	$g_default_language     = 'english';

	# list the choices that the users are allowed to choose
	$g_language_choices_arr = array( 'english', 'chinese_simplified', 'chinese_traditional', 'czech', 'danish', 'dutch', 'french', 'german', 'hungarian', 'italian', 'japanese_euc', 'japanese_sjis', 'korean', 'norwegian', 'polish', 'portuguese_brazil', 'portuguese_standard', 'romanian', 'russian', 'russian_koi8', 'spanish', 'swedish', 'turkish' );

	###############################
	# Mantis Display Settings
	###############################

	# --- sitewide variables ----------
	$g_window_title = 'Mantis';     # browser window title
	$g_page_title   = 'Mantis';     # title at top of html page

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

	# --- show projects when in All Projects mode ---
	$g_show_bug_project_links = ON;

	# --- Position of the status colour legend, can be: STATUS_LEGEND_POSITION_*
	# --- see constant_inc.php. (*: BOTTOM or TOP)
	$g_status_legend_position = STATUS_LEGEND_POSITION_BOTTOM;

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
	# (DEFINE('DIR_BASE','/www/mantisbt/jpgraph/');)

	$g_use_jpgraph = OFF;
	$g_jpgraph_path = './jpgraph/';   # dont forget the ending slash!

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
	$g_short_date_format    = 'm-d';
	$g_normal_date_format   = 'm-d H:i';
	$g_complete_date_format = 'm-d-y H:i T';

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
	
	# threshold for viewing private news
	$g_private_news_threshold = DEVELOPER;

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
	$g_hide_closed_default        = ON;

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

	# shows project '[project] category' when 'All Products' is selected
	# otherwise only 'category name'
	$g_summary_product_colon_category = OFF;
	
	# threshold for viewing summary
	$g_view_summary_threshold = VIEWER;

	###############################
	# Mantis Bugnote Settings
	###############################

	# --- bugnote ordering ------------
	# change to ASC or DESC
	$g_bugnote_order = 'ASC';

	# --- bugnote history ordering ----
	# change to ASC or DESC
	$g_history_order = 'ASC';

	###################################
	# Mantis File Upload Settings
	###################################

	# --- file upload settings --------
	# @@@ This should be broken into per project settings and split between bug uploads and project document uploads
	$g_allow_file_upload    = ON;

	# Upload destination: specify actual location in project settings
	# DISK, DATABASE, or FTP.
	$g_file_upload_method   = DATABASE;
	
	# FTP settings, used if $g_file_upload_method = FTP
	$g_file_upload_ftp_server = 'ftp.myserver.com';
	$g_file_upload_ftp_user = 'readwriteuser';
	$g_file_upload_ftp_pass = 'readwritepass';

	# Maximum file size that can be uploaded
	# Also check your PHP settings (default is usually 2MBs)
	$g_max_file_size        = 5000000; # 5 MB

	# Files that are allowed or not allowed.  Separate items by commas.
	# eg. 'php,html,java,exe,pl'
	# if $g_allowed_files is filled in NO other file types will be allowed.
	# $g_disallowed_files takes precedence over $g_allowed_files
	$g_allowed_files     = '';
	$g_disallowed_files  = '';

	############################
	# Mantis HTML Settings
	############################

	# --- html tags -------------------
	$g_allow_html_tags        = ON;

	# do NOT include href or img tags here
	# do NOT include tags that have parameters (eg. <font face="arial">)
	$g_html_tags              = array('<p>','</p>','<li>','</li>','<ul>','</ul>',
									'<ol>','</ol>','<br />','<br>','<pre>','</pre>',
									'<i>','</i>','<b>','</b>','<u>','</u>');

	$g_allow_href_tags        = ON;

	# --- table tags ------------------
	# this is inserted into the outermost tables ( tags like border, cellspacing, etc)
	$g_primary_table_tags          = '';

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
	$g_ldap_server       = '192.168.192.38';
	$g_ldap_root_dn      = 'dc=traffic,dc=redflex,dc=com,dc=au';
	$g_ldap_organisation = '(organizationname=*Traffic)'; # optional
	$g_use_ldap_email    = OFF; # Should we send to the LDAP email address or what MySql tells us
	# --- ldapauth type --- CLEAR or CRYPT (as in /etc/passwd /etc/shadow)
	$g_ldapauth_type	= 'CRYPT';

	############################
	# Mantis Misc Settings
	############################

	# --- threshold -------------------
	# access level needed to re-open bugs
	# Look in the constant_inc.php file if you want to set a different value
	$g_reopen_bug_threshold = DEVELOPER;

	# access level needed to close bugs
	# Look in the constant_inc.php file if you want to set a different value
	$g_close_bug_threshold = DEVELOPER;

	# access level needed to monitor bugs
	# Look in the constant_inc.php file if you want to set a different value
	$g_monitor_bug_threshold = REPORTER;

	# access level needed to view private bugs
	# Look in the constant_inc.php file if you want to set a different value
	$g_private_bug_threshold = DEVELOPER;

	# access level needed to be able to be listed in the assign to field.
	$g_handle_bug_threshold = DEVELOPER;

	# access level needed to view private bugnotes
	# Look in the constant_inc.php file if you want to set a different value
	$g_private_bugnote_threshold = DEVELOPER;

	# access level needed to view attachments to bugs reported by other users.
	$g_view_attachments_threshold = VIEWER;

	# --- login method ----------------
	# CRYPT or CRYPT_FULL_SALT or PLAIN or MD5 or LDAP or BASIC_AUTH
	# If you were using CRYPT and it now fails, try CRYPT_FULL_SALT
	$g_login_method = MD5;

	# --- limit reporters -------------
	# Set to ON if you wish to limit reporters to only viewing bugs that they report.
	$g_limit_reporters = OFF;

	# --- close immediately -----------
	# Allow developers and above to close bugs immediately when resolving bugs
	$g_allow_close_immediately = OFF;

	# --- reporter can close ----------
	# Allow reporters to close the bugs they reported, after they're marked resolved.
	$g_allow_reporter_close = OFF;

	# --- bug delete -----------
	# Allow the specified access level and higher to delete bugs
	$g_allow_bug_delete_access_level = DEVELOPER;

	# --- move bugs -----------
	# Allow the specified access level and higher to move bugs between projects
	$g_bug_move_access_level = DEVELOPER;

	# --- account delete -----------
	# Allow users to delete their own accounts
	$g_allow_account_delete = OFF;

	# --- anonymous login -----------
	# Allow anonymous login
	$g_allow_anonymous_login = OFF;
	$g_anonymous_account = '';

	# --- CVS linking ---------------
	# insert the URL to your CVSweb or ViewCVS
	$g_cvs_web = 'http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/mantisbt/mantisbt/';

	# --- Bug Linking ---------------
	# if a number follows this tag it will create a link to a bug.
	# eg. for # a link would be #45
	# eg. for bug: a link would be bug:98
	$g_bug_link_tag = '#';

	# --- Timer ----------------------
	# Time page loads.  Shows at the bottom of the page.
	$g_show_timer   = OFF;

	# used for development only.  Leave OFF
	$g_debug_timer = OFF;
	
	# --- Queries --------------------
	# Shows the total number/unique number of queries executed to serve the page.
	$g_show_queries_count = ON;
	
	# Shows the list of all queries that are executed in chronological order from top
	# to bottom.  This option is only effective when $g_show_queries_count is ON.
	# WARNING: Potential security hazard.  Only turn this on when you really
	# need it (for debugging/profiling)
	$g_show_queries_list = OFF;

	# --- register globals -----------
	# @@@ experimental
	# if your register_globals is Off then set this to OFF
	$g_register_globals          = ON;

	# Automatically set status to ASSIGNED whenever a bug is assigned to a person.
	# This is useful for installations where assigned status is to be used when
	# the defect is in progress, rather than just put in a person's queue.
	$g_auto_set_status_to_assigned = ON;

	# --- Custom attributes --------
	# Enables custom attributes
	$g_customize_attributes = ON;

  	################################
	# Mantis Look and Feel Variables
	################################

	# --- color values ----------------
	# you can change the look and feel by modifying these values

	$g_background_color        = '#ffffff'; # white
	$g_required_color          = '#bb0000'; # red
	$g_table_border_color      = '#000000'; # black
	$g_category_title_color    = '#c8c8e8'; # blue
	$g_primary_color1          = '#d8d8d8'; # dark gray
	$g_primary_color2          = '#e8e8e8'; # light gray
	$g_form_title_color        = '#ffffff'; # white
	$g_spacer_color            = '#ffffff'; # white
	$g_menu_color              = '#e8e8e8'; # light gray

	# --- status color codes ----------
	#
	$g_status_colors = array (	'new' => '#ffffff', # red,
							'feedback' => '#ffc0cc', # purple
							'acknowledged' => '#ff6600', # orange
							'confirmed' => '#ffd850', # yellow
							'assigned' => '#c8c8ff', # blue
							'resolved' => '#cceedd', # buish-green
							'closed' => '#e8e8e8'); # light gray
	# --- custom status color codes ----------
	# array for colors assoociated with custom attributes
	#
	$g_custom_colors		   = array( '#FAEBD7' => 'ANTIQUEWHITE',
										'#F5DEB3' =>  'WHEAT',     
										'#FFD700' => 'GOLD',  
										'#FFFFF0' => 'IVORY',  
										'#F0E68C' => 'KHAKI',  
										'#E6E6FA' => 'LAVENDER',  
										'#FFF0F5' => 'LAVENDERBLUSH',  
										'#FFFACD' => 'LEMONCHIFFON',  
										'#ADD8E6' => 'LIGHTBLUE',     
										'#90EE90' => 'LIGHTGREEN',  
										'#D3D3D3' => 'LIGHTGREY',  
										'#FFB6C1' => 'LIGHTPINK',  
										'#FFA07A' => 'LIGHTSALMON',  
										'#20B2AA' => 'LIGHTSEAGREEN',  
										'#87CEFA' => 'LIGHTSKYBLUE',  
										'#778899' => 'LIGHTSLATEGRAY',  
										'#B0C4DE' => 'LIGHTSTEELBLUE',  
										'#BA55D3' => 'MEDIUMORCHID',  
										'#D8BFD8' => 'THISTLE', 
										'#48D1CC' => 'MEDIUMTURQUOISE',        
										'#C71585' => 'MEDIUMVIOLETRED');   
	# the 'slots', umber between whic you can insert status. Between 51 and 79 (included)									
	$g_custom_status_slot = array('59','79');
						
	# --- fonts ----------
	#
	$g_fonts                   = 'Verdana, Arial, Helvetica, sans-serif';
	$g_font_small              = '8pt';
	$g_font_normal             = '10pt';
	$g_font_large              = '12pt';
	$g_font_color              = '#000000'; # black

	###############################
	# Mantis Cookie Variables
	###############################

	# --- cookie path ---------------
	# set this to something more restrictive if needed
	# http://www.php.net/manual/en/function.setcookie.php
	$g_cookie_path = '/';
	# unused
	$g_cookie_domain = '.mydomain.extension';
	# cookie version for view_all_page
	$g_cookie_version = 'v3';

	# --- cookie prefix ---------------
	# set this to a unique identifier.  No spaces.
	$g_cookie_prefix = 'MANTIS';

	# --- cookie names ----------------
	$g_string_cookie           = $g_cookie_prefix.'_STRING_COOKIE';
	$g_project_cookie          = $g_cookie_prefix.'_PROJECT_COOKIE';
	$g_view_all_cookie         = $g_cookie_prefix.'_VIEW_ALL_COOKIE';
	$g_manage_cookie           = $g_cookie_prefix.'_MANAGE_COOKIE';

	# --- cookie values ---------------
	$g_string_cookie_val           = '';
	$g_project_cookie_val          = '';
	$g_view_all_cookie_val         = '';
	$g_manage_cookie_val           = '';

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
	$g_db_table_prefix = 'mantis';

	# --- table names -----------------
	$g_mantis_bug_file_table          = $g_db_table_prefix.'_bug_file_table';
	$g_mantis_bug_history_table       = $g_db_table_prefix.'_bug_history_table';
	$g_mantis_bug_monitor_table       = $g_db_table_prefix.'_bug_monitor_table';
	$g_mantis_bug_relationship_table  = $g_db_table_prefix.'_bug_relationship_table';
	$g_mantis_bug_table               = $g_db_table_prefix.'_bug_table';
	$g_mantis_bug_text_table          = $g_db_table_prefix.'_bug_text_table';
	$g_mantis_bugnote_table           = $g_db_table_prefix.'_bugnote_table';
	$g_mantis_bugnote_text_table      = $g_db_table_prefix.'_bugnote_text_table';
	$g_mantis_news_table              = $g_db_table_prefix.'_news_table';
	$g_mantis_project_category_table  = $g_db_table_prefix.'_project_category_table';
	$g_mantis_project_file_table      = $g_db_table_prefix.'_project_file_table';
	$g_mantis_project_table           = $g_db_table_prefix.'_project_table';
	$g_mantis_project_user_list_table = $g_db_table_prefix.'_project_user_list_table';
	$g_mantis_project_version_table   = $g_db_table_prefix.'_project_version_table';
	$g_mantis_user_table              = $g_db_table_prefix.'_user_table';
	$g_mantis_user_profile_table      = $g_db_table_prefix.'_user_profile_table';
	$g_mantis_user_pref_table         = $g_db_table_prefix.'_user_pref_table';
	$g_mantis_user_print_pref_table   = $g_db_table_prefix.'_user_print_pref_table';
	$g_mantis_project_customization_table = $g_db_table_prefix.'_project_customization_table';

	###########################
	# Mantis Enum Strings
	###########################

	# --- enum strings ----------------
	# status from $g_status_index-1 to 79 are used for the onboard customization (if enabled)
	# directly use Mantis to edit them.
	$g_access_levels_enum_string      = '10:viewer,25:reporter,40:updater,55:developer,70:manager,90:administrator';
	$g_project_status_enum_string     = '10:development,30:release,50:stable,70:obsolete';
	$g_project_view_state_enum_string = '10:public,50:private';
	$g_view_state_enum_string         = '10:public,50:private';

	$g_priority_enum_string           = '10:none,20:low,30:normal,40:high,50:urgent,60:immediate';
	$g_severity_enum_string           = '10:feature,20:trivial,30:text,40:tweak,50:minor,60:major,70:crash,80:block';
	$g_reproducibility_enum_string    = '10:always,30:sometimes,50:random,70:have not tried,90:unable to duplicate,100:N/A';
	$g_status_enum_string             = '10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed';
	$g_resolution_enum_string         = '10:open,20:fixed,30:reopened,40:unable to duplicate,50:not fixable,60:duplicate,70:not a bug,80:suspended,90:wont fix';
	$g_projection_enum_string         = '10:none,30:tweak,50:minor fix,70:major rework,90:redesign';
	$g_eta_enum_string                = '10:none,20:< 1 day,30:2-3 days,40:< 1 week,50:< 1 month,60:> 1 month';

	#############################
	# Mantis Page Variables
	#############################

	#----------------------------------
	# extensions for php 3 and php 4
	# set this to php for php4 or whatever your webserver needs
	$g_php             = '.php';
	#----------------------------------

	#############################
	# Mantis Javascript Variables
	#############################
	#----------------------------------
	# allow the use of Javascript?
	# @@@ not yet implemented, but wanted this in config for 0.17.2+ -SC
	$g_use_javascript  = ON;
	#----------------------------------

	###########################
	# Mantis Speed Optimisation
	###########################
	#----------------------------------
	# Use compression of generated html if browser supports it
	# requires PHP 4.0.4 or later.
	$g_compress_html = OFF;
	#----------------------------------

	#----------------------------------
	# Use persistent database connections
	$g_use_persistent_connections = OFF;
	#----------------------------------

	###########################
	# Include files
	###########################
	#----------------------------------
	# Specify your top/bottom include file (logos, banners, etc)
	if ( ! isset( $g_bottom_include_page) ) {
		$g_bottom_include_page            = $g_absolute_path."";
	}
	if ( ! isset( $g_top_include_page) ) {
		$g_top_include_page               = $g_absolute_path."";
	}
	# CSS file
	if ( ! isset( $g_css_include_file) ) {
		$g_css_include_file               = $g_absolute_path."css_inc".$g_php;
	}
	# meta tags
	if ( ! isset( $g_meta_include_file) ) {
		$g_meta_include_file              = $g_absolute_path."meta_inc".$g_php;
	}
	#----------------------------------
	# Internal includes
	$g_bugnote_include_file           = $g_absolute_path."bugnote_inc.php";
	$g_history_include_file           = $g_absolute_path."history_inc".$g_php;
	$g_print_bugnote_include_file     = $g_absolute_path."print_bugnote_inc".$g_php;
	$g_view_all_include_file          = $g_absolute_path."view_all_inc".$g_php;
	$g_view_bug_inc                   = $g_absolute_path."view_bug_inc".$g_php;
	$g_bug_file_upload_inc            = $g_absolute_path."bug_file_upload_inc".$g_php;
	$g_summary_jpgraph_function       = $g_absolute_path."summary_graph_functions".$g_php;
	#----------------------------------

	###########################
	# Redirections
	###########################
	# ---------------------------------
	# Specify where the user should be sent after logging out.
	$g_logout_redirect_page           = $g_path."login_page".$g_php;
?>
