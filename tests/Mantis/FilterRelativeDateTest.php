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
 * Test cases for the relative date filter helpers.
 *
 * @package    Tests
 * @subpackage Filter
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

namespace Mantis\tests\Mantis;

use Mantis\Exceptions\ClientException;

require_once( __DIR__ . '/MantisCoreBase.php' );
require_api( 'filter_api.php' );
require_once( dirname( __DIR__, 2 ) . '/core/classes/FilterConverter.class.php' );

/**
 * Relative date filter test cases.
 *
 * Covers the helpers that make up the relative date feature: resolving a
 * descriptor to a date, the descriptor/form-field pair that has to stay each
 * other's inverse, the per-operator boundary table for custom date fields, and
 * the rule that rejects a date field mixing relative and fixed endpoints.
 *
 * Beyond those, the three places a descriptor crosses a boundary: the resolve
 * pass that turns descriptors into the date slots the query builder reads, the
 * serialization that has to carry them to storage and back, and the form input
 * capture for custom date fields.
 *
 * Month and year offsets get their own cases: they are the ones that can land on
 * a day the target month does not have, and the ones where an offset in the
 * anchor's own unit has to move the period rather than the boundary date.
 *
 * Two shapes recur throughout, both spelled out here rather than in each test:
 *
 * - A *descriptor* is one endpoint's relative expression as it is stored,
 *   array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ). A date field
 *   holds one per endpoint, and holding one at all is what puts that endpoint in
 *   relative mode - there is no separate flag.
 * - A custom date field's selection is the triple
 *   array( date control, start timestamp, end timestamp ), which is why
 *   the tests below read it by index.
 *
 * @package    Tests
 * @subpackage Filter
 */
class FilterRelativeDateTest extends MantisCoreBase {

	/**
	 * Saved superglobal, so a test that feeds GPC input cannot leak into the next.
	 * @var array
	 */
	private $get_backup;

	/**
	 * Test class setup - log in, as the REST payload cases build a converter for
	 * the current user.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::login();
	}

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		self::dbConnect();
		$this->get_backup = $_GET;
	}

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		$_GET = $this->get_backup;

		# Ensure the next test starts from the database's own set of fields.
		custom_field_clear_cache();
		parent::tearDown();
	}

	/**
	 * Every anchor resolves to midnight of the date it names, taken as of now.
	 *
	 * @return void
	 */
	public function testAnchorsResolveToTheirOwnBoundary() {
		$t_today = filter_relative_descriptor_to_date( array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ) );
		$this->assertEquals( date( 'Y-m-d' ), date( 'Y-m-d', $t_today ) );
		$this->assertEquals( '00:00:00', date( 'H:i:s', $t_today ), 'anchors resolve to midnight' );

		$t_som = filter_relative_descriptor_to_date( array( 'anchor' => 'start_of_month', 'offset' => 0, 'unit' => 'day' ) );
		$this->assertEquals( '1', date( 'j', $t_som ) );
		$this->assertEquals( date( 'Y-m' ), date( 'Y-m', $t_som ) );

		$t_eom = filter_relative_descriptor_to_date( array( 'anchor' => 'end_of_month', 'offset' => 0, 'unit' => 'day' ) );
		$this->assertEquals( date( 't', $t_eom ), date( 'j', $t_eom ), 'end_of_month is the last day of its month' );
		$this->assertEquals( date( 'Y-m' ), date( 'Y-m', $t_eom ) );

		$t_soy = filter_relative_descriptor_to_date( array( 'anchor' => 'start_of_year', 'offset' => 0, 'unit' => 'day' ) );
		$this->assertEquals( '01-01', date( 'm-d', $t_soy ) );
		$this->assertEquals( date( 'Y' ), date( 'Y', $t_soy ) );

		$t_eoy = filter_relative_descriptor_to_date( array( 'anchor' => 'end_of_year', 'offset' => 0, 'unit' => 'day' ) );
		$this->assertEquals( '12-31', date( 'm-d', $t_eoy ) );
		$this->assertEquals( date( 'Y' ), date( 'Y', $t_eoy ) );
	}

	/**
	 * The offset is applied to the anchor's own boundary, not to today. A day
	 * offset against a non-today anchor is the case that would expose the anchor
	 * being ignored.
	 *
	 * @return void
	 */
	public function testOffsetAppliesToTheAnchorNotToToday() {
		$t_ts = filter_relative_descriptor_to_date(
			array( 'anchor' => 'start_of_year', 'offset' => 7, 'unit' => 'day' ) );
		$this->assertEquals( '01-08', date( 'm-d', $t_ts ) );
		$this->assertEquals( date( 'Y' ), date( 'Y', $t_ts ) );

		$t_ts = filter_relative_descriptor_to_date(
			array( 'anchor' => 'start_of_month', 'offset' => -1, 'unit' => 'day' ) );
		$t_som = filter_relative_descriptor_to_date(
			array( 'anchor' => 'start_of_month', 'offset' => 0, 'unit' => 'day' ) );
		$this->assertEquals( 1, $this->daysBetween( $t_ts, $t_som ) );
		$this->assertLessThan( $t_som, $t_ts );

		$t_ts = filter_relative_descriptor_to_date(
			array( 'anchor' => 'end_of_year', 'offset' => 1, 'unit' => 'day' ) );
		$this->assertEquals( '01-01', date( 'm-d', $t_ts ) );
		$this->assertEquals( date( 'Y' ) + 1, (int)date( 'Y', $t_ts ) );
	}

	/**
	 * Day and week offsets move whole calendar days, in the direction the sign
	 * says, and a week is seven days.
	 *
	 * @return void
	 */
	public function testDayAndWeekOffsets() {
		$t_today = filter_relative_descriptor_to_date( array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ) );

		$t_back = filter_relative_descriptor_to_date( array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ) );
		$this->assertEquals( 7, $this->daysBetween( $t_back, $t_today ) );
		$this->assertLessThan( $t_today, $t_back );

		$t_week = filter_relative_descriptor_to_date( array( 'anchor' => 'today', 'offset' => -1, 'unit' => 'week' ) );
		$this->assertEquals( date( 'Y-m-d', $t_back ), date( 'Y-m-d', $t_week ), 'one week back is seven days back' );

		$t_fwd = filter_relative_descriptor_to_date( array( 'anchor' => 'today', 'offset' => 3, 'unit' => 'day' ) );
		$this->assertEquals( 3, $this->daysBetween( $t_today, $t_fwd ) );
		$this->assertGreaterThan( $t_today, $t_fwd );
	}

	/**
	 * Month and year steps land on a day the target month has, rather than
	 * overflowing into the month after it the way PHP's own steps do.
	 *
	 * @dataProvider providerShifts
	 * @param string  $p_from   Date to move from.
	 * @param integer $p_offset Signed offset.
	 * @param string  $p_unit   Offset unit.
	 * @param string  $p_expect Expected date.
	 * @return void
	 */
	public function testShiftClampsToTheTargetMonth( $p_from, $p_offset, $p_unit, $p_expect ) {
		$t_ts = filter_relative_date_shift( strtotime( $p_from . ' 00:00:00' ), $p_offset, $p_unit );
		$this->assertEquals( $p_expect, date( 'Y-m-d', $t_ts ) );
		$this->assertEquals( '00:00:00', date( 'H:i:s', $t_ts ) );
	}

	/**
	 * Dates whose day does not survive a naive month or year step, plus the day
	 * and week steps that can never need clamping.
	 *
	 * @return array
	 */
	public static function providerShifts() {
		return array(
			'month end back a month'      => array( '2026-03-31', -1, 'month', '2026-02-28' ),
			'month end on a month'        => array( '2026-01-31', 1, 'month', '2026-02-28' ),
			'month end forward a month'   => array( '2026-03-31', 1, 'month', '2026-04-30' ),
			'month end back three months' => array( '2026-05-31', -3, 'month', '2026-02-28' ),
			'leap day back a year'        => array( '2028-02-29', -1, 'year', '2027-02-28' ),
			'leap day forward a year'     => array( '2028-02-29', 1, 'year', '2029-02-28' ),
			'to a leap february'          => array( '2027-01-31', 13, 'month', '2028-02-29' ),
			'day step never clamps'       => array( '2026-03-31', -1, 'day', '2026-03-30' ),
			'week step never clamps'      => array( '2026-03-31', -1, 'week', '2026-03-24' ),
			'week step crosses a month'   => array( '2026-03-03', -1, 'week', '2026-02-24' ),
			'a day is not 86400 seconds'  => array( '2026-03-01', 1, 'day', '2026-03-02' ),
		);
	}

	/**
	 * An offset in the anchor's own unit moves the period and the boundary is then
	 * taken afresh, so a month-end anchor always lands on a month end.
	 *
	 * @return void
	 */
	public function testOffsetInTheAnchorsOwnUnitMovesThePeriod() {
		foreach( array( -13, -2, -1, 1, 2, 13 ) as $t_offset ) {
			$t_ts = filter_relative_descriptor_to_date(
				array( 'anchor' => 'end_of_month', 'offset' => $t_offset, 'unit' => 'month' ) );
			$this->assertEquals( $this->monthsFromNow( $t_offset ), date( 'Y-m', $t_ts ),
				"end_of_month $t_offset months is in the month $t_offset months away" );
			$this->assertEquals( date( 't', $t_ts ), date( 'j', $t_ts ),
				"end_of_month $t_offset months is still a month end" );

			$t_ts = filter_relative_descriptor_to_date(
				array( 'anchor' => 'start_of_month', 'offset' => $t_offset, 'unit' => 'month' ) );
			$this->assertEquals( $this->monthsFromNow( $t_offset ), date( 'Y-m', $t_ts ) );
			$this->assertEquals( '1', date( 'j', $t_ts ) );
		}

		foreach( array( -2, -1, 1, 2 ) as $t_offset ) {
			$t_ts = filter_relative_descriptor_to_date(
				array( 'anchor' => 'end_of_year', 'offset' => $t_offset, 'unit' => 'year' ) );
			$this->assertEquals( date( 'Y' ) + $t_offset, (int)date( 'Y', $t_ts ) );
			$this->assertEquals( '12-31', date( 'm-d', $t_ts ) );

			$t_ts = filter_relative_descriptor_to_date(
				array( 'anchor' => 'start_of_year', 'offset' => $t_offset, 'unit' => 'year' ) );
			$this->assertEquals( date( 'Y' ) + $t_offset, (int)date( 'Y', $t_ts ) );
			$this->assertEquals( '01-01', date( 'm-d', $t_ts ) );
		}
	}

	/**
	 * An offset in any other unit moves the boundary date itself.
	 *
	 * @return void
	 */
	public function testOffsetInAnotherUnitMovesTheDate() {
		$t_eom = filter_relative_descriptor_to_date(
			array( 'anchor' => 'end_of_month', 'offset' => 0, 'unit' => 'day' ) );
		$t_ts = filter_relative_descriptor_to_date(
			array( 'anchor' => 'end_of_month', 'offset' => -10, 'unit' => 'day' ) );
		$this->assertEquals( 10, $this->daysBetween( $t_ts, $t_eom ),
			'end_of_month - 10 days is ten days before this month ends' );

		# A month step off a month-end anchor still cannot overflow.
		$t_ts = filter_relative_descriptor_to_date(
			array( 'anchor' => 'end_of_month', 'offset' => -1, 'unit' => 'year' ) );
		$this->assertLessThanOrEqual( (int)date( 't', $t_ts ), (int)date( 'j', $t_ts ) );
		$this->assertEquals( date( 'Y' ) - 1, (int)date( 'Y', $t_ts ) );
	}

	/**
	 * A stored descriptor is validated when it is resolved, not only when it is
	 * read from a form. An unrecognised part must not reach strtotime(), which
	 * would return false and resolve the endpoint to 1970-01-01 - a bound that
	 * silently matches everything.
	 *
	 * @return void
	 */
	public function testStoredDescriptorIsValidatedAtResolveTime() {
		$t_today = filter_relative_descriptor_to_date(
			array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ) );

		$t_ts = filter_relative_descriptor_to_date(
			array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'fortnight' ) );
		$this->assertEquals( 7, $this->daysBetween( $t_ts, $t_today ),
			'an unknown unit falls back to days rather than failing to parse' );

		$t_ts = filter_relative_descriptor_to_date(
			array( 'anchor' => 'end_of_time', 'offset' => 0, 'unit' => 'day' ) );
		$this->assertEquals( date( 'Y-m-d', $t_today ), date( 'Y-m-d', $t_ts ),
			'an unknown anchor falls back to today' );

		foreach( array( PHP_INT_MAX, -PHP_INT_MAX, 999999999, -999999999 ) as $t_offset ) {
			$t_ts = filter_relative_descriptor_to_date(
				array( 'anchor' => 'today', 'offset' => $t_offset, 'unit' => 'year' ) );
			$this->assertEquals( date( 'Y', $t_today ) + ( $t_offset > 0 ? 1 : -1 ) * FILTER_RELATIVE_DATE_MAX_OFFSET,
				(int)date( 'Y', $t_ts ), 'an absurd offset is clamped rather than overflowing' );
		}

		foreach( array(
			array( 'anchor' => 'nope', 'offset' => 5, 'unit' => 'nope' ),
			array(),
			array( 'anchor' => 'end_of_month', 'unit' => 'month' ),
		) as $t_descriptor ) {
			$this->assertGreaterThan( strtotime( '2000-01-01' ),
				filter_relative_descriptor_to_date( $t_descriptor ),
				'no descriptor resolves to the epoch' );
		}
	}

	/**
	 * A descriptor decomposed into form fields and read back must come out
	 * unchanged - the two helpers are each other's inverse, and a permalink
	 * round-trips through exactly that pair.
	 *
	 * @dataProvider providerDescriptors
	 * @param array $p_descriptor Descriptor to round-trip.
	 * @return void
	 */
	public function testDescriptorRoundTripsThroughFormFields( array $p_descriptor ) {
		$t_prefix = 'start_relative';
		$_GET = array();
		foreach( filter_relative_descriptor_to_parts( $p_descriptor ) as $t_key => $t_value ) {
			$_GET[$t_prefix . '_' . $t_key] = (string)$t_value;
		}

		$this->assertEquals( $p_descriptor, filter_gpc_get_relative_descriptor( $t_prefix ) );
	}

	/**
	 * Descriptors covering every anchor, unit and sign.
	 *
	 * @return array
	 */
	public static function providerDescriptors() {
		$t_cases = array();
		foreach( array( 'today', 'start_of_month', 'end_of_month', 'start_of_year', 'end_of_year' ) as $t_anchor ) {
			foreach( array( 'day', 'week', 'month', 'year' ) as $t_unit ) {
				foreach( array( -7, -1, 0, 1, 30 ) as $t_offset ) {
					$t_cases[$t_anchor . ' ' . $t_offset . ' ' . $t_unit] = array(
						array( 'anchor' => $t_anchor, 'offset' => $t_offset, 'unit' => $t_unit ) );
				}
			}
		}
		return $t_cases;
	}

	/**
	 * With no anchor parameter submitted the endpoint is not in relative mode, so
	 * the caller's default is handed back - this is what gives an absent
	 * submission merge semantics rather than clearing the stored descriptor.
	 *
	 * @return void
	 */
	public function testAbsentAnchorReturnsTheDefault() {
		$_GET = array();
		$this->assertNull( filter_gpc_get_relative_descriptor( 'start_relative' ) );

		$t_stored = array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' );
		$this->assertEquals( $t_stored, filter_gpc_get_relative_descriptor( 'start_relative', $t_stored ) );
	}

	/**
	 * Values the form could not have produced are corrected rather than trusted.
	 *
	 * @return void
	 */
	public function testSubmittedValuesAreValidated() {
		$_GET = array(
			'start_relative_anchor' => 'end_of_time',
			'start_relative_sign' => '-',
			'start_relative_num' => '5',
			'start_relative_unit' => 'fortnight',
		);
		$this->assertEquals(
			array( 'anchor' => 'today', 'offset' => -5, 'unit' => 'day' ),
			filter_gpc_get_relative_descriptor( 'start_relative' ),
			'an unknown anchor or unit falls back to the default' );

		$_GET = array(
			'start_relative_anchor' => 'today',
			'start_relative_sign' => '-',
			'start_relative_num' => '999999999',
			'start_relative_unit' => 'y',
		);
		$t_descriptor = filter_gpc_get_relative_descriptor( 'start_relative' );
		$this->assertEquals( -FILTER_RELATIVE_DATE_MAX_OFFSET, $t_descriptor['offset'],
			'the offset magnitude is clamped to what the input allows' );
	}

	/**
	 * The per-operator boundary table for custom date fields.
	 *
	 * @return void
	 */
	public function testCustomFieldDateEndpoints() {
		$t_start = mktime( 0, 0, 0, 3, 10, 2026 );
		$t_end = mktime( 0, 0, 0, 3, 20, 2026 );
		$t_day = 86399;

		$this->assertEquals( array( 1, 1 ),
			filter_custom_field_date_endpoints( CUSTOM_FIELD_DATE_ANY, $t_start, $t_end ) );
		$this->assertEquals( array( 1, 1 ),
			filter_custom_field_date_endpoints( CUSTOM_FIELD_DATE_NONE, $t_start, $t_end ) );
		$this->assertEquals( array( $t_start, $t_end + $t_day - 1 ),
			filter_custom_field_date_endpoints( CUSTOM_FIELD_DATE_BETWEEN, $t_start, $t_end ) );
		$this->assertEquals( array( $t_start, $t_start + $t_day - 1 ),
			filter_custom_field_date_endpoints( CUSTOM_FIELD_DATE_ON, $t_start, $t_end ) );
		$this->assertEquals( array( 1, $t_start + $t_day - 1 ),
			filter_custom_field_date_endpoints( CUSTOM_FIELD_DATE_ONORBEFORE, $t_start, $t_end ) );
		$this->assertEquals( array( 1, $t_start ),
			filter_custom_field_date_endpoints( CUSTOM_FIELD_DATE_BEFORE, $t_start, $t_end ) );
		$this->assertEquals( array( $t_start, 2147483647 ),
			filter_custom_field_date_endpoints( CUSTOM_FIELD_DATE_ONORAFTER, $t_start, $t_end ) );
		$this->assertEquals( array( $t_start + $t_day - 1, 2147483647 ),
			filter_custom_field_date_endpoints( CUSTOM_FIELD_DATE_AFTER, $t_start, $t_end ) );
	}

	/**
	 * A built-in date field with both endpoints in the same mode is accepted.
	 *
	 * @return void
	 */
	public function testConsistentRelativeModeIsAccepted() {
		$t_descriptor = array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' );

		filter_ensure_consistent_relative_mode( array() );
		filter_ensure_consistent_relative_mode( array(
			FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE => $t_descriptor,
			FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE => $t_descriptor,
			FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE => $t_descriptor,
			FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE => $t_descriptor,
		) );

		# A single-sided custom field operator cannot be mixed, whatever it carries.
		filter_ensure_consistent_relative_mode( array(
			'custom_fields' => array( 7 => array( CUSTOM_FIELD_DATE_ON, 1, 1 ) ),
			FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE => array(
				7 => array( 'start' => $t_descriptor, 'end' => null ) ),
		) );

		$this->addToAssertionCount( 3 );
	}

	/**
	 * One relative endpoint paired with a fixed one is rejected.
	 *
	 * @dataProvider providerMixedFilters
	 * @param array $p_filter Filter that mixes modes within one date field.
	 * @return void
	 */
	public function testMixedRelativeModeIsRejected( array $p_filter ) {
		$this->expectException( ClientException::class );
		$this->expectExceptionCode( ERROR_FILTER_RELATIVE_DATE_MODE_MIXED );
		filter_ensure_consistent_relative_mode( $p_filter );
	}

	/**
	 * Filters mixing relative and fixed endpoints within a single date field.
	 *
	 * @return array
	 */
	public static function providerMixedFilters() {
		$t_descriptor = array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' );

		return array(
			'date submitted, start only' => array( array(
				FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE => $t_descriptor ) ),
			'date submitted, end only' => array( array(
				FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE => $t_descriptor ) ),
			'last updated, start only' => array( array(
				FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE => $t_descriptor ) ),
			'last updated, end only' => array( array(
				FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE => $t_descriptor ) ),
			'custom field between, start only' => array( array(
				'custom_fields' => array( 7 => array( CUSTOM_FIELD_DATE_BETWEEN, 1, 1 ) ),
				FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE => array(
					7 => array( 'start' => $t_descriptor, 'end' => null ) ) ) ),
			'custom field between, end only' => array( array(
				'custom_fields' => array( 7 => array( CUSTOM_FIELD_DATE_BETWEEN, 1, 1 ) ),
				FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE => array(
					7 => array( 'start' => null, 'end' => $t_descriptor ) ) ) ),
		);
	}

	/**
	 * Descriptors are resolved into the date parts the query builder reads, one
	 * endpoint at a time, and the filter handed in is left as it was.
	 *
	 * @return void
	 */
	public function testResolveWritesEveryBuiltInEndpoint() {
		$t_filter = array(
			# Relative start, over stale fixed date parts.
			FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE =>
				array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ),
			FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR => 1999,
			FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH => 1,
			FILTER_PROPERTY_DATE_SUBMITTED_START_DAY => 1,

			# Relative end, with no fixed date parts stored.
			FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE =>
				array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ),

			# Last updated, on offsets that cannot coincide with the two above, so
			# the four endpoints always resolve to four distinct dates.
			FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE =>
				array( 'anchor' => 'today', 'offset' => -30, 'unit' => 'day' ),
			FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE =>
				array( 'anchor' => 'today', 'offset' => 5, 'unit' => 'day' ),
		);
		$t_original = $t_filter;

		$t_resolved = filter_resolve_relative_dates( $t_filter );

		# Asserts relative overrides fixed: today - 7 days, not the stored 1999-01-01.
		$this->assertDateParts( date( 'Y-m-d', strtotime( '-7 days' ) ), $t_resolved,
			FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR,
			FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH,
			FILTER_PROPERTY_DATE_SUBMITTED_START_DAY );

		# Asserts the end is written into date parts the filter did not carry.
		$this->assertDateParts( date( 'Y-m-d' ), $t_resolved,
			FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR,
			FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH,
			FILTER_PROPERTY_DATE_SUBMITTED_END_DAY );

		# Asserts each descriptor lands in its own endpoint's date parts, rather
		# than the right dates reaching the wrong slots.
		$this->assertDateParts( date( 'Y-m-d', strtotime( '-30 days' ) ), $t_resolved,
			FILTER_PROPERTY_LAST_UPDATED_START_YEAR,
			FILTER_PROPERTY_LAST_UPDATED_START_MONTH,
			FILTER_PROPERTY_LAST_UPDATED_START_DAY );
		$this->assertDateParts( date( 'Y-m-d', strtotime( '+5 days' ) ), $t_resolved,
			FILTER_PROPERTY_LAST_UPDATED_END_YEAR,
			FILTER_PROPERTY_LAST_UPDATED_END_MONTH,
			FILTER_PROPERTY_LAST_UPDATED_END_DAY );

		# Asserts the filter handed in is unchanged - the pass resolves into a copy.
		$this->assertEquals( $t_original, $t_filter, 'the filter handed in is not modified' );
	}

	/**
	 * A filter carrying no descriptors comes back unchanged - every filter saved
	 * before this feature existed runs through the same pass.
	 *
	 * @return void
	 */
	public function testResolveLeavesAFilterWithoutDescriptorsUntouched() {
		$t_filter = array(
			FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED => true,
			FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR => 2024,
			FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH => 3,
			FILTER_PROPERTY_DATE_SUBMITTED_START_DAY => 10,
			FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR => 2024,
			FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH => 3,
			FILTER_PROPERTY_DATE_SUBMITTED_END_DAY => 20,
			'custom_fields' => array( 7 => array( CUSTOM_FIELD_DATE_BETWEEN,
				strtotime( '2024-03-10' ), strtotime( '2024-03-20' ) + 86399 - 1 ) ),
		);

		$this->assertEquals( $t_filter, filter_resolve_relative_dates( $t_filter ) );
	}

	/**
	 * A custom date field in relative mode has both of its stored timestamps
	 * recomputed, replacing the snapshot the slots were left holding.
	 *
	 * @return void
	 */
	public function testResolveRecomputesCustomFieldBetweenBounds() {
		$t_filter = array(
			# The window a previous run resolved, now a year out of date. The resolve
			# pass replaces both timestamps, so these values never reach a query.
			'custom_fields' => array( 7 => array( CUSTOM_FIELD_DATE_BETWEEN,
				strtotime( '2025-09-01' ), strtotime( '2025-09-08' ) + 86399 - 1 ) ),
			FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE => array(
				7 => array(
					'start' => array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ),
					'end' => array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ),
				),
			),
		);

		$t_resolved = filter_resolve_relative_dates( $t_filter );

		$this->assertEquals( strtotime( 'today -7 days' ), $t_resolved['custom_fields'][7][1],
			'the start bound is the start descriptor resolved as of now' );
		$this->assertEquals( strtotime( 'today' ) + 86399 - 1, $t_resolved['custom_fields'][7][2],
			'the end bound runs to the end of the end descriptor\'s day' );
	}

	/**
	 * Every operator other than "between" reads the start date alone, so a
	 * single-sided field resolves from its start descriptor and needs no end one.
	 *
	 * @return void
	 */
	public function testResolveUsesOnlyTheStartDateForSingleSidedOperators() {
		$t_filter = array(
			# As above, the window a previous run left in the slots.
			'custom_fields' => array( 7 => array( CUSTOM_FIELD_DATE_ONORAFTER,
				strtotime( '2025-09-01' ), 2147483647 ) ),
			FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE => array(
				7 => array(
					'start' => array( 'anchor' => 'start_of_month', 'offset' => 0, 'unit' => 'month' ),
					'end' => null,
				),
			),
		);

		$t_resolved = filter_resolve_relative_dates( $t_filter );

		$this->assertEquals( strtotime( date( 'Y-m-01' ) ), $t_resolved['custom_fields'][7][1],
			'the start bound is the start descriptor resolved as of now' );
		$this->assertEquals( 2147483647, $t_resolved['custom_fields'][7][2],
			'"on or after" leaves the end bound open, whatever the end descriptor is' );
	}

	/**
	 * A descriptor naming a field the filter does not select on is skipped: a
	 * filter can outlive the custom field it referenced, and the descriptor must
	 * not conjure a selection back into existence.
	 *
	 * @return void
	 */
	public function testResolveIgnoresDescriptorsWithNoMatchingCustomField() {
		$t_filter = array(
			# No field is selected, yet field 99 carries descriptors.
			'custom_fields' => array(),
			FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE => array(
				99 => array(
					'start' => array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ),
					'end' => array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ),
				),
			),
		);

		$this->assertEquals( array(), filter_resolve_relative_dates( $t_filter )['custom_fields'] );
	}

	/**
	 * Descriptors survive being written to storage and read back. They are nested
	 * arrays inside a json blob, so this is where a change to the serialization
	 * format would silently drop them.
	 *
	 * @return void
	 */
	public function testSerializeRoundTripsRelativeDescriptors() {
		$t_start = array( 'anchor' => 'start_of_year', 'offset' => -1, 'unit' => 'year' );
		$t_end = array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' );

		# One save and one load.
		$t_filter = filter_deserialize( filter_serialize( array(
			FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED => true,
			FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE => $t_start,
			FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE => $t_end,
			FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE => array(
				7 => array( 'start' => $t_start, 'end' => $t_end ) ),
		) ) );

		$this->assertEquals( $t_start, $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE] );
		$this->assertEquals( $t_end, $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE] );
		$this->assertEquals( array( 7 => array( 'start' => $t_start, 'end' => $t_end ) ),
			$t_filter[FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE] );
	}

	/**
	 * Every path that persists a filter goes through filter_serialize(), so the
	 * consistency rule has to be enforced there and not only at the form.
	 *
	 * @return void
	 */
	public function testSerializeRejectsAMixedFilter() {
		$this->expectException( ClientException::class );
		$this->expectExceptionCode( ERROR_FILTER_RELATIVE_DATE_MODE_MIXED );

		# Relative start, fixed end - one field's endpoints must share a mode.
		filter_serialize( array(
			FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED => true,
			FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE =>
				array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ),
		) );
	}

	/**
	 * The read-only filter view shows a relative endpoint as the expression it
	 * holds, so it can be told apart from a fixed one. A zero offset is the
	 * anchor alone, an offset from today is the offset alone, and a count of one
	 * takes the singular unit.
	 *
	 * @return void
	 */
	public function testDescriptorLabel() {
		$this->assertEquals( 'today', filter_relative_descriptor_label(
			array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ) ) );
		$this->assertEquals( '-7 days', filter_relative_descriptor_label(
			array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ) ) );
		$this->assertEquals( '+1 week', filter_relative_descriptor_label(
			array( 'anchor' => 'today', 'offset' => 1, 'unit' => 'week' ) ) );
		$this->assertEquals( 'start of month', filter_relative_descriptor_label(
			array( 'anchor' => 'start_of_month', 'offset' => 0, 'unit' => 'month' ) ) );
		$this->assertEquals( 'start of month -1 month', filter_relative_descriptor_label(
			array( 'anchor' => 'start_of_month', 'offset' => -1, 'unit' => 'month' ) ) );
		$this->assertEquals( 'end of year +2 weeks', filter_relative_descriptor_label(
			array( 'anchor' => 'end_of_year', 'offset' => 2, 'unit' => 'week' ) ) );

		# Each date is shown resolved, with its own expression as the tooltip; a
		# single icon before the field's dates marks it as relative.
		$this->assertEquals(
			'<span class="relative-date" title="-7 days">' . date( 'Y-m-d', strtotime( 'today -7 days' ) ) . '</span>',
			filter_relative_date_display(
				array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ), 'Y-m-d' ) );
		$this->assertEquals( '<i class="fa fa-repeat grey" ></i>', filter_relative_date_icon() );
	}

	/**
	 * A date custom field submitted in fixed mode yields no descriptor, and its
	 * timestamps come from the submitted date parts. A submitted timestamp input
	 * is stored verbatim instead - that is the form a permalink carries.
	 *
	 * @return void
	 */
	public function testCustomFieldDateReadsFixedInputs() {
		# What the filter form submits for field 7. No "_relative" input means
		# fixed mode. gpc_get() reads $_POST then $_GET, so either drives this.
		$_GET = array(
			'custom_field_7_control' => (string)CUSTOM_FIELD_DATE_BETWEEN,
			'custom_field_7_start_year' => '2026', 'custom_field_7_start_month' => '3',
			'custom_field_7_start_day' => '10',
			'custom_field_7_end_year' => '2026', 'custom_field_7_end_month' => '3',
			'custom_field_7_end_day' => '20',
		);

		list( $t_values, $t_relative ) = filter_gpc_get_custom_field_date( 7 );

		$this->assertNull( $t_relative, 'fixed mode stores no descriptor' );
		$this->assertEquals(
			array( CUSTOM_FIELD_DATE_BETWEEN, mktime( 0, 0, 0, 3, 10, 2026 ), mktime( 0, 0, 0, 3, 20, 2026 ) + 86399 - 1 ),
			$t_values,
			'"between" spans the start day to the end of the end day' );

		# A permalink carries timestamps instead, taken as given.
		$_GET['custom_field_7_start_timestamp'] = '12345';
		$_GET['custom_field_7_end_timestamp'] = '67890';
		list( $t_values, ) = filter_gpc_get_custom_field_date( 7 );
		$this->assertEquals( array( CUSTOM_FIELD_DATE_BETWEEN, 12345, 67890 ), $t_values );
	}

	/**
	 * In relative mode the descriptors are captured, and the timestamps stored
	 * beside them are a snapshot resolved as of now - so the filter is usable
	 * straight away as well as on later runs.
	 *
	 * @return void
	 */
	public function testCustomFieldDateReadsRelativeInputs() {
		# Same field submitted in relative mode. Four controls per endpoint.
		$_GET = array(
			'custom_field_7_control' => (string)CUSTOM_FIELD_DATE_BETWEEN,
			'custom_field_7_relative' => '1',
			'custom_field_7_start_relative_anchor' => 'today',
			'custom_field_7_start_relative_sign' => '-',
			'custom_field_7_start_relative_num' => '7',
			'custom_field_7_start_relative_unit' => 'd',
			'custom_field_7_end_relative_anchor' => 'today',
			'custom_field_7_end_relative_sign' => '+',
			'custom_field_7_end_relative_num' => '0',
			'custom_field_7_end_relative_unit' => 'd',
		);

		list( $t_values, $t_relative ) = filter_gpc_get_custom_field_date( 7 );

		$this->assertEquals( array(
			'start' => array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ),
			'end' => array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ),
		), $t_relative, 'the four controls per endpoint become one descriptor each' );

		# Asserts the same span as fixed mode, resolved from the descriptors.
		$this->assertEquals(
			array( CUSTOM_FIELD_DATE_BETWEEN, strtotime( 'today -7 days' ), strtotime( 'today' ) + 86399 - 1 ),
			$t_values );
	}

	/**
	 * An endpoint with nothing chosen for it falls back to the last seven days
	 * ending today, which is what the form renders: a start seven days back, an
	 * end on the day itself. The endpoint is told apart by its field-name prefix.
	 *
	 * @return void
	 */
	public function testEndpointDefaults() {
		$this->assertEquals( array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ),
			filter_relative_descriptor_default( FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE ) );
		$this->assertEquals( array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ),
			filter_relative_descriptor_default( FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE ) );

		# Custom date fields prefix the same two suffixes with the field id.
		$this->assertEquals( array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ),
			filter_relative_descriptor_default( 'custom_field_7_start_relative' ) );
		$this->assertEquals( array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ),
			filter_relative_descriptor_default( 'custom_field_7_end_relative' ) );

		# Only the endpoints that are missing one are filled in.
		$t_chosen = array( 'anchor' => 'start_of_month', 'offset' => -1, 'unit' => 'month' );
		$this->assertEquals(
			array(
				FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE => $t_chosen,
				FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE =>
					array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ),
			),
			filter_relative_descriptors_fill( array(
				FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE => $t_chosen,
				FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE => null ) ) );
	}

	/**
	 * A truncated link - one that carries one endpoint of a date field and not
	 * the other - is completed from the defaults rather than producing a field
	 * that mixes a relative endpoint with a fixed one, which every write path
	 * would then refuse to save.
	 *
	 * @return void
	 */
	public function testTruncatedEndpointPairIsCompleted() {
		$_GET = array(
			'custom_field_7_control' => (string)CUSTOM_FIELD_DATE_BETWEEN,
			'custom_field_7_relative' => '1',
			'custom_field_7_start_relative_anchor' => 'today',
			'custom_field_7_start_relative_sign' => '-',
			'custom_field_7_start_relative_num' => '7',
			'custom_field_7_start_relative_unit' => 'd',
			# the end endpoint's four inputs are missing
		);

		list( $t_values, $t_relative ) = filter_gpc_get_custom_field_date( 7 );

		$this->assertEquals( array(
			'start' => array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ),
			'end' => array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ),
		), $t_relative, '"between" needs both endpoints, so the missing one is defaulted' );
		$this->assertEquals(
			array( CUSTOM_FIELD_DATE_BETWEEN, strtotime( 'today -7 days' ), strtotime( 'today' ) + 86399 - 1 ),
			$t_values );

		$t_filter = array(
			'custom_fields' => array( 7 => $t_values ),
			FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE => array( 7 => $t_relative ),
		);
		filter_ensure_consistent_relative_mode( $t_filter );
		$this->addToAssertionCount( 1 );

		$t_filter[FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE][7]['end'] = null;
		$this->expectException( ClientException::class );
		filter_ensure_consistent_relative_mode( $t_filter );
	}

	/**
	 * Only the endpoints a date control actually uses are given a descriptor. A
	 * single-sided control leaves the end endpoint's inputs disabled, so their
	 * absence is by design and must not be filled in.
	 *
	 * @return void
	 */
	public function testOnlyTheEndpointsTheControlUsesAreFilled() {
		$_GET = array(
			'custom_field_7_control' => (string)CUSTOM_FIELD_DATE_ON,
			'custom_field_7_relative' => '1',
			'custom_field_7_start_relative_anchor' => 'today',
			'custom_field_7_start_relative_sign' => '-',
			'custom_field_7_start_relative_num' => '7',
			'custom_field_7_start_relative_unit' => 'd',
		);

		list( , $t_relative ) = filter_gpc_get_custom_field_date( 7 );
		$this->assertEquals( array(
			'start' => array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ),
			'end' => null,
		), $t_relative, '"on" derives both timestamps from the start endpoint' );
	}

	/**
	 * "Any" and "none" use neither endpoint, so both relative inputs are rendered
	 * disabled and the mode arrives with no descriptor at all. There is nothing
	 * relative to store: the field is read as fixed, which is also how it renders
	 * next time.
	 *
	 * @return void
	 */
	public function testRelativeModeWithoutEndpointsIsReadAsFixed() {
		foreach( array( CUSTOM_FIELD_DATE_ANY, CUSTOM_FIELD_DATE_NONE ) as $t_control ) {
			$_GET = array(
				'custom_field_7_control' => (string)$t_control,
				'custom_field_7_relative' => '1',
			);

			list( $t_values, $t_relative ) = filter_gpc_get_custom_field_date( 7 );

			$this->assertNull( $t_relative,
				'a control that uses no date stores no descriptor' );
			$this->assertEquals( array( $t_control, 1, 1 ), $t_values,
				'and the same timestamps fixed mode stores for it' );
		}
	}

	/**
	 * The REST payload renders a date endpoint as a plain date when it is fixed,
	 * and as the descriptor that produced it - carrying the resolved date - when
	 * it is relative. The two forms are exclusive, so a client cannot read a date
	 * and write back a filter that has silently stopped being relative.
	 *
	 * @return void
	 */
	public function testRestPayloadRendersDateEndpoints() {
		$t_fixed = $this->filterCriteriaToJson( array() );
		$this->assertEquals( array( 'from' => date( 'Y-m-01' ), 'to' => date( 'Y-m-d' ) ),
			$t_fixed['created_at'],
			'a fixed filter is rendered exactly as it was before descriptors existed' );

		$t_relative = $this->filterCriteriaToJson( array(
			FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE =>
				array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day' ),
			FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE =>
				array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ),
		) );
		$this->assertEquals( array(
			'from' => array( 'anchor' => 'today', 'offset' => -7, 'unit' => 'day',
				'date' => date( 'Y-m-d', strtotime( '-7 days' ) ) ),
			'to' => array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day',
				'date' => date( 'Y-m-d' ) ),
		), $t_relative['created_at'] );

		# The field that stayed fixed is untouched by the other one being relative.
		$this->assertEquals( $t_fixed['updated_at'], $t_relative['updated_at'] );
	}

	/**
	 * A descriptor is published as the one that was actually applied, so the
	 * date beside it cannot disagree with it, and the internal storage keys never
	 * reach the payload under any of their spellings.
	 *
	 * @return void
	 */
	public function testRestPayloadPublishesTheAppliedDescriptor() {
		$t_json = $this->filterCriteriaToJson( array(
			FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE =>
				array( 'anchor' => 'end_of_time', 'offset' => 999999, 'unit' => 'fortnight' ),
			FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE =>
				array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' ),
		) );

		$t_from = $t_json['created_at']['from'];
		$this->assertEquals(
			array( 'anchor' => 'today', 'offset' => FILTER_RELATIVE_DATE_MAX_OFFSET, 'unit' => 'day' ),
			array( 'anchor' => $t_from['anchor'], 'offset' => $t_from['offset'], 'unit' => $t_from['unit'] ),
			'an unrecognised part is published as the default that replaced it' );
		$this->assertEquals(
			date( 'Y-m-d', filter_relative_descriptor_to_date( $t_from ) ), $t_from['date'],
			'the date is what the published descriptor resolves to' );

		foreach( array_keys( $t_json ) as $t_key ) {
			$this->assertStringNotContainsString( 'relative', $t_key,
				'no internal descriptor property reaches the payload' );
		}
	}

	/**
	 * A date custom field keeps its stored triple in the payload, with each
	 * relative bound replaced by the descriptor that produced it. Which
	 * descriptor that is depends on the date control, not on the slot's position:
	 * "on or before" fills the end slot from the start date, and the open-ended
	 * controls fill one slot with a sentinel no descriptor produced.
	 *
	 * @return void
	 */
	public function testRestPayloadRendersCustomFieldDateBounds() {
		$t_start = array( 'anchor' => 'start_of_month', 'offset' => -1, 'unit' => 'month' );
		$t_end = array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day' );

		# Asserts the bound carries the timestamp the slot already held, so the
		# descriptor and the value beside it cannot disagree.
		$t_between = $this->customFieldDateBoundsToJson(
			array( CUSTOM_FIELD_DATE_BETWEEN, 111, 222 ), $t_start, $t_end );
		$this->assertEquals(
			array( CUSTOM_FIELD_DATE_BETWEEN,
				array( 'anchor' => 'start_of_month', 'offset' => -1, 'unit' => 'month', 'timestamp' => 111 ),
				array( 'anchor' => 'today', 'offset' => 0, 'unit' => 'day', 'timestamp' => 222 ) ),
			$t_between,
			'between reads one descriptor per slot' );

		# The end slot comes from the start date, so it must carry the start
		# descriptor, and the unused start slot keeps its sentinel.
		$t_on_or_before = $this->customFieldDateBoundsToJson(
			array( CUSTOM_FIELD_DATE_ONORBEFORE, 1, 222 ), $t_start, $t_end );
		$this->assertEquals( 1, $t_on_or_before[1],
			'a slot no date produced is left as the sentinel it holds' );
		$this->assertEquals( 'start_of_month', $t_on_or_before[2]['anchor'],
			'the end slot carries the start descriptor, not the end one' );

		# The open end has no descriptor behind it either.
		$t_on_or_after = $this->customFieldDateBoundsToJson(
			array( CUSTOM_FIELD_DATE_ONORAFTER, 111, 2147483647 ), $t_start, $t_end );
		$this->assertEquals( 'start_of_month', $t_on_or_after[1]['anchor'] );
		$this->assertEquals( 2147483647, $t_on_or_after[2] );

		# Asserts a field in relative mode with only one descriptor set leaves the
		# other slot as the plain timestamp rather than inventing a descriptor.
		$t_partial = $this->customFieldDateBoundsToJson(
			array( CUSTOM_FIELD_DATE_BETWEEN, 111, 222 ), $t_start, null );
		$this->assertEquals( 222, $t_partial[2] );
	}

	/**
	 * filter_custom_field_date_endpoint_sources() names, per date control, which date
	 * fed each stored timestamp. That claim is only useful while it agrees with
	 * filter_custom_field_date_endpoints(), which does the actual arithmetic, so
	 * it is checked against it rather than restated: a slot must move when the
	 * date it names moves, and stay put when the other one does.
	 *
	 * @return void
	 */
	public function testCustomFieldDateEndpointSourcesMatchTheArithmetic() {
		$t_first = strtotime( '2026-03-10' );
		$t_second = strtotime( '2026-04-20' );

		$t_controls = array( CUSTOM_FIELD_DATE_ANY, CUSTOM_FIELD_DATE_NONE, CUSTOM_FIELD_DATE_BETWEEN,
			CUSTOM_FIELD_DATE_ONORBEFORE, CUSTOM_FIELD_DATE_BEFORE, CUSTOM_FIELD_DATE_ON,
			CUSTOM_FIELD_DATE_AFTER, CUSTOM_FIELD_DATE_ONORAFTER );
		foreach( $t_controls as $t_control ) {
			$t_base = filter_custom_field_date_endpoints( $t_control, $t_first, $t_first );
			$t_moved = array(
				'start' => filter_custom_field_date_endpoints( $t_control, $t_second, $t_first ),
				'end' => filter_custom_field_date_endpoints( $t_control, $t_first, $t_second ) );

			$t_sources = filter_custom_field_date_endpoint_sources( $t_control );

			foreach( array( 0, 1 ) as $t_slot ) {
				foreach( array( 'start', 'end' ) as $t_date ) {
					$t_responds = $t_moved[$t_date][$t_slot] !== $t_base[$t_slot];
					$this->assertEquals( $t_sources[$t_slot] === $t_date, $t_responds,
						'control ' . $t_control . ' slot ' . $t_slot . ' vs the ' . $t_date . ' date' );
				}
			}
		}
	}

	/**
	 * Render a date custom field's stored triple the way the REST filters
	 * endpoint does. The conversion is private, so this reaches it directly.
	 *
	 * @param array      $p_values The stored triple.
	 * @param array|null $p_start  Start descriptor, or null if that end is fixed.
	 * @param array|null $p_end    End descriptor, or null if that end is fixed.
	 * @return array The triple in API format.
	 */
	private function customFieldDateBoundsToJson( array $p_values, $p_start, $p_end ) {
		$t_method = new \ReflectionMethod( '\FilterConverter', 'customFieldDateBoundsToJson' );
		return $t_method->invoke( new \FilterConverter( auth_get_current_user_id(), 'english' ),
			$p_values, array( 'start' => $p_start, 'end' => $p_end ) );
	}

	/**
	 * Build the API-format criteria for a filter, the way the REST filters
	 * endpoint does. The conversion is private, so this reaches it directly
	 * rather than standing up the user and project lookups filterToJson() needs.
	 *
	 * @param array $p_criteria Filter properties to set over the defaults.
	 * @return array Criteria in API format.
	 */
	private function filterCriteriaToJson( array $p_criteria ) {
		$t_filter = filter_ensure_valid_filter( array_merge( array(
			FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED => true,
			FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE => true,
		), $p_criteria ) );

		$t_method = new \ReflectionMethod( '\FilterConverter', 'filterCriteriaToJson' );
		return $t_method->invoke( new \FilterConverter( auth_get_current_user_id(), 'english' ), $t_filter, 1 );
	}

	/**
	 * Assert that a filter's year, month and day slots spell the given date. The
	 * year property names the assertion, so a failure says which endpoint it was.
	 *
	 * @param string  $p_expected Expected date, Y-m-d.
	 * @param array   $p_filter   Filter to read.
	 * @param string  $p_year     Year property.
	 * @param string  $p_month    Month property.
	 * @param string  $p_day      Day property.
	 * @return void
	 */
	private function assertDateParts( $p_expected, array $p_filter, $p_year, $p_month, $p_day ) {
		$this->assertEquals( $p_expected, sprintf( '%04d-%02d-%02d',
			$p_filter[$p_year], $p_filter[$p_month], $p_filter[$p_day] ), $p_year );
	}

	/**
	 * The year and month the given number of months from now, computed from the
	 * first of the month so the expectation itself cannot overflow.
	 *
	 * @param integer $p_offset Signed number of months.
	 * @return string Y-m
	 */
	private function monthsFromNow( $p_offset ) {
		$t_date = new \DateTime( date( 'Y-m-01' ) );
		$t_date->modify( sprintf( '%+d months', $p_offset ) );
		return $t_date->format( 'Y-m' );
	}

	/**
	 * Whole calendar days between two midnight timestamps, so assertions do not
	 * assume a day is 86400 seconds long.
	 *
	 * @param integer $p_from Earlier timestamp.
	 * @param integer $p_to   Later timestamp.
	 * @return integer
	 */
	private function daysBetween( $p_from, $p_to ) {
		$t_from = new \DateTime( date( 'Y-m-d', $p_from ) );
		$t_to = new \DateTime( date( 'Y-m-d', $p_to ) );
		return (int)$t_from->diff( $t_to )->days;
	}
}
