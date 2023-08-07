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
 * Download fonts files in WOFF, WOFF2 formats for local usage.
 *
 * Files are retrieved using Mario Ranftl's google-webfonts-helper REST API
 * @link https://gwfh.mranftl.com/fonts
 *
 * @package MantisBT
 * @copyright Copyright 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://mantisbt.org
 */

use GuzzleHttp\Client;

$g_mantis_root = dirname( __DIR__ ) . '/';

require_once( $g_mantis_root . 'core.php' );


/**
 * Class FontDownload
 */
class FontDownload {
	const FONT_API_ROOT = 'https://gwfh.mranftl.com/api/fonts/';
	const FONT_FORMATS = 'woff,woff2';
	const FONT_VARIANTS = 'regular';

	/** @var string Directory where MantisBT local font files are stored */
	protected $fonts_dir;

	/** @var string Path to local CSS fonts file */
	protected $fonts_css;

	/** @var string $css Local CSS file contents */
	protected $css;

	/** @var GuzzleHttp\Client */
	static protected $request;

	/** @var string Font's Id for use with google-font-helper's API */
	protected $font_id;

	/** @var string Font's version */
	protected $version;

	/** @var string[] Font's available subsets */
	protected $subsets = [];

	/** @var string[] Existing font files */
	protected $files = [];

	/** @var string Search pattern */
	protected $pattern;

	/** @var int Downloaded files count */
	protected $count;

	/**
	 * FontDownload constructor.
	 *
	 * @param string $p_font Font Id
	 */
	public function __construct( string $p_font ) {
		global $g_mantis_root;

		echo "Processing font: $p_font\n";

		$this->fonts_dir = $g_mantis_root . 'fonts/';
		$this->fonts_css = $g_mantis_root . 'css/fonts.css';

		if( !self::$request ) {
			self::$request = new Client( [
				'base_uri' => self::FONT_API_ROOT
			] );
		}

		# Get font information
		$t_response = self::$request->get( $p_font );
		$t_font_info = json_decode( $t_response->getBody() );

		$this->font_id = $p_font;
		$this->version = $t_font_info->version;
		$this->subsets = $t_font_info->subsets;
		$this->pattern = '/(' . $this->font_id . '-)(v[0-9]+)(-.*\.woff2?)/';
	}

	/**
	 * Retrieve the list of locally-available fonts from MantisBT config.
	 *
	 * Font IDs are determined from the font family names referenced in the
	 * configuration (converting them to lowercase and replacing spaces by `-`).
	 * @see $g_font_family_choices_local
	 *
	 * @return array List of font ids.
	 */
	static public function getLocalFonts(): array
	{
		$t_fonts = [];
		foreach( config_get('font_family_choices_local') as $t_font ) {
			$t_font = str_replace( ' ', '-', $t_font );
			$t_fonts[] = strtolower( $t_font );
		}
		return $t_fonts;
	}

	public function getFiles() {
		if( !$this->files ) {
			$this->files = glob( $this->fonts_dir . $this->font_id . '-*.woff*' );
		}
		return $this->files;
	}

	public function getCSS() {
		if( !$this->css ) {
			$this->css = file_get_contents( $this->fonts_css );
		}
		return $this->css;
	}

	/**
	 * Check if font files already exist.
	 *
	 * @return bool|string false if no old files exist, files' version otherwise
	 */
	public function checkOldFiles() {
		$t_files = $this->getFiles();
		if( $t_files ) {
			if( preg_match( $this->pattern, $t_files[0], $t_matches ) ) {
				return $t_matches[2];
			}
		}
		return false;
	}

	public function deleteFiles() {
		echo "  Deleting old font files\n";
		foreach( $this->files as $t_file ) {
			unlink( $t_file );
		}
		$this->files = [];
	}

	/**
	 * Download font files from google-webfonts-helper.
	 */
	public function downloadFiles() {
		echo "  Downloading $this->version: ";
		foreach( $this->subsets as $t_subset ) {
			echo $t_subset . ' ';

			# Get the font files from API
			$t_response = self::$request->get( $this->font_id, [
				'query' => [
					'download' => 'zip',
					'subsets' => $t_subset,
					'formats' => self::FONT_FORMATS,
					'variants' => self::FONT_VARIANTS
				],
			] );

			# Create temp ZIP file and extract the font files from it
			$t_zipfile = tmpfile();
			fwrite( $t_zipfile, $t_response->getBody() );
			$t_zipfile_name = stream_get_meta_data( $t_zipfile )['uri'];

			/** @noinspection PhpComposerExtensionStubsInspection */
			$t_zip = new ZipArchive();
			$t_zip->open( $t_zipfile_name );
			$t_zip->extractTo( $this->fonts_dir );
			$this->count += $t_zip->numFiles;
			$t_zip->close();

			fclose( $t_zipfile );
		}
		echo "- $this->count files\n";
	}

	/**
	 * Update CSS font file with new file names
	 */
	public function updateCSS() {
		echo "  Updating CSS";
		$this->css = preg_replace(
			$this->pattern,
			'$1' . $this->version . '$3',
			$this->getCSS(),
			-1,
			$t_count
		);

		$t_file = fopen( $this->fonts_css, 'w' );
		fwrite( $t_file, $this->css );
		fclose( $t_file );
		echo " - $t_count references\n";

		# Sanity checks
		$this->sanityCheck( $t_count );
	}

	/**
	 * Sanity check on local font files and CSS.
	 *
	 * - Verifies that files referenced in CSS exist in fonts directory, and
	 *   vice-versa.
	 * - If $p_count is provided, will compare it with number of downloaded font
	 *   files.
	 * Prints warnings if checks fail.
	 *
	 * @param int $p_count Reference number of font files
	 */
	public function sanityCheck( int $p_count = 0 ) {
		if( $p_count && $p_count != $this->count ) {
			echo "WARNING: mismatched number of downloaded font files "
				. "vs updated CSS references\n";
		}

		# Get font file names
		$t_files = array_map( 'basename', $this->getFiles());

		# Get font name references in CSS
		preg_match_all( $this->pattern, $this->getCSS(), $t_matches );
		$t_refs = $t_matches[0];

		foreach( array_diff( $t_files, $t_refs ) as $t_file ) {
			echo "WARNING: local font '$t_file' not referenced in CSS\n";
		}
		foreach( array_diff( $t_refs, $t_files ) as $t_file ) {
			echo "WARNING: '$t_file' defined in CSS but local font file not found\n";
		}
	}

	/**
	 * @return string
	 */
	public function getVersion(): string {
		return $this->version;
	}
}


foreach( FontDownload::getLocalFonts() as $t_font_id ) {
	$t_font = new FontDownload( $t_font_id );

	# Check if font files already exist, delete old ones if found
	$t_old_version = $t_font->checkOldFiles();
	if( $t_font->getVersion() == $t_old_version ) {
		echo "  $t_old_version files already exist.\n";
		$t_font->sanityCheck();
		continue;
	}
	if( $t_old_version ) {
		$t_font->deleteFiles();
	}

	# Download new font files
	$t_font->downloadFiles();

	$t_font->updateCSS();
}
