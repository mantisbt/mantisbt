<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2002 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.

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
# $Id$
# --------------------------------------------------------

define( 'PLUGINS_DISABLED', true );
require_once( 'core.php' );

# helper_ensure_post();

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

$f_basename = gpc_get_string( 'name' );
$f_priority = gpc_get_int( 'priority' );
$f_protected = gpc_get_int( 'protected', 0 );

$t_plugin_table	= db_get_table( 'mantis_plugin_table' );
$t_query = "UPDATE $t_plugin_table SET priority=" . db_param() . ', protected=' . db_param() .
	' WHERE basename=' . db_param();

db_query_bound( $t_query, array( $f_priority, $f_protected, $f_basename ) );

print_successful_redirect( 'manage_plugin_page.php' );
