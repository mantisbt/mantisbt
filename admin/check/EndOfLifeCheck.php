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

namespace Mantis\admin\check;

use DateTimeImmutable;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use ReflectionClass;
use stdClass;

/**
 * End-of-life version checks.
 *
 * @see https://endoflife.date/
 */
class EndOfLifeCheck
{
	const URL = 'https://endoflife.date/';
	const URL_API = self::URL . 'api/v1/';

	/**
	 * Directory to store dumps of endoflife.date for offline usage.
	 * @see dumpProductInfo()
	 */
	const DATA_DIR = 'eol_data/';

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
	 * @param string $p_product Product to check (use one of the PRODUCT_*
	 *                          constants).
	 * @param string $p_version Product Version to check.
	 *
	 * @throws Exception If release information cannot be retrieved from
	 *                   endoflife.date.
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
			$t_response = $t_client->get( 'products/' . $this->product . '/releases/' . $t_version );
		}
		catch( GuzzleException $e ) {
			throw new Exception( "$this->product version $t_version not found.",
				0,
				$e
			);
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
	 * Get Version number being checked.
	 *
	 * @return string
	 */
	public function getVersion(): string {
		return $this->version;
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
	 * @param string $p_message Optional. If provided, the function will
	 *                          provide an informational message about the
	 *                          Release's EOL status.
	 *
	 * @return bool True if end-of-life, False if not.
	 */
	public function isEOL( string &$p_message = '' ): bool {
		$t_info = $this->info->result;
		if( $t_info->isEol === false ) {
			$p_message = '';
			return false;
		}

		$p_message = "Version " . htmlspecialchars( $this->prepareVersion() );
		if( $t_info->eolFrom ) {
			$t_today = new DateTimeImmutable();
			$t_eol_date = DateTimeImmutable::createFromFormat( 'Y-m-d',
				$t_info->eol
			);
			$t_eol = $t_today > $t_eol_date;

			$p_message = "Support for $p_message "
				. ( $t_eol ? 'ended' : 'ends' )
				. " on $t_info->eolFrom.";
		} else {
			$p_message .= " has reached end-of-life.";
			$t_eol = true;
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
		return (bool)$this->info->result->isLts;
	}

	/**
	 * Check whether the Version is the latest available release.
	 *
	 * Some Products do not provide latest release information.
	 *
	 * @param string $p_message Optional. If provided, the function will
	 *                          provide an informational message about the
	 *                          latest available release.
	 *
	 * @return bool True if latest release, False if a newer release is
	 *              available.
	 */
	public function isLatest( string &$p_message = '' ) {
		$t_info = $this->info->result;
		if( empty( $t_info->latest ) ) {
			$p_message = "Latest Release information is not available.";
			return true;
		}

		if( version_compare( $t_info->latest->name, $this->version, '>' ) ) {
			# A newer release is available
			$p_message = "Version {$t_info->latest->name} was released on {$t_info->latest->date}.";
			return false;
		}
		$p_message = '';
		return true;
	}

	/**
	 * Dump endoflife.date information for supported Products for offline usage.
	 *
	 * Data will be stored in JSON format in the {@see self::DATA_DIR} directory
	 * (one file per Product).
	 *
	 * @return void
	 * @throws Exception
	 */
	public static function dumpProductInfo() {
		# Get list of Products handled by the class
		$t_reflection = new ReflectionClass( __CLASS__ );
		$t_products = array_filter( $t_reflection->getConstants(),
			function( $t_constant ) {
				return substr( $t_constant, 0, 7 ) == 'PRODUCT';
			},
			ARRAY_FILTER_USE_KEY
		);

		# Create data directory if necessary
		if( !file_exists( self::DATA_DIR ) ) {
			if( !mkdir( self::DATA_DIR ) ) {
				throw new Exception( "Failed to create directory '" . self::DATA_DIR . "'" );
			};
		}

		# Retrieve product info from endoflife.date and save it
		foreach( $t_products as $t_product ) {
			$t_options = array(
				'base_uri' => self::URL_API,
			);
			$t_client = new Client( $t_options );

			try {
				$t_response = $t_client->get( 'products/' . $t_product );
			}
			catch( GuzzleException $e ) {
				throw new Exception( $t_product . " not found.", 0, $e );
			}

			$t_filename = self::DATA_DIR . $t_product . '.json';
			if( false === file_put_contents( $t_filename, $t_response->getBody() ) ) {
				throw new Exception( "Failed to create file '$t_filename'" );
			}
		}
	}

}
