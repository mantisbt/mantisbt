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
 * A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @copyright Copyright 2005  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Bypass default Mantis headers
$g_bypass_headers = true;

require_once( __DIR__ . '/../../vendor/autoload.php' );
require_once( __DIR__ . '/../../core.php' );
require_once( __DIR__ . '/../soap/mc_core.php' );
require_once( __DIR__ . '/restcore/ApiEnabledMiddleware.php' );
require_once( __DIR__ . '/restcore/AuthMiddleware.php' );
require_once( __DIR__ . '/restcore/VersionMiddleware.php' );

# Hint to re-used mantisconnect code that it is being executed from REST rather than SOAP.
# For example, this will disable logic like encoding dates with XSD meta-data.
ApiObjectFactory::$soap = false;

$app = new \Slim\App();

# Add middleware - executed in reverse order of appearing here.
$app->add( new AuthMiddleware() );
$app->add( new ApiEnabledMiddleware() );
$app->add( new VersionMiddleware() );

event_signal( 'EVENT_REST_API_ROUTES', array( array( 'app' => $app ) ) );

require_once( __DIR__ . '/restcore/issues_rest.php' );

$app->run();

