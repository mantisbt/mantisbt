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
 * Custom Fields Checks
 * @package MantisBT
 * @copyright Copyright (C) 2021  MantisBT Team -
 *            mantisbt-dev@lists.sourceforge.net
 * @link https://mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */

if( !defined( 'CHECK_CUSTOMFIELDS_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );

check_print_section_header_row( 'Custom Fields' );

$t_checks = new CustomFieldsChecks();
$t_checks->register( new CheckDateDefaultWithBrackets );
$t_checks->register( new CheckTextareaMaxLength );
$t_checks->execute();

/**
 * Custom Fields checks controller class.
 *
 * Register individual checks with register() then call execute().
 */
class CustomFieldsChecks {

	/**
	 * @var array Custom Field definitions.
	 */
	private array $definitions;

	/**
	 * @var CustomFieldCheck[] Registered checks.
	 */
	private array $checks = [];


	public function __construct() {
		foreach( custom_field_get_ids() as $t_id ) {
			$this->definitions[$t_id] = custom_field_get_definition( $t_id );
		}
	}

	public function register( CustomFieldCheck $check ) {
		$this->checks[] = $check;
	}

	public function execute() {
		foreach( $this->definitions as $t_cfdef ) {
			foreach( $this->checks as $t_check ) {
				$t_check->execute( $t_cfdef );
			}
		}

		foreach( $this->checks as $t_check ) {
			$t_check->printResults();
		}
	}
}

/**
 * Abstract base class for Custom Field definition checks.
 *
 * Child classes must:
 * - define the test() method
 * - override $msg_* properties and related get methods as appropriate
 *
 * @todo child class should be allowed to select warning or error checks.
 *       currently, we only call check_print_test_warn_row().
 */
abstract class CustomFieldCheck {

	/**
	 * Message to print if the check passes.
	 */
	protected string $msg_pass;

	/**
	 * Failed check description.
	 */
	protected string $msg_fail = '';

	/**
	 * Failed check additional info.
	 */
	protected string $msg_info = '';

	/**
	 * Link to edit Custom Field (use sprintf to insert CF id).
	 */
	protected string $msg_edit_cf_link;

	/**
	 * @var array CFname => (id, additional_fields...)
	 */
	protected array $results = [];

	public function __construct() {
		$t_cf_edit_page = helper_mantis_url( 'manage_custom_field_edit_page.php' );
		$this->msg_edit_cf_link = '<a href="' . $t_cf_edit_page	.'?field_id=%d">Edit the Custom Field</a>';
	}

	/**
	 * Test to determine whether the check passes or not.
	 *
	 * @param array       $p_cfdef
	 * @param string|null $p_result Additional info about the test, for the message
	 *                              display.
	 *
	 * @return bool True if pass.
	 */
	abstract public function test( array $p_cfdef, ?string &$p_result ): bool;

	/**
	 * Executes the check and store the result.
	 *
	 * @param array $p_cfdef Custom Field definition
	 * @return void
	 */
	public function execute( array $p_cfdef ) {
		if( !$this->test( $p_cfdef, $t_result ) ) {
			$this->results[$p_cfdef['name']] = array( $p_cfdef['id'], $t_result );
		}
	}

	/**
	 * Prints the check result
	 * @return void
	 */
	public function printResults() {
		if( $this->results ) {
			ksort( $this->results );
			foreach( array_keys( $this->results ) as $t_name ) {
				check_print_test_warn_row(
					$this->getFailMessage( $t_name ),
					false,
					$this->getInfoMessage( $t_name ) . $this->getEditLink( $t_name )
				);
			}
		} else {
			check_print_test_warn_row( $this->getPassMessage(), true );
		}
	}

	public function getPassMessage(): string {
		return $this->msg_pass;
	}

	public function getFailMessage(string $p_name): string {
		return $this->msg_fail;
	}

	public function getInfoMessage(string $p_name): string {
		return $this->msg_info;
	}

	public function getEditLink(string $p_name): string {
		return sprintf( $this->msg_edit_cf_link, $this->results[$p_name][0] );
	}

}

/**
 * Checks for usage of curly brackets in Date Custom Fields default value.
 */
class CheckDateDefaultWithBrackets extends CustomFieldCheck
{
	protected string $msg_pass = 'Deprecated usage of curly brackets in Date Custom Fields default value';
	protected string $msg_fail = "Date Custom Field '%s' specifies its Default Value with deprecated curly brackets format.";
	protected string $msg_info = "Use the same format, but without the '{}', i.e. '%s'. ";

	public function test( array $p_cfdef, ?string &$p_result ): bool {
		/**
		 * @var int        $v_type
		 * @var string|int $v_default_value
		 */
		extract( $p_cfdef, EXTR_PREFIX_ALL, 'v');

		if( $v_type == CUSTOM_FIELD_TYPE_DATE
			&& preg_match( '/^{(.*)}$/', $v_default_value, $t_matches )
		) {
			$p_result = $t_matches[1];
			return false;
		}
		return true;
	}

	public function getFailMessage(string $p_name): string {
		return sprintf( $this->msg_fail, $p_name );
	}

	public function getInfoMessage(string $p_name): string {
		return sprintf( $this->msg_info, $this->results[$p_name][1] );
	}

}

/**
 * Checks if Textarea Custom Fields maximum length is bigger than $g_max_textarea_length.
 */
class CheckTextareaMaxLength extends CustomFieldCheck
{
	protected string $msg_pass = 'Maximum length of Textarea Custom Fields is smaller than $g_max_textarea_length';

	private int $max_textarea_length;

	public function __construct() {
		parent::__construct();
		$this->max_textarea_length = config_get_global( 'max_textarea_length' );
		$this->msg_fail = 'Maximum length of Textarea Custom Field "%s" is bigger than $g_max_textarea_length'
			. " ($this->max_textarea_length)";
	}

	public function test( array $p_cfdef, ?string &$p_result ): bool {
		/**
		 * @var string     $v_name
		 * @var int        $v_type
		 * @var int        $v_length_max
		 */
		extract( $p_cfdef, EXTR_PREFIX_ALL, 'v');

		if( $v_type == CUSTOM_FIELD_TYPE_TEXTAREA
			&& $v_length_max > $this->max_textarea_length
		) {
			$p_result = '';
			return false;
		}
		return true;
	}

	public function getFailMessage(string $p_name): string {
		return sprintf( $this->msg_fail, $p_name );
	}

}
