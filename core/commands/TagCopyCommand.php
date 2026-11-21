<?php
# MantisBT - A PHP based bugtracking system
#
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

use Mantis\Exceptions\ClientException;

require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'helper_api.php' );
require_api( 'project_api.php' );
require_api( 'tag_api.php' );

/**
 * A command that copies the tags of a project to another project.
 *
 * The tags copied are the ones scoped to the source project and, if
 * inherited is true (the default), the ones it inherits from its parent projects
 * (when $g_subprojects_inherit_tags is ON).  Global tags are never copied.  If
 * a name is inherited from several projects, the nearest tag is copied.
 *
 * Tags whose names already exist in the target project (compared case-insensitively)
 * are left unchanged and reported as skipped.
 *
 * Sample:
 * {
 *   "query": { "source_project_id": 1, "target_project_id": 2 },
 *   "payload": { "inherited": true }
 * }
 *
 * The response has the ids of the tags created in the target project ("tag_ids")
 * and the names of the tags that were skipped because they already exist ("skipped").
 */
class TagCopyCommand extends Command {
	/**
	 * @var integer Project to copy the tags from.
	 */
	private $source_project_id;

	/**
	 * @var integer Project to copy the tags to.
	 */
	private $target_project_id;

	/**
	 * @var boolean True to also copy the tags inherited by the source project.
	 */
	private $inherited;

	/**
	 * Validate the data.
	 *
	 * @throws ClientException
	 */
	function validate() {
		$this->source_project_id = helper_parse_id( $this->query( 'source_project_id' ), 'source_project_id' );
		$this->target_project_id = helper_parse_id( $this->query( 'target_project_id' ), 'target_project_id' );

		project_ensure_exists( $this->source_project_id );
		project_ensure_exists( $this->target_project_id );

		$this->inherited = (bool)$this->payload( 'inherited', true );

		foreach( array( $this->source_project_id, $this->target_project_id ) as $t_id ) {
			if( !access_has_project_level( config_get( 'manage_project_threshold', null, null, $t_id ), $t_id ) ) {
				throw new ClientException( 'Access denied to copy tags', ERROR_ACCESS_DENIED );
			}
		}
	}

	/**
	 * Process the command.
	 *
	 * @return array Command response
	 */
	protected function process() {
		$t_copied = array();
		$t_skipped = array();

		# Tags of the source project, then the ones inherited from its parents.
		# The nearest tag wins if a name is used by several of them.
		if( $this->inherited ) {
			$t_project_ids = array_diff( tag_get_project_ids_in_scope( $this->source_project_id ), array( ALL_PROJECTS ) );
		} else {
			$t_project_ids = array( $this->source_project_id );
		}

		$t_rows = array();
		foreach( $t_project_ids as $t_project_id ) {
			$t_result = tag_get_all( '', -1, -1, $t_project_id );
			while( $t_row = db_fetch_array( $t_result ) ) {
				$t_key = mb_strtolower( $t_row['name'] );
				if( !isset( $t_rows[$t_key] ) ) {
					$t_rows[$t_key] = $t_row;
				}
			}
		}

		foreach( $t_rows as $t_row ) {
			if( tag_is_unique( $t_row['name'], $this->target_project_id ) ) {
				$t_copied[] = tag_create( $t_row['name'], null, $t_row['description'], $this->target_project_id );
			} else {
				$t_skipped[] = $t_row['name'];
			}
		}

		return array( 'tag_ids' => $t_copied, 'skipped' => $t_skipped );
	}
}
