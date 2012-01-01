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

require_once( 'bug_api.php' );
require_once( 'user_api.php' );
require_once( 'Interface.php' );

class ImportXml_Issue implements ImportXml_Interface {

	private $old_id_;
	private $new_id_;

	private $newbug_;

	// import Issues options
	private $keepCategory_;
	private $defaultCategory_;

	public function __construct( $keepCategory, $defaultCategory ) {
		$this->newbug_ = new BugData;
		$this->keepCategory_ = $keepCategory;
		$this->defaultCategory_ = $defaultCategory;
	}

	// Read stream until current item finishes, processing
	// the data found
	public function process( XMLreader$reader ) {

		//print "\nImportIssue process()\n";
		$t_project_id = helper_get_current_project(); // TODO: category_get_id_by_name could work by default on current project
		$userId = auth_get_current_user_id( );

		$depth = $reader->depth;
		while( $reader->read() &&
				($reader->depth > $depth ||
				 $reader->nodeType != XMLReader::END_ELEMENT)) {
			if( $reader->nodeType == XMLReader::ELEMENT ) {
				switch( $reader->localName ) {
					case 'reporter':
						$t_old_id = $reader->getAttribute( 'id' );
						$reader->read( );
						$this->newbug_->reporter_id = $this->get_user_id( $reader->value, $userId );

						//echo "reporter: old id = $t_old_id - new id = {$this->newbug_->reporter_id}\n";
						break;

					case 'handler':
						$t_old_id = $reader->getAttribute( 'id' );
						$reader->read( );
						$this->newbug_->handler_id = $this->get_user_id( $reader->value, $userId );

						//echo "handler: old id = $t_old_id - new id = {$this->newbug_->handler_id}\n";
						break;

					case 'category':
						$this->newbug_->category_id = $this->defaultCategory_;

						// TODO: if we port the import/export code to 1.1.x, this needs to be
						//       improved to cope with the different cases (1.1 => 1.2, 1.2 => 1.1 etc)
						if( version_compare( MANTIS_VERSION, '1.2', '>' ) === true ) {
							$reader->read( );

							if( $this->keepCategory_ ) {
								$t_category_id = category_get_id_by_name( $reader->value, $t_project_id );
								if( $t_category_id !== false ) {
									$this->newbug_->category_id = $t_category_id;
								}
							}

							//	echo "new id = {$this->newbug_->category_id}\n";
						}
						break;

					case 'eta':
					case 'priority':
					case 'projection':
					case 'reproducibility':
					case 'resolution':
					case 'severity':
					case 'status':
					case 'view_state':
						$t_field = $reader->localName;
						$t_id = $reader->getAttribute( 'id' );
						$reader->read( );
						$t_value = $reader->value;

						// Here we assume ids have the same meaning in both installations
						// TODO add a check for customized values
						$this->newbug_->$t_field = $t_id;
						break;

					case 'id':
						$reader->read( );
						$this->old_id_ = $reader->value;
						break;

					case 'project';

					// ignore original value, use current project
					$this->newbug_->project_id = $t_project_id;
					break;
				default:
					$field = $reader->localName;

					//echo "using default handler for field: $field\n";
					$reader->read( );
					$this->newbug_->$field = $reader->value;
				}
			}
		}

		// now save the new bug
		$this->new_id_ = $this->newbug_->create();

		//echo "\nnew bug: $this->new_id_\n";
	}

	public function update_map( Mapper$mapper ) {
		$mapper->add( 'issue', $this->old_id_, $this->new_id_ );
	}


	public function dumpbug( ) {
		var_dump( $this->newbug_ );
		var_dump( $this->issueMap );
	}

	/**
	* Return the user id in the destination tracker
	*
	* Current logic is: try to find the same user by username;
	* if it fails, use $squash_userid
	*
	* @param $field string bugdata filed to update
	* @param $username string username as imported
	* @param $squash_userid integer fallback userid
	*/
	private function get_user_id( $username, $squash_userid = 0 ) {
		$t_user_id = user_get_id_by_name( $username );
		if( $t_user_id === false ) {

			//not found
			$t_user_id = $squash_userid;
		}
		return $t_user_id;
	}
}
