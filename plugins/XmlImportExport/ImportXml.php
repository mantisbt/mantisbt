<?php
# MantisBT - a php based bugtracking system
# Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

require_once( 'ImportXml' . DIRECTORY_SEPARATOR . 'Mapper.php' );
require_once( 'ImportXml' . DIRECTORY_SEPARATOR . 'Issue.php' );

class SourceData {
	public $version;
	public $urlbase;
	public $issuelink;
	public $notelink;
	public $format;

	public function get_issue_url( $issue_id ) {
		return $this->urlbase . 'view.php?id=' . $issue_id;
	}

	public function get_note_url( $issue_id, $note_id ) {
		return $this->urlbase . 'view.php?id=' . $issue_id . '#c' . $note_id;
	}
}

/**
  * Perform import from an XML file
  */
class ImportXML {
	private $source_;
	private $reader_;
	private $itemsMap_;
	private $strategy_;
	private $fallback_;

	// issues specific options
	private $keepCategory_;
	private $defaultCategory_;

	/**
	  * Constructor
	  *
	  * @param string $filename name of the file to read
	  * @param string $strategy conversion strategy; one of "renumber", "link" or "disable"
	  * @param string $fallback alternative conversion strategy when "renumber" does not apply
	  */
	public function __construct( $filename, $strategy, $fallback, $keepCategory, $defaultCategory ) {
		$this->source_ = new SourceData;
		$this->reader_ = new XMLReader( );
		$this->itemsMap_ = new ImportXml_Mapper;
		$this->strategy_ = $strategy;
		$this->fallback_ = $fallback;
		$this->keepCategory_ = $keepCategory;
		$this->defaultCategory_ = $defaultCategory;

		$this->reader_->open( $filename['tmp_name'] );
	}

	/**
	 * Perform import from an XML file
 *
	 * @param string $p_filename name of the file to read
	 * @param string $p_strategy conversion strategy; one of "renumber", "link" or "disable"
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

		$importedIssues = $this->itemsMap_->getall( 'issue' );
		printf( "Processing cross-references for %s issues...", count( $importedIssues ) );
		foreach( $importedIssues as $oldId => $newId ) {
			$bugData = bug_get( $newId, true );

			$bugLinkRegexp = '/(^|[^\w])(' . preg_quote( $this->source_->issuelink, '/' ) . ')(\d+)\b/e';
			$replacement = '"\\1" . $this->getReplacementString( "\\2", "\\3" )';

			$bugData->description = preg_replace( $bugLinkRegexp, $replacement, $bugData->description );
			$bugData->update( true, true );
		}
		echo " Done\n";
	}

	/**
	 * Compute and return the new link
 *
	 */
	private function getReplacementString( $oldLinkTag, $oldId ) {
		$linkTag = config_get( 'bug_link_tag' );

		$replacement = '';
		switch( $this->strategy_ ) {
			case 'link':
				$replacement = $this->source_->get_issue_url( $oldId );
				break;

			case 'disable':
				$replacement = htmlFullEntities( $oldLinkTag ) . $oldId;
				break;

			case 'renumber':
				if( $this->itemsMap_->exists( 'issue', $oldId ) ) {
					// regular renumber
					$replacement = $linkTag . $this->itemsMap_->getNewID( 'issue', $oldId );
				} else {
					// fallback strategy
					if( $this->fallback_ == 'link' ) {
						$replacement = $this->source_->get_issue_url( $oldId );
					}
					if( $this->fallback_ == 'disable' ) {
						$replacement = htmlFullEntities( $oldLinkTag ) . $oldId;
					}
				}
				break;

			default:
				echo "Unknown method";
		}

		//echo "$oldId -> $replacement\n"; // DEBUG
		return $replacement;
	}

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
 * Convert each character of the passed string to the
 * corresponding HTML entity.
 */
function htmlFullEntities( $string ) {
	$chars = str_split( $string );
	$escaped = array_map( 'getEntity', $chars );
	return implode( '', $escaped );
}

function getEntity( $char ) {
	return '&#' . ord( $char ) . ';';
}
