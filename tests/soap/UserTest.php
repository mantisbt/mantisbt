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
 * Mantis Webservice Tests
 *
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'SoapBase.php';

/**
 * Test fixture for non-login user methods
 *
 * @requires extension soap
 * @group SOAP
 */
class UserTest extends SoapBase {

	/**
	 * Tests getting a user preference
	 * @return void
	 */
	public function testGetPreference() {
		$t_default_order = $this->client->mc_config_get_string( $this->userName, $this->password, 'default_bugnote_order' );
		$t_bugnote_order = $this->client->mc_user_pref_get_pref( $this->userName, $this->password, 0, 'bugnote_order' );

		$this->assertEquals( $t_default_order, $t_bugnote_order );

	}
}
