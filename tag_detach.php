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
 * Detach a tag
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses tag_api.php
 */

require_once( 'core.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'tag_api.php' );

form_security_validate( 'tag_detach' );

$f_tag_id = gpc_get_int( 'tag_id' );
$f_bug_id = gpc_get_int( 'bug_id' );

tag_bug_detach( $f_tag_id, $f_bug_id );

event_signal( 'EVENT_TAG_DETACHED', array( $f_bug_id, array( $f_tag_id ) ) );

form_security_purge( 'tag_detach' );

print_successful_redirect_to_bug( $f_bug_id );
