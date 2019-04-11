<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Check that obsolete configs are not used.
 * THIS FILE ASSUMES THAT THE CONFIGURATION IS INCLUDED AS WELL AS THE
 * config_api.php.
 *
 * @package CoreAPI
 * @subpackage ObsoleteAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

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

# removed in 0.19.1
config_obsolete( 'port', 'hostname' );

# changes in 0.19.3
config_obsolete( 'relationship_graph_fontpath', 'system_font_folder' );

# changes in 1.1.0rc1
config_obsolete( 'show_notices', 'display_errors' );
config_obsolete( 'show_warnings', 'display_errors' );

# changes in 1.1.0rc2
config_obsolete( 'wait_time', 'default_redirect_delay' );
config_obsolete( 'default_bug_category', '' );

# changes in 1.2.0a1
config_obsolete( 'enable_relationship', '' );
config_obsolete( 'ldap_port', 'ldap_server' );

# changes in 1.2.0rc1
config_obsolete( 'jpgraph_path', '' );
config_obsolete( 'use_jpgraph', '' );
config_obsolete( 'jpgraph_antialias', '' );

# changes in 1.2.0rc2
config_obsolete( 'reminder_recipents_monitor_bug', 'reminder_recipients_monitor_bug' );
config_obsolete( 'graph_window_width', '' );
config_obsolete( 'graph_bar_aspect', '' );
config_obsolete( 'graph_summary_graphs_per_row', '' );
config_obsolete( 'show_report', '' );
config_obsolete( 'show_view', '' );
config_obsolete( 'show_update', '' );
config_obsolete( 'default_advanced_report', '' );
config_obsolete( 'default_advanced_view', '' );
config_obsolete( 'default_advanced_update', '' );
config_obsolete( 'default_graph_type', '' );
config_obsolete( 'graph_font', '' );
config_obsolete( 'graph_colors', '' );

# changes in 1.2.8
config_obsolete( 'show_attachment_indicator' );
config_obsolete( 'default_avatar', '' );

# changes in 1.2.13
config_obsolete( 'manage_cookie', 'manage_users_cookie' );

# changes in 1.3.0dev
config_obsolete( 'bugnote_allow_user_edit_delete', '' );
config_obsolete( 'password_confirm_hash_magic_string', 'crypto_master_salt' );
config_obsolete( 'rss_key_seed', 'crypto_master_salt' );
config_obsolete( 'cvs_web' );
config_obsolete( 'source_control_notes_view_status' );
config_obsolete( 'source_control_account' );
config_obsolete( 'source_control_set_status_to' );
config_obsolete( 'source_control_set_resolution_to' );
config_obsolete( 'source_control_regexp' );
config_obsolete( 'source_control_fixed_regexp' );
config_obsolete( 'allow_close_immediately' );
config_obsolete( 'show_extended_project_browser' );
config_obsolete( 'show_queries_threshold', 'show_log_threshold' );
config_obsolete( 'show_queries_list' );
config_obsolete( 'administrator_email', 'webmaster_email' );
config_obsolete( 'session_key' );
config_obsolete( 'dhtml_filters', 'use_dynamic_filters' );
config_obsolete( 'use_iis' );
config_obsolete( 'page_title', 'top_include_page' );
config_obsolete( 'limit_email_domain', 'limit_email_domains' );
config_obsolete( 'file_upload_ftp_server' );
config_obsolete( 'file_upload_ftp_user' );
config_obsolete( 'file_upload_ftp_pass' );
config_obsolete( 'mantistouch_url' );
config_obsolete( 'custom_strings_file' );
config_obsolete( 'mc_readonly_access_level_threshold', 'webservice_readonly_access_level_threshold' );
config_obsolete( 'mc_readwrite_access_level_threshold', 'webservice_readwrite_access_level_threshold' );
config_obsolete( 'mc_admin_access_level_threshold', 'webservice_admin_access_level_threshold' );
config_obsolete( 'mc_specify_reporter_on_add_access_level_threshold', 'webservice_specify_reporter_on_add_access_level_threshold' );
config_obsolete( 'mc_priority_enum_default_when_not_found', 'webservice_priority_enum_default_when_not_found' );
config_obsolete( 'mc_severity_enum_default_when_not_found', 'webservice_severity_enum_default_when_not_found' );
config_obsolete( 'mc_status_enum_default_when_not_found', 'webservice_status_enum_default_when_not_found' );
config_obsolete( 'mc_resolution_enum_default_when_not_found', 'webservice_resolution_enum_default_when_not_found' );
config_obsolete( 'mc_projection_enum_default_when_not_found', 'webservice_projection_enum_default_when_not_found' );
config_obsolete( 'mc_eta_enum_default_when_not_found', 'webservice_eta_enum_default_when_not_found' );
config_obsolete( 'mc_error_when_version_not_found', 'webservice_error_when_version_not_found' );
config_obsolete( 'mc_version_when_not_found', 'webservice_version_when_not_found' );
env_obsolete( 'MANTIS_CONFIG', 'MANTIS_CONFIG_FOLDER' );
config_obsolete( 'colour_project' );
config_obsolete( 'colour_global' );
config_obsolete( 'content_expire' );
config_obsolete( 'use_javascript' );
config_obsolete( 'recently_visited', 'recently_visited_count' );
config_obsolete( 'email_set_category' );
config_obsolete( 'show_footer_menu' );
config_obsolete( 'status_legend_position' );
config_obsolete( 'status_percentage_legend' );
config_obsolete( 'font_per_captcha' );
config_obsolete( 'csv_add_bom' );
config_obsolete( 'hr_size' );
config_obsolete( 'hr_width' );
config_obsolete( 'db_schema' );

# changes in 1.3.4
config_obsolete( 'mail_priority' );

# changes in 2.0.0dev
config_obsolete( 'icon_path' );
config_obsolete( 'bug_print_page_fields' );
config_obsolete( 'calendar_js_date_format', 'datetime_picker_format' );
config_obsolete( 'calendar_date_format', 'normal_date_format' );

# changes in 2.8.0
config_obsolete( 'inline_file_exts' );

# changes in 2.9.0
config_obsolete( 'meta_include_file' );

# changes in 2.11.0
config_obsolete( 'differentiate_duplicates' );

# changes in 2.12.0
config_obsolete( 'session_handler' );

# changes in 2.17.0
config_obsolete( 'default_manage_user_prefix' );

# changes in 2.21.0
config_obsolete( 'my_view_boxes_fixed_position' );
