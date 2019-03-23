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

require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'constant_inc.php' );
require_api( 'config_api.php' );
require_api( 'helper_api.php' );
require_api( 'user_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that attaches a tag to an issue and attempts to create it
 * if not already defined.
 *
 * {
 *   "query": { "issue_id" => 1234 },
 *   "payload": {
 *     "tags": [
 *       {
 *          "id": 1
 *       },
 *       {
 *          "name": "tag2"
 *       },
 *       {
 *          "id": 3,
 *          "name": "tag3"
 *       }
 *     ]
 *   }
 * }
 */
class TagAttachCommand extends Command {
	/**
	 * @var integer issue id
	 */
	private $issue_id;

	/**
	 * @var integer logged in user id
	 */
	private $user_id;

	/**
	 * @var array Array of tag names to be added.
	 */
	private $tagsToCreate = array();

	/**
	 * @var array Array of tag ids to be attached.  This doesn't include tags to be created the attached.
	 */
	private $tagsToAttach = array();

	/**
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data );
	}

	/**
	 * Validate the data.
	 */
	function validate() {
		$this->issue_id = helper_parse_issue_id( $this->query( 'issue_id' ) );
		$this->user_id = auth_get_current_user_id();

		if( !access_has_bug_level( config_get( 'tag_attach_threshold' ), $this->issue_id, $this->user_id ) ) {
			throw new ClientException( 'Access denied to attach tags', ERROR_ACCESS_DENIED );
		}

		$t_tags = $this->payload( 'tags', array() );
		if( !is_array( $t_tags ) || empty( $t_tags ) ) {
			throw new ClientException( 'Invalid tags array', ERROR_INVALID_FIELD_VALUE, array( 'tags' ) );
		}

		$t_can_create = access_has_global_level( config_get( 'tag_create_threshold' ) );

		foreach( $t_tags as $t_tag ) {
			if( isset( $t_tag['id'] ) ) {
				tag_ensure_exists( $t_tag['id'] );
				$this->tagsToAttach[] = (int)$t_tag['id'];
			} else if( isset( $t_tag['name'] ) ) {
				$t_tag_row = tag_get_by_name( $t_tag['name'] );
				if( $t_tag_row === false ) {
					if( $t_can_create ) {
						if( !in_array( $t_tag['name'], $this->tagsToCreate ) ) {
							$this->tagsToCreate[] = $t_tag['name'];
						}
					} else {
						throw new ClientException(
							sprintf( "Tag '%s' not found.  Access denied to auto-create tag.", $t_tag['name'] ),
							ERROR_INVALID_FIELD_VALUE,
							array( 'tags' ) );
					}
				} else {
					$this->tagsToAttach[] = (int)$t_tag_row['id'];
				}
			} else {
				# invalid tag with no id or name.
				throw new ClientException( "Invalid tag with no id or name", ERROR_INVALID_FIELD_VALUE, array( 'tags' ) );
			}
		}
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		$t_attached_tags = array();

		# Attach tags that already exist
		foreach( $this->tagsToAttach as $t_tag_id ) {
			if( !tag_bug_is_attached( $t_tag_id, $this->issue_id ) ) {
				tag_bug_attach( $t_tag_id, $this->issue_id, $this->user_id );
				$t_attached_tags[] = tag_get( $t_tag_id );
			}
		}

		# Create new tags and then attach them
		foreach( $this->tagsToCreate as $t_tag_name ) {
			$t_tag_id = tag_create( $t_tag_name, $this->user_id );
			if( !tag_bug_is_attached( $t_tag_id, $this->issue_id ) ) {
				tag_bug_attach( $t_tag_id, $this->issue_id, $this->user_id );
				$t_attached_tags[] = tag_get( $t_tag_id );
			}
		}

		if( !empty( $t_attached_tags ) ) {
			event_signal( 'EVENT_TAG_ATTACHED', array( $this->issue_id, $t_attached_tags ) );
		}
	}
}

