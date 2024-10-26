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
 * Supporting functions for the Check configuration
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

$g_show_all = false;
$g_show_errors = false;

$g_failed_test = false;
$g_passed_test_with_warnings = false;

$g_errors_temporarily_suppressed = false;
$g_errors_raised = array();

/**
 * Initialise error handler for checks
 * @return void
 */
function check_init_error_handler() {
	set_error_handler( 'check_error_handler' );
	error_reporting( E_ALL );
}

/**
 * Implement Error handler for check framework.
 *
 * @param integer $p_type    Error type.
 * @param string  $p_error   Error number.
 * @param string  $p_file    File error occurred in.
 * @param integer $p_line    Line number.
 *
 * @return bool True if error was handled, false to have it processed by PHP.
 */
function check_error_handler( $p_type, $p_error, $p_file, $p_line ) {
	global $g_errors_raised;

	# Do not handle PHP errors that have already been caught by MantisBT. These
	# are likely triggered by the admin checks script itself, so we let PHP
	# process them, otherwise the check will fail silently.
	if( $p_type == E_USER_ERROR && $p_error == ERROR_PHP ) {
		return false;
	}
	$g_errors_raised[] = array(
		'type' => $p_type,
		'error' => $p_error,
		'file' => $p_file,
		'line' => $p_line
	);
	return true;
}

/**
 * Check whether any unhandled errors exist
 * @return boolean|integer false if there are no unhandled errors, or the lowest
 *                  unhandled {@see http://php.net/errorfunc.constants Error Type}
 */
function check_unhandled_errors_exist() {
	global $g_errors_raised;
	if( count( $g_errors_raised ) > 0 ) {
		$t_type = E_ALL;
		foreach( $g_errors_raised as $t_error ) {
			$t_type = min( $t_type, $t_error['type'] );
		}
		return $t_type;
	}
	return false;
}

/**
 * Print out errors raised to html
 * @return void
 */
function check_print_error_rows() {
	global $g_show_errors, $g_errors_temporarily_suppressed, $g_errors_raised;
	if( !$g_show_errors || $g_errors_temporarily_suppressed ) {
		$g_errors_raised = array();
		return;
	}
	foreach( $g_errors_raised as $t_error ) {
		# build an appropriate error string
		switch( $t_error['type'] ) {
			case E_WARNING:
				$t_error_type = 'SYSTEM WARNING';
				$t_error_description = htmlentities( $t_error['error'] );
				break;
			case E_NOTICE:
				$t_error_type = 'SYSTEM NOTICE';
				$t_error_description = htmlentities( $t_error['error'] );
				break;
			case E_DEPRECATED:
				$t_error_type = 'DEPRECATED';
				$t_error_description = htmlentities( $t_error['error'] );
				break;
			case E_USER_ERROR:
				$t_error_type = 'APPLICATION ERROR #' . $t_error['error'];
				$t_error_description = htmlentities( error_string( $t_error['error'] ) );
				break;
			case E_USER_WARNING:
				$t_error_type = 'APPLICATION WARNING #' . $t_error['error'];
				$t_error_description = htmlentities( error_string( $t_error['error'] ) );
				break;
			case E_USER_NOTICE:
				# used for debugging
				$t_error_type = 'DEBUG';
				$t_error_description = htmlentities( $t_error['error'] );
				break;
			default:
				# shouldn't happen, display the error just in case
				$t_error_type = 'UNHANDLED ERROR TYPE ' . $t_error['type'];
				$t_error_description = htmlentities( $t_error['error'] );
		}
		echo "\t<tr>\n\t\t<td colspan=\"2\" class=\"alert alert-danger\">";
		echo '<strong>' . $t_error_type . ':</strong> ' . $t_error_description . '<br />';
		echo '<em>Raised in file ' . htmlentities( $t_error['file'] ) . ' on line ' . htmlentities( $t_error['line'] ) . '</em>';
		echo "</td>\n\t</tr>\n";
	}
	$g_errors_raised = array();
}

/**
 * Print section header
 *
 * @param string $p_heading Heading.
 * @return void
 */
function check_print_section_header_row( $p_heading ) {
?>
	<tr>
		<td colspan="2" class="thead2"><strong><?php echo $p_heading ?></strong></td>
	</tr>
<?php
}

/**
 * Print Check result - information only
 *
 * @param string $p_description Description.
 * @param string $p_info        Information.
 * @return void
 */
function check_print_info_row( $p_description, $p_info = null ) {
	global $g_show_all;
	if( !$g_show_all ) {
		return;
	}
	echo "\t" . '<tr>' . "\n\t\t";
	echo '<td class="description">' . $p_description . '</td>' . "\n";
	echo "\t\t" . '<td class="info">' . $p_info . '</td>' . "\n";
	echo "\t" . '</tr>' . "\n";
}

/**
 * Print Check Test Result
 * @param integer $p_result One of BAD|GOOD|WARN.
 * @return void
 */
function check_print_test_result( $p_result ) {
	global $g_failed_test, $g_passed_test_with_warnings;
	switch( $p_result ) {
		case BAD:
			echo "\t\t" . '<td class="alert alert-danger">FAIL</td>' . "\n";
			$g_failed_test = true;
			break;
		case GOOD:
			echo "\t\t" . '<td class="alert alert-success">PASS</td>' . "\n";
			break;
		case WARN:
			echo "\t\t" . '<td class="alert alert-warning">WARN</td>' . "\n";
			$g_passed_test_with_warnings = true;
			break;
	}
}

/**
 * Print Check Test Row.
 *
 * @param string $p_description Check's description.
 * @param bool   $p_pass        True if test passed.
 * @param null   $p_info        Optional additional information to print below the description.
 *                              If a string is given, the message is always displayed;
 *                              Providing array with true/false keys allows differentiated
 *                              messages depending on the Check's result, and if a key is
 *                              missing then the message is not printed.
 * @param bool   $p_warning     True if it's a Warning check.
 *
 * @return bool
 */
function check_print_test_row( $p_description, $p_pass, $p_info = null, $p_warning = false ) {
	global $g_show_all;
	$t_unhandled = check_unhandled_errors_exist();
	if( !$g_show_all && $p_pass && !$t_unhandled ) {
		return $p_pass;
	}

	echo "\t<tr>\n\t\t<td>$p_description";
	if( $p_info !== null ) {
		if( is_array( $p_info ) && isset( $p_info[$p_pass] ) ) {
			echo '<br /><em>' . $p_info[$p_pass] . '</em>';
		} else if( !is_array( $p_info ) ) {
			echo '<br /><em>' . $p_info . '</em>';
		}
	}
	echo "</td>\n";

	if( $p_pass && !$t_unhandled ) {
		$t_result = GOOD;
	} elseif( $p_warning && !$t_unhandled || $t_unhandled == E_DEPRECATED ) {
		$t_result = WARN;
	} else {
		$t_result = BAD;
	}
	check_print_test_result( $t_result );
	echo "\t</tr>\n";

	if( $t_unhandled ) {
		check_print_error_rows();
	}
	return $p_pass;
}

/**
 * Print Check Test Warning Row.
 *
 * @param string       $p_description Check's description.
 * @param bool         $p_pass        True if test passed.
 * @param string|array $p_info        Optional additional information to print below the description.
 *                                    If a string is given, the message is always displayed;
 *                                    Providing array with true/false keys allows differentiated
 *                                    messages depending on the Check's result, and if a key is
 *                                    missing then the message is not printed.
 *
 * @return bool
 */
function check_print_test_warn_row( $p_description, $p_pass, $p_info = null ) {
	return check_print_test_row( $p_description, $p_pass, $p_info, true );
}

/**
 * Verifies that the given collation is UTF-8
 * @param string $p_collation
 * @return boolean True if UTF-8
 */
function check_is_collation_utf8( $p_collation ) {
	return substr( $p_collation, 0, 4 ) === 'utf8';
}

/**
 * Formats a number with thousand separators and an optional unit
 * @param float  $p_number Number to print
 * @param string $p_unit   Printed after number
 * @return string
 */
function check_format_number( $p_number, $p_unit = 'bytes' ) {
	return number_format( (float)$p_number ) . ' ' . $p_unit;
}

/**
 * End-of-life version checks.
 *
 * @see https://endoflife.date/
 */
class EndOfLifeCheck
{
	const URL = 'https://endoflife.date/';
	const URL_API = self::URL . 'api/';

	/**
	 * Product names constants, to pass to the constructor.
	 *
	 * @see https://endoflife.date/api/all.json Full list of supported products.
	 */
	const PRODUCT_MARIADB = 'mariadb';
	const PRODUCT_MYSQL = 'mysql';
	const PRODUCT_SQLSERVER = 'mssqlserver';
	const PRODUCT_ORACLE = 'oracle-database';
	const PRODUCT_POSTGRESQL = 'postgresql';
	const PRODUCT_PHP = 'php';

	/**
	 * @var string Product name to query endoflife.date API.
	 */
	protected string $product;

	/**
	 * @var string Product Version.
	 */
	protected string $version;

	/**
	 * @var stdClass Release information returned by endoflife.date API.
	 */
	protected stdClass $info;

	/**
	 * Constructor.
	 *
	 * @param string $p_product Product to check (use one of the PRODUCT_* constants).
	 * @param string $p_version Product Version to check.
	 *
	 * @throws Exception If release information cannot be retrieved from endoflife.date.
	 */
	public function __construct( string $p_product, string $p_version ) {
		$this->product = strtolower( $p_product );
		$this->version = $p_version;

		$this->queryEndOfLife();
	}

	/**
	 * Prepares the version string for the endoflife.date API.
	 *
	 * Some products expect a X.Y version number, others just the major version.
	 *
	 * @return string Version number
	 */
	protected function prepareVersion(): string {
		preg_match( '/^((\w+)(?:\.\w+)?)/', $this->version, $t_matches );
		$t_major = $t_matches[2];
		$t_minor = $t_matches[1];

		switch( $this->product ) {
			case self::PRODUCT_ORACLE:
				# After version 12.2, there is no minor version
				return $t_major > 12 ? $t_major : $t_minor;

			case self::PRODUCT_POSTGRESQL:
				# Since version 10, there is no minor version
				return $t_major >= 10 ? $t_major : $t_minor;

			case self::PRODUCT_MARIADB:
			case self::PRODUCT_SQLSERVER:
			case self::PRODUCT_MYSQL:
			case self::PRODUCT_PHP:
			default:
				return $t_minor;
		}
	}

	/**
	 * Retrieve Release information from endoflife.date.
	 *
	 * @return void
	 * @throws Exception If release information cannot be retrieved.
	 */
	protected function queryEndOfLife() {
		$t_version = $this->prepareVersion();

		$t_options = array(
			'base_uri' => self::URL_API,
		);
		$t_client = new Client( $t_options );

		try {
			$t_response = $t_client->get( $this->product . '/' . $t_version . '.json' );
		}
		catch( GuzzleException $e ) {
			throw new Exception( "$this->product version $t_version not found.", 0, $e );
		}

		$this->info = json_decode( $t_response->getBody() );
	}

	/**
	 * URL to endoflife.date page for the product.
	 *
	 * @return string
	 */
	public function getUrl(): string {
		return self::URL . $this->product;
	}

	/**
	 * Get Release information.
	 *
	 * @return stdClass Release information
	 */
	public function getInfo(): stdClass {
		return $this->info;
	}

	/**
	 * Check whether the Version is end-of life.
	 *
	 * @param string $p_message Optional. If provided, the function will provide an
	 *                          informational message about the Release's EOL status.
	 *
	 * @return bool True if end-of-life, False if not.
	 */
	public function isEOL( string &$p_message = '' ): bool {
		if( $this->info->eol === false ) {
			$p_message = '';
			return false;
		}

		$p_message = "Version " . htmlspecialchars( $this->prepareVersion() );
		if( $this->info->eol === true ) {
			$p_message .= " has reached end-of-life.";
			$t_eol = true;
		} else {
			$t_today = new DateTimeImmutable();
			$t_eol_date = DateTimeImmutable::createFromFormat( 'Y-m-d', $this->info->eol );
			$t_eol = $t_today > $t_eol_date;

			$p_message = "Support for $p_message " . ( $t_eol ? 'ended' : 'ends' ) . " on {$this->info->eol}.";
		}

		if( $t_eol ) {
			$p_message .= 'You should upgrade to a '
				. '<a href="' . $this->getUrl() . '">supported release</a>, '
				. 'as bugs and security flaws discovered in this version will not be fixed.';
		}

		return $t_eol;
	}

	/**
	 * Check whether the Version is a long-term support (LTS) release.
	 *
	 * @return bool True if LTS, False if not.
	 */
	public function isLTS(): bool {
		return (bool)$this->info->lts;
	}

	/**
	 * Check whether the Version is the latest available release.
	 *
	 * Some Products do not provide latest release information
	 *
	 * @param string $p_message Optional. If provided, the function will provide an
	 *                          informational message about the latest available
	 *                          release.
	 *
	 * @return bool True if latest release, False if a newer release is available.
	 */
	public function isLatest( string &$p_message = '' ) {
		if( !isset( $this->info->latest ) ) {
			$p_message = "Latest Release information is not available.";
			return true;
		}

		if( version_compare( $this->info->latest, $this->version, '>' ) ) {
			# A newer release is available
			$p_message = "Version {$this->info->latest} was released on {$this->info->latestReleaseDate}.";
			return false;
		}
		$p_message = '';
		return true;
	}
}
