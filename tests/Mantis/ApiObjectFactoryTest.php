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
 * MantisBT Tests
 *
 * @package    Tests
 * @subpackage UnitTests
 * @copyright  Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       https://mantisbt.org
 */

namespace Mantis\tests\Mantis;

use Mantis\Exceptions\LegacyApiFaultException;
use Mantis\tests\core\MantisTestCase;

# Includes
require_once dirname( __DIR__ ) . '/TestConfig.php';

# MantisBT Core API
require_mantis_core();
require_once 'api/soap/mc_api.php';

/**
 * Tests for the API object factory.
 */
class ApiObjectFactoryTest extends MantisTestCase {
	/**
	 * Whether the factory was configured for SOAP before the test.
	 *
	 * @var bool
	 */
	private $soap;

	/**
	 * Configure the API object factory for REST faults.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->soap = \ApiObjectFactory::$soap;
		\ApiObjectFactory::$soap = false;
	}

	/**
	 * Restore the API object factory mode.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		\ApiObjectFactory::$soap = $this->soap;

		parent::tearDown();
	}

	/**
	 * A REST fault must be converted to an exception that the Slim error
	 * handler can turn into the corresponding HTTP response.
	 *
	 * Regression test for issue #26455.
	 */
	public function testThrowIfRestFault(): void {
		$t_message = 'Not allowed to change Issue status';
		$t_fault = \ApiObjectFactory::faultForbidden( $t_message );

		$this->expectException( LegacyApiFaultException::class );
		$this->expectExceptionCode( HTTP_STATUS_FORBIDDEN );
		$this->expectExceptionMessage( $t_message );

		\ApiObjectFactory::throwIfFault( $t_fault );
	}
}
