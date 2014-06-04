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
 * Import XML issue class
 */


require_api( 'bug_api.php' );
require_api( 'user_api.php' );
require_once( 'Interface.php' );

/**
 * Import XML issue class
 */
class ImportXml_Issue implements ImportXml_Interface {
	/**
	 * old issue id
	 */
	private $old_id_;
	/**
	 * new issue id
	 */
	private $new_id_;

	/**
	 * new bug object
	 */
	private $newbug_;

	/**
	 * keep existing category
	 * @var bool
	 */
	private $keepCategory_;
	/**
	 * default category
	 * @var int
	 */
	private $defaultCategory_;

	/**
	 * Default Constructor
	 * @param bool $keepCategory
	 * @param int $defaultCategory
	 */
	public function __construct( $keepCategory, $defaultCategory ) {
		$this->newbug_ = new BugData;
		$this->keepCategory_ = $keepCategory;
		$this->defaultCategory_ = $defaultCategory;
	}

	/**
	 * Read stream until current item finishes, processing the data found
	 * @param XMLreader $reader
	 */
	public function process( XMLreader $reader ) {

		# print "\nImportIssue process()\n";
		$t_project_id = helper_get_current_project(); # TODO: category_get_id_by_name could work by default on current project
		$userId = auth_get_current_user_id( );

		$t_custom_fields = array();
		$t_bugnotes = array();
		$t_attachments = array();

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

						# echo "reporter: old id = $t_old_id - new id = {$this->newbug_->reporter_id}\n";
						break;

					case 'handler':
						$t_old_id = $reader->getAttribute( 'id' );
						$reader->read( );
						$this->newbug_->handler_id = $this->get_user_id( $reader->value, $userId );

						# echo "handler: old id = $t_old_id - new id = {$this->newbug_->handler_id}\n";
						break;

					case 'category':
						$this->newbug_->category_id = $this->defaultCategory_;

						if( version_compare( MANTIS_VERSION, '1.2', '>' ) === true ) {
							$reader->read( );

							if( $this->keepCategory_ ) {
								# Check for the category's existence in the current project
								# well as its parents (if any)
								$t_projects_hierarchy = project_hierarchy_inheritance( $t_project_id );
								foreach( $t_projects_hierarchy as $t_project ) {
									$t_category_id = category_get_id_by_name( $reader->value, $t_project, false );
									if( $t_category_id !== false ) {
										$this->newbug_->category_id = $t_category_id;
										break;
									}
								}
							}

							# echo "new id = {$this->newbug_->category_id}\n";
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

						# Here we assume ids have the same meaning in both installations
						# TODO add a check for customized values
						$this->newbug_->$t_field = $t_id;
						break;

					case 'id':
						$reader->read( );
						$this->old_id_ = $reader->value;
						break;

					case 'project';
						# ignore original value, use current project
						$this->newbug_->project_id = $t_project_id;
						break;

					case 'custom_fields':
						# store custom fields
						$i = -1;
						$depth_cf = $reader->depth;
						while ( $reader->read() &&
						        ( $reader->depth > $depth_cf ||
						          $reader->nodeType != XMLReader::END_ELEMENT ) ) {
							if( $reader->nodeType == XMLReader::ELEMENT ) {
								if ($reader->localName == 'custom_field') {
									$t_custom_fields[++$i] = new stdClass();
								}
								switch ( $reader->localName ) {
									default:
										$field = $reader->localName;
										$reader->read( );
										$t_custom_fields[$i]->$field = $reader->value;
								}
							}
						}
						break;

					case 'bugnotes':
						# store bug notes
						$i = -1;
						$depth_bn = $reader->depth;
						while ( $reader->read() &&
						        ( $reader->depth > $depth_bn ||
						          $reader->nodeType != XMLReader::END_ELEMENT ) ) {
							if( $reader->nodeType == XMLReader::ELEMENT ) {
								if ($reader->localName == 'bugnote') {
									$t_bugnotes[++$i] = new stdClass();
								}
								switch ( $reader->localName ) {
									case 'reporter':
										$t_old_id = $reader->getAttribute( 'id' );
										$reader->read( );
										$t_bugnotes[$i]->reporter_id = $this->get_user_id( $reader->value, $userId );
										break;

									case 'view_state':
										$t_old_id = $reader->getAttribute( 'id' );
										$reader->read( );
										$t_bugnotes[$i]->private = $reader->value == VS_PRIVATE ? true : false;
										break;

									default:
										$field = $reader->localName;
										$reader->read( );
										$t_bugnotes[$i]->$field = $reader->value;
								}
							}
						}
						break;

					case 'attachments':
						# store attachments
						$i = -1;
						$depth_att = $reader->depth;
						while ( $reader->read() &&
						        ( $reader->depth > $depth_att ||
						          $reader->nodeType != XMLReader::END_ELEMENT ) ) {
							if( $reader->nodeType == XMLReader::ELEMENT ) {
								if ($reader->localName == 'attachment') {
									$t_attachments[++$i] = new stdClass();
								}
								switch ( $reader->localName ) {
									default:
										$field = $reader->localName;
										$reader->read( );
										$t_attachments[$i]->$field = $reader->value;
								}
							}
						}
						break;

					default:
						$field = $reader->localName;

						# echo "using default handler for field: $field\n";
						$reader->read( );
						$this->newbug_->$field = $reader->value;
				}
			}
		}

		# now save the new bug
		$this->new_id_ = $this->newbug_->create();

		# add custom fields
		if( $this->new_id_ > 0 && is_array( $t_custom_fields ) && count( $t_custom_fields ) > 0 ) {
			foreach ( $t_custom_fields as $t_custom_field) {
				$t_custom_field_id = custom_field_get_id_from_name( $t_custom_field->name );
				if( custom_field_ensure_exists( $t_custom_field_id ) && custom_field_is_linked( $t_custom_field_id, $t_project_id ) ) {
					custom_field_set_value( $t_custom_field->id, $this->new_id_, $t_custom_field->value );
				}
				else {
					error_parameters( $t_custom_field->name, $t_custom_field_id );
					trigger_error( ERROR_CUSTOM_FIELD_NOT_LINKED_TO_PROJECT, ERROR );
				}
			}
		}

		# add bugnotes
		if( $this->new_id_ > 0 && is_array( $t_bugnotes ) && count( $t_bugnotes ) > 0 ) {
			foreach ( $t_bugnotes as $t_bugnote) {
				bugnote_add(
					$this->new_id_,
					$t_bugnote->note,
					$t_bugnote->time_tracking,
					$t_bugnote->private,
					$t_bugnote->note_type,
					$t_bugnote->note_attr,
					$t_bugnote->reporter_id,
					false,
					$t_bugnote->date_submitted,
					$t_bugnote->last_modified,
					true
				);
			}
		}

		# add attachments
		if( $this->new_id_ > 0 && is_array( $t_attachments ) && count( $t_attachments ) > 0 ) {
			foreach ( $t_attachments as $t_attachment) {
				# Create a temporary file in the temporary files directory using sys_get_temp_dir()
				$temp_file_name = tempnam( sys_get_temp_dir(), 'MantisImport' );
				file_put_contents( $temp_file_name, base64_decode( $t_attachment->content ) );
				$file_data = array(
					'name'     => $t_attachment->filename,
					'type'     => $t_attachment->file_type,
					'tmp_name' => $temp_file_name,
					'size'     => filesize( $temp_file_name ),
					'error'    => UPLOAD_ERR_OK,
				);
				# unfortunately we have no clue who has added the attachment (this could only be fetched from history -> feel free to implement this)
				# also I have no clue where description should come from...
				file_add( $this->new_id_, $file_data, 'bug', $t_attachment->title, $p_desc = '', $p_user_id = null, $t_attachment->date_added, true );
				unlink( $temp_file_name );
			}
		}

		#echo "\nnew bug: $this->new_id_\n";
	}

	/**
	 * update mapper
	 * @param ImportXml_Mapper mapper
	 */
	public function update_map( ImportXml_Mapper $mapper ) {
		$mapper->add( 'issue', $this->old_id_, $this->new_id_ );
	}

	/**
	 * Dump Diagnostic information
	 */
	public function dumpbug( ) {
		var_dump( $this->newbug_ );
		var_dump( $this->issueMap );
	}

	/**
	* Return the user id in the destination tracker
	*
	* Current logic is: try to find the same user by username;
	 * if it fails, use $p_squash_userid
	*
	 * @param string $p_username username as imported
	 * @param int $p_squash_userid fallback userid
     * @return int
	*/
	private function get_user_id( $p_username, $p_squash_userid = 0 ) {
		$t_user_id = user_get_id_by_name( $p_username );
		if( $t_user_id === false ) {
			# user not found by username -> check real name
			# keep in mind that the setting config_get( 'show_user_realname_threshold' ) may differ between import and export system!
			$t_user_id = user_get_id_by_realname( $p_username );
			if( $t_user_id === false ) {
				# not found
				$t_user_id = $p_squash_userid;
			}
		}
		return $t_user_id;
	}
}
