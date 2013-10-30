<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
 * Import XML Plugin
 */

require_once( 'ImportXml/Mapper.php' );
require_once( 'ImportXml/Issue.php' );

/**
 * Source Data
 */
class SourceData {
	/**
	 * Version
	 */
	public $version;
	/**
	 * Urlbase
	 */
	public $urlbase;
	/**
	 * Issue link
	 */
	public $issuelink;
	/**
	 * Note link
	 */
	public $notelink;
	/**
	 * Format
	 */
	public $format;

	/**
	 * Get url to view issue
	 * @param int issue id
     * @return string
	 */
	public function get_issue_url( $issue_id ) {
		return $this->urlbase . 'view.php?id=' . $issue_id;
	}

	/**
	 * Get url to view bugnote
	 * @param int $p_issue_id issue id
	 * @param int $p_note_id note id
     * @return string
	 */
	public function get_note_url( $p_issue_id, $p_note_id ) {
		return $this->urlbase . 'view.php?id=' . $p_issue_id . '#c' . $p_note_id;
	}
}

/**
  * Perform import from an XML file
  */
class ImportXML {
	/**
	 * Source
	 * @access private
	 */
	private $source_;
	/**
	 * reader
	 * @access private
	 */
	private $reader_;
	/**
	 * itemsmap
	 * @access private
	 */
	private $itemsMap_;
	/**
	 * strategy
	 * @access private
	 */
	private $strategy_;
	/**
	 * fallback
	 * @access private
	 */
	private $fallback_;

	// issues specific options
	/**
	 * keep category
	 * @access private
	 */
	private $keepCategory_;
	/**
	 * default category
	 * @access private
	 */
	private $defaultCategory_;

	/**
	  * Constructor
	  *
	  * @param string $p_filename name of the file to read
	  * @param string $strategy conversion strategy; one of "renumber", "link" or "disable"
	  * @param string $fallback alternative conversion strategy when "renumber" does not apply
	  * @param string $keepCategory keep category
	  * @param string $defaultCategory default category
	  */
	public function __construct( $p_filename, $strategy, $fallback, $keepCategory, $defaultCategory ) {
		$this->source_ = new SourceData;
		$this->reader_ = new XMLReader( );
		$this->itemsMap_ = new ImportXml_Mapper;
		$this->strategy_ = $strategy;
		$this->fallback_ = $fallback;
		$this->keepCategory_ = $keepCategory;
		$this->defaultCategory_ = $defaultCategory;

		$this->reader_->open( $p_filename['tmp_name'] );
	}

	/**
	 * Perform import from an XML file
	 */
	public function import( ) {
		// Read the <mantis> element and it's attributes
		while( $this->reader_->read( ) && $this->reader_->name == 'mantis' ) {
			$this->source_->version = $this->reader_->getAttribute( 'version' );
			$this->source_->urlbase = $this->reader_->getAttribute( 'urlbase' );
			$this->source_->issuelink = $this->reader_->getAttribute( 'issuelink' );
			$this->source_->notelink = $this->reader_->getAttribute( 'notelink' );
			$this->source_->format = $this->reader_->getAttribute( 'format' );
		}

		echo 'Importing file, please wait...';

		// loop through the elements
		while( $this->reader_->read( ) ) {
			switch( $this->reader_->nodeType ) {
				case XMLReader::ELEMENT:

					/* element start */
					$t_element_name = $this->reader_->localName;
					$t_importer = $this->get_importer_object( $t_element_name );
					if( !is_null( $t_importer ) ) {
						$t_importer->process( $this->reader_ );
						$t_importer->update_map( $this->itemsMap_ );
					}
					break;
			}
		}

		echo " Done\n";

		// replace references in bug description and additional information
		$importedIssues = $this->itemsMap_->getall( 'issue' );
		printf( "Processing cross-references for %s issues...", count( $importedIssues ) );
		foreach( $importedIssues as $oldId => $newId ) {
			$bugData = bug_get( $newId, true );
			$content_replaced = false;

			$bugLinkRegexp = '/(^|[^\w])(' . preg_quote( $this->source_->issuelink, '/' ) . ')(\d+)\b/e';
			// replace links in description
			preg_match_all( $bugLinkRegexp, $bugData->description, $matches );
			if ( is_array( $matches[3] && count( $matches[3] ) > 0 ) ) {
				$content_replaced = true;
				foreach ( $matches[3] as $old_id ) {
					$bugData->description = str_replace( $this->source_->issuelink . $old_id, $this->getReplacementString( $this->source_->issuelink, $old_id ), $bugData->description);
				}
			}
			// replace links in additional information
			preg_match_all( $bugLinkRegexp, $bugData->additional_information, $matches );
			if ( is_array( $matches[3] && count( $matches[3] ) > 0 ) ) {
				$content_replaced = true;
				foreach ( $matches[3] as $old_id ) {
					$bugData->additional_information = str_replace( $this->source_->issuelink . $old_id, $this->getReplacementString( $this->source_->issuelink, $old_id ), $bugData->additional_information);
				}
			}
			if ( $content_replaced ) {
				// only update bug if necessary (otherwise last update date would be unnecessarily overwritten)
				$bugData->update( true );
			}
		}

		// @todo: replace references within bugnotes
		echo " Done\n";
	}

	/**
	 * Compute and return the new link
	 *
	 * @param string $p_oldLinkTag old link tag
	 * @param string $p_oldId old issue id
     * @return string
	 */
	private function getReplacementString( $p_oldLinkTag, $p_oldId ) {
		$linkTag = config_get( 'bug_link_tag' );

		$replacement = '';
		switch( $this->strategy_ ) {
			case 'link':
				$replacement = $this->source_->get_issue_url( $p_oldId );
				break;

			case 'disable':
				$replacement = htmlFullEntities( $p_oldLinkTag ) . $p_oldId;
				break;

			case 'renumber':
				if( $this->itemsMap_->exists( 'issue', $p_oldId ) ) {
					// regular renumber
					$replacement = $linkTag . $this->itemsMap_->getNewID( 'issue', $p_oldId );
				} else {
					// fallback strategy
					if( $this->fallback_ == 'link' ) {
						$replacement = $this->source_->get_issue_url( $p_oldId );
					}
					if( $this->fallback_ == 'disable' ) {
						$replacement = htmlFullEntities( $p_oldLinkTag ) . $p_oldId;
					}
				}
				break;

			default:
				echo "Unknown method";
		}

		return $replacement;
	}

	/**
	 * Get importer object
	 * @param element $p_element_name name
     * @return ImportXml_Issue
	 */
	private function get_importer_object( $p_element_name ) {
		$importer = null;
		switch( $p_element_name ) {
			case 'issue':
				$importer = new ImportXml_Issue( $this->keepCategory_, $this->defaultCategory_ );
				break;
		}
		return $importer;
	}
}

/** candidates for string api **/

/**
 * Convert each character of the passed string to the corresponding HTML entity.
 * @param string string to convert
 * @return string
 */
function htmlFullEntities( $string ) {
	$chars = str_split( $string );
	$escaped = array_map( 'getEntity', $chars );
	return implode( '', $escaped );
}

/**
 * Get entity
 * @param string character to convert
 * @return string
 */
function getEntity( $char ) {
	return '&#' . ord( $char ) . ';';
}
