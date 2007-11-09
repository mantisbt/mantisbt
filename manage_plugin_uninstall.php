<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.

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

require_once( 'core.php' );

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

$f_basename = gpc_get_string( 'name' );
$t_plugin_info = plugin_get_info( $f_basename );

helper_ensure_confirmed( sprintf( lang_get( 'plugin_uninstall_message' ), $t_plugin_info['name'] ), lang_get( 'plugin_uninstall' ) );

plugin_uninstall( $f_basename );

print_successful_redirect( 'manage_plugin_page.php' );
