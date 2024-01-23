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
 *   "query": { "issue_id" => 1234, "tag_id" => 1 }
 * }
 */
class TagDetachCommand extends Command {
	/**
	 * @var integer issue id
	 */
	private $issue_id;

	/**
	 * @var integer tag id
	 */
	private $tag_id;

	/**
	 * @var integer logged in user id
	 */
	private $user_id;

	/**
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data );
	}

	/**
	 * Validate the data.
	 *
	 * @throws ClientException
	 */
	function validate() {
		$this->issue_id = helper_parse_issue_id( $this->query( 'issue_id' ) );
		$this->tag_id = $this->query( 'tag_id' );
		$this->user_id = auth_get_current_user_id();

		if( !is_numeric( $this->tag_id ) ) {
			throw new ClientException(
				sprintf( "Invalid tag id '%s'", $this->tag_id ),
				ERROR_INVALID_FIELD_VALUE,
				array( 'tag_id' ) );
		}

		tag_ensure_exists( $this->tag_id );
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 * @throws ClientException
	 */
	protected function process() {
		if( tag_bug_is_attached( $this->tag_id, $this->issue_id ) ) {
			tag_bug_detach( $this->tag_id, $this->issue_id );
			event_signal( 'EVENT_TAG_DETACHED', array( $this->issue_id, array( $this->tag_id ) ) );
		}

		return [];
	}
}
