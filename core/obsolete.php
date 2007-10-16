<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: obsolete.php,v 1.32.2.2 2007-10-16 22:15:41 giallu Exp $
	# --------------------------------------------------------

	### Obsolete API ###

	###########################################################################
	# Check that obsolete configs are not used.
	# THIS FILE ASSUMES THAT THE CONFIGURATION IS INCLUDED AS WELL AS THE
	# config_api.php.
	###########################################################################

	# Check for obsolete variables

	# ==== Changes after 0.18.2 ====

	config_obsolete( 'use_phpMailer', '' );
	config_obsolete( 'phpMailer_path', '' );
	config_obsolete( 'use_x_priority', '' );

	# ==== Changes after 0.17.5 ====

	config_obsolete( 'new_color', 'status_colors' );
	config_obsolete( 'feedback_color', 'status_colors' );
	config_obsolete( 'acknowledged_color', 'status_colors' );
	config_obsolete( 'confirmed_color', 'status_colors' );
	config_obsolete( 'assigned_color', 'status_colors' );
	config_obsolete( 'resolved_color', 'status_colors' );
	config_obsolete( 'closed_color', 'status_colors' );

	config_obsolete( 'primary_table_tags', '' );
	config_obsolete( 'background_color', '' );
	config_obsolete( 'required_color', '' );
	config_obsolete( 'table_border_color', '' );
	config_obsolete( 'category_title_color', '' );
	config_obsolete( 'primary_color1', '' );
	config_obsolete( 'primary_color2', '' );
	config_obsolete( 'form_title_color', '' );
	config_obsolete( 'spacer_color', '' );
	config_obsolete( 'menu_color', '' );
	config_obsolete( 'fonts', '' );
	config_obsolete( 'font_small', '' );
	config_obsolete( 'font_normal', '' );
	config_obsolete( 'font_large', '' );
	config_obsolete( 'font_color', '' );

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

	config_obsolete( 'show_source', '' );

	config_obsolete( 'summary_pad', '' );

	config_obsolete( 'show_project_in_title', '' );

# removed in 0.19
	config_obsolete( 'hide_closed_default', 'hide_status_default' );

	config_obsolete( 'close_bug_threshold', 'set_status_threshold' );

	config_obsolete( 'status_pulldown_enum_mask_string', '' );
	config_obsolete( 'to_email', '' );
	config_obsolete( 'use_bcc', '' );

#removed in 0.19.1
	config_obsolete( 'port', 'hostname');

# changes in 0.19.3
	config_obsolete( 'relationship_graph_fontpath', 'system_font_folder' );

# changes in 1.1.0rc1
	config_obsolete( 'show_notices', 'display_errors' );
	config_obsolete( 'show_warnings', 'display_errors' );

# changes in 1.1.0rc2
	config_obsolete( 'wait_time', 'default_redirect_delay' );
?>
