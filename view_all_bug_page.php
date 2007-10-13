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
	# $Revision: 1.62.2.1 $
	# $Author: giallu $
	# $Date: 2007-10-13 22:34:48 $
	#
	# $Id: view_all_bug_page.php,v 1.62.2.1 2007-10-13 22:34:48 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'filter_api.php' );
	require_once( $t_core_path.'last_visited_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_page_number		= gpc_get_int( 'page_number', 1 );

	$t_per_page = null;
	$t_bug_count = null;
	$t_page_count = null;

	$rows = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, null, null, null, true );
	if ( $rows === false ) {
		print_header_redirect( 'view_all_set.php?type=0' );
	}

	$t_bugslist = Array();
	$t_row_count = sizeof( $rows );
	for($i=0; $i < $t_row_count; $i++) {
		array_push($t_bugslist, $rows[$i]["id"] );
	}

	gpc_set_cookie( config_get( 'bug_list_cookie' ), implode( ',', $t_bugslist ) );

	compress_enable();

	html_page_top1( lang_get( 'view_bugs_link' ) );

	if ( current_user_get_pref( 'refresh_delay' ) > 0 ) {
		html_meta_redirect( 'view_all_bug_page.php?page_number='.$f_page_number, current_user_get_pref( 'refresh_delay' )*60 );
	}

	html_page_top2();

	print_recently_visited();

	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'view_all_inc.php' );

	html_page_bottom1( __FILE__ );
?>
