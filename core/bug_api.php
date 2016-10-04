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
 * Bug API
 *
 * @package CoreAPI
 * @subpackage BugAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses antispam_api.php
 * @uses authentication_api.php
 * @uses bugnote_api.php
 * @uses bug_revision_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses database_api.php
 * @uses date_api.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses file_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses mention_api.php
 * @uses relationship_api.php
 * @uses sponsorship_api.php
 * @uses tag_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'access_api.php' );
require_api( 'antispam_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'bug_revision_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'database_api.php' );
require_api( 'date_api.php' );
require_api( 'email_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'file_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'lang_api.php' );
require_api( 'mention_api.php' );
require_api( 'relationship_api.php' );
require_api( 'sponsorship_api.php' );
require_api( 'tag_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

/**
 * Bug Data Structure Definition
 */
class BugData {
	/**
	 * Bug ID
	 */
	protected $id;

	/**
	 * Project ID
	 */
	protected $project_id = null;

	/**
	 * Reporter ID
	 */
	protected $reporter_id = 0;

	/**
	 * Bug Handler ID
	 */
	protected $handler_id = 0;

	/**
	 * Duplicate ID
	 */
	protected $duplicate_id = 0;

	/**
	 * Priority
	 */
	protected $priority = NORMAL;

	/**
	 * Severity
	 */
	protected $severity = MINOR;

	/**
	 * Reproducibility
	 */
	protected $reproducibility = 10;

	/**
	 * Status
	 */
	protected $status = NEW_;

	/**
	 * Resolution
	 */
	protected $resolution = OPEN;

	/**
	 * Projection
	 */
	protected $projection = 10;

	/**
	 * Category ID
	 */
	protected $category_id = 1;

	/**
	 * Date Submitted
	 */
	protected $date_submitted = '';

	/**
	 * Last Updated
	 */
	protected $last_updated = '';

	/**
	 * ETA
	 */
	protected $eta = 10;

	/**
	 * OS
	 */
	protected $os = '';

	/**
	 * OS Build
	 */
	protected $os_build = '';

	/**
	 * Platform
	 */
	protected $platform = '';

	/**
	 * Version
	 */
	protected $version = '';

	/**
	 * Fixed in version
	 */
	protected $fixed_in_version = '';

	/**
	 * Target Version
	 */
	protected $target_version = '';

	/**
	 * Build
	 */
	protected $build = '';

	/**
	 * View State
	 */
	protected $view_state = VS_PUBLIC;

	/**
	 * Summary
	 */
	protected $summary = '';

	/**
	 * Sponsorship Total
	 */
	protected $sponsorship_total = 0;

	/**
	 * Sticky
	 */
	protected $sticky = 0;

	/**
	 * Due Date
	 */
	protected $due_date = '';

	/**
	 * Profile ID
	 */
	protected $profile_id = 0;

	/**
	 * Description
	 */
	protected $description = '';

	/**
	 * Steps to reproduce
	 */
	protected $steps_to_reproduce = '';

	/**
	 * Additional Information
	 */
	protected $additional_information = '';

	/**
	 * Stats
	 */
	private $_stats = null;

	/**
	 * Attachment Count
	 */
	public $attachment_count = null;

	/**
	 * Bugnotes count
	 */
	public $bugnotes_count = null;

	/**
	 * Indicates if bug is currently being loaded from database
	 */
	private $loading = false;

	/**
	 * an array of tuples: ( 'func' => callable, 'params' => array )
	 * to be used as callbacks on the update process
	 */
	private $update_callbacks = array();

	/**
	 * return number of file attachment's linked to current bug
	 * @return integer
	 */
	public function get_attachment_count() {
		if( $this->attachment_count === null ) {
			$this->attachment_count = file_bug_attachment_count( $this->id );
			return $this->attachment_count;
		} else {
			return $this->attachment_count;
		}
	}

	/**
	 * return number of bugnotes's linked to current bug
	 * @return integer
	 */
	public function get_bugnotes_count() {
		if( $this->bugnotes_count === null ) {
			$this->bugnotes_count = self::bug_get_bugnote_count();
			return $this->bugnotes_count;
		} else {
			return $this->bugnotes_count;
		}
	}

	/**
	 * Overloaded Function handling property sets
	 *
	 * @param string $p_name  Property name.
	 * @param string $p_value Value to set.
	 * @private
	 * @return void
	 */
	public function __set( $p_name, $p_value ) {
		switch( $p_name ) {
			# integer types
			case 'id':
			case 'project_id':
			case 'reporter_id':
			case 'handler_id':
			case 'duplicate_id':
			case 'priority':
			case 'severity':
			case 'reproducibility':
			case 'status':
			case 'resolution':
			case 'projection':
			case 'category_id':
				$p_value = (int)$p_value;
				break;
			case 'target_version':
				if( !$this->loading && $this->$p_name != $p_value ) {
					# Only set target_version if user has access to do so
					if( !access_has_project_level( config_get( 'roadmap_update_threshold' ) ) ) {
						trigger_error( ERROR_ACCESS_DENIED, ERROR );
					}
				}
				break;
			case 'due_date':
				if( !is_numeric( $p_value ) ) {
					$p_value = strtotime( $p_value );
				}
				break;
			case 'summary':
				# MySQL 4-bytes UTF-8 chars workaround #21101
				$p_value = db_mysql_fix_utf8( $p_value );
				# Fall through
			case 'build':
				if ( !$this->loading ) {
					$p_value = trim( $p_value );
				}
				break;
			case 'description':
			case 'steps_to_reproduce':
			case 'additional_information':
				# MySQL 4-bytes UTF-8 chars workaround #21101
				$p_value = db_mysql_fix_utf8( $p_value );
				break;

		}
		$this->$p_name = $p_value;
	}

	/**
	 * Overloaded Function handling property get
	 *
	 * @param string $p_name Property name.
	 * @private
	 * @return string|integer|boolean
	 */
	public function __get( $p_name ) {
		if( $this->is_extended_field( $p_name ) ) {
			$this->fetch_extended_info();
		}
		return $this->{$p_name};
	}

	/**
	 * Overloaded Function handling property isset
	 *
	 * @param string $p_name Property name.
	 * @private
	 * @return boolean
	 */
	public function __isset( $p_name ) {
		return isset( $this->{$p_name} );
	}

	/**
	 * fast-load database row into bugobject
	 * @param array $p_row Database result to load into a bug object.
	 * @return void
	 */
	public function loadrow( array $p_row ) {
		$this->loading = true;

		foreach( $p_row as $t_var => $t_val ) {
			$this->__set( $t_var, $p_row[$t_var] );
		}
		$this->loading = false;
	}

	/**
	 * Retrieves extended information for bug (e.g. bug description)
	 * @return void
	 */
	private function fetch_extended_info() {
		if( $this->description == '' ) {
			$t_text = bug_text_cache_row( $this->id );

			$this->description = $t_text['description'];
			$this->steps_to_reproduce = $t_text['steps_to_reproduce'];
			$this->additional_information = $t_text['additional_information'];
		}
	}

	/**
	 * Returns if the field is an extended field which needs fetch_extended_info()
	 *
	 * @param string $p_field_name Field Name.
	 * @return boolean
	 */
	private function is_extended_field( $p_field_name ) {
		switch( $p_field_name ) {
			case 'description':
			case 'steps_to_reproduce':
			case 'additional_information':
				return true;
			default:
				return false;
		}
	}

	/**
	 * Returns the number of bugnotes for the given bug_id
	 * @return integer number of bugnotes
	 * @access private
	 * @uses database_api.php
	 */
	private function bug_get_bugnote_count() {
		if( !access_has_project_level( config_get( 'private_bugnote_threshold' ), $this->project_id ) ) {
			$t_restriction = 'AND view_state=' . VS_PUBLIC;
		} else {
			$t_restriction = '';
		}

		db_param_push();
		$t_query = 'SELECT COUNT(*) FROM {bugnote}
					  WHERE bug_id =' . db_param() . ' ' . $t_restriction;
		$t_result = db_query( $t_query, array( $this->id ) );

		return db_result( $t_result );
	}

	/**
	 * validate current bug object for database insert/update
	 * triggers error on failure
	 * @param boolean $p_update_extended Whether to validate extended fields.
	 * @return void
	 */
	function validate( $p_update_extended = true ) {
		# Summary cannot be blank
		if( is_blank( $this->summary ) ) {
			error_parameters( lang_get( 'summary' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		if( $p_update_extended ) {
			# Description field cannot be empty
			if( is_blank( $this->description ) ) {
				error_parameters( lang_get( 'description' ) );
				trigger_error( ERROR_EMPTY_FIELD, ERROR );
			}
		}

		# Make sure a category is set
		if( 0 == $this->category_id && !config_get( 'allow_no_category' ) ) {
			error_parameters( lang_get( 'category' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		# Ensure that category id is a valid category
		if( $this->category_id > 0 ) {
			category_ensure_exists( $this->category_id );
		}

		if( !is_blank( $this->duplicate_id ) && ( $this->duplicate_id != 0 ) && ( $this->id == $this->duplicate_id ) ) {
			trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );
			# never returns
		}
	}

	/**
	 * Insert a new bug into the database
	 * @return integer integer representing the bug identifier that was created
	 * @access public
	 * @uses database_api.php
	 * @uses lang_api.php
	 */
	function create() {
		antispam_check();

		# check due_date format
		if( is_blank( $this->due_date ) ) {
			$this->due_date = date_get_null();
		}
		# check date submitted and last modified
		if( is_blank( $this->date_submitted ) ) {
			$this->date_submitted = db_now();
		}
		if( is_blank( $this->last_updated ) ) {
			$this->last_updated = db_now();
		}

		# check to see if we want to assign this right off
		# if not assigned, check if it should auto-assigned by category.
		if( 0 == $this->handler_id ) {
			# if a default user is associated with the category and we know at this point
			# that that the bug was not assigned to somebody, then assign it automatically.
			db_param_push();
			$t_query = 'SELECT user_id FROM {category} WHERE id=' . db_param();
			$t_result = db_query( $t_query, array( $this->category_id ) );
			$t_handler = db_result( $t_result );

			if( $t_handler !== false && user_exists( $t_handler ) ) {
				$this->handler_id = $t_handler;
			}
		}

		# Get modified status for auto-assign option if applicable
		$t_original_status = $this->status;
		$this->status = bug_get_status_for_assign( NO_USER, $this->handler_id, $this->status);

		# @TODO This event must be defined as EVENT_TYPE_EXECUTE to avoid having a different object returned
		$t_object = event_signal( 'EVENT_REPORT_BUG_DATA', $this );
		# Check if returned object is a different object, trigger an error if it is
		if( $t_object !== $this ) {
			trigger_error( ERROR_TYPE_MISMATCH, ERROR );
		}

		self::validate( true );

		# Insert text information
		db_param_push();
		$t_query = 'INSERT INTO {bug_text}
					    ( description, steps_to_reproduce, additional_information )
					  VALUES
					    ( ' . db_param() . ',' . db_param() . ',' . db_param() . ')';
		db_query( $t_query, array( $this->description, $this->steps_to_reproduce, $this->additional_information ) );

		# Get the id of the text information we just inserted
		# NOTE: this is guaranteed to be the correct one.
		# The value LAST_INSERT_ID is stored on a per connection basis.

		$t_text_id = db_insert_id( db_get_table( 'bug_text' ) );

		# Insert the rest of the data
		db_param_push();
		$t_query = 'INSERT INTO {bug}
					    ( project_id,reporter_id, handler_id,duplicate_id,
					      priority,severity, reproducibility,status,
					      resolution,projection, category_id,date_submitted,
					      last_updated,eta, bug_text_id,
					      os, os_build,platform, version,build,
					      profile_id, summary, view_state, sponsorship_total, sticky, fixed_in_version,
					      target_version, due_date
					    )
					  VALUES
					    ( ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ',
					      ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ',
					      ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ',
					      ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ',
					      ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ',
					      ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ',
					      ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ')';
		db_query( $t_query, array( $this->project_id, $this->reporter_id, $this->handler_id,
			$this->duplicate_id, $this->priority, $this->severity, $this->reproducibility,
			$this->status, $this->resolution, $this->projection, $this->category_id,
			$this->date_submitted, $this->last_updated, $this->eta, $t_text_id, $this->os,
			$this->os_build, $this->platform, $this->version, $this->build, $this->profile_id,
			$this->summary, $this->view_state, $this->sponsorship_total, $this->sticky,
			$this->fixed_in_version, $this->target_version, $this->due_date ) );

		$this->id = db_insert_id( db_get_table( 'bug' ) );

		# log new bug
		history_log_event_special( $this->id, NEW_BUG );

		# log changes, if any (compare happens in history_log_event_direct)
		history_log_event_direct( $this->id, 'status', $t_original_status, $this->status );
		history_log_event_direct( $this->id, 'handler_id', 0, $this->handler_id );

		# Execute all registered update callbacks
		while( !empty( $this->update_callbacks ) ) {
			$t_callback = array_shift( $this->update_callbacks );
			call_user_func_array( $t_callback['func'], $t_callback['params'] );
		}

		# Allow plugins to post-process bug data with the new bug ID
		event_signal( 'EVENT_REPORT_BUG', array( $this, $this->id ) );

		return $this->id;
	}

	/**
	 * Process mentions in the current issue, for example, after the issue is created.
	 * @return void
	 * @access public
	 */
	function process_mentions() {
		# Now that the issue is added process the @ mentions
		$t_all_mentioned_user_ids = array();

		$t_mentioned_user_ids = mention_get_users( $this->summary );
		$t_all_mentioned_user_ids = array_merge( $t_all_mentioned_user_ids, $t_mentioned_user_ids );
		
		$t_mentioned_user_ids = mention_get_users( $this->description );
		$t_all_mentioned_user_ids = array_merge( $t_all_mentioned_user_ids, $t_mentioned_user_ids );

		if( !is_blank( $this->steps_to_reproduce ) ) {
			$t_mentioned_user_ids = mention_get_users( $this->steps_to_reproduce );
			$t_all_mentioned_user_ids = array_merge( $t_all_mentioned_user_ids, $t_mentioned_user_ids );
		}

		if( !is_blank( $this->additional_information ) ) {
			$t_mentioned_user_ids = mention_get_users( $this->additional_information );
			$t_all_mentioned_user_ids = array_merge( $t_all_mentioned_user_ids, $t_mentioned_user_ids );
		}

		$t_filtered_mentioned_user_ids = access_has_bug_level_filter(
			config_get( 'view_bug_threshold' ),
			$this->id,
			$t_all_mentioned_user_ids );

		$t_removed_mentions_user_ids = array_diff( $t_all_mentioned_user_ids, $t_filtered_mentioned_user_ids );

		if( !empty( $t_all_mentioned_user_ids ) ) {
			$t_mention_text = $this->description . "\n\n";

			if( !is_blank( $this->steps_to_reproduce ) ) {
				$t_mention_text .= lang_get( 'email_steps_to_reproduce' ) . "\n\n";
				$t_mention_text .= $this->steps_to_reproduce . "\n\n";
			}

			if( !is_blank( $this->additional_information ) ) {
				$t_mention_text .= lang_get( 'email_additional_information' ) . "\n\n";
				$t_mention_text .= $this->additional_information . "\n\n";
			}

			mention_process_user_mentions(
				$this->id,
				$t_filtered_mentioned_user_ids,
				$t_mention_text,
				$t_removed_mentions_user_ids );
		}
	}

	/**
     * Update a bug from the given data structure
     *  If the third parameter is true, also update the longer strings table
     * @param boolean $p_update_extended Whether to update extended fields.
     * @param boolean $p_bypass_mail     Whether to bypass sending email notifications.
     * @internal param boolean $p_bypass_email Default false, set to true to avoid generating emails (if sending elsewhere)
     * @return boolean (always true)
     * @access public
	 */
	function update( $p_update_extended = false, $p_bypass_mail = false ) {

		if( is_blank( $this->due_date ) ) {
			$this->due_date = date_get_null();
		}

		$t_old_data = bug_get( $this->id, true );

		# From this point, use returned object from event in case the object refenced is not $this
		# @TODO This event is better defined as EVENT_TYPE_EXECUTE to avoid that situation
		$t_updated_data = event_signal( 'EVENT_UPDATE_BUG_DATA', $this, $t_old_data );
		$c_bug_id = $t_updated_data->id;

		$t_updated_data->validate( $p_update_extended );

		# Update all fields
		# Ignore date_submitted and last_updated since they are pulled out
		#  as unix timestamps which could confuse the history log and they
		#  shouldn't get updated like this anyway.  If you really need to change
		#  them use bug_set_field()
		db_param_push();
		$t_query = 'UPDATE {bug}
					SET project_id=' . db_param() . ', reporter_id=' . db_param() . ',
						handler_id=' . db_param() . ', duplicate_id=' . db_param() . ',
						priority=' . db_param() . ', severity=' . db_param() . ',
						reproducibility=' . db_param() . ', status=' . db_param() . ',
						resolution=' . db_param() . ', projection=' . db_param() . ',
						category_id=' . db_param() . ', eta=' . db_param() . ',
						os=' . db_param() . ', os_build=' . db_param() . ',
						platform=' . db_param() . ', version=' . db_param() . ',
						build=' . db_param() . ', fixed_in_version=' . db_param() . ',';

		$t_fields = array(
			$t_updated_data->project_id, $t_updated_data->reporter_id,
			$t_updated_data->handler_id, $t_updated_data->duplicate_id,
			$t_updated_data->priority, $t_updated_data->severity,
			$t_updated_data->reproducibility, $t_updated_data->status,
			$t_updated_data->resolution, $t_updated_data->projection,
			$t_updated_data->category_id, $t_updated_data->eta,
			$t_updated_data->os, $t_updated_data->os_build,
			$t_updated_data->platform, $t_updated_data->version,
			$t_updated_data->build, $t_updated_data->fixed_in_version,
		);
		$t_roadmap_updated = false;
		if( access_has_project_level( config_get( 'roadmap_update_threshold' ) ) ) {
			$t_query .= '
						target_version=' . db_param() . ',';
			$t_fields[] = $t_updated_data->target_version;
			$t_roadmap_updated = true;
		}

		$t_query .= '
						view_state=' . db_param() . ',
						summary=' . db_param() . ',
						sponsorship_total=' . db_param() . ',
						sticky=' . db_param() . ',
						due_date=' . db_param() . '
					WHERE id=' . db_param();
		$t_fields[] = $t_updated_data->view_state;
		$t_fields[] = $t_updated_data->summary;
		$t_fields[] = $t_updated_data->sponsorship_total;
		$t_fields[] = (bool)$t_updated_data->sticky;
		$t_fields[] = $t_updated_data->due_date;
		$t_fields[] = $t_updated_data->id;

		db_query( $t_query, $t_fields );

		bug_clear_cache( $t_updated_data->id );

		# log changes
		history_log_event_direct( $c_bug_id, 'project_id', $t_old_data->project_id, $t_updated_data->project_id );
		history_log_event_direct( $c_bug_id, 'reporter_id', $t_old_data->reporter_id, $t_updated_data->reporter_id );
		history_log_event_direct( $c_bug_id, 'handler_id', $t_old_data->handler_id, $t_updated_data->handler_id );
		history_log_event_direct( $c_bug_id, 'priority', $t_old_data->priority, $t_updated_data->priority );
		history_log_event_direct( $c_bug_id, 'severity', $t_old_data->severity, $t_updated_data->severity );
		history_log_event_direct( $c_bug_id, 'reproducibility', $t_old_data->reproducibility, $t_updated_data->reproducibility );
		history_log_event_direct( $c_bug_id, 'status', $t_old_data->status, $t_updated_data->status );
		history_log_event_direct( $c_bug_id, 'resolution', $t_old_data->resolution, $t_updated_data->resolution );
		history_log_event_direct( $c_bug_id, 'projection', $t_old_data->projection, $t_updated_data->projection );
		history_log_event_direct( $c_bug_id, 'category', category_full_name( $t_old_data->category_id, false ), category_full_name( $t_updated_data->category_id, false ) );
		history_log_event_direct( $c_bug_id, 'eta', $t_old_data->eta, $t_updated_data->eta );
		history_log_event_direct( $c_bug_id, 'os', $t_old_data->os, $t_updated_data->os );
		history_log_event_direct( $c_bug_id, 'os_build', $t_old_data->os_build, $t_updated_data->os_build );
		history_log_event_direct( $c_bug_id, 'platform', $t_old_data->platform, $t_updated_data->platform );
		history_log_event_direct( $c_bug_id, 'version', $t_old_data->version, $t_updated_data->version );
		history_log_event_direct( $c_bug_id, 'build', $t_old_data->build, $t_updated_data->build );
		history_log_event_direct( $c_bug_id, 'fixed_in_version', $t_old_data->fixed_in_version, $t_updated_data->fixed_in_version );
		if( $t_roadmap_updated ) {
			history_log_event_direct( $c_bug_id, 'target_version', $t_old_data->target_version, $t_updated_data->target_version );
		}
		history_log_event_direct( $c_bug_id, 'view_state', $t_old_data->view_state, $t_updated_data->view_state );
		history_log_event_direct( $c_bug_id, 'summary', $t_old_data->summary, $t_updated_data->summary );
		history_log_event_direct( $c_bug_id, 'sponsorship_total', $t_old_data->sponsorship_total, $t_updated_data->sponsorship_total );
		history_log_event_direct( $c_bug_id, 'sticky', $t_old_data->sticky, $t_updated_data->sticky );

		history_log_event_direct( $c_bug_id, 'due_date', ( $t_old_data->due_date != date_get_null() ) ? $t_old_data->due_date : null, ( $t_updated_data->due_date != date_get_null() ) ? $t_updated_data->due_date : null );

		# Update extended info if requested
		if( $p_update_extended ) {
			$t_bug_text_id = bug_get_field( $c_bug_id, 'bug_text_id' );

			db_param_push();
			$t_query = 'UPDATE {bug_text}
							SET description=' . db_param() . ',
								steps_to_reproduce=' . db_param() . ',
								additional_information=' . db_param() . '
							WHERE id=' . db_param();
			db_query( $t_query, array(
				$t_updated_data->description,
				$t_updated_data->steps_to_reproduce,
				$t_updated_data->additional_information,
				$t_bug_text_id ) );

			bug_text_clear_cache( $c_bug_id );

			$t_current_user = auth_get_current_user_id();

			if( $t_old_data->description != $t_updated_data->description ) {
				if( bug_revision_count( $c_bug_id, REV_DESCRIPTION ) < 1 ) {
					bug_revision_add( $c_bug_id, $t_old_data->reporter_id, REV_DESCRIPTION, $t_old_data->description, 0, $t_old_data->date_submitted );
				}
				$t_revision_id = bug_revision_add( $c_bug_id, $t_current_user, REV_DESCRIPTION, $t_updated_data->description );
				history_log_event_special( $c_bug_id, DESCRIPTION_UPDATED, $t_revision_id );
			}

			if( $t_old_data->steps_to_reproduce != $t_updated_data->steps_to_reproduce ) {
				if( bug_revision_count( $c_bug_id, REV_STEPS_TO_REPRODUCE ) < 1 ) {
					bug_revision_add( $c_bug_id, $t_old_data->reporter_id, REV_STEPS_TO_REPRODUCE, $t_old_data->steps_to_reproduce, 0, $t_old_data->date_submitted );
				}
				$t_revision_id = bug_revision_add( $c_bug_id, $t_current_user, REV_STEPS_TO_REPRODUCE, $t_updated_data->steps_to_reproduce );
				history_log_event_special( $c_bug_id, STEP_TO_REPRODUCE_UPDATED, $t_revision_id );
			}

			if( $t_old_data->additional_information != $t_updated_data->additional_information ) {
				if( bug_revision_count( $c_bug_id, REV_ADDITIONAL_INFO ) < 1 ) {
					bug_revision_add( $c_bug_id, $t_old_data->reporter_id, REV_ADDITIONAL_INFO, $t_old_data->additional_information, 0, $t_old_data->date_submitted );
				}
				$t_revision_id = bug_revision_add( $c_bug_id, $t_current_user, REV_ADDITIONAL_INFO, $t_updated_data->additional_information );
				history_log_event_special( $c_bug_id, ADDITIONAL_INFO_UPDATED, $t_revision_id );
			}
		}

		# Update the last update date
		bug_update_date( $c_bug_id );

		# allow bypass if user is sending mail separately
		if( false == $p_bypass_mail ) {
			# If handler changes, send out owner change email
			if( $t_old_data->handler_id != $t_updated_data->handler_id ) {
				email_owner_changed( $c_bug_id, $t_old_data->handler_id, $t_updated_data->handler_id );
			} elseif( $t_old_data->status != $t_updated_data->status ) {
				# status changed
				$t_status = MantisEnum::getLabel( config_get( 'status_enum_string' ), $t_updated_data->status );
				$t_status = str_replace( ' ', '_', $t_status );
				email_bug_status_changed( $c_bug_id, $t_status );
			} else {
				# @todo handle priority change if it requires special handling
				email_bug_updated( $c_bug_id );
			}
		}

		# Execute all registered update callbacks
		while( !empty( $t_updated_data->update_callbacks ) ) {
			$t_callback = array_shift( $t_updated_data->update_callbacks );
			call_user_func_array( $t_callback['func'], $t_callback['params'] );
		}

		event_signal( 'EVENT_UPDATE_BUG', array( $t_old_data, $t_updated_data ) );

		return true;
	}

	/**
	 * Add a callable item to the callback queue, which is executed after BugData update.
	 * @param callable	A callable item, in any form of the PHP Callable interface
	 * @param array $p_params	Params to be used with the callable item
	 */
	function add_update_callback( callable $p_callable, array $p_params = null ) {
		if( null === $p_params ) {
			$p_params = array();
		}
		$t_callback = array();
		$t_callback['func'] = $p_callable;
		$t_callback['params'] = $p_params;
		$this->update_callbacks[] = $t_callback;
	}
}

$g_cache_bug = array();
$g_cache_bug_text = array();

/**
 * Cache a database result-set containing full contents of bug_table row.
 * $p_stats parameter is an optional array representing bugnote statistics.
 * This parameter can be "false" if the bug has no bugnotes, so the cache can differentiate
 * from a still not cached stats registry.
 * @param array $p_bug_database_result  Database row containing all columns from mantis_bug_table.
 * @param array|boolean|null $p_stats   Optional: array representing bugnote statistics, or false to store empty cache value
 * @return array returns an array representing the bug row if bug exists
 * @access public
 */
function bug_cache_database_result( array $p_bug_database_result, $p_stats = null ) {
	global $g_cache_bug;

	if( !is_array( $p_bug_database_result ) || isset( $g_cache_bug[(int)$p_bug_database_result['id']] ) ) {
		if( !is_null($p_stats) ) {
			# force store the bugnote statistics
			return bug_add_to_cache( $p_bug_database_result, $p_stats );
		} else {
			return $g_cache_bug[(int)$p_bug_database_result['id']];
		}
	}

	return bug_add_to_cache( $p_bug_database_result, $p_stats );
}

/**
 * Cache a bug row if necessary and return the cached copy
 * @param integer $p_bug_id         Identifier of bug to cache from mantis_bug_table.
 * @param boolean $p_trigger_errors Set to true to trigger an error if the bug does not exist.
 * @return boolean|array returns an array representing the bug row if bug exists or false if bug does not exist
 * @access public
 * @uses database_api.php
 */
function bug_cache_row( $p_bug_id, $p_trigger_errors = true ) {
	global $g_cache_bug;

	if( isset( $g_cache_bug[$p_bug_id] ) ) {
		return $g_cache_bug[$p_bug_id];
	}

	$c_bug_id = (int)$p_bug_id;

	db_param_push();
	$t_query = 'SELECT * FROM {bug} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $c_bug_id ) );

	$t_row = db_fetch_array( $t_result );

	if( !$t_row ) {
		$g_cache_bug[$c_bug_id] = false;

		if( $p_trigger_errors ) {
			error_parameters( $p_bug_id );
			trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
		} else {
			return false;
		}
	}

	return bug_add_to_cache( $t_row );
}

/**
 * Cache a set of bugs
 * @param array $p_bug_id_array Integer array representing bug identifiers to cache.
 * @return void
 * @access public
 * @uses database_api.php
 */
function bug_cache_array_rows( array $p_bug_id_array ) {
	global $g_cache_bug;
	$c_bug_id_array = array();

	foreach( $p_bug_id_array as $t_bug_id ) {
		if( !isset( $g_cache_bug[(int)$t_bug_id] ) ) {
			$c_bug_id_array[] = (int)$t_bug_id;
		}
	}

	if( empty( $c_bug_id_array ) ) {
		return;
	}

	$t_query = 'SELECT * FROM {bug} WHERE id IN (' . implode( ',', $c_bug_id_array ) . ')';
	$t_result = db_query( $t_query );

	while( $t_row = db_fetch_array( $t_result ) ) {
		bug_add_to_cache( $t_row );
	}
	return;
}

/**
 * Inject a bug into the bug cache.
 * $p_stats parameter is an optional array representing bugnote statistics.
 * This parameter can be "false" if the bug has no bugnotes, so the cache can differentiate
 * from a still not cached stats registry.
 * @param array $p_bug_row A bug row to cache.
 * @param array|boolean|null $p_stats   Array of Bugnote stats to cache, false to store empty value, null to skip
 * @return array
 * @access private
 */
function bug_add_to_cache( array $p_bug_row, $p_stats = null ) {
	global $g_cache_bug;

	$g_cache_bug[(int)$p_bug_row['id']] = $p_bug_row;

	if( !is_null( $p_stats ) ) {
		$g_cache_bug[(int)$p_bug_row['id']]['_stats'] = $p_stats;
	}

	return $g_cache_bug[(int)$p_bug_row['id']];
}

/**
 * Clear a bug from the cache or all bugs if no bug id specified.
 * @param integer $p_bug_id A bug identifier to clear (optional).
 * @return boolean
 * @access public
 */
function bug_clear_cache( $p_bug_id = null ) {
	global $g_cache_bug;

	if( null === $p_bug_id ) {
		$g_cache_bug = array();
	} else {
		unset( $g_cache_bug[(int)$p_bug_id] );
	}

	return true;
}

/**
 * Cache a bug text row if necessary and return the cached copy
 * @param integer $p_bug_id         Integer bug id to retrieve text for.
 * @param boolean $p_trigger_errors If the second parameter is true (default), trigger an error if bug text not found.
 * @return boolean|array returns false if not bug text found or array of bug text
 * @access public
 * @uses database_api.php
 */
function bug_text_cache_row( $p_bug_id, $p_trigger_errors = true ) {
	global $g_cache_bug_text;

	$c_bug_id = (int)$p_bug_id;

	if( isset( $g_cache_bug_text[$c_bug_id] ) ) {
		return $g_cache_bug_text[$c_bug_id];
	}

	db_param_push();
	$t_query = 'SELECT bt.* FROM {bug_text} bt, {bug} b
				  WHERE b.id=' . db_param() . ' AND b.bug_text_id = bt.id';
	$t_result = db_query( $t_query, array( $c_bug_id ) );

	$t_row = db_fetch_array( $t_result );

	if( !$t_row ) {
		$g_cache_bug_text[$c_bug_id] = false;

		if( $p_trigger_errors ) {
			error_parameters( $p_bug_id );
			trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
		} else {
			return false;
		}
	}

	$g_cache_bug_text[$c_bug_id] = $t_row;

	return $t_row;
}

/**
 * Clear a bug's bug text from the cache or all bug text if no bug id specified.
 * @param integer $p_bug_id A bug identifier to clear (optional).
 * @return boolean
 * @access public
 */
function bug_text_clear_cache( $p_bug_id = null ) {
	global $g_cache_bug_text;

	if( null === $p_bug_id ) {
		$g_cache_bug_text = array();
	} else {
		unset( $g_cache_bug_text[(int)$p_bug_id] );
	}

	return true;
}

/**
 * Check if a bug exists
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return boolean true if bug exists, false otherwise
 * @access public
 */
function bug_exists( $p_bug_id ) {
	if( false == bug_cache_row( $p_bug_id, false ) ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Check if a bug exists. If it doesn't then trigger an error
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return void
 * @access public
 */
function bug_ensure_exists( $p_bug_id ) {
	if( !bug_exists( $p_bug_id ) ) {
		error_parameters( $p_bug_id );
		trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
	}
}

/**
 * check if the given user is the reporter of the bug
 * @param integer $p_bug_id  Integer representing bug identifier.
 * @param integer $p_user_id Integer representing a user identifier.
 * @return boolean return true if the user is the reporter, false otherwise
 * @access public
 */
function bug_is_user_reporter( $p_bug_id, $p_user_id ) {
	if( bug_get_field( $p_bug_id, 'reporter_id' ) == $p_user_id ) {
		return true;
	} else {
		return false;
	}
}

/**
 * check if the given user is the handler of the bug
 * @param integer $p_bug_id  Integer representing bug identifier.
 * @param integer $p_user_id Integer representing a user identifier.
 * @return boolean return true if the user is the handler, false otherwise
 * @access public
 */
function bug_is_user_handler( $p_bug_id, $p_user_id ) {
	if( bug_get_field( $p_bug_id, 'handler_id' ) == $p_user_id ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if the bug is readonly and shouldn't be modified
 * For a bug to be readonly the status has to be >= bug_readonly_status_threshold and
 * current user access level < update_readonly_bug_threshold.
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return boolean
 * @access public
 * @uses access_api.php
 * @uses config_api.php
 */
function bug_is_readonly( $p_bug_id ) {
	$t_status = bug_get_field( $p_bug_id, 'status' );
	if( $t_status < config_get( 'bug_readonly_status_threshold' ) ) {
		return false;
	}

	if( access_has_bug_level( config_get( 'update_readonly_bug_threshold' ), $p_bug_id ) ) {
		return false;
	}

	return true;
}

/**
 * Check if a given bug is resolved
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return boolean true if bug is resolved, false otherwise
 * @access public
 * @uses config_api.php
 */
function bug_is_resolved( $p_bug_id ) {
	$t_bug = bug_get( $p_bug_id );
	return( $t_bug->status >= config_get( 'bug_resolved_status_threshold', null, null, $t_bug->project_id ) );
}

/**
 * Check if a given bug is closed
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return boolean true if bug is closed, false otherwise
 * @access public
 * @uses config_api.php
 */
function bug_is_closed( $p_bug_id ) {
	$t_bug = bug_get( $p_bug_id );
	return( $t_bug->status >= config_get( 'bug_closed_status_threshold', null, null, $t_bug->project_id ) );
}

/**
 * Check if a given bug is overdue
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return boolean true if bug is overdue, false otherwise
 * @access public
 * @uses database_api.php
 */
function bug_is_overdue( $p_bug_id ) {
	$t_due_date = bug_get_field( $p_bug_id, 'due_date' );
	if( !date_is_null( $t_due_date ) ) {
		$t_now = db_now();
		if( $t_now > $t_due_date ) {
			if( !bug_is_resolved( $p_bug_id ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Validate workflow state to see if bug can be moved to requested state
 * @param integer $p_bug_status    Current bug status.
 * @param integer $p_wanted_status New bug status.
 * @return boolean
 * @access public
 * @uses config_api.php
 * @uses utility_api.php
 */
function bug_check_workflow( $p_bug_status, $p_wanted_status ) {
	$t_status_enum_workflow = config_get( 'status_enum_workflow' );

	if( count( $t_status_enum_workflow ) < 1 ) {
		# workflow not defined, use default enum
		return true;
	}

	if( $p_bug_status == $p_wanted_status ) {
		# no change in state, allow the transition
		return true;
	}

	# There should always be a possible next status, if not defined, then allow all.
	if( !isset( $t_status_enum_workflow[$p_bug_status] ) ) {
		return true;
	}

	# workflow defined - find allowed states
	$t_allowed_states = $t_status_enum_workflow[$p_bug_status];

	return MantisEnum::hasValue( $t_allowed_states, $p_wanted_status );
}

/**
 * Copy a bug from one project to another. Also make copies of issue notes, attachments, history,
 * email notifications etc.
 * @param integer $p_bug_id                A bug identifier.
 * @param integer $p_target_project_id     A target project identifier.
 * @param boolean $p_copy_custom_fields    Whether to copy custom fields.
 * @param boolean $p_copy_relationships    Whether to copy relationships.
 * @param boolean $p_copy_history          Whether to copy history.
 * @param boolean $p_copy_attachments      Whether to copy attachments.
 * @param boolean $p_copy_bugnotes         Whether to copy bugnotes.
 * @param boolean $p_copy_monitoring_users Whether to copy monitoring users.
 * @return integer representing the new bug identifier
 * @access public
 */
function bug_copy( $p_bug_id, $p_target_project_id = null, $p_copy_custom_fields = false, $p_copy_relationships = false, $p_copy_history = false, $p_copy_attachments = false, $p_copy_bugnotes = false, $p_copy_monitoring_users = false ) {
	global $g_db;

	$t_bug_id = (int)$p_bug_id;
	$t_target_project_id = (int)$p_target_project_id;

	$t_bug_data = bug_get( $t_bug_id, true );

	# retrieve the project id associated with the bug
	if( ( $p_target_project_id == null ) || is_blank( $p_target_project_id ) ) {
		$t_target_project_id = $t_bug_data->project_id;
	}

	$t_bug_data->project_id = $t_target_project_id;
	$t_bug_data->reporter_id = auth_get_current_user_id();
	$t_bug_data->date_submitted = db_now();
	$t_bug_data->last_updated = db_now();

	$t_new_bug_id = $t_bug_data->create();

	# MASC ATTENTION: IF THE SOURCE BUG HAS TO HANDLER THE bug_create FUNCTION CAN TRY TO AUTO-ASSIGN THE BUG
	# WE FORCE HERE TO DUPLICATE THE SAME HANDLER OF THE SOURCE BUG
	# @todo VB: Shouldn't we check if the handler in the source project is also a handler in the destination project?
	bug_set_field( $t_new_bug_id, 'handler_id', $t_bug_data->handler_id );

	bug_set_field( $t_new_bug_id, 'duplicate_id', $t_bug_data->duplicate_id );
	bug_set_field( $t_new_bug_id, 'status', $t_bug_data->status );
	bug_set_field( $t_new_bug_id, 'resolution', $t_bug_data->resolution );
	bug_set_field( $t_new_bug_id, 'projection', $t_bug_data->projection );
	bug_set_field( $t_new_bug_id, 'eta', $t_bug_data->eta );
	bug_set_field( $t_new_bug_id, 'fixed_in_version', $t_bug_data->fixed_in_version );
	bug_set_field( $t_new_bug_id, 'target_version', $t_bug_data->target_version );
	bug_set_field( $t_new_bug_id, 'sponsorship_total', 0 );
	bug_set_field( $t_new_bug_id, 'sticky', 0 );
	bug_set_field( $t_new_bug_id, 'due_date', $t_bug_data->due_date );

	# COPY CUSTOM FIELDS
	if( $p_copy_custom_fields ) {
		db_param_push();
		$t_query = 'SELECT field_id, bug_id, value, text FROM {custom_field_string} WHERE bug_id=' . db_param();
		$t_result = db_query( $t_query, array( $t_bug_id ) );

		while( $t_bug_custom = db_fetch_array( $t_result ) ) {
			$c_field_id = (int)$t_bug_custom['field_id'];
			$c_new_bug_id = (int)$t_new_bug_id;
			$c_value = $t_bug_custom['value'];
			$c_text = $t_bug_custom['text'];

			db_param_push();
			$t_query = 'INSERT INTO {custom_field_string}
						   ( field_id, bug_id, value, text )
						   VALUES (' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ')';
			db_query( $t_query, array( $c_field_id, $c_new_bug_id, $c_value, $c_text ) );
		}
	}

	# Copy Relationships
	if( $p_copy_relationships ) {
		relationship_copy_all( $t_bug_id, $t_new_bug_id );
	}

	# Copy bugnotes
	if( $p_copy_bugnotes ) {
		db_param_push();
		$t_query = 'SELECT * FROM {bugnote} WHERE bug_id=' . db_param();
		$t_result = db_query( $t_query, array( $t_bug_id ) );

		while( $t_bug_note = db_fetch_array( $t_result ) ) {
			$t_bugnote_text_id = $t_bug_note['bugnote_text_id'];

			db_param_push();
			$t_query2 = 'SELECT * FROM {bugnote_text} WHERE id=' . db_param();
			$t_result2 = db_query( $t_query2, array( $t_bugnote_text_id ) );

			$t_bugnote_text_insert_id = -1;
			if( $t_bugnote_text = db_fetch_array( $t_result2 ) ) {
				db_param_push();
				$t_query2 = 'INSERT INTO {bugnote_text}
							   ( note )
							   VALUES ( ' . db_param() . ' )';
				db_query( $t_query2, array( $t_bugnote_text['note'] ) );
				$t_bugnote_text_insert_id = db_insert_id( db_get_table( 'bugnote_text' ) );
			}

			db_param_push();
			$t_query2 = 'INSERT INTO {bugnote}
						   ( bug_id, reporter_id, bugnote_text_id, view_state, date_submitted, last_modified )
						   VALUES ( ' . db_param() . ',
						   			' . db_param() . ',
						   			' . db_param() . ',
						   			' . db_param() . ',
						   			' . db_param() . ',
						   			' . db_param() . ')';
			db_query( $t_query2, array( $t_new_bug_id, $t_bug_note['reporter_id'], $t_bugnote_text_insert_id, $t_bug_note['view_state'], $t_bug_note['date_submitted'], $t_bug_note['last_modified'] ) );
		}
	}

	# Copy attachments
	if( $p_copy_attachments ) {
	    file_copy_attachments( $t_bug_id, $t_new_bug_id );
	}

	# Copy users monitoring bug
	if( $p_copy_monitoring_users ) {
		bug_monitor_copy( $t_bug_id, $t_new_bug_id );
	}

	# COPY HISTORY
	history_delete( $t_new_bug_id );	# should history only be deleted inside the if statement below?
	if( $p_copy_history ) {
		# @todo problem with this code: the generated history trail is incorrect because the note IDs are those of the original bug, not the copied ones
		# @todo actually, does it even make sense to copy the history ?
		db_param_push();
		$t_query = 'SELECT * FROM {bug_history} WHERE bug_id = ' . db_param();
		$t_result = db_query( $t_query, array( $t_bug_id ) );

		while( $t_bug_history = db_fetch_array( $t_result ) ) {
			db_param_push();
			$t_query = 'INSERT INTO {bug_history}
						  ( user_id, bug_id, date_modified, field_name, old_value, new_value, type )
						  VALUES ( ' . db_param() . ',' . db_param() . ',' . db_param() . ',
						  		   ' . db_param() . ',' . db_param() . ',' . db_param() . ',
						  		   ' . db_param() . ' );';
			db_query( $t_query, array( $t_bug_history['user_id'], $t_new_bug_id, $t_bug_history['date_modified'], $t_bug_history['field_name'], $t_bug_history['old_value'], $t_bug_history['new_value'], $t_bug_history['type'] ) );
		}
	} else {
		# Create a "New Issue" history entry
		history_log_event_special( $t_new_bug_id, NEW_BUG );
	}

	# Create history entries to reflect the copy operation
	history_log_event_special( $t_new_bug_id, BUG_CREATED_FROM, '', $t_bug_id );
	history_log_event_special( $t_bug_id, BUG_CLONED_TO, '', $t_new_bug_id );

	return $t_new_bug_id;
}

/**
 * Moves an issue from a project to another.
 *
 * @todo Validate with sub-project / category inheritance scenarios.
 * @param integer $p_bug_id            The bug to be moved.
 * @param integer $p_target_project_id The target project to move the bug to.
 * @return void
 * @access public
 */
function bug_move( $p_bug_id, $p_target_project_id ) {
	$t_bugdata = bug_get( $p_bug_id );
	$t_bugdata->project_id = $p_target_project_id;
	$t_category_id = $t_bugdata->category_id;

	# Bug has no category
	if( $t_category_id == 0 ) {
		# Category is required in target project, set it to default
		if( ON != config_get( 'allow_no_category', null, null, $p_target_project_id ) ) {
			$t_bugdata->category_id = config_get( 'default_category_for_moves', null, null, $p_target_project_id );
		}
	} else {
		# Check if the category is global, and if not attempt mapping it to the new project
		$t_category_project_id = category_get_field( $t_category_id, 'project_id' );

		if( $t_category_project_id != ALL_PROJECTS
		  && !in_array( $t_category_project_id, project_hierarchy_inheritance( $p_target_project_id ) )
		) {
			# Map by name
			$t_category_name = category_get_field( $t_category_id, 'name' );
			$t_target_project_category_id = category_get_id_by_name( $t_category_name, $p_target_project_id, false );
			if( $t_target_project_category_id === false ) {
				# Use target project's default category for moves, since there is no match by name.
				$t_target_project_category_id = config_get( 'default_category_for_moves', null, null, $p_target_project_id );
			}
			$t_bugdata->category_id = $t_target_project_category_id;
		}
	}

	# Attempt to move disk based attachments to new project file directory.
	$t_bugdata->add_update_callback( file_move_bug_attachments, array( $t_bugdata->id, $t_bugdata->project_id ) );

	# @TODO email is bypassed, add a notification for MOVE
	$t_bugdata->update( /* update extended */ false, /* bypass mail */ true );
}

/**
 * allows bug deletion :
 * delete the bug, bugtext, bugnote, and bugtexts selected
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return void
 * @access public
 */
function bug_delete( $p_bug_id ) {
	$c_bug_id = (int)$p_bug_id;

	# call pre-deletion custom function
	helper_call_custom_function( 'issue_delete_validate', array( $p_bug_id ) );

	# log deletion of bug
	history_log_event_special( $p_bug_id, BUG_DELETED, bug_format_id( $p_bug_id ) );

	email_bug_deleted( $p_bug_id );

	# call post-deletion custom function.  We call this here to allow the custom function to access the details of the bug before
	# they are deleted from the database given it's id.  The other option would be to move this to the end of the function and
	# provide it with bug data rather than an id, but this will break backward compatibility.
	helper_call_custom_function( 'issue_delete_notify', array( $p_bug_id ) );

	# Unmonitor bug for all users
	bug_unmonitor( $p_bug_id, null );

	# Delete custom fields
	custom_field_delete_all_values( $p_bug_id );

	# Delete bugnotes
	bugnote_delete_all( $p_bug_id );

	# Delete all sponsorships
	sponsorship_delete_all( $p_bug_id );

	# MASC RELATIONSHIP
	# we delete relationships even if the feature is currently off.
	relationship_delete_all( $p_bug_id );

	# MASC RELATIONSHIP
	# Delete files
	file_delete_attachments( $p_bug_id );

	# Detach tags
	tag_bug_detach_all( $p_bug_id, false );

	# Delete the bug history
	history_delete( $p_bug_id );

	# Delete bug info revisions
	bug_revision_delete( $p_bug_id );

	# Delete the bugnote text
	$t_bug_text_id = bug_get_field( $p_bug_id, 'bug_text_id' );

	db_param_push();
	$t_query = 'DELETE FROM {bug_text} WHERE id=' . db_param();
	db_query( $t_query, array( $t_bug_text_id ) );

	# Delete the bug entry
	db_param_push();
	$t_query = 'DELETE FROM {bug} WHERE id=' . db_param();
	db_query( $t_query, array( $c_bug_id ) );

	bug_clear_cache( $p_bug_id );
	bug_text_clear_cache( $p_bug_id );
}

/**
 * Delete all bugs associated with a project
 * @param integer $p_project_id Integer representing a project identifier.
 * @access public
 * @uses database_api.php
 * @return void
 */
function bug_delete_all( $p_project_id ) {
	$c_project_id = (int)$p_project_id;

	db_param_push();
	$t_query = 'SELECT id FROM {bug} WHERE project_id=' . db_param();
	$t_result = db_query( $t_query, array( $c_project_id ) );

	while( $t_row = db_fetch_array( $t_result ) ) {
		bug_delete( $t_row['id'] );
	}

	# @todo should we check the return value of each bug_delete() and
	#  return false if any of them return false? Presumable bug_delete()
	#  will eventually trigger an error on failure so it won't matter...
}

/**
 * Returns the extended record of the specified bug, this includes
 * the bug text fields
 * @todo include reporter name and handler name, the problem is that
 *      handler can be 0, in this case no corresponding name will be
 *      found.  Use equivalent of (+) in Oracle.
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return array
 * @access public
 */
function bug_get_extended_row( $p_bug_id ) {
	$t_base = bug_cache_row( $p_bug_id );
	$t_text = bug_text_cache_row( $p_bug_id );

	# merge $t_text first so that the 'id' key has the bug id not the bug text id
	return array_merge( $t_text, $t_base );
}

/**
 * Returns the record of the specified bug
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return array
 * @access public
 */
function bug_get_row( $p_bug_id ) {
	return bug_cache_row( $p_bug_id );
}

/**
 * Returns an object representing the specified bug
 * @param integer $p_bug_id       Integer representing bug identifier.
 * @param boolean $p_get_extended Whether to include extended information (including bug_text).
 * @return BugData BugData Object
 * @access public
 */
function bug_get( $p_bug_id, $p_get_extended = false ) {
	if( $p_get_extended ) {
		$t_row = bug_get_extended_row( $p_bug_id );
	} else {
		$t_row = bug_get_row( $p_bug_id );
	}

	$t_bug_data = new BugData;
	$t_bug_data->loadrow( $t_row );
	return $t_bug_data;
}

/**
 * Convert row [from database] to bug object
 * @param array $p_row Bug database row.
 * @return BugData
 */
function bug_row_to_object( array $p_row ) {
	$t_bug_data = new BugData;
	$t_bug_data->loadrow( $p_row );
	return $t_bug_data;
}

/**
 * return the specified field of the given bug
 *  if the field does not exist, display a warning and return ''
 * @param integer $p_bug_id     Integer representing bug identifier.
 * @param string  $p_field_name Field name to retrieve.
 * @return string
 * @access public
 */
function bug_get_field( $p_bug_id, $p_field_name ) {
	$t_row = bug_get_row( $p_bug_id );

	if( isset( $t_row[$p_field_name] ) ) {
		return $t_row[$p_field_name];
	} else {
		error_parameters( $p_field_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}
}

/**
 * return the specified text field of the given bug
 *  if the field does not exist, display a warning and return ''
 * @param integer $p_bug_id     Integer representing bug identifier.
 * @param string  $p_field_name Field name to retrieve.
 * @return string
 * @access public
 */
function bug_get_text_field( $p_bug_id, $p_field_name ) {
	$t_row = bug_text_cache_row( $p_bug_id );

	if( isset( $t_row[$p_field_name] ) ) {
		return $t_row[$p_field_name];
	} else {
		error_parameters( $p_field_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}
}

/**
 * return the bug summary
 *  this is a wrapper for the custom function
 * @param integer $p_bug_id  Integer representing bug identifier.
 * @param integer $p_context Representing SUMMARY_CAPTION, SUMMARY_FIELD.
 * @return string
 * @access public
 * @uses helper_api.php
 */
function bug_format_summary( $p_bug_id, $p_context ) {
	return helper_call_custom_function( 'format_issue_summary', array( $p_bug_id, $p_context ) );
}

/**
 * return the timestamp for the most recent time at which a bugnote
 *  associated with the bug was modified
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return boolean|integer false or timestamp in integer format representing newest bugnote timestamp
 * @access public
 * @uses database_api.php
 */
function bug_get_newest_bugnote_timestamp( $p_bug_id ) {
	$c_bug_id = (int)$p_bug_id;

	db_param_push();
	$t_query = 'SELECT last_modified FROM {bugnote} WHERE bug_id=' . db_param() . ' ORDER BY last_modified DESC';
	$t_result = db_query( $t_query, array( $c_bug_id ), 1 );
	$t_row = db_result( $t_result );

	if( false === $t_row ) {
		return false;
	} else {
		return $t_row;
	}
}

/**
 * For a list of bug ids, returns an array of bugnote stats.
 * If a bug has no visible bugnotes, returns "false" as the stats item for that bug id.
 * @param array $p_bugs_id         Array of Integer representing bug identifiers.
 * @param integer|null $p_user_id  User for checking access levels. null defaults to current user
 * @return array                   Array of bugnote stats
 * @access public
 * @uses database_api.php
 */
function bug_get_bugnote_stats_array( array $p_bugs_id, $p_user_id = null ) {
	$t_id_array = array();
	foreach( $p_bugs_id as $t_id ) {
		$t_id_array[$t_id] = (int)$t_id;
	}
	if( empty( $t_id_array ) ) {
		return array();
	}

	if ( null === $p_user_id ) {
		$t_user_id = auth_get_current_user_id();
	}
	else {
		$t_user_id = $p_user_id;
	}

	db_param_push();
	$t_params = array();
	$t_in_clause_elems = array();
	foreach( $t_id_array as $t_id ) {
		$t_in_clause_elems[] = db_param();
		$t_params[] = $t_id;
	}
	$t_query = 'SELECT n.id, n.bug_id, n.reporter_id, n.view_state, n.last_modified, n.date_submitted, b.project_id'
		. ' FROM {bugnote} n JOIN {bug} b ON (n.bug_id = b.id)'
		. ' WHERE n.bug_id IN (' . implode( ', ', $t_in_clause_elems ) . ')'
		. ' ORDER BY b.project_id, n.bug_id, n.last_modified';
	# perform query
	$t_result = db_query( $t_query, $t_params );
	$t_counter = 0;
	$t_stats = array();
	# We need to check for each bugnote if it has permissions to view in respective project.
	# bugnotes are grouped by project_id and bug_id to save calls to config_get
	$t_current_project_id = null;
	$t_current_bug_id = null;
	while( $t_query_row = db_fetch_array( $t_result ) ) {
		$c_bug_id = (int)$t_query_row['bug_id'];
		if( 0 == $t_counter || $t_current_project_id !== $t_query_row['project_id'] ) {
			# evaluating a new project from the rowset
			$t_current_project_id = $t_query_row['project_id'];
			$t_user_access_level = access_get_project_level( $t_query_row['project_id'], $t_user_id );
			$t_private_bugnote_visible = access_compare_level(
					$t_user_access_level,
					config_get( 'private_bugnote_threshold', null, $t_user_id, $t_query_row['project_id'] )
					);
		}
		if( 0 == $t_counter || $t_current_bug_id !== $c_bug_id ) {
			# evaluating a new bug from the rowset
			$t_current_bug_id = $c_bug_id;
			$t_note_count = 0;
			$t_last_submit_date= 0;
		}
		$t_note_visible = $t_private_bugnote_visible
				|| $t_query_row['reporter_id'] == $t_user_id
				|| ( VS_PUBLIC == $t_query_row['view_state'] );
		if( $t_note_visible ) {
			# only count the bugnote if user has access
			$t_stats[$c_bug_id]['bug_id'] = $c_bug_id;
			$t_stats[$c_bug_id]['last_modified'] = $t_query_row['last_modified'];
			$t_stats[$c_bug_id]['count'] = ++$t_note_count;
			$t_stats[$c_bug_id]['last_modified_bugnote'] = $t_query_row['id'];
			if( $t_query_row['date_submitted'] > $t_last_submit_date ) {
				$t_last_submit_date = $t_query_row['date_submitted'];
				$t_stats[$c_bug_id]['last_submitted_bugnote'] = $t_query_row['id'];
			}
			if( isset( $t_id_array[$c_bug_id] ) ) {
				unset( $t_id_array[$c_bug_id] );
			}
		}
		$t_counter++;
	}

	# The remaining bug ids, are those without visible notes. Save false as cached value
	foreach( $t_id_array as $t_id ) {
		$t_stats[$t_id] = false;
	}
	return $t_stats;
}

/**
 * return the timestamp for the most recent time at which a bugnote
 * associated with the bug was modified and the total bugnote
 * count in one db query
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return object consisting of bugnote stats
 * @access public
 * @uses database_api.php
 */
function bug_get_bugnote_stats( $p_bug_id ) {
	global $g_cache_bug;
	$c_bug_id = (int)$p_bug_id;

	if( array_key_exists( '_stats', $g_cache_bug[$c_bug_id] ) ) {
		return $g_cache_bug[$c_bug_id]['_stats'];
	}
	else {
		$t_stats = bug_get_bugnote_stats_array( array( $p_bug_id ) );
		return $t_stats[$p_bug_id];
	}
}

/**
 * Get array of attachments associated with the specified bug id.  The array will be
 * sorted in terms of date added (ASC).  The array will include the following fields:
 * id, title, diskfile, filename, filesize, file_type, date_added, user_id.
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return array array of results or empty array
 * @access public
 * @uses database_api.php
 * @uses file_api.php
 */
function bug_get_attachments( $p_bug_id ) {
	db_param_push();
	$t_query = 'SELECT id, title, diskfile, filename, filesize, file_type, date_added, user_id
		                FROM {bug_file}
		                WHERE bug_id=' . db_param() . '
		                ORDER BY date_added';
	$t_db_result = db_query( $t_query, array( $p_bug_id ) );

	$t_result = array();

	while( $t_row = db_fetch_array( $t_db_result ) ) {
		$t_result[] = $t_row;
	}

	return $t_result;
}

/**
 * Set the value of a bug field
 * @param integer                $p_bug_id     Integer representing bug identifier.
 * @param string                 $p_field_name Pre-defined field name.
 * @param boolean|integer|string $p_value      Value to set.
 * @return boolean (always true)
 * @access public
 * @uses database_api.php
 * @uses history_api.php
 */
function bug_set_field( $p_bug_id, $p_field_name, $p_value ) {
	$c_bug_id = (int)$p_bug_id;
	$c_value = null;

	switch( $p_field_name ) {
		# boolean
		case 'sticky':
			$c_value = $p_value;
			break;

		# integer
		case 'project_id':
		case 'reporter_id':
		case 'handler_id':
		case 'duplicate_id':
		case 'priority':
		case 'severity':
		case 'reproducibility':
		case 'status':
		case 'resolution':
		case 'projection':
		case 'category_id':
		case 'eta':
		case 'view_state':
		case 'profile_id':
		case 'sponsorship_total':
			$c_value = (int)$p_value;
			break;

		# string
		case 'os':
		case 'os_build':
		case 'platform':
		case 'version':
		case 'fixed_in_version':
		case 'target_version':
		case 'build':
		case 'summary':
			$c_value = $p_value;
			break;

		# dates
		case 'last_updated':
		case 'date_submitted':
		case 'due_date':
			if( !is_numeric( $p_value ) ) {
				trigger_error( ERROR_GENERIC, ERROR );
			}
			$c_value = $p_value;
			break;

		default:
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			break;
	}

	$t_current_value = bug_get_field( $p_bug_id, $p_field_name );

	# return if status is already set
	if( $c_value == $t_current_value ) {
		return true;
	}

	# Update fields
	db_param_push();
	$t_query = 'UPDATE {bug} SET ' . $p_field_name . '=' . db_param() . ' WHERE id=' . db_param();
	db_query( $t_query, array( $c_value, $c_bug_id ) );

	# updated the last_updated date
	if( $p_field_name != 'last_updated' ) {
		bug_update_date( $p_bug_id );
	}

	# log changes except for duplicate_id which is obsolete and should be removed in
	# MantisBT 1.3.
	switch( $p_field_name ) {
		case 'duplicate_id':
			break;

		case 'category_id':
			history_log_event_direct( $p_bug_id, 'category', category_full_name( $t_current_value, false ), category_full_name( $c_value, false ) );
			break;

		default:
			history_log_event_direct( $p_bug_id, $p_field_name, $t_current_value, $c_value );
	}

	bug_clear_cache( $p_bug_id );

	return true;
}

/**
 * assign the bug to the given user
 * @param integer $p_bug_id          A bug identifier.
 * @param integer $p_user_id         A user identifier.
 * @param string  $p_bugnote_text    The bugnote text.
 * @param boolean $p_bugnote_private Indicate whether bugnote is private.
 * @return boolean
 * @access public
 * @uses database_api.php
 */
function bug_assign( $p_bug_id, $p_user_id, $p_bugnote_text = '', $p_bugnote_private = false ) {
	if( ( $p_user_id != NO_USER ) && !access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug_id, $p_user_id ) ) {
		trigger_error( ERROR_USER_DOES_NOT_HAVE_REQ_ACCESS );
	}
	$t_bugdata = bug_get( $p_bug_id );
	$t_original_handler_id = $t_bugdata->handler_id;
	$t_original_status = $t_bugdata->status;
	$t_bugdata->handler_id = $p_user_id;

	$t_assigned_status = bug_get_status_for_assign( $t_original_handler_id, $t_bugdata->handler_id, $t_original_status );
	$t_bugdata->status = $t_assigned_status;

	# callback for history logging:
	$t_bugdata->add_update_callback( 'history_log_event_direct', array( $t_bugdata->id, 'status', $t_original_status, $t_bugdata->status ) );
	$t_bugdata->add_update_callback( 'history_log_event_direct', array( $t_bugdata->id, 'handler_id', $t_original_handler_id, $t_bugdata->handler_id ) );

	# callback for note add
	if( !empty($p_bugnote_text) ) {
		$add_bugnote_func = function( array $p_bugnote_params ) {
			$t_bugnote_id = call_user_func_array( 'bugnote_add', $p_bugnote_params );
			bugnote_process_mentions( $p_bugnote_params[0], $t_bugnote_id, $p_bugnote_params[1] );
		};
		$t_bugnote_params = array( $t_bugdata->id, $p_bugnote_text, 0, $p_bugnote_private, BUGNOTE, '', null, false );
		$t_bugdata->add_update_callback( $add_bugnote_func, array( $t_bugnote_params ) );
	}

	$t_bugdata->update( /* update extended */ false, /* bypass mail */ true );

	if( $t_original_handler_id != $t_bugdata->handler_id) {
		# Send email for change of handler
		email_owner_changed( $t_bugdata->id, $t_original_handler_id, $t_bugdata->handler_id );
	}

	return true;
}

/**
 * close the given bug
 * @param integer $p_bug_id          A bug identifier.
 * @param string  $p_bugnote_text    The bugnote text.
 * @param boolean $p_bugnote_private Whether the bugnote is private.
 * @param string  $p_time_tracking   Time tracking value.
 * @return boolean (always true)
 * @access public
 */
function bug_close( $p_bug_id, $p_bugnote_text = '', $p_bugnote_private = false, $p_time_tracking = '0:00' ) {
	$p_bugnote_text = trim( $p_bugnote_text );

	# @TODO
	# If time tracking is enabled, this code is executed outside of BugData validation.
	# These time tracking dependencies should be removed
	$t_time_tracking_enabled = config_get( 'time_tracking_enabled' );
	if( ON == $t_time_tracking_enabled ) {
		# Add bugnote if supplied ignore a false return
		# Moved bugnote_add before bug_set_field calls in case time_tracking_no_note is off.
		# Error condition stopped execution but status had already been changed
		$t_bugnote_id = bugnote_add( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, BUGNOTE, '', null, false );
		bugnote_process_mentions( $p_bug_id, $t_bugnote_id, $p_bugnote_text );
	}

	$t_bugdata = bug_get( $p_bug_id );
	$t_bugdata->status = config_get( 'bug_closed_status_threshold' );

	# If time tracking is disabled, the bug note is added as a callback after validation
	# This is the proper way.
	if( OFF == $t_time_tracking_enabled ) {
		$add_bugnote_func = function( array $p_bugnote_params ) {
			$t_bugnote_id = call_user_func_array( 'bugnote_add', $p_bugnote_params );
			bugnote_process_mentions( $p_bugnote_params[0], $t_bugnote_id, $p_bugnote_params[1] );
		};
		$t_bugnote_params = array( $t_bugdata->id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, BUGNOTE, '', null, false );
		$t_bugdata->add_update_callback( $add_bugnote_func, array( $t_bugnote_params ) );
	}

	$t_bugdata->update( /* update extended */ false, /* bypass mail */ true );

	email_close( $p_bug_id );
	email_relationship_child_closed( $p_bug_id );

	return true;
}

/**
 * resolve the given bug
 * @param integer $p_bug_id           A bug identifier.
 * @param integer $p_resolution       Resolution status.
 * @param string  $p_fixed_in_version Fixed in version.
 * @param string  $p_bugnote_text     The bugnote text.
 * @param integer $p_duplicate_id     A duplicate identifier.
 * @param integer $p_handler_id       A handler identifier.
 * @param boolean $p_bugnote_private  Whether this is a private bugnote.
 * @param string  $p_time_tracking    Time tracking value.
 * @access public
 * @return boolean
 */
function bug_resolve( $p_bug_id, $p_resolution, $p_fixed_in_version = '', $p_bugnote_text = '', $p_duplicate_id = null, $p_handler_id = null, $p_bugnote_private = false, $p_time_tracking = '0:00' ) {
	$c_resolution = (int)$p_resolution;
	$p_bugnote_text = trim( $p_bugnote_text );

	# @TODO
	# If time tracking is enabled, this code is executed outside of BugData validation.
	# These time tracking dependencies should be removed
	$t_time_tracking_enabled = config_get( 'time_tracking_enabled' );
	if( ON == $t_time_tracking_enabled ) {
		# Add bugnote if supplied
		# Moved bugnote_add before bug_set_field calls in case time_tracking_no_note is off.
		# Error condition stopped execution but status had already been changed
		$t_bugnote_id = bugnote_add( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, BUGNOTE, '', null, false );
		bugnote_process_mentions( $p_bug_id, $t_bugnote_id, $p_bugnote_text );
	}

	$t_bugdata = bug_get( $p_bug_id );

	$t_bugdata->status = config_get( 'bug_resolved_status_threshold' );
	$t_bugdata->fixed_in_version = $p_fixed_in_version;
	$t_bugdata->resolution = $c_resolution;

	$t_old_handler_id = $t_bugdata->handler_id;
	# only set handler if specified explicitly or if bug was not assigned to a handler
	if( null == $p_handler_id ) {
		if( $t_bugdata->handler_id == 0 ) {
			$t_bugdata->handler_id = auth_get_current_user_id();
		}
	} else {
		$t_bugdata->handler_id = $p_handler_id;
	}

	# If time tracking is disabled, the bug note is added as a callback after validation
	# This is the proper way.
	if( OFF == $t_time_tracking_enabled ) {
		$add_bugnote_func = function( array $p_bugnote_params ) {
			$t_bugnote_id = call_user_func_array( 'bugnote_add', $p_bugnote_params );
			bugnote_process_mentions( $p_bugnote_params[0], $t_bugnote_id, $p_bugnote_params[1] );
		};
		$t_bugnote_params = array( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, BUGNOTE, '', null, false );
		$t_bugdata->add_update_callback( $add_bugnote_func, array( $t_bugnote_params ) );
	}

	$t_duplicate = !is_blank( $p_duplicate_id ) && ( $p_duplicate_id != 0 );
	if( $t_duplicate ) {
		if( $p_bug_id == $p_duplicate_id ) {
			trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );
			# never returns
		}
		# the related bug exists...
		bug_ensure_exists( $p_duplicate_id );
		$t_bugdata->duplicate_id = $p_duplicate_id;

		# create callbacks for duplicate bug actions:

		# check if there is other relationship between the bugs...
		$t_id_relationship = relationship_same_type_exists( $p_bug_id, $p_duplicate_id, BUG_DUPLICATE );

		if( $t_id_relationship > 0 ) {
			# Update the relationship
			$t_bugdata->add_update_callback( 'relationship_update', array( $t_id_relationship, $p_bug_id, $p_duplicate_id, BUG_DUPLICATE ) );
			# Add log line to the history (both bugs)
			$t_bugdata->add_update_callback( 'history_log_event_special', array( $p_bug_id, BUG_REPLACE_RELATIONSHIP, BUG_DUPLICATE, $p_duplicate_id ) );
			$t_bugdata->add_update_callback( 'history_log_event_special', array( $p_duplicate_id, BUG_REPLACE_RELATIONSHIP, BUG_HAS_DUPLICATE, $p_bug_id ) );
		} else if( $t_id_relationship != -1 ) {
			# Add the new relationship
			$t_bugdata->add_update_callback( 'relationship_add', array( $p_bug_id, $p_duplicate_id, BUG_DUPLICATE ) );
			# Add log line to the history (both bugs)
			$t_bugdata->add_update_callback( 'history_log_event_special', array( $p_bug_id, BUG_ADD_RELATIONSHIP, BUG_DUPLICATE, $p_duplicate_id ) );
			$t_bugdata->add_update_callback( 'history_log_event_special', array( $p_duplicate_id, BUG_ADD_RELATIONSHIP, BUG_HAS_DUPLICATE, $p_bug_id ) );
		} # else relationship is -1 - same type exists, do nothing

		# Copy list of users monitoring the duplicate bug to the original bug
		if( user_exists( $t_bugdata->reporter_id ) ) {
			$t_bugdata->add_update_callback( 'bug_monitor', array( $p_duplicate_id, $t_bugdata->reporter_id ) );
		}
		if( user_exists( $t_old_handler_id ) ) {
			$t_bugdata->add_update_callback( 'bug_monitor', array( $p_duplicate_id, $t_old_handler_id ) );
		}
		$t_bugdata->add_update_callback( 'bug_monitor_copy', array( $p_bug_id, $p_duplicate_id ) );
	}

	$t_bugdata->update( /* update extended */ false, /* bypass mail */ true );

	email_resolved( $p_bug_id );
	email_relationship_child_resolved( $p_bug_id );

	return true;
}

/**
 * reopen the given bug
 * @param integer $p_bug_id          A bug identifier.
 * @param string  $p_bugnote_text    The bugnote text.
 * @param string  $p_time_tracking   Time tracking value.
 * @param boolean $p_bugnote_private Whether this is a private bugnote.
 * @return boolean (always true)
 * @access public
 * @uses database_api.php
 * @uses email_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 */
function bug_reopen( $p_bug_id, $p_bugnote_text = '', $p_time_tracking = '0:00', $p_bugnote_private = false ) {
	$p_bugnote_text = trim( $p_bugnote_text );

	# @TODO
	# If time tracking is enabled, this code is executed outside of BugData validation.
	# These time tracking dependencies should be removed
	$t_time_tracking_enabled = config_get( 'time_tracking_enabled' );
	if( ON == $t_time_tracking_enabled ) {
		# Add bugnote if supplied ignore a false return
		# Moved bugnote_add before bug_set_field calls in case time_tracking_no_note is off.
		# Error condition stopped execution but status had already been changed
		$t_bugnote_id = bugnote_add( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, BUGNOTE, '', null, false );
		bugnote_process_mentions( $p_bug_id, $t_bugnote_id, $p_bugnote_text );
	}

	$t_bugdata = bug_get( $p_bug_id );

	# If time tracking is disabled, the bug note is added as a callback after validation
	# This is the proper way.
	if( OFF == $t_time_tracking_enabled ) {
		$add_bugnote_func = function( array $p_bugnote_params ) {
			$t_bugnote_id = call_user_func_array( 'bugnote_add', $p_bugnote_params );
			bugnote_process_mentions( $p_bugnote_params[0], $t_bugnote_id, $p_bugnote_params[1] );
		};
		$t_bugnote_params = array( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, BUGNOTE, '', null, false );
		$t_bugdata->add_update_callback( $add_bugnote_func, array( $t_bugnote_params ) );
	}

	$t_bugdata->status = config_get( 'bug_reopen_status' );
	$t_bugdata->resolution = config_get( 'bug_reopen_resolution' );
	$t_bugdata->update( /* update extended */ false, /* bypass mail */ true );

	email_bug_reopened( $p_bug_id );

	return true;
}

/**
 * updates the last_updated field
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return boolean (always true)
 * @access public
 * @uses database_api.php
 */
function bug_update_date( $p_bug_id ) {
	db_param_push();
	$t_query = 'UPDATE {bug} SET last_updated=' . db_param() . ' WHERE id=' . db_param();
	db_query( $t_query, array( db_now(), $p_bug_id ) );

	bug_clear_cache( $p_bug_id );

	return true;
}

/**
 * enable monitoring of this bug for the user
 * @param integer $p_bug_id  Integer representing bug identifier.
 * @param integer $p_user_id Integer representing user identifier.
 * @return boolean true if successful, false if unsuccessful
 * @access public
 * @uses database_api.php
 * @uses history_api.php
 * @uses user_api.php
 */
function bug_monitor( $p_bug_id, $p_user_id ) {
	$c_bug_id = (int)$p_bug_id;
	$c_user_id = (int)$p_user_id;

	# Make sure we aren't already monitoring this bug
	if( user_is_monitoring_bug( $c_user_id, $c_bug_id ) ) {
		return true;
	}

	# Don't let the anonymous user monitor bugs
	if( user_is_anonymous( $c_user_id ) ) {
		return false;
	}

	# Insert monitoring record
	db_param_push();
	$t_query = 'INSERT INTO {bug_monitor} ( user_id, bug_id ) VALUES (' . db_param() . ',' . db_param() . ')';
	db_query( $t_query, array( $c_user_id, $c_bug_id ) );

	# log new monitoring action
	history_log_event_special( $c_bug_id, BUG_MONITOR, $c_user_id );

	# updated the last_updated date
	bug_update_date( $p_bug_id );

	email_monitor_added( $p_bug_id, $p_user_id );

	return true;
}

/**
 * Returns the list of users monitoring the specified bug
 *
 * @param integer $p_bug_id Integer representing bug identifier.
 * @return array
 */
function bug_get_monitors( $p_bug_id ) {
	if( ! access_has_bug_level( config_get( 'show_monitor_list_threshold' ), $p_bug_id ) ) {
		return array();
	}

	# get the bugnote data
	db_param_push();
	$t_query = 'SELECT user_id, enabled
			FROM {bug_monitor} m, {user} u
			WHERE m.bug_id=' . db_param() . ' AND m.user_id = u.id
			ORDER BY u.realname, u.username';
	$t_result = db_query( $t_query, array( $p_bug_id ) );

	$t_users = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_users[] = $t_row['user_id'];
	}

	user_cache_array_rows( $t_users );

	return $t_users;
}

/**
 * Copy list of users monitoring a bug to the monitor list of a second bug
 * @param integer $p_source_bug_id Integer representing the bug identifier of the source bug.
 * @param integer $p_dest_bug_id   Integer representing the bug identifier of the destination bug.
 * @return void
 * @access public
 * @uses database_api.php
 * @uses history_api.php
 * @uses user_api.php
 */
function bug_monitor_copy( $p_source_bug_id, $p_dest_bug_id ) {
	$c_source_bug_id = (int)$p_source_bug_id;
	$c_dest_bug_id = (int)$p_dest_bug_id;

	db_param_push();
	$t_query = 'SELECT user_id FROM {bug_monitor} WHERE bug_id = ' . db_param();
	$t_result = db_query( $t_query, array( $c_source_bug_id ) );

	while( $t_bug_monitor = db_fetch_array( $t_result ) ) {
		if( user_exists( $t_bug_monitor['user_id'] ) &&
			!user_is_monitoring_bug( $t_bug_monitor['user_id'], $c_dest_bug_id ) ) {
			db_param_push();
			$t_query = 'INSERT INTO {bug_monitor} ( user_id, bug_id )
				VALUES ( ' . db_param() . ', ' . db_param() . ' )';
			db_query( $t_query, array( $t_bug_monitor['user_id'], $c_dest_bug_id ) );
			history_log_event_special( $c_dest_bug_id, BUG_MONITOR, $t_bug_monitor['user_id'] );
		}
	}
}

/**
 * disable monitoring of this bug for the user
 * if $p_user_id = null, then bug is unmonitored for all users.
 * @param integer $p_bug_id  Integer representing bug identifier.
 * @param integer $p_user_id Integer representing user identifier.
 * @return boolean (always true)
 * @access public
 * @uses database_api.php
 * @uses history_api.php
 */
function bug_unmonitor( $p_bug_id, $p_user_id ) {
	# Delete monitoring record
	db_param_push();
	$t_query = 'DELETE FROM {bug_monitor} WHERE bug_id = ' . db_param();
	$t_db_query_params[] = $p_bug_id;

	if( $p_user_id !== null ) {
		$t_query .= ' AND user_id = ' . db_param();
		$t_db_query_params[] = $p_user_id;
	}

	db_query( $t_query, $t_db_query_params );

	# log new un-monitor action
	history_log_event_special( $p_bug_id, BUG_UNMONITOR, (int)$p_user_id );

	# updated the last_updated date
	bug_update_date( $p_bug_id );

	return true;
}

/**
 * Pads the bug id with the appropriate number of zeros.
 * @param integer $p_bug_id A bug identifier.
 * @return string
 * @access public
 * @uses config_api.php
 */
function bug_format_id( $p_bug_id ) {
	$t_padding = config_get( 'display_bug_padding' );
	$t_string = sprintf( '%0' . (int)$t_padding . 'd', $p_bug_id );

	return event_signal( 'EVENT_DISPLAY_BUG_ID', $t_string, array( $p_bug_id ) );
}

/**
 * Returns the resulting status for a bug after an assignment action is performed.
 * If the option "auto_set_status_to_assigned" is enabled, the resulting status
 * is calculated based on current handler and status , and requested modifications.
 * @param integer $p_current_handler	Current handler user id
 * @param integer $p_new_handler		New handler user id
 * @param integer $p_current_status		Current bug status
 * @param integer $p_new_status			New bug status (as being part of a status change combined action)
 * @return integer		Calculated status after assignment
 */
function bug_get_status_for_assign( $p_current_handler, $p_new_handler, $p_current_status, $p_new_status = null ) {
	if( null === $p_new_status ) {
		$p_new_status = $p_current_status;
	}
	if( config_get( 'auto_set_status_to_assigned' ) ) {
		$t_assigned_status = config_get( 'bug_assigned_status' );

		if(		$p_current_handler == NO_USER &&
				$p_new_handler != NO_USER &&
				$p_new_status == $p_current_status &&
				$p_new_status < $t_assigned_status &&
				bug_check_workflow( $p_current_status, $t_assigned_status ) ) {

			return $t_assigned_status;
		}
	}
	return $p_new_status;
}