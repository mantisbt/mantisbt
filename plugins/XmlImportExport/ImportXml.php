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
	 * @param integer $p_issue_id An issue identifier.
	 * @return string
	 */
	public function get_issue_url( $p_issue_id ) {
		return $this->urlbase . 'view.php?id=' . $p_issue_id;
	}

	/**
	 * Get url to view bugnote
	 * @param integer $p_issue_id An issue identifier.
	 * @param integer $p_note_id  A note identifier.
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
	  * @param string $p_filename         Name of the file to read.
	  * @param string $p_strategy         Conversion strategy; one of "renumber", "link" or "disable".
	  * @param string $p_fallback         Alternative conversion strategy when "renumber" does not apply.
	  * @param string $p_keep_category    Keep category.
	  * @param string $p_default_category Default category.
	  */
	public function __construct( $p_filename, $p_strategy, $p_fallback, $p_keep_category, $p_default_category ) {
		$this->source_ = new SourceData;
		$this->reader_ = new XMLReader( );
		$this->itemsMap_ = new ImportXml_Mapper;
		$this->strategy_ = $p_strategy;
		$this->fallback_ = $p_fallback;
		$this->keepCategory_ = $p_keep_category;
		$this->defaultCategory_ = $p_default_category;

		$this->reader_->open( $p_filename['tmp_name'] );
	}

	/**
	 * Perform import from an XML file
	 * @return void
	 */
	public function import() {
		# Read the <mantis> element and it's attributes
		while( $this->reader_->read( ) && $this->reader_->name == 'mantis' ) {
			$this->source_->version = $this->reader_->getAttribute( 'version' );
			$this->source_->urlbase = $this->reader_->getAttribute( 'urlbase' );
			$this->source_->issuelink = $this->reader_->getAttribute( 'issuelink' );
			$this->source_->notelink = $this->reader_->getAttribute( 'notelink' );
			$this->source_->format = $this->reader_->getAttribute( 'format' );
		}

		echo 'Importing file, please wait...';

		# loop through the elements
		while( $this->reader_->read( ) ) {
			switch( $this->reader_->nodeType ) {
				case XMLReader::ELEMENT:

					# element start
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

		# replace bug references
		$t_imported_issues = $this->itemsMap_->getall( 'issue' );
		printf( 'Processing cross-references for %s issues...', count( $t_imported_issues ) );
		foreach( $t_imported_issues as $t_old_id => $t_new_id ) {
			$t_bug = bug_get( $t_new_id, true );

			# Using bitwise 'or' here to ensure the all replacements are made
			# regardless of outcome of the previous one(s)
			$t_content_replaced =
				  $this->replaceLinks( $t_bug, 'description' )
				| $this->replaceLinks( $t_bug, 'steps_to_reproduce' )
				| $this->replaceLinks( $t_bug, 'additional_information' );

			if( $t_content_replaced ) {
				# only update bug if necessary (otherwise last update date would be unnecessarily overwritten)
				$t_bug->update( true );
			}
		}

		# @todo: replace references within bug notes
		echo " Done\n";
	}

	/**
	 * Replace links in the given bug for the specified field
	 * @param object $p_bug
	 * @param string $p_field Field to process (one of 'description',
	 *                        'steps_to_reproduce' or 'additional_information')
	 * @return boolean true if replacements have been made
	 */
	private function replaceLinks( $p_bug, $p_field ) {
		static $s_bug_link_regexp;
		$t_content_replaced = false;

		if( is_null( $s_bug_link_regexp ) ) {
			$s_bug_link_regexp = '/(?:^|[^\w])'
				. preg_quote( $this->source_->issuelink, '/' )
				. '(\d+)\b/';
		}

		preg_match_all( $s_bug_link_regexp, $p_bug->$p_field, $t_matches );

		if( is_array( $t_matches[1] ) && count( $t_matches[1] ) > 0 ) {
			$t_content_replaced = true;
			foreach ( $t_matches[1] as $t_old_id ) {
				$p_bug->$p_field = str_replace(
					$this->source_->issuelink . $t_old_id,
					$this->getReplacementString( $this->source_->issuelink, $t_old_id ),
					$p_bug->$p_field
				);
			}
		}

		return $t_content_replaced;
	}

	/**
	 * Compute and return the new link
	 *
	 * @param string $p_oldLinkTag Old link tag.
	 * @param string $p_oldId      Old issue identifier.
	 * @return string
	 */
	private function getReplacementString( $p_oldLinkTag, $p_oldId ) {
		$t_link_tag = config_get( 'bug_link_tag' );

		$t_replacement = '';
		switch( $this->strategy_ ) {
			case 'link':
				$t_replacement = $this->source_->get_issue_url( $p_oldId );
				break;

			case 'disable':
				$t_replacement = htmlFullEntities( $p_oldLinkTag ) . $p_oldId;
				break;

			case 'renumber':
				if( $this->itemsMap_->exists( 'issue', $p_oldId ) ) {
					# regular renumber
					$t_replacement = $t_link_tag . $this->itemsMap_->getNewID( 'issue', $p_oldId );
				} else {
					# fallback strategy
					if( $this->fallback_ == 'link' ) {
						$t_replacement = $this->source_->get_issue_url( $p_oldId );
					}
					if( $this->fallback_ == 'disable' ) {
						$t_replacement = htmlFullEntities( $p_oldLinkTag ) . $p_oldId;
					}
				}
				break;

			default:
				echo 'Unknown method';
		}

		return $t_replacement;
	}

	/**
	 * Get importer object
	 * @param string $p_element_name Name.
	 * @return ImportXml_Issue
	 */
	private function get_importer_object( $p_element_name ) {
		$t_importer = null;
		switch( $p_element_name ) {
			case 'issue':
				$t_importer = new ImportXml_Issue( $this->keepCategory_, $this->defaultCategory_ );
				break;
		}
		return $t_importer;
	}
}

/**
 * Convert each character of the passed string to the corresponding HTML entity.
 * @param string $p_string String to convert.
 * @return string
 */
function htmlFullEntities( $p_string ) {
	$t_chars = str_split( $p_string );
	$t_escaped = array_map( 'getEntity', $t_chars );
	return implode( '', $t_escaped );
}

/**
 * Get entity
 * @param string $p_char Character to convert.
 * @return string
 */
function getEntity( $p_char ) {
	return '&#' . ord( $p_char ) . ';';
}
