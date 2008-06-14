<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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
	# $Id: account_prefs_update.php,v 1.36.16.1 2007-10-13 22:32:08 giallu Exp $
	# --------------------------------------------------------

	# Updates prefs then redirect to account_prefs_page.php3

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'user_pref_api.php' );

	# helper_ensure_post();

	auth_ensure_user_authenticated();

	$f_user_id					= gpc_get_int( 'user_id' );
	$f_redirect_url				= gpc_get_string( 'redirect_url' );

	# If the user is trying to modify an account other than their own
	#  they must have high enough permissions to do so
	# @@@ should we really be sharing this file between the manage section
	#  and the account section.  The account section should always be operating
	#  on the current user, so passing in a user ID here is a little odd.
	if ( auth_get_current_user_id() != $f_user_id ) {
		access_ensure_global_level( config_get( 'manage_user_threshold' ) );
	}

	user_ensure_unprotected( $f_user_id );

	$t_prefs = user_pref_get( $f_user_id );

	$t_prefs->redirect_delay	= gpc_get_int( 'redirect_delay' );
	$t_prefs->refresh_delay		= gpc_get_int( 'refresh_delay' );
	$t_prefs->default_project	= gpc_get_int( 'default_project' );

	$t_lang = gpc_get_string( 'language' );
	if ( lang_language_exists( $t_lang ) ) {
		$t_prefs->language = $t_lang;
	}

	$t_prefs->advanced_report	= gpc_get_bool( 'advanced_report' );
	$t_prefs->advanced_view		= gpc_get_bool( 'advanced_view' );
	$t_prefs->advanced_update	= gpc_get_bool( 'advanced_update' );
	$t_prefs->email_on_new		= gpc_get_bool( 'email_on_new' );
	$t_prefs->email_on_assigned	= gpc_get_bool( 'email_on_assigned' );
	$t_prefs->email_on_feedback	= gpc_get_bool( 'email_on_feedback' );
	$t_prefs->email_on_resolved	= gpc_get_bool( 'email_on_resolved' );
	$t_prefs->email_on_closed	= gpc_get_bool( 'email_on_closed' );
	$t_prefs->email_on_reopened	= gpc_get_bool( 'email_on_reopened' );
	$t_prefs->email_on_bugnote	= gpc_get_bool( 'email_on_bugnote' );
	$t_prefs->email_on_status	= gpc_get_bool( 'email_on_status' );
	$t_prefs->email_on_priority	= gpc_get_bool( 'email_on_priority' );
	$t_prefs->email_on_new_min_severity			= gpc_get_int( 'email_on_new_min_severity' );
	$t_prefs->email_on_assigned_min_severity	= gpc_get_int( 'email_on_assigned_min_severity' );
	$t_prefs->email_on_feedback_min_severity	= gpc_get_int( 'email_on_feedback_min_severity' );
	$t_prefs->email_on_resolved_min_severity	= gpc_get_int( 'email_on_resolved_min_severity' );
	$t_prefs->email_on_closed_min_severity		= gpc_get_int( 'email_on_closed_min_severity' );
	$t_prefs->email_on_reopened_min_severity	= gpc_get_int( 'email_on_reopened_min_severity' );
	$t_prefs->email_on_bugnote_min_severity		= gpc_get_int( 'email_on_bugnote_min_severity' );
	$t_prefs->email_on_status_min_severity		= gpc_get_int( 'email_on_status_min_severity' );
	$t_prefs->email_on_priority_min_severity	= gpc_get_int( 'email_on_priority_min_severity' );

	$t_prefs->bugnote_order = gpc_get_string( 'bugnote_order' );
	$t_prefs->email_bugnote_limit = gpc_get_int( 'email_bugnote_limit' );

	# prevent users from changing other user's accounts
	if ( $f_user_id != auth_get_current_user_id() ) {
		access_ensure_project_level( ADMINISTRATOR );
	}

	# make sure the delay isn't too low
	if (( config_get( 'min_refresh_delay' ) > $t_prefs->refresh_delay )&&
		( $t_prefs->refresh_delay != 0 )) {
		$t_prefs->refresh_delay = config_get( 'min_refresh_delay' );
	}

	user_pref_set( $f_user_id, $t_prefs );

	html_page_top1();
	html_meta_redirect( $f_redirect_url );
	html_page_top2();
	echo '<br /><div align="center">';

	echo lang_get( 'operation_successful' );

	echo '<br />';
	print_bracket_link( $f_redirect_url, lang_get( 'proceed' ) );
	echo '<br /></div>';
	html_page_bottom1( __FILE__ );
?>
