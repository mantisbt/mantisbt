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
	# $Id: wiki.php,v 1.2.2.1 2007-10-13 22:34:49 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'wiki_api.php' );

	$f_id = gpc_get_int( 'id' );
	$f_type = gpc_get_string( 'type', 'issue' );
	
	if ( $f_type == 'project' ) {
		if ( $f_id !== 0 ) {
			project_ensure_exists( $f_id );
		}

		$t_url = wiki_get_url_for_project( $f_id );
	} else {
		bug_ensure_exists( $f_id );
		$t_url = wiki_get_url_for_issue( $f_id );
	}

	print_header_redirect( $t_url );
?>
