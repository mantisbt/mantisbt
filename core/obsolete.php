<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: obsolete.php,v 1.10 2003-01-23 06:24:55 robertjf Exp $
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
	config_obsolete( 'view_bug_inc', 'bug_view_inc' );
	config_obsolete( 'ldap_organisation', 'ldap_organization' );
	config_obsolete( 'ldapauth_type', '' );
	config_obsolete( 'summary_product_colon_category', 'summary_category_include_project' );

	config_obsolete( 'allow_href_tags', 'html_make_links' );
	config_obsolete( 'allow_html_tags', 'html_valid_tags' );
	config_obsolete( 'html_tags', 'html_valid_tags' );
?>
