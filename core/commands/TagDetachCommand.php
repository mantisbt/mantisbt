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
 * A command that detaches a tag from an issue.
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
class TagDetachCommand extends Command {
	/**
	 * @var integer issue id
	 */
	private $issue_id;

	/**
	 * @var integer logged in user id
	 */
	private $user_id;

	/**
	 * @var array Array of tag ids to be detached.
	 */
	private $tagsToDetach = array();

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

		$t_tags = $this->payload( 'tags', array() );
		if( !is_array( $t_tags ) || empty( $t_tags ) ) {
			throw new ClientException( 'Invalid tags array', ERROR_INVALID_FIELD_VALUE, array( 'tags' ) );
		}

		foreach( $t_tags as $t_tag ) {
			if( isset( $t_tag['id'] ) ) {
				$this->tagsToDetach[] = (int)$t_tag['id'];
			} else if( isset( $t_tag['name'] ) ) {
				$t_tag_row = tag_get_by_name( $t_tag['name'] );
				if( $t_tag_row === false ) {
					throw new ClientException(
						sprintf( "Tag '%s' not found", $t_tag['name'] ),
						ERROR_INVALID_FIELD_VALUE,
						array( 'tags' ) );
				} else {
					$this->tagsToDetach[] = (int)$t_tag_row['id'];
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
		foreach( $this->tagsToDetach as $t_tag_id ) {
			if( tag_bug_is_attached( $t_tag_id, $this->issue_id ) ) {
				tag_bug_detach( $t_tag_id, $this->issue_id );
				event_signal( 'EVENT_TAG_DETACHED', array( $this->issue_id, array( $t_tag_id ) ) );
			}
		}
	}
}

