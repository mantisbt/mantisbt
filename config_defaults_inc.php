<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: config_defaults_inc.php,v 1.112 2003-03-10 09:40:53 int2str Exp $
	# --------------------------------------------------------

	
	###########################################################################
	# CONFIGURATION VARIABLES
	###########################################################################

	# config_defaults_inc.php

	# This file should not be changed. If you want to override any of the values
	# defined here, define them in a file called custom_config_inc.php, which will
	# be loaded after this file.

	# In general a value of OFF means the feature is disabled and ON means the
	# feature is enabled.  Any other cases will have an explanation.

	# Look in configuration.html for more detailed comments.

	################################
	# Mantis Database Settings
	################################

	# --- database variables ---------

	# set these values to match your setup
	$g_hostname				= 'localhost';
	$g_port					= 3306;		 # 3306 is default
	$g_db_username			= 'root';
	$g_db_password			= '';
	$g_database_name		= 'bugtracker';

	############################
	# Mantis Path Settings
	############################

	# --- path variables --------------

	# path to your installation as seen from the web browser
	# requires trailing /
	if ( isset ( $_SERVER['PHP_SELF'] ) ) {
		$t_protocol = 'http';
		if ( isset( $_SERVER['HTTPS'] ) && ( $_SERVER['HTTPS'] != 'off' ) ) {
			$t_protocol = 'https';
		}

		$t_port = ':' . $_SERVER['SERVER_PORT'];
		if ( ( ':80' == $t_port && 'http' == $t_protocol )
		  || ( ':443' == $t_port && 'https' == $t_protocol )) {
			$t_port = '';
		}

		if ( isset( $_SERVER['HTTP_HOST'] ) ) {
			$t_host = $_SERVER['HTTP_HOST'];
		} else if ( isset( $_SERVER['SERVER_NAME'] ) ) {
			$t_host = $_SERVER['SERVER_NAME'] . $t_port;
		} else if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
			$t_host = $_SERVER['SERVER_ADDR'] . $t_port;
		} else {
			$t_host = 'yourhostnamehere';
		}

		$t_path = dirname( $_SERVER['PHP_SELF'] );
		if ( '/' == $t_path || '\\' == $t_path ) {
			$t_path = '';
		}

		$g_path	= $t_protocol . '://' . $t_host . $t_path.'/';
	} else {
		$g_path	= 'http://yourhostnamehere/mantis/';
	}

	# path to your images directory (for icons)
	# requires trailing /
	$g_icon_path			= $g_path.'images/';

	# absolute path to your installation.  Requires trailing / or \
	# Symbolic links are allowed since release 0.17.3
	$g_absolute_path		= dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

	# absolute patch to your core files. The default is usually OK,
	# unless you moved the 'core' directory out of your webroot (recommended).
	$g_core_path			= $g_absolute_path.'core' . DIRECTORY_SEPARATOR;

	#############################
	# Web Server
	#############################

	# Using Microsoft Internet Information Server (IIS)
	$g_use_iis = ( strstr( $_SERVER['SERVER_SOFTWARE'], 'IIS' ) !== false ) ? ON : OFF;

	#############################
	# Mantis Email Settings
	#############################

	# --- email variables -------------
	$g_administrator_email	= 'administrator@nowhere';
	$g_webmaster_email		= 'webmaster@nowhere';

	# the 'From: ' field in emails
	$g_from_email			= 'noreply@nowhere';

	# the 'To: ' address all emails are sent.  This can be a mailing list or archive address.
	# Actual users are emailed via the bcc: fields
	$g_to_email				= 'nobody@nowhere';

	# the return address for bounced mail
	$g_return_path_email	= 'admin@nowhere';

	# allow users to signup for their own accounts
	$g_allow_signup			= ON;

	# if ON users will be sent their password when reset.
	# if OFF the password will be set to blank.
	$g_send_reset_password	= ON;

	# allow email notification
	$g_enable_email_notification	= ON;

	# The following two config options allow you to control who should get email
	# notifications on different actions.  The first option (default_notify_flags)
	# sets the default values for different user categories.  The user categories
	# are:
	# 
	# 	   'reporter': the reporter of the bug
	# 	    'handler': the handler of the bug
	# 	    'monitor': users who are monitoring a bug
	# 	   'bugnotes': users who have added a bugnote to the bug
	# 'threshold_max': all users below this access level...
	# 'threshold_min': ..and above this access level
	# 
	# The second config option (notify_flags) sets overrides for specific actions.
	# If a user category is not listed for an action, the default from the config
	# option above is used.  The possible actions are:
	# 
	# 	   'new': a new bug has been added
	# 'assigned': a bug has been assigned
	# 'resolved': a bug has been resolved
	#  'bugnote': a bugnote has been added to a bug
	# 'reopened': a bugnote has been reopened
	#   'closed': a bug has been closed
	#  'deleted': a bug has been deleted
	# 'feedback': a bug has been put into the FEEDBACK state
	# 
	# If you wanted to have all developers get notified of new bugs you might add
	# the following lines to your config file:
	# 
	# $g_notify_flags['new']['threshold_min'] = DEVELOPER;
	# $g_notify_flags['new']['threshold_max'] = DEVELOPER;
	# 
	# You might want to do something similar so all managers are notified when a
	# bug is closed.  If you didn't want reporters to be notified when a bug is
	# closed (only when it is resolved) you would use:
	# 
	# $g_notify_flags['closed']['reporter'] = OFF;

	$g_default_notify_flags	= array('reporter'	=> ON,
									'handler'	=> ON,
									'monitor'	=> ON,
									'bugnotes'	=> ON,
									'threshold_min'	=> NOBODY,
									'threshold_max' => NOBODY);

	# We don't need to send these notifications on new bugs
	# (see above for info on this config option)
	#@@@ (though I'm not sure they need to be turned off anymore
	#      - there just won't be anyone in those categories)
	#      I guess it serves as an example and a placeholder for this
	#      config option
	$g_notify_flags['new']	= array('bugnotes'	=> OFF,
									'monitor'	=> OFF);

	# Whether user's should receive emails for their own actions
	$g_email_receive_own	= OFF;
	
	# set to OFF to disable email check
	$g_validate_email		= ON;
	$g_check_mx_record		= ON;

	# if ON, allow the user to omit an email field
	# note if you allow users to create their own accounts, they
	#  must specify an email at that point, no matter what the value
	#  of this option is.  Otherwise they wouldn't get their passwords.
	$g_allow_blank_email	= ON;

	# Only allow and send email to addresses in the given domain
	# For example:
	# $g_limit_email_domain		= 'users.sourceforge.net';
	$g_limit_email_domain	= OFF;

	# This specifies the access level that is needed to get the mailto: links.
	$g_show_user_email_threshold = NOBODY;

	# Set to OFF to remove X-Priority header
	$g_use_x_priority		= ON;
	
	# If use_x_priority is set to ON, what should the value be?
	# Urgent = 1, Not Urgent = 5, Disable = 0
	# Note: some MTAs interpret X-Priority = 0 to mean 'Very Urgent'
	$g_mail_priority		= 3;

	# Set to OFF on Windows systems, as long as php-mail-function has its bcc-bug (~PHP 4.0.6)
	$g_use_bcc				= ON;

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

	$g_use_phpMailer		= OFF;

	# select the method to mail by:
	# 0 - mail()
	# 1 - sendmail
	# 2 - SMTP
	$g_phpMailer_method		= 0;

	# This option allows you to use a remote SMTP host.  Must use the phpMailer script
	# Name of smtp host, needed for phpMailer, taken from php.ini
	$g_smtp_host			= 'localhost';

	# These options allow you to use SMTP Authentication when you use a remote
	# SMTP host with phpMailer.  If smtp_username is not '' then the username
	# and password will be used when logging in to the SMTP server.
	$g_smtp_username = '';
	$g_smtp_password = '';

	# Specify whether e-mails should be sent with the category set or not.  This is tested
	# with Microsoft Outlook.  More testing for this feature + other formats will be added
	# in the future.
	# OFF, EMAIL_CATEGORY_PROJECT_CATEGORY (format: [Project] Category)
	$g_email_set_category		= OFF;

	# --- email separator and padding ------------
	$g_email_separator1		= str_pad('', 70, '=');
	$g_email_separator2		= str_pad('', 70, '-');
	$g_email_padding_length	= 28;

	#############################
	# Mantis Version String
	#############################

	# --- version variables -----------
	$g_mantis_version		= '0.18.0-CVS';
	$g_show_version			= ON;

	################################
	# Mantis Language Settings
	################################

	# --- language settings -----------
	$g_default_language		= 'english';

	# list the choices that the users are allowed to choose
	$g_language_choices_arr	= array( 'english', 'chinese_simplified', 'chinese_traditional', 'czech', 'danish', 'dutch', 'french', 'german', 'hungarian', 'italian', 'japanese_euc', 'japanese_sjis', 'korean', 'norwegian', 'polish', 'portuguese_brazil', 'portuguese_standard', 'romanian', 'russian', 'russian_koi8', 'slovak', 'spanish', 'swedish', 'turkish' );

	###############################
	# Mantis Display Settings
	###############################

	# --- sitewide variables ----------
	$g_window_title			= 'Mantis';	 # browser window title
	$g_page_title			= 'Mantis';	 # title at top of html page

	# --- project name -----------------
	# show the project name in the page title
	# OFF/ON
	# Previous versions supported a third value that displayed only the project
	#  name.  If you want this behaviour, simply set the titles above to ''
	$g_show_project_in_title	= ON;

	# --- advanced views --------------
	# BOTH, SIMPLE_ONLY, ADVANCED_ONLY
	$g_show_report			= BOTH;
	$g_show_update			= BOTH;
	$g_show_view			= BOTH;

	# --- footer menu -----------------
	# Display another instance of the menu at the bottom.  The top menu will still remain.
	$g_show_footer_menu		= OFF;

	# --- show extra menu bar with all available projects ---
	$g_show_project_menu_bar = OFF;

	# --- show assigned to names ------
	# This is in the view all pages
	$g_show_assigned_names	= ON;

	# --- show priority as icon ---
	# OFF: Shows priority as icon in view all bugs page
	# ON:  Shows priority as text in view all bugs page
	$g_show_priority_text	= OFF;

	# --- show projects when in All Projects mode ---
	$g_show_bug_project_links	= ON;

	# --- Position of the status colour legend, can be: STATUS_LEGEND_POSITION_*
	# --- see constant_inc.php. (*: BOTTOM or TOP)
	$g_status_legend_position	= STATUS_LEGEND_POSITION_BOTTOM;

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

	$g_use_jpgraph			= OFF;
	$g_jpgraph_path			= '.' . DIRECTORY_SEPARATOR . 'jpgraph' . DIRECTORY_SEPARATOR;   # dont forget the ending slash!

	############################
	# Mantis Time Settings
	############################

	# --- time varaibles --------------

	# time for 'permanent' cookie to live in seconds (1 year)
	$g_cookie_time_length	= 30000000;

	# time to delay between page redirects (in seconds)
	$g_wait_time			= 2;

	# minutes to wait before document is stale (in minutes)
	$g_content_expire		= 0;

	# The time (in seconds) to allow for page execution during long processes
	#  such as upgrading your database.
	# The default value of 0 indicates that the page should be allowed to
	#  execute until it is finished.
	$g_long_process_timeout	= 0;

	############################
	# Mantis Date Settings
	############################

	# --- date format settings --------
	# date format strings (default is 'US' formatting)
	# go to http://www.php.net/manual/en/function.date.php
	# for detailed instructions on date formatting
	$g_short_date_format	= 'm-d-y';
	$g_normal_date_format	= 'm-d-y H:i';
	$g_complete_date_format	= 'm-d-Y H:i T';
#MANUAL---------------------------------------STOPPED HERE-------------------------
	############################
	# Mantis News Settings
	############################

	# --- Limit News Items ------------
	# limit by entry count or date
	# BY_LIMIT - entry limit
	# BY_DATE - by date
	$g_news_limit_method	= BY_LIMIT;

	# limit by last X entries
	$g_news_view_limit		= 7;

	# limit by days
	$g_news_view_limit_days	= 30;

	# threshold for viewing private news
	$g_private_news_threshold	= DEVELOPER;

	##################################
	# Mantis Default Preferences
	##################################

	# --- signup default ---------------
	# look in constant_inc.php for values
	$g_default_new_account_access_level	= REPORTER;

	# --- viewing defaults ------------
	# site defaults for viewing preferences
	$g_default_limit_view	= 50;
	$g_default_show_changed	= 6;
	$g_hide_closed_default	= ON;

	# make sure people aren't refreshing too often
	$g_min_refresh_delay	= 10;    # in minutes

	# --- account pref defaults -------
	$g_default_advanced_report		= OFF;
	$g_default_advanced_view		= OFF;
	$g_default_advanced_update		= OFF;
	$g_default_refresh_delay		= 30;    # in minutes
	$g_default_redirect_delay		= 2;     # in seconds
	$g_default_email_on_new			= ON;
	$g_default_email_on_assigned	= ON;
	$g_default_email_on_feedback	= ON;
	$g_default_email_on_resolved	= ON;
	$g_default_email_on_closed		= ON;
	$g_default_email_on_reopened	= ON;
	$g_default_email_on_bugnote		= ON;
	$g_default_email_on_status		= 0; # @@@ Unused
	$g_default_email_on_priority	= 0; # @@@ Unused
	# default_language - is set to site language

	###############################
	# Mantis Summary Settings
	###############################

	# how many reporters to show
	# this is useful when there are hundreds of reporters
	$g_reporter_summary_limit	= 10;

	# --- summary date displays -------
	# date lengths to count bugs by (in days)
	$g_date_partitions			= array( 1, 2, 3, 7, 30, 60, 90, 180, 365);

	# shows project '[project] category' when 'All Projects' is selected
	# otherwise only 'category name'
	$g_summary_category_include_project	= OFF;

	# threshold for viewing summary
	$g_view_summary_threshold	= VIEWER;

	###############################
	# Mantis Bugnote Settings
	###############################

	# --- bugnote ordering ------------
	# change to ASC or DESC
	$g_bugnote_order		= 'ASC';

	################################
	# Mantis Bug History Settings
	################################

	# --- bug history visible by default when you view a bug ----
	# change to ON or OFF
	$g_history_default_visible	= ON;

	# --- bug history ordering ----
	# change to ASC or DESC
	$g_history_order		= 'ASC';

	###############################
	# Mantis Reminder Settings
	###############################

	# are reminders stored as bugnotes
	$g_store_reminders		= ON;

	# Automatically add recipients of reminders to monitor list, if they are not
	# the handler or the reporter (since they automatically get notified, if required)
	# If recipients of the reminders are below the monitor threshold, they will not be added.
	$g_reminder_recipents_monitor_bug = ON;

	###################################
	# Mantis File Upload Settings
	###################################

	# --- file upload settings --------
	# This is the master setting to disable *all* file uploading functionality
	#
	# If you want to allow file uploads, you must also make sure that they are
	#  enabled in php.  You may need to add 'file_uploads = TRUE' to your php.ini
	#
	# See also: $g_upload_project_file_threshold, $g_upload_bug_file_threshold,
	#   $g_allow_reporter_upload
	$g_allow_file_upload	= ON;

	# Upload destination: specify actual location in project settings
	# DISK, DATABASE, or FTP.
	$g_file_upload_method	= DATABASE;

	# FTP settings, used if $g_file_upload_method = FTP
	$g_file_upload_ftp_server	= 'ftp.myserver.com';
	$g_file_upload_ftp_user		= 'readwriteuser';
	$g_file_upload_ftp_pass		= 'readwritepass';

	# Maximum file size that can be uploaded
	# Also check your PHP settings (default is usually 2MBs)
	$g_max_file_size		= 5000000; # 5 MB

	# Files that are allowed or not allowed.  Separate items by commas.
	# eg. 'php,html,java,exe,pl'
	# if $g_allowed_files is filled in NO other file types will be allowed.
	# $g_disallowed_files takes precedence over $g_allowed_files
	$g_allowed_files		= '';
	$g_disallowed_files		= '';

	# prefix to be used for the file system names of files uploaded to projects.
	# Eg: doc-001-myprojdoc.zip
	$g_document_files_prefix = 'doc';

	# Specifies the maximum size below which an attachment is previewed in the bug
	# view pages.  To disable this feature, set max size to 0.
	# This feature applies to: bmp, png, gif, jpg
	$g_preview_attachments_inline_max_size = 0;

	############################
	# Mantis HTML Settings
	############################

	# --- html tags -------------------

	# Set this flag to automatically convert www URLs and
	# email adresses into clickable links
	$g_html_make_links		= ON;

	# do NOT include href or img tags here
	# do NOT include tags that have parameters (eg. <font face="arial">)
	$g_html_valid_tags		= 'p, li, ul, ol, br, pre, i, b, u';

	##########################
	# Mantis HR Settings
	##########################

	# --- hr --------------------------
	$g_hr_size				= 1;
	$g_hr_width				= 50;

	#############################
	# Mantis LDAP Settings
	#############################

	# look in README.LDAP for details

	# --- using openldap -------------
	$g_ldap_server			= 'ldaps://ldap.example.com.au/';
	$g_ldap_port			= '636';
	$g_ldap_root_dn			= 'dc=example,dc=com,dc=au';
	#$g_ldap_organization	= '(organizationname=*Traffic)'; # optional
	$g_ldap_bind_dn			= '';
	$g_ldap_bind_passwd		= '';
	$g_use_ldap_email		= OFF; # Should we send to the LDAP email address or what MySql tells us

	############################
	# Mantis Misc Settings
	############################

	# --- status thresholds (*_status_threshold) ---

	# Bug is resolved, ready to be closed or reopened.  In some custom installations a bug
	# maybe considered as resolved when it is moved to a custom (FIXED OR TESTED) status.
	$g_bug_resolved_status_threshold = RESOLVED;

	# --- access level thresholds (*_threshold) ---

	# access level needed to report a bug
	$g_report_bug_threshold			= REPORTER;

	# access level needed to update bugs
	$g_update_bug_threshold			= UPDATER;

	# access level needed to re-open bugs
	# Look in the constant_inc.php file if you want to set a different value
	$g_reopen_bug_threshold			= DEVELOPER;

	# access level needed to close bugs
	# Look in the constant_inc.php file if you want to set a different value
	$g_close_bug_threshold			= DEVELOPER;

	# access level needed to monitor bugs
	# Look in the constant_inc.php file if you want to set a different value
	$g_monitor_bug_threshold		= REPORTER;

	# access level needed to view private bugs
	# Look in the constant_inc.php file if you want to set a different value
	$g_private_bug_threshold		= DEVELOPER;

	# access level needed to be able to be listed in the assign to field.
	$g_handle_bug_threshold			= DEVELOPER;

	# access level needed to view private bugnotes
	# Look in the constant_inc.php file if you want to set a different value
	$g_private_bugnote_threshold	= DEVELOPER;

	# access level needed to view attachments to bugs reported by other users.
	$g_view_attachments_threshold	= VIEWER;

	# access level needed to send a reminder from the bug view pages
	# set to NOBODY to disable the feature
	$g_bug_reminder_threshold		= REPORTER;

	# access level needed to upload files to the project documentation section
	# You can set this to NOBODY to prevent uploads to projects
	# See also: $g_upload_bug_file_threshold, $g_allow_file_upload
	$g_upload_project_file_threshold = MANAGER;

	# access level needed to upload files to attach to a bug
	# You can set this to NOBODY to prevent uploads to bugs but note that
	#  the reporter of the bug will still be able to upload unless you set
	#  $g_allow_reporter_upload or $g_allow_file_upload to OFF
	# See also: $g_upload_project_file_threshold, $g_allow_file_upload,
	#			$g_allow_reporter_upload
	$g_upload_bug_file_threshold	= REPORTER;

	# Add bugnote threshold
	$g_add_bugnote_threshold = REPORTER;

	# Update bugnote threshold (if the bugnote is not your own)
	$g_update_bugnote_threshold = DEVELOPER;

	# Add profile threshold
	$g_add_profile_threshold = REPORTER;

	# Threshold needed to view project documentation
	$g_view_proj_doc_threshold = ANYBODY;

	# Threshold needed to manage a project: edit project
	# details (not to add/delete projects), upload documentation, ...etc.
	$g_manage_project_threshold = MANAGER;

	# Threshold needed to add/delete/modify news
	$g_manage_news_threshold = MANAGER;

	# Threshold required to delete a project
	$g_delete_project_threshold = ADMINISTRATOR;

	# Threshold needed to create a new project
	$g_create_project_threshold = ADMINISTRATOR;

	# Threshold needed to be automatically included in private projects
	$g_private_project_threshold = ADMINISTRATOR;

	# Threshold needed to manage user access to a project
	$g_project_user_threshold = MANAGER;

	# Threshold needed to manage user accounts
	$g_manage_user_threshold = ADMINISTRATOR;
	
	# Delete bug threshold
	$g_delete_bug_threshold = DEVELOPER;
	
	# Delete bugnote threshold
	$g_delete_bugnote_threshold = $g_delete_bug_threshold;
	
	# Are users allowed to change and delete their own bugnotes?
	$g_bugnote_allow_user_edit_delete = ON;

	# Move bug threshold
	$g_move_bug_threshold = DEVELOPER;

	# --- Threshold needed to show the list of users montoring a bug on the bug view pages.
	$g_show_monitor_list_threshold = DEVELOPER;

	# --- login method ----------------
	# CRYPT or PLAIN or MD5 or LDAP or BASIC_AUTH
	# You can simply change this at will. Mantis will try to figure out how the passwords were encrypted.
	$g_login_method				= MD5;

	# --- limit reporters -------------
	# Set to ON if you wish to limit reporters to only viewing bugs that they report.
	$g_limit_reporters			= OFF;

	# --- close immediately -----------
	# Allow developers and above to close bugs immediately when resolving bugs
	$g_allow_close_immediately	= OFF;

	# --- reporter can close ----------
	# Allow reporters to close the bugs they reported, after they're marked resolved.
	$g_allow_reporter_close		= OFF;

	# --- reporter can reopen ---------
	# Allow reporters to reopen the bugs they reported, after they're marked resolved.
	$g_allow_reporter_reopen	= ON;

	# --- reporter can upload ---------
	# Allow reporters to upload attachments to bugs they reported.
	$g_allow_reporter_upload	= ON;

	# --- account delete -----------
	# Allow users to delete their own accounts
	$g_allow_account_delete		= OFF;

	# --- anonymous login -----------
	# Allow anonymous login
	$g_allow_anonymous_login	= OFF;
	$g_anonymous_account		= '';

	# --- CVS linking ---------------
	# insert the URL to your CVSweb or ViewCVS
	# eg: http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/mantisbt/mantisbt/
	$g_cvs_web				= '';

	# --- Bug Linking ---------------
	# if a number follows this tag it will create a link to a bug.
	# eg. for # a link would be #45
	# eg. for bug: a link would be bug:98
	$g_bug_link_tag			= '#';

	# --- Queries --------------------
	# Shows the total number/unique number of queries executed to serve the page.
	$g_show_queries_count	= ON;

	# Automatically set status to ASSIGNED whenever a bug is assigned to a person.
	# This is useful for installations where assigned status is to be used when
	# the bug is in progress, rather than just put in a person's queue.
	$g_auto_set_status_to_assigned	= ON;

	################################
	# Mantis Look and Feel Variables
	################################

	# --- status color codes ----------
	#
	$g_status_colors		= array( 'new'			=> '#ffa0a0', # red,
									 'feedback'		=> '#ff50a8', # purple
									 'acknowledged'	=> '#ffd850', # orange
									 'confirmed'	=> '#ffffb0', # yellow
									 'assigned'		=> '#c8c8ff', # blue
									 'resolved'		=> '#cceedd', # buish-green
									 'closed'		=> '#e8e8e8'); # light gray

	# The padding level when displaying project ids
	#  The bug id will be padded with 0's up to the size given
	$g_display_project_padding	= 3;

	# The padding level when displaying bug ids
	#  The bug id will be padded with 0's up to the size given
	$g_display_bug_padding		= 7;

	# The padding level when displaying bugnote ids
	#  The bugnote id will be padded with 0's up to the size given
	$g_display_bugnote_padding	= 7;

	###############################
	# Mantis Cookie Variables
	###############################

	# --- cookie path ---------------
	# set this to something more restrictive if needed
	# http://www.php.net/manual/en/function.setcookie.php
	$g_cookie_path			= '/';
	# unused
	$g_cookie_domain		= '';
	# cookie version for view_all_page
	$g_cookie_version		= 'v4';

	# --- cookie prefix ---------------
	# set this to a unique identifier.  No spaces.
	$g_cookie_prefix		= 'MANTIS';

	# --- cookie names ----------------
	$g_string_cookie		= $g_cookie_prefix.'_STRING_COOKIE';
	$g_project_cookie		= $g_cookie_prefix.'_PROJECT_COOKIE';
	$g_view_all_cookie		= $g_cookie_prefix.'_VIEW_ALL_COOKIE';
	$g_manage_cookie		= $g_cookie_prefix.'_MANAGE_COOKIE';

	#######################################
	# Mantis Database Table Variables
	#######################################

	# --- table prefix ----------------
	# if you change this remember to reflect the changes in the database
	$g_db_table_prefix		= 'mantis';

	# --- table names -----------------
	$g_mantis_bug_file_table				= $g_db_table_prefix.'_bug_file_table';
	$g_mantis_bug_history_table				= $g_db_table_prefix.'_bug_history_table';
	$g_mantis_bug_monitor_table				= $g_db_table_prefix.'_bug_monitor_table';
	$g_mantis_bug_relationship_table		= $g_db_table_prefix.'_bug_relationship_table';
	$g_mantis_bug_table						= $g_db_table_prefix.'_bug_table';
	$g_mantis_bug_text_table				= $g_db_table_prefix.'_bug_text_table';
	$g_mantis_bugnote_table					= $g_db_table_prefix.'_bugnote_table';
	$g_mantis_bugnote_text_table			= $g_db_table_prefix.'_bugnote_text_table';
	$g_mantis_news_table					= $g_db_table_prefix.'_news_table';
	$g_mantis_project_category_table		= $g_db_table_prefix.'_project_category_table';
	$g_mantis_project_file_table			= $g_db_table_prefix.'_project_file_table';
	$g_mantis_project_table					= $g_db_table_prefix.'_project_table';
	$g_mantis_project_user_list_table		= $g_db_table_prefix.'_project_user_list_table';
	$g_mantis_project_version_table			= $g_db_table_prefix.'_project_version_table';
	$g_mantis_user_table					= $g_db_table_prefix.'_user_table';
	$g_mantis_user_profile_table			= $g_db_table_prefix.'_user_profile_table';
	$g_mantis_user_pref_table				= $g_db_table_prefix.'_user_pref_table';
	$g_mantis_user_print_pref_table			= $g_db_table_prefix.'_user_print_pref_table';
	$g_mantis_custom_field_project_table	= $g_db_table_prefix.'_custom_field_project_table';
	$g_mantis_custom_field_table      	    = $g_db_table_prefix.'_custom_field_table';
	$g_mantis_custom_field_string_table     = $g_db_table_prefix.'_custom_field_string_table';
	$g_mantis_upgrade_table					= $g_db_table_prefix.'_upgrade_table';

	###########################
	# Mantis Enum Strings
	###########################

	# --- enum strings ----------------
	# status from $g_status_index-1 to 79 are used for the onboard customization (if enabled)
	# directly use Mantis to edit them.
	$g_access_levels_enum_string		= '10:viewer,25:reporter,40:updater,55:developer,70:manager,90:administrator';
	$g_project_status_enum_string		= '10:development,30:release,50:stable,70:obsolete';
	$g_project_view_state_enum_string	= '10:public,50:private';
	$g_view_state_enum_string			= '10:public,50:private';

	$g_priority_enum_string				= '10:none,20:low,30:normal,40:high,50:urgent,60:immediate';
	$g_severity_enum_string				= '10:feature,20:trivial,30:text,40:tweak,50:minor,60:major,70:crash,80:block';
	$g_reproducibility_enum_string		= '10:always,30:sometimes,50:random,70:have not tried,90:unable to duplicate,100:N/A';
	$g_status_enum_string				= '10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed';
	$g_resolution_enum_string			= '10:open,20:fixed,30:reopened,40:unable to duplicate,50:not fixable,60:duplicate,70:not a bug,80:suspended,90:wont fix';
	$g_projection_enum_string			= '10:none,30:tweak,50:minor fix,70:major rework,90:redesign';
	$g_eta_enum_string					= '10:none,20:< 1 day,30:2-3 days,40:< 1 week,50:< 1 month,60:> 1 month';

	$g_custom_field_type_enum_string    = '0:string,1:numeric,2:float,3:enum,4:email';

	#############################
	# Mantis Page Variables
	#############################

	#############################
	# Mantis Javascript Variables
	#############################
	#----------------------------------
	# allow the use of Javascript?
	$g_use_javascript		= ON;
	#----------------------------------

	###########################
	# Mantis Speed Optimisation
	###########################
	#----------------------------------
	# Use compression of generated html if browser supports it
	$g_compress_html		= ON;
	#----------------------------------

	#----------------------------------
	# Use persistent database connections
	$g_use_persistent_connections	= OFF;
	#----------------------------------

	###########################
	# Include files
	###########################
	#----------------------------------
	# Specify your top/bottom include file (logos, banners, etc)
	$g_bottom_include_page			= $g_absolute_path.'';
	$g_top_include_page				= $g_absolute_path.'';
	# CSS file
	$g_css_include_file				= $g_path.'css/default.css';
	# meta tags
	$g_meta_include_file			= $g_absolute_path.'meta_inc.php';
	#----------------------------------

	###########################
	# Redirections
	###########################
	# ---------------------------------
	# Specify where the user should be sent after logging out.
	$g_logout_redirect_page			= $g_path.'login_page.php';

	###########################
	# Headers
	###########################
	# ---------------------------------
	# An array of headers to be sent with each page.
	# For example, to allow your mantis installation to be viewed in a frame in IE 6
	#  when the frameset is not at the same hostname as the mantis install, you need
	#  to add a P3P header.  You could try something like 'P3P: CP="CUR ADM"' in your
	#  config file, but make sure to check that the your policy actually matches with
	#  what you are promising. See
	#  http://msdn.microsoft.com/library/default.asp?url=/library/en-us/dnpriv/html/ie6privacyfeature.asp
	#  for more information.

	$g_custom_headers				= array();
	#$g_custom_headers[]			= 'P3P: CP="CUR ADM"';

	###########################
	# Debugging
	###########################

	# --- Timer ----------------------
	# Time page loads.  Shows at the bottom of the page.
	$g_show_timer			= OFF;

	# used for development only.  Leave OFF
	$g_debug_timer			= OFF;

	# Used for debugging e-mail feature, when set to OFF the emails work as normal.
	# when set to e-mail address, all e-mails are sent to this address with the
	# original To, Cc, Bcc included in the message body.
	$g_debug_email			= OFF;

	# Shows the list of all queries that are executed in chronological order from top
	# to bottom.  This option is only effective when $g_show_queries_count is ON.
	# WARNING: Potential security hazard.  Only turn this on when you really
	# need it (for debugging/profiling)
	$g_show_queries_list	= OFF;

	# --- detailed error messages -----
	# Shows a list of variables and their values when an error is triggered
	# Only applies to error types configured to 'halt' in $g_display_errors, below
	# WARNING: Potential security hazard.  Only turn this on when you really
	# need it for debugging
	$g_show_detailed_errors	= OFF;

	# --- notice display ---
	# Control whether errors of level NOTICE, the lowest level of error,
	#  are displayed to the user.  Default is OFF, but turning it ON may
	#  be useful while debugging
	$g_show_notices			= OFF;

	# --- warning display ---
	# Control whether errors of level WARNING, the middle level of error,
	#  are displayed to the user.  Default is ON.  Turning it OFF may
	#  hide useful information from the user.
	$g_show_warnings		= ON;

	# --- debug messages ---
	# If this option is turned OFF (default) page redirects will continue to
	#  function even if a non-fatal error occurs.  For debugging purposes, you
	#  can set this to ON so that any non-fatal error will prevent page redirection,
	#  allowing you to see the errors.
	# Only turn this option on for debugging
	$g_stop_on_errors		= OFF;

	##################
	# Custom Fields
	##################

	# Threshold needed to manage custom fields
	$g_manage_custom_fields_threshold = ADMINISTRATOR;

	# Threshold needed to link/unlink custom field to/from a project
	$g_custom_field_link_threshold = MANAGER;

	# Whether to start editng a custom field immediately after creating it
	$g_custom_field_edit_after_create = ON;

	##########
	# Icons
	##########

	# Maps a file extension to a file type icon.  These icons are printed 
	# next to project documents and bug attachments.
	# Note:
	# - Extensions must be in lower case
	# - All icons will be displayed as 16x16 pixels.
	$g_file_type_icons = array(	'pdf' => 'pdficon.gif',
					'doc' => 'wordicon.gif',
					'dot' => 'wordicon.gif',
					'rtf' => 'wordicon.gif',
					'xls' => 'excelicon.gif',
					'xlk' => 'excelicon.gif',
					'csv' => 'excelicon.gif',
					'ppt' => 'ppticon.gif',
					'htm' => 'htmlicon.gif',
					'html' => 'htmlicon.gif',
					'css' => 'htmlicon.gif',
					'gif' => 'gificon.gif',
					'jpg' => 'jpgicon.gif',
					'png' => 'pngicon.gif',
					'zip' => 'zipicon.gif',
					'tar' => 'zipicon.gif',
					'gz' => 'zipicon.gif',
					'tgz' => 'zipicon.gif',
					'rar' => 'zipicon.gif',
					'arj' => 'zipicon.gif',
					'lzh' => 'zipicon.gif',
					'uc2' => 'zipicon.gif',
					'ace' => 'zipicon.gif',
					'txt' => 'texticon.gif',
					'log' => 'texticon.gif',
					'eml' => 'mailicon.gif',
					'?' => 'fileicon.gif' );
?>
