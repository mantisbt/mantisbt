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
 * Test cases for MantisEnum Class
 *
 * @package    Tests
 * @subpackage UnitTests
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Includes
 */
require_once 'MantisCoreBase.php';
require_once 'MantisEnum.class.php';

/**
 * Test cases for MantisEnum class.
 * @package    Tests
 * @subpackage Enum
 */
class MantisEnumTest extends MantisCoreBase {

	const ACCESS_LEVELS_ENUM = '10:viewer,25:reporter,40:updater,55:developer,70:manager,90:administrator';
	const ACCESS_LEVELS_ENUM_EXTRA = '10:viewer,25:reporter,40:updater,55:developer,70:manager,90:administrator,100:missing';
	const ACCESS_LEVELS_LOCALIZED_ENUM = '10:viewer_x,25:reporter_x,40:updater_x,55:developer_x,70:manager_x,90:administrator_x,95:extra_x';
	const EMPTY_ENUM = '';
	const DUPLICATE_VALUES_ENUM = '10:viewer1,10:viewer2';
	const DUPLICATE_LABELS_ENUM = '10:viewer,20:viewer';
	const SINGLE_VALUE_ENUM = '10:viewer';
	const NAME_WITH_SPACES_ENUM = '10:first label,20:second label';
	const NON_TRIMMED_ENUM = '10 : viewer, 20 : reporter';

	/**
	 * Tests getLabel() method.
	 * @return void
	 */
	public function testGetLabel() {
		$this->assertEquals( 'viewer', MantisEnum::getLabel( MantisEnumTest::ACCESS_LEVELS_ENUM, 10 ) );
		$this->assertEquals( 'reporter', MantisEnum::getLabel( MantisEnumTest::ACCESS_LEVELS_ENUM, 25 ) );
		$this->assertEquals( 'updater', MantisEnum::getLabel( MantisEnumTest::ACCESS_LEVELS_ENUM, 40 ) );
		$this->assertEquals( 'developer', MantisEnum::getLabel( MantisEnumTest::ACCESS_LEVELS_ENUM, 55 ) );
		$this->assertEquals( 'manager', MantisEnum::getLabel( MantisEnumTest::ACCESS_LEVELS_ENUM, 70 ) );
		$this->assertEquals( 'administrator', MantisEnum::getLabel( MantisEnumTest::ACCESS_LEVELS_ENUM, 90 ) );
		$this->assertEquals( '@100@', MantisEnum::getLabel( MantisEnumTest::ACCESS_LEVELS_ENUM, 100 ) );
		$this->assertEquals( '@-1@', MantisEnum::getLabel( MantisEnumTest::ACCESS_LEVELS_ENUM, -1 ) );
		$this->assertEquals( '@10@', MantisEnum::getLabel( MantisEnumTest::EMPTY_ENUM, 10 ) );
	}

	/**
	 * Tests getLocalizedLabel() method.
	 * @return void
	 */
	public function testGetLocalizedLabel() {
		# Test existing case
		$this->assertEquals( 'viewer_x', MantisEnum::getLocalizedLabel( MantisEnumTest::ACCESS_LEVELS_ENUM, MantisEnumTest::ACCESS_LEVELS_LOCALIZED_ENUM, 10 ) );

		# Test unknown case
		$this->assertEquals( '@5@', MantisEnum::getLocalizedLabel( MantisEnumTest::ACCESS_LEVELS_ENUM, MantisEnumTest::ACCESS_LEVELS_LOCALIZED_ENUM, 5 ) );

		# Test the case where the value is in the localized enumeration but not the standard one.
		# In this case it should be treated as unknown.
		$this->assertEquals( '@95@', MantisEnum::getLocalizedLabel( MantisEnumTest::ACCESS_LEVELS_ENUM, MantisEnumTest::ACCESS_LEVELS_LOCALIZED_ENUM, 95 ) );

		# Test the case where the value is in the standard enumeration but not in the localized one.
		# In this case we should fall back to the standard enumeration (as we do with language strings)
		# as the value is a known good value - just that it has not yet been localized.
		$this->assertEquals( 'missing', MantisEnum::getLocalizedLabel( MantisEnumTest::ACCESS_LEVELS_ENUM_EXTRA, MantisEnumTest::ACCESS_LEVELS_LOCALIZED_ENUM, 100 ) );

	}

	/**
	 * Tests getValues() method.
	 * @return void
	 */
	public function testGetValues() {
		$this->assertEquals( array( 10, 25, 40, 55, 70,90 ), MantisEnum::getValues( MantisEnumTest::ACCESS_LEVELS_ENUM, 10 ) );
		$this->assertEquals( array(), MantisEnum::getValues( MantisEnumTest::EMPTY_ENUM, 10 ) );
	}

	/**
	 * Tests getAssocArrayIndexedByValues() method.
	 * @return void
	 */
	public function testGetAssocArrayIndexedByValues() {
		$this->assertEquals( array(), MantisEnum::getAssocArrayIndexedByValues( MantisEnumTest::EMPTY_ENUM ) );
		$this->assertEquals( array( 10 => 'viewer' ), MantisEnum::getAssocArrayIndexedByValues( MantisEnumTest::SINGLE_VALUE_ENUM ) );
		$this->assertEquals( array( 10 => 'viewer1' ), MantisEnum::getAssocArrayIndexedByValues( MantisEnumTest::DUPLICATE_VALUES_ENUM ) );
		$this->assertEquals( array( 10 => 'viewer', 20 => 'viewer' ), MantisEnum::getAssocArrayIndexedByValues( MantisEnumTest::DUPLICATE_LABELS_ENUM ) );
		$this->assertEquals( array( 10 => 'first label', 20 => 'second label' ), MantisEnum::getAssocArrayIndexedByValues( MantisEnumTest::NAME_WITH_SPACES_ENUM ) );
	}

	/**
	 * Tests getAssocArrayIndexedByLabels() method.
	 * @return void
	 */
	public function testGetAssocArrayIndexedByLabels() {
		$this->assertEquals( array(), MantisEnum::getAssocArrayIndexedByLabels( MantisEnumTest::EMPTY_ENUM ) );
		$this->assertEquals( array( 'viewer' => 10 ), MantisEnum::getAssocArrayIndexedByLabels( MantisEnumTest::SINGLE_VALUE_ENUM ) );
		$this->assertEquals( array( 'viewer1' => 10 ), MantisEnum::getAssocArrayIndexedByLabels( MantisEnumTest::DUPLICATE_VALUES_ENUM ) );
		$this->assertEquals( array( 'viewer' => 10, 'viewer' => 20 ), MantisEnum::getAssocArrayIndexedByLabels( MantisEnumTest::DUPLICATE_LABELS_ENUM ) );
		$this->assertEquals( array( 'first label' => 10, 'second label' => 20 ), MantisEnum::getAssocArrayIndexedByLabels( MantisEnumTest::NAME_WITH_SPACES_ENUM ) );
	}

	/**
	 * Tests getValue() method.
	 * @return void
	 */
	public function testGetValue() {
		$this->assertEquals( false, MantisEnum::getValue( MantisEnumTest::EMPTY_ENUM, 'viewer' ) );
		$this->assertEquals( 10, MantisEnum::getValue( MantisEnumTest::SINGLE_VALUE_ENUM, 'viewer' ) );
		$this->assertEquals( 10, MantisEnum::getValue( MantisEnumTest::DUPLICATE_VALUES_ENUM, 'viewer1' ) );
		$this->assertEquals( 20, MantisEnum::getValue( MantisEnumTest::NAME_WITH_SPACES_ENUM, 'second label' ) );

		# This is not inconsistent with duplicate values behaviour, however, it is considered correct
		# since it simplifies the code and it is not a real scenario.
		$this->assertEquals( 20, MantisEnum::getValue( MantisEnumTest::DUPLICATE_LABELS_ENUM, 'viewer' ) );
	}

	/**
	 * Tests hasValue() method.
	 * @return void
	 */
	public function testHasValue() {
		$this->assertEquals( true, MantisEnum::hasValue( MantisEnumTest::ACCESS_LEVELS_ENUM, 10 ) );
		$this->assertEquals( false, MantisEnum::hasValue( MantisEnumTest::ACCESS_LEVELS_ENUM, 5 ) );
		$this->assertEquals( false, MantisEnum::hasValue( MantisEnumTest::EMPTY_ENUM, 10 ) );
	}

	/**
	 * Tests enumerations that contain duplicate values.
	 * @return void
	 */
	public function testDuplicateValuesEnum() {
		$this->assertEquals( 'viewer1', MantisEnum::getLabel( MantisEnumTest::DUPLICATE_VALUES_ENUM, 10 ) );
		$this->assertEquals( '@100@', MantisEnum::getLabel( MantisEnumTest::DUPLICATE_VALUES_ENUM, 100 ) );
	}

	/**
	 * Tests enumerations that contain duplicate labels.
	 * @return void
	 */
	public function testDuplicateLabelsValuesEnum() {
		$this->assertEquals( 'viewer', MantisEnum::getLabel( MantisEnumTest::DUPLICATE_LABELS_ENUM, 10 ) );
		$this->assertEquals( 'viewer', MantisEnum::getLabel( MantisEnumTest::DUPLICATE_LABELS_ENUM, 20 ) );
		$this->assertEquals( '@100@', MantisEnum::getLabel( MantisEnumTest::DUPLICATE_LABELS_ENUM, 100 ) );
	}

	/**
	 * Tests enumerations with a single tuple.
	 * @return void
	 */
	public function testSingleValueEnum() {
		$this->assertEquals( 'viewer', MantisEnum::getLabel( MantisEnumTest::SINGLE_VALUE_ENUM, 10 ) );
		$this->assertEquals( '@100@', MantisEnum::getLabel( MantisEnumTest::SINGLE_VALUE_ENUM, 100 ) );
	}

	/**
	 * Tests enumerations with labels that contain spaces.
	 * @return void
	 */
	public function testNameWithSpacesEnum() {
		$this->assertEquals( 'first label', MantisEnum::getLabel( MantisEnumTest::NAME_WITH_SPACES_ENUM, 10 ) );
		$this->assertEquals( 'second label', MantisEnum::getLabel( MantisEnumTest::NAME_WITH_SPACES_ENUM, 20 ) );
		$this->assertEquals( '@100@', MantisEnum::getLabel( MantisEnumTest::NAME_WITH_SPACES_ENUM, 100 ) );
	}

	/**
	 * Tests enumerations that contain duplicate labels.
	 * @return void
	 */
	public function testNonTrimmedEnum() {
		$this->assertEquals( 'viewer', MantisEnum::getLabel( MantisEnumTest::NON_TRIMMED_ENUM, 10 ) );
		$this->assertEquals( 'reporter', MantisEnum::getLabel( MantisEnumTest::NON_TRIMMED_ENUM, 20 ) );
		$this->assertEquals( '@100@', MantisEnum::getLabel( MantisEnumTest::NON_TRIMMED_ENUM, 100 ) );
	}
}
