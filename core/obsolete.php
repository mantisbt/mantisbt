<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: obsolete.php,v 1.16 2003-02-21 00:32:38 vboctor Exp $
	# --------------------------------------------------------
	
	###########################################################################
	# Check that obsolete configs are not used.
	# THIS FILE ASSUMES THAT THE CONFIGURATION IS INCLUDED AS WELL AS THE
	# config_api.php.
	###########################################################################

	# Check for obsolete variables

	# ==== Changes after 0.17.5 ====

	config_obsolete( 'new_color', 'status_colors' );
	config_obsolete( 'feedback_color', 'status_colors' );
	config_obsolete( 'acknowledged_color', 'status_colors' );
	config_obsolete( 'confirmed_color', 'status_colors' );
	config_obsolete( 'assigned_color', 'status_colors' );
	config_obsolete( 'resolved_color', 'status_colors' );
	config_obsolete( 'closed_color', 'status_colors' );

	config_obsolete( 'notify_developers_on_new', 'notify_flags' );
	config_obsolete( 'notify_on_new_threshold', 'notify_flags' );
	config_obsolete( 'notify_admin_on_new', 'notify_flags' );
	config_obsolete( 'view_bug_inc', '' );
	config_obsolete( 'ldap_organisation', 'ldap_organization' );
	config_obsolete( 'ldapauth_type', '' );
	config_obsolete( 'summary_product_colon_category', 'summary_category_include_project' );

	config_obsolete( 'allow_href_tags', 'html_make_links' );
	config_obsolete( 'allow_html_tags', 'html_valid_tags' );
	config_obsolete( 'html_tags', 'html_valid_tags' );
	config_obsolete( 'show_user_email', 'show_user_email_threshold' );
	
	config_obsolete( 'manage_custom_fields', 'manage_custom_fields_threshold' );
	config_obsolete( 'allow_bug_delete_access_level', 'delete_bug_threshold' );
	config_obsolete( 'bug_move_access_level', 'move_bug_threshold' );
	
	config_obsolete( 'php', '' );
	config_obsolete( 'use_experimental_custom_fields', '' );
	config_obsolete( 'mail_send_crlf', '' );

	config_obsolete( 'bugnote_include_file', '' );
	config_obsolete( 'bugnote_view_include_file', '' );
	config_obsolete( 'bugnote_add_include_file', '' );
	config_obsolete( 'history_include_file', '' );
	config_obsolete( 'print_bugnote_include_file', '' );
	config_obsolete( 'view_all_include_file', '' );
	config_obsolete( 'bug_view_inc', '' );
	config_obsolete( 'bug_file_upload_inc', '' );
?>
