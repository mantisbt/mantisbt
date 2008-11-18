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
	# $Id: set_project.php,v 1.57.2.1 2007-10-13 22:34:30 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );

	# helper_ensure_post();

	$f_project_id	= gpc_get_string( 'project_id' );
	$f_make_default	= gpc_get_bool  ( 'make_default' );
	$f_ref			= gpc_get_string( 'ref', '' );
	
	$c_ref = string_prepare_header( $f_ref );

	$t_project = split( ';', $f_project_id );
	$t_top     = $t_project[0];
	$t_bottom  = $t_project[ count( $t_project ) - 1 ];

	if ( ALL_PROJECTS != $t_bottom ) {
		project_ensure_exists( $t_bottom );
	}

	# Set default project
	if ( $f_make_default ) {
		current_user_set_default_project( $t_top );
	}

	helper_set_current_project( $f_project_id );

	# redirect to 'same page' when switching projects.

	# for proxies that clear out HTTP_REFERER
	if ( !is_blank( $c_ref ) ) {
		$t_redirect_url = $c_ref;
	} else if ( !isset( $_SERVER['HTTP_REFERER'] ) || is_blank( $_SERVER['HTTP_REFERER'] ) ) {
		$t_redirect_url = config_get( 'default_home_page' );
	} else {
		$t_home_page = config_get( 'default_home_page' );

		# Check that referrer matches our address after squashing case (case insensitive compare)
		$t_path = config_get( 'path' );
		if ( strtolower( $t_path ) == strtolower( substr( $_SERVER['HTTP_REFERER'], 0, strlen( $t_path ) ) ) ) {
			$t_referrer_page = substr( $_SERVER['HTTP_REFERER'], strlen( $t_path ) );
			# if view_all_bug_page, pass on filter	
			if ( eregi( 'view_all_bug_page.php', $t_referrer_page ) ) {
				$t_source_filter_id = filter_db_get_project_current( $f_project_id );
				$t_redirect_url = 'view_all_set.php?type=4';

				if ( $t_source_filter_id !== null ) {
					$t_redirect_url = 'view_all_set.php?type=3&amp;source_query_id=' . $t_source_filter_id;
				}
			} else if ( eregi( '_page.php', $t_referrer_page ) ) {
				# get just the page component
				if ( strpos( $t_referrer_page, '?' ) !== FALSE ) {
					list( $t_path, $t_param ) = split( '\?', $t_referrer_page, 2 );
				} else {
					$t_path = $t_referrer_page;
					$t_param = '';
				} 
						
				switch ($t_path ) {
					case 'bug_view_page.php':
					case 'bug_view_advanced_page.php':
					case 'bug_update_page.php':
					case 'bug_update_advanced_page.php':
					case 'bug_change_status_page.php':
						$t_path = $t_home_page;
						break;
					default:					
						$t_path = $t_referrer_page;
						break;
				}
				$t_redirect_url = $t_path;
			} else {
				$t_redirect_url = $t_home_page;
			}
		} else {
			$t_redirect_url = $t_home_page;
		}
	}

	print_header_redirect( $t_redirect_url, true, true );
?>
<?php html_page_top1() ?>
<?php
	html_meta_redirect( $t_redirect_url );
?>
<?php html_page_top1() ?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
