<?php
# Mantis - a php based bugtracking system

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

/**
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( 'core.php' );

form_security_validate( 'bug_revision_drop' );

$f_revision_id = gpc_get_int( 'id' );
$t_revision = bug_revision_get( $f_revision_id );

access_ensure_bug_level( config_get( 'bug_revision_drop_threshold' ), $t_revision['bug_id'] );
helper_ensure_confirmed( lang_get( 'confirm_revision_drop' ), lang_get( 'revision_drop' ) );

bug_revision_drop( $f_revision_id );
form_security_purge( 'bug_revision_drop' );

print_successful_redirect_to_bug( $t_revision['bug_id'] );

