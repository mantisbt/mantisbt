<?php


class BugFilterQuery extends DbQuery {

	const QUERY_TYPE_LIST = 0;
	const QUERY_TYPE_COUNT = 1;
	const QUERY_TYPE_IDS = 2;
	const QUERY_TYPE_DISTINCT_IDS = 3;

	# properties used to build the query
	public $filter;
	public $project_id;
	public $user_id;
	public $use_sticky;

	# internal storage for intermediate data
	protected $parts_select = array();
	protected $parts_from = array();
	protected $parts_join = array();
	protected $parts_where = array(); # these are joined by the filter operator
	protected $parts_order = array();
	protected $fixed_where = array(); # these are joined always by AND
	protected $filter_operator;

	# runtime variables for building the filter query
	protected $rt_stop_build;
	protected $rt_included_projects;
	protected $rt_table_alias_cf;
	protected $table_alias_bugnote = null;

	/**
	 * $p_config can be either:
	 * - A single type constant, for easy object creation using default attributes
	 * - An array of options, for more advanced configuration.
	 *
	 * Option array uses "option => value" pairs, supported as:
	 * - 'query_type':	Any of QUERY_TYPE_xxx class constants, meaning.
	 *					QUERY_TYPE_LIST, query listing all fields of matched bugs. This is the default.
	 *					QUERY_TYPE_COUNT, query to return number of matched bugs.
	 *					QUERY_TYPE_IDS, query to return only matched bug ids, which may not
	 *						be unique, but may be faster and convenient for use as a subquery.
	 *					QUERY_TYPE_DISTINCT_IDS, query to return unique matched bug ids .
	 * - 'project_id':	(integer) A project id to be used, if needed by the filer. By default,
	 *					current project is used.
	 * - 'user_id':		(integer) A user id to be used to determine visibility for the filter.
	 *					By default current user is used.
	 * - 'use_sticky':	(boolean) Whether to allow returning the bug list sorted so that sticky
	 *					bugs are placed first in the result order. This is false by default.
	 *
	 * @param type $p_filter
	 * @param type $p_config
	 */
	public function __construct( $p_filter, $p_config = self::QUERY_TYPE_LIST ) {
		# defaults
		$t_query_type = self::QUERY_TYPE_LIST;
		$this->filter = $p_filter;
		$this->use_sticky = false;
		$this->project_id = helper_get_current_project();
		$this->user_id = auth_get_current_user_id();

		# $p_config can be an array or an integer
		if( is_array( $p_config ) ) {
			foreach( $p_config as $t_option => $t_value ) {
				if( null === $t_value ) {
					continue;
				}
				switch( $t_option ) {
					case 'query_type':
						$t_query_type = (int)$t_value;
						break;
					case 'project_id':
						$this->project_id = (int)$t_value;
						break;
					case 'user_id':
						$this->user_id = (int)$t_value;
						break;
					case 'use_sticky':
						$this->use_sticky = (boolean)$t_value;
						break;
				}
			}
		} else {
			$t_query_type = $p_config;
		}

		# The query string must be built here to have a valid DbQuery object ready for use
		$this->build_main();
		$this->set_query_type( $t_query_type );
	}

	public function set_query_type( $p_query_type ) {
		switch( $p_query_type ) {
			case self::QUERY_TYPE_COUNT:
				$this->sql( $this->string_query_count() );
				break;
			case self::QUERY_TYPE_IDS:
				$this->sql( $this->string_query_ids() );
				break;
			case self::QUERY_TYPE_DISTINCT_IDS:
				$this->sql( $this->string_query_dinstinct_ids() );
				break;
			case self::QUERY_TYPE_LIST:
			default:
				$this->sql( $this->string_query_list() );
				break;
		}
		$this->db_result = null;
	}

	public function get_bug_count() {
		# create a copy from current query
		$t_query_count = clone $this;
		# rebuild clauses for count query type
		$t_query_count->set_query_type( self::QUERY_TYPE_COUNT );
		# set defaults
		$t_query_count->set_limit();
		$t_query_count->set_offset();
		$t_query_count->execute();
		return $t_query_count->value();
	}

	public function add_select( $p_string ) {
		$this->parts_select[] = $p_string;
	}

	public function add_from( $p_string ) {
		$this->parts_from[] = $p_string;
	}

	public function add_join( $p_string ) {
		$this->parts_join[] = $p_string;
	}

	public function add_where( $p_string ) {
		$this->parts_where[] = $p_string;
	}

	public function add_fixed_where( $p_string ) {
		$this->fixed_where[] = $p_string;
	}

	public function add_order( $p_string ) {
		$this->parts_order[] = $p_string;
	}

	protected function helper_string_query_inner() {
		$t_from_string = ' FROM ' . implode( ', ', $this->parts_from );
		$t_join_string = count( $this->parts_join ) > 0 ? ' ' . implode( ' ', $this->parts_join ) : '';
		$t_where_string = ' WHERE '. implode( ' AND ', $this->fixed_where );
		if( count( $this->parts_where ) > 0 ) {
			$t_where_string .= ' AND ( ';
			$t_where_string .= implode( $this->filter_operator, $this->parts_where );
			$t_where_string .= ' )';
		}
		return $t_from_string . $t_join_string . $t_where_string;
	}

	protected function string_query_list() {
		if( empty( $this->parts_order ) ) {
			$this->build_order_by();
		}
		$t_select_string = 'SELECT DISTINCT ' . implode( ', ', $this->parts_select );
		$t_order_string = ' ORDER BY ' . implode( ', ', $this->parts_order );
		return $t_select_string . $this->helper_string_query_inner() . $t_order_string;
	}

	protected function string_query_count() {
		$t_select_string = 'SELECT COUNT( DISTINCT {bug}.id )';
		return $t_select_string . $this->helper_string_query_inner();
	}

	protected function string_query_ids() {
		$t_select_string = 'SELECT {bug}.id';
		return $t_select_string . $this->helper_string_query_inner();
	}

	protected function string_query_dinstinct_ids() {
		$t_select_string = 'SELECT DISTINCT {bug}.id';
		return $t_select_string . $this->helper_string_query_inner();
	}

	protected function build_main() {
		$this->rt_stop_build = false;
		$this->add_from( '{bug}' );
		$this->add_select( '{bug}.*' );

		if( $this->filter[FILTER_PROPERTY_MATCH_TYPE] == FILTER_MATCH_ANY ) {
			$this->filter_operator = ' OR ';
		} else {
			$this->filter_operator = ' AND ';
		}

		$this->build_projects();
		# if no projects where found, stop here
		if( $this->rt_stop_build ) {
			return;
		}

		foreach( $this->filter as $t_prop => $t_value ) {
			# These are the main entries for filter properties
			switch( $t_prop ) {
				case FILTER_PROPERTY_REPORTER_ID:
					$this->build_prop_reporter();
					break;
				case FILTER_PROPERTY_HANDLER_ID:
					$this->build_prop_handler();
					break;
				case FILTER_PROPERTY_MONITOR_USER_ID:
					$this->build_prop_monitor_by();
					break;
				case FILTER_PROPERTY_NOTE_USER_ID:
					$this->build_prop_note_by();
					break;
				case FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED:
					$this->build_prop_date_created();
					break;
				case FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE:
					$this->build_prop_date_updated();
					break;
				case FILTER_PROPERTY_BUILD:
					$this->build_prop_build();
					break;
				case FILTER_PROPERTY_VERSION:
					$this->build_prop_version();
					break;
				case FILTER_PROPERTY_FIXED_IN_VERSION:
					$this->build_prop_fixed_version();
					break;
				case FILTER_PROPERTY_TARGET_VERSION:
					$this->build_prop_target_version();
					break;
				case FILTER_PROPERTY_VIEW_STATE:
					$this->build_prop_view_state();
					break;
				case FILTER_PROPERTY_CATEGORY_ID:
					$this->build_prop_category();
					break;
				case FILTER_PROPERTY_SEVERITY:
					$this->build_prop_severity();
					break;
				case FILTER_PROPERTY_RESOLUTION:
					$this->build_prop_resolution();
					break;
				case FILTER_PROPERTY_PRIORITY:
					$this->build_prop_priority();
					break;
				case FILTER_PROPERTY_PROFILE_ID:
					$this->build_prop_profile();
					break;
				case FILTER_PROPERTY_PLATFORM:
					$this->build_prop_platform();
					break;
				case FILTER_PROPERTY_OS:
					$this->build_prop_os();
					break;
				case FILTER_PROPERTY_OS_BUILD:
					$this->build_prop_os_build();
					break;
				case FILTER_PROPERTY_SEARCH:
					$this->build_prop_search();
					break;
				case FILTER_PROPERTY_RELATIONSHIP_TYPE:
					$this->build_prop_relationship();
					break;
			}
		}
		# these have several properties that must be built only once
		if( isset( $this->filter[FILTER_PROPERTY_TAG_STRING] )
				|| isset( $this->filter[FILTER_PROPERTY_TAG_SELECT] ) ) {
			$this->build_prop_tags();
		}
		if( isset( $this->filter['custom_fields'] ) ) {
			$this->build_prop_custom_fields();
		}

		if( isset( $this->filter[FILTER_PROPERTY_HIDE_STATUS] )
				|| isset( $this->filter[FILTER_PROPERTY_STATUS] )) {
			$this->build_prop_status();
		}

		$this->build_prop_plugin_filters();

		$this->parts_select = array_unique( $this->parts_select );
		$this->parts_from = array_unique( $this->parts_from );
		$this->parts_join = array_unique( $this->parts_join );
		$this->parts_order = array_unique( $this->parts_order );
	}

	protected function build_projects() {
		$this->add_join( 'JOIN {project} ON {project}.id = {bug}.project_id' );
		$this->add_fixed_where( '{project}.enabled =' . $this->param( true ) );

		$t_user_id = $this->user_id;
		$t_project_id = $this->project_id;

		$t_projects_query_required = true;
		$t_included_project_ids = filter_get_included_projects( $this->filter, $t_project_id, $t_user_id, true /* return ALL_PROJECTS */ );
		if( ALL_PROJECTS == $t_included_project_ids ) {
			# The list of expanded projects is needed later even if project_query is not required
			$t_included_project_ids = filter_get_included_projects( $this->filter, $t_project_id, $t_user_id, false /* return ALL_PROJECTS */ );
			# this special case can skip the projects query clause:
			if( user_is_administrator( $t_user_id ) ) {
				log_event( LOG_FILTERING, 'all projects + administrator, hence no project filter.' );
				$t_projects_query_required = false;
			}
		}
		$this->rt_included_projects = $t_included_project_ids;

		if( $t_projects_query_required ) {

			# if no projects are accessible, then stop here
			if( count( $t_included_project_ids ) == 0 ) {
				log_event( LOG_FILTERING, 'no accessible projects' );
				$this->add_fixed_where( '{project}.id =' . $this->param( 0 ) );
				$this->rt_stop_build = true;
				return;
			}

			$t_limit_reporters = config_get( 'limit_reporters' );

			# this array is to be populated with project ids for which we only want to show public issues.  This is due to the limited
			# access of the current user.
			$t_public_only_project_ids = array();

			# this array is populated with project ids that the current user has full access to.
			$t_private_and_public_project_ids = array();

			# this array is populated with projects where the user can view only bugs for which he is the reporter user
			$t_limit_reporter_project_ids = array();

			# make sure the project rows are cached, as they will be used to check access levels.
			project_cache_array_rows( $t_included_project_ids );

			foreach( $t_included_project_ids as $t_pid ) {
				if( ( ON === $t_limit_reporters ) && ( !access_has_project_level( access_threshold_min_level( config_get( 'report_bug_threshold', null, $t_user_id, $t_pid ) ) + 1, $t_pid, $t_user_id ) ) ) {
					# project is limited, only view own reported bugs
					$t_limit_reporter_project_ids[] = $t_pid;
					# as we will check the user is reporter for each bug, and reporter can view his own private bugs, there's no need to check for private bug access
					continue;
				}
				$t_access_required_to_view_private_bugs = config_get( 'private_bug_threshold', null, null, $t_pid );
				if( access_has_project_level( $t_access_required_to_view_private_bugs, $t_pid, $t_user_id ) ) {
					$t_private_and_public_project_ids[] = $t_pid;
				} else {
					$t_public_only_project_ids[] = $t_pid;
				}
			}

			log_event( LOG_FILTERING, 'project_ids (with access to public/private issues) = @P' . implode( ', @P', $t_private_and_public_project_ids ) );
			log_event( LOG_FILTERING, 'project_ids (with access limited to public issues) = @P' . implode( ', @P', $t_public_only_project_ids ) );
			log_event( LOG_FILTERING, 'project_ids (with access limited to own issues) = @P' . implode( ', @P', $t_limit_reporter_project_ids ) );

			$t_query_projects_or = array();
			# for projects with total visibility
			if( !empty( $t_private_and_public_project_ids ) ) {
				$t_query_projects_or[] = $this->sql_in( '{bug}.project_id', $t_private_and_public_project_ids );
			}
			# for projects with public visibility, public issues can be shown
			if( !empty( $t_public_only_project_ids ) ) {
				$t_query_projects_or[] = $this->sql_in( '{bug}.project_id', $t_public_only_project_ids ) . ' AND {bug}.view_state = ' . $this->param( VS_PUBLIC );
			}
			# for projects with public visibility, the issue can be shown if the user is the reporter, regardless of public/private issue
			# also, for projects limited to reporters, the same condition applies
			# combine both arrays for this condition
			$t_projects_for_reporter_visibility = array_merge( $t_public_only_project_ids, $t_limit_reporter_project_ids );
			if( !empty( $t_projects_for_reporter_visibility ) ) {
				$t_query_projects_or[] = $this->sql_in( '{bug}.project_id', $t_projects_for_reporter_visibility ) . ' AND {bug}.reporter_id = ' . $this->param( $t_user_id );
			}

			$t_project_query = '(' . implode( ' OR ', $t_query_projects_or ) . ')';
			log_event( LOG_FILTERING, 'project query = ' . $t_project_query );

			$this->add_fixed_where( $t_project_query );
		}
	}

	protected function build_prop_status() {
		# take a list of all available statuses then remove the ones that we want hidden, then make sure
		# the ones we want shown are still available
		$t_desired_statuses = $this->filter[FILTER_PROPERTY_STATUS];

		# simple filtering: restrict by the hide status value if present
		if( FILTER_VIEW_TYPE_SIMPLE == $this->filter['_view_type'] ) {
			if( isset( $this->filter[FILTER_PROPERTY_HIDE_STATUS][0] ) && !filter_field_is_none( $this->filter[FILTER_PROPERTY_HIDE_STATUS][0] ) ) {
				$t_selected_status_array = $this->filter[FILTER_PROPERTY_STATUS];
				# if we have metavalue for "any", expand to all status, to filter them
				if( filter_field_is_any( $t_selected_status_array ) ) {
					$t_selected_status_array = MantisEnum::getValues( config_get( 'status_enum_string' ) );
				}
				$t_hide_status = $this->filter[FILTER_PROPERTY_HIDE_STATUS][0];
				# Filter out status that must be hidden
				$t_desired_statuses = array();
				foreach( $t_selected_status_array as $t_this_status ) {
					if( $t_hide_status > $t_this_status ) {
						$t_desired_statuses[] = $t_this_status;
					}
				}
			}
		}
		# advanced filtering: ignore hide_status, do nothing.

		# if show_status is "any", empty the array, to not include any condition on status.
		if( filter_field_is_any( $t_desired_statuses ) ) {
				$t_desired_statuses = array();
		}

		if( count( $t_desired_statuses ) > 0 ) {
			$t_clauses = $this->helper_array_map_int( $t_desired_statuses );
			$this->add_where( $this->sql_in( '{bug}.status', $t_clauses ) );
		}
	}

	protected function build_prop_date_created() {
		if( ( gpc_string_to_bool( $this->filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] ) )
				&& is_numeric( $this->filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] )
				&& is_numeric( $this->filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] )
				&& is_numeric( $this->filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] )
				&& is_numeric( $this->filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] )
				&& is_numeric( $this->filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] )
				&& is_numeric( $this->filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] )
				) {
			$t_start_string = $this->filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR]
					. '-' . $this->filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH]
					. '-' . $this->filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY]
					. ' 00:00:00';
			$t_end_string = $this->filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR]
					. '-' . $this->filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH]
					. '-' . $this->filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY]
					. ' 23:59:59';

			$t_query_created_at = '{bug}.date_submitted BETWEEN '
					. $this->param( strtotime( $t_start_string ) ) . ' AND '
					. $this->param( strtotime( $t_end_string ) ) ;
			$this->add_fixed_where( $t_query_created_at );
		}
	}

	protected function build_prop_date_updated() {
		if( ( gpc_string_to_bool( $this->filter[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] ) )
				&& is_numeric( $this->filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] )
				&& is_numeric( $this->filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY] )
				&& is_numeric( $this->filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] )
				&& is_numeric( $this->filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] )
				&& is_numeric( $this->filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY] )
				&& is_numeric( $this->filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] )
				) {
			$t_start_string = $this->filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR]
					. '-' . $this->filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH]
					. '-' . $this->filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY]
					. ' 00:00:00';
			$t_end_string = $this->filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR]
					. '-' . $this->filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH]
					. '-' . $this->filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY]
					. ' 23:59:59';

			$t_query_updated_at = '{bug}.last_updated BETWEEN '
					. $this->param( strtotime( $t_start_string ) ) . ' AND '
					. $this->param( strtotime( $t_end_string ) ) ;
			$this->add_fixed_where( $t_query_updated_at );
		}
	}

	protected function build_prop_view_state() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_VIEW_STATE] ) ) {
			return;
		}
		$t_view_state = (int)$this->filter[FILTER_PROPERTY_VIEW_STATE];
		$t_view_state_query = '{bug}.view_state = ' . $this->param( $t_view_state ) ;
		log_event( LOG_FILTERING, 'view_state query = ' . $t_view_state_query );
		$this->add_where( $t_view_state_query );
	}

	protected function helper_process_users_property( $p_users_array ) {
		$t_new_array = array();
		foreach( $p_users_array as $t_user ) {
			if( filter_field_is_none( $t_user ) ) {
				$t_new_array[] = 0;
			} else {
				$c_user_id = (int)$t_user;
				if( filter_field_is_myself( $c_user_id ) ) {
					$t_new_array[] = $this->user_id;
				} else {
					$t_new_array[] = $c_user_id;
				}
			}
		}
		return $t_new_array;
	}

	protected function build_prop_reporter() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_REPORTER_ID] ) ) {
			return;
		}
		$t_user_ids = $this->helper_process_users_property( $this->filter[FILTER_PROPERTY_REPORTER_ID] );
		$t_users_query = $this->sql_in( '{bug}.reporter_id', $t_user_ids );
		log_event( LOG_FILTERING, 'reporter query = ' . $t_users_query );
		$this->add_where( $t_users_query );
	}

	protected function build_prop_handler() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_HANDLER_ID] ) ) {
			return;
		}

		# the user can view handler if he meets access level for
		# 'view_handler_threshold' or if he is the handler
		$t_projects_can_view = $this->helper_filter_projects_using_access( 'view_handler_threshold' );
		if( ALL_PROJECTS == $t_projects_can_view ) {
			$t_view_condition = null;
		} else {
			$t_view_condition = '{bug}.handler_id = ' . $this->param( $this->user_id );
			if( !empty( $t_projects_can_view ) ) {
				$t_view_condition = '(' . $t_view_condition . ' OR '
						. $this->sql_in( '{bug}.project_id', $t_projects_can_view ) . ')';
			}
		}
		if( $t_view_condition ) {
			$t_view_condition = ' AND ' . $t_view_condition;
		}

		$t_user_ids = $this->helper_process_users_property( $this->filter[FILTER_PROPERTY_HANDLER_ID] );
		$t_query = $this->sql_in( '{bug}.handler_id', $t_user_ids ) . $t_view_condition;
		log_event( LOG_FILTERING, 'handler query = ' . $t_query );
		$this->add_where( $t_query );
	}

	protected function build_prop_category() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_CATEGORY_ID] ) ) {
			return;
		}
		$t_names = array();
		$t_use_none = false;
		foreach( $this->filter[FILTER_PROPERTY_CATEGORY_ID] as $t_filter_member ) {
			if( filter_field_is_none( $t_filter_member ) ) {
				$t_use_none = true;
			} else {
				$t_names[] = $t_filter_member;
			}
		}

		$t_query_or = array();
		if( !empty( $t_names ) ) {
			$t_subselect = 'SELECT id FROM {category} WHERE ' . $this->sql_in( 'name', $t_names );
			$t_query_names = '{bug}.category_id IN ( '. $t_subselect . ')';
			$t_query_or[] = $t_query_names;
		}
		if( $t_use_none ) {
			$t_query_or[] = '{bug}.category_id = ' . $this->param( 0 );
		}

		$t_query_category = '(' . implode( ' OR ', $t_query_or ) . ')';
		log_event( LOG_FILTERING, 'category query = ' . $t_query_category );
		$this->add_where( $t_query_category );
	}

	protected function build_prop_severity() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_SEVERITY] ) ) {
			return;
		}
		$t_query = $this->sql_in( '{bug}.severity', $this->filter[FILTER_PROPERTY_SEVERITY] );
		$this->add_where( $t_query );
	}

	protected function build_prop_resolution() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_RESOLUTION] ) ) {
			return;
		}
		$t_query = $this->sql_in( '{bug}.resolution', $this->filter[FILTER_PROPERTY_RESOLUTION] );
		$this->add_where( $t_query );
	}

	protected function build_prop_priority() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_PRIORITY] ) ) {
			return;
		}
		$t_query = $this->sql_in( '{bug}.priority', $this->filter[FILTER_PROPERTY_PRIORITY] );
		$this->add_where( $t_query );
	}

	protected function helper_process_string_property( $p_array ) {
		$t_new_array = array();
		foreach( $p_array as $t_elem ) {
			if( filter_field_is_none( $t_elem ) ) {
				$t_new_array[] = '';
			} else {
				$t_new_array[] = $t_elem;
			}
		}
		return $t_new_array;
	}

	protected function build_prop_build() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_BUILD] ) ) {
			return;
		}
		$t_array = $this->helper_process_string_property( $this->filter[FILTER_PROPERTY_BUILD] );
		$t_query = $this->sql_in( '{bug}.build', $t_array );
		$this->add_where( $t_query );
	}

	protected function build_prop_version() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_VERSION] ) ) {
			return;
		}
		$t_array = $this->helper_process_string_property( $this->filter[FILTER_PROPERTY_VERSION] );
		$t_query = $this->sql_in( '{bug}.version', $t_array );
		$this->add_where( $t_query );
	}

	protected function helper_process_id_property( $p_array ) {
		$t_new_array = array();
		foreach( $p_array as $t_elem ) {
			if( filter_field_is_none( $t_elem ) ) {
				$t_new_array[] = 0;
			} else {
				$t_new_array[] = $t_elem;
			}
		}
		return $t_new_array;
	}

	protected function build_prop_profile() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_PROFILE_ID] ) ) {
			return;
		}
		$t_array = $this->helper_process_id_property( $this->filter[FILTER_PROPERTY_PROFILE_ID] );
		$t_query = $this->sql_in( '{bug}.profile_id', $t_array );
		$this->add_where( $t_query );
	}

	protected function build_prop_platform() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_PLATFORM] ) ) {
			return;
		}
		$t_array = $this->helper_process_string_property( $this->filter[FILTER_PROPERTY_PLATFORM] );
		$t_query = $this->sql_in( '{bug}.platform', $t_array );
		$this->add_where( $t_query );
	}

	protected function build_prop_os() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_OS] ) ) {
			return;
		}
		$t_array = $this->helper_process_string_property( $this->filter[FILTER_PROPERTY_OS] );
		$t_query = $this->sql_in( '{bug}.os', $t_array );
		$this->add_where( $t_query );
	}

	protected function build_prop_os_build() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_OS_BUILD] ) ) {
			return;
		}
		$t_array = $this->helper_process_string_property( $this->filter[FILTER_PROPERTY_OS_BUILD] );
		$t_query = $this->sql_in( '{bug}.os_build', $t_array );
		$this->add_where( $t_query );
	}

	protected function build_prop_fixed_version() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_FIXED_IN_VERSION] ) ) {
			return;
		}
		$t_array = $this->helper_process_string_property( $this->filter[FILTER_PROPERTY_FIXED_IN_VERSION] );
		$t_query = $this->sql_in( '{bug}.fixed_in_version', $t_array );
		$this->add_where( $t_query );
	}

	protected function build_prop_target_version() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_TARGET_VERSION] ) ) {
			return;
		}
		$t_array = $this->helper_process_string_property( $this->filter[FILTER_PROPERTY_TARGET_VERSION] );
		$t_query = $this->sql_in( '{bug}.target_version', $t_array );
		$this->add_where( $t_query );
	}

	protected function build_prop_monitor_by() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_MONITOR_USER_ID] ) ) {
			return;
		}
		$t_user_ids = $this->helper_process_users_property( $this->filter[FILTER_PROPERTY_MONITOR_USER_ID] );
		$t_use_none = ( in_array( 0, $t_user_ids ) );

		# Build a condition for determining monitoring visibility, the user can view:
		# - his own monitored issues
		# - other users monitoring if he meets access level for 'show_monitor_list_threshold'
		$t_projects_can_view = $this->helper_filter_projects_using_access( 'show_monitor_list_threshold' );
		if( ALL_PROJECTS == $t_projects_can_view ) {
			$t_view_condition = null;
		} else {
			$t_view_condition = '{bug_monitor}.user_id = ' . $this->param( $this->user_id );
			if( !empty( $t_projects_can_view ) ) {
				$t_view_condition = '(' . $t_view_condition . ' OR '
						. $this->sql_in( '{bug}.project_id', $t_projects_can_view ) . ')';
			}
		}
		if( $t_view_condition ) {
			$t_view_condition = ' AND ' . $t_view_condition;
		}

		$this->add_join( 'LEFT JOIN {bug_monitor} ON {bug}.id = {bug_monitor}.bug_id' . $t_view_condition );
		if( $t_use_none ) {
			$t_expr = 'COALESCE( {bug_monitor}.user_id, 0 )';
		} else {
			$t_expr = '{bug_monitor}.user_id';
		}

		$t_where = $this->sql_in( $t_expr, $t_user_ids );
		$this->add_where( $t_where );
	}

	protected function helper_table_alias_for_bugnote() {
		if( $this->table_alias_bugnote ) {
			return $this->table_alias_bugnote;
		}
		# Build a condition for determining note visibility, the user can view:
		# - public notes
		# - his own private notes
		# - private notes if meets access level for 'private_bugnote_threshold'
		$t_projects_can_view_private = $this->helper_filter_projects_using_access( 'private_bugnote_threshold' );
		$t_table_alias = 'visible_bugnote';
		if( ALL_PROJECTS == $t_projects_can_view_private ) {
			$t_view_condition = null;
		} else {
			$t_view_condition = $t_table_alias . '.view_state = ' . $this->param( VS_PUBLIC )
					. ' OR ' . $t_table_alias . '.reporter_id = ' . $this->param( $this->user_id );
			if( !empty( $t_projects_can_view_private ) ) {
				$t_view_condition .= ' OR ' . $this->sql_in( '{bug}.project_id', $t_projects_can_view_private );
			}
		}
		if( $t_view_condition ) {
			$t_view_condition = ' AND (' . $t_view_condition . ')';
		}
		$t_join = 'LEFT JOIN {bugnote} ' . $t_table_alias
				. ' ON {bug}.id = ' . $t_table_alias . '.bug_id'
				. $t_view_condition;

		$this->add_join( $t_join );
		$this->table_alias_bugnote = $t_table_alias;
		return $this->table_alias_bugnote;
	}

	protected function build_prop_note_by() {
		if( filter_field_is_any( $this->filter[FILTER_PROPERTY_NOTE_USER_ID] ) ) {
			return;
		}
		$t_user_ids = $this->helper_process_users_property( $this->filter[FILTER_PROPERTY_NOTE_USER_ID] );
		$t_use_none = ( in_array( 0, $t_user_ids ) );

		$t_table_alias = $this->helper_table_alias_for_bugnote();

		if( $t_use_none ) {
			$t_alias = 'COALESCE( ' . $t_table_alias . '.reporter_id, 0 )';
		} else {
			$t_alias = $t_table_alias . '.reporter_id';
		}

		$t_where = $this->sql_in( $t_alias, $t_user_ids );
		$this->add_where( $t_where );
	}

	protected function build_prop_relationship() {
		$t_any_found = false;
		$c_rel_type = $this->filter[FILTER_PROPERTY_RELATIONSHIP_TYPE];
		$c_rel_bug = $this->filter[FILTER_PROPERTY_RELATIONSHIP_BUG];
		if( -1 == $c_rel_type || 0 == $c_rel_bug ) {
			return;
		}
		# use the complementary type
		$t_comp_type = relationship_get_complementary_type( $c_rel_type );
		$t_table_dst = 'rel_dst';
		$t_table_src = 'rel_src';
		$this->add_join( 'LEFT JOIN {bug_relationship} ' . $t_table_dst . ' ON ' . $t_table_dst . '.destination_bug_id = {bug}.id' );
		$this->add_join( 'LEFT JOIN {bug_relationship} ' . $t_table_src . ' ON ' . $t_table_src . '.source_bug_id = {bug}.id' );

		# get reverse relationships
		$t_clauses = array();
		$t_clauses[] = '(' . $t_table_dst . '.relationship_type=' . $this->param( $t_comp_type )
				. ' AND ' . $t_table_dst . '.source_bug_id=' . $this->param( $c_rel_bug ) . ')';
		$t_clauses[] = '(' . $t_table_src . '.relationship_type=' . $this->param( $c_rel_type )
				. ' AND ' . $t_table_src . '.destination_bug_id=' . $this->param( $c_rel_bug ) . ')';
		$this->add_where( '(' . implode( ' OR ', $t_clauses ) . ')' );
	}

	protected function helper_filter_projects_using_access( $p_access ) {
		$t_filtered_projects = access_project_array_filter( $p_access, $this->rt_included_projects, $this->user_id );
		$t_diff = array_diff( $this->rt_included_projects, $t_filtered_projects );
		if( empty( $t_diff ) ) {
			return ALL_PROJECTS;
		} else {
			return $t_filtered_projects;
		}
	}

	protected function helper_array_map_int( $p_array ) {
		$t_new_array = array();
		foreach( $p_array as $t_elem ) {
			$t_new_array[] = (int)$t_elem;
		}
		return $t_new_array;
	}

	protected function build_prop_tags() {
		$c_tag_string = trim( $this->filter[FILTER_PROPERTY_TAG_STRING] );
		$c_tag_select = (int)$this->filter[FILTER_PROPERTY_TAG_SELECT];
		if( is_blank( $c_tag_string ) && $c_tag_select == 0 ) {
			# shortcut exit
			return;
		}

		$t_tags = tag_parse_filters( $c_tag_string );
		if( empty( $t_tags ) && $c_tag_select == 0 ) {
			# shortcut exit
			return;
		}

		$t_projects_can_view_tags = $this->helper_filter_projects_using_access( 'tag_view_threshold' );
		if( ALL_PROJECTS == $t_projects_can_view_tags ) {
			$t_tag_projects_clause = '';
		} else {
			if( empty( $t_projects_can_view_tags ) ) {
				# if cant view tags in any project, exit
				log_event( LOG_FILTERING, 'tags query, no accesible projects ' );
				return;
			} else {
				$t_tag_projects_clause = ' AND ' . $this->sql_in( '{bug}.project_id', $t_projects_can_view_tags );
				log_event( LOG_FILTERING, 'tags query, accesible projects =  @P' . implode( ', @P', $t_projects_can_view_tags ) );
			}
		}

		$t_tags_always = array();
		$t_tags_any = array();
		$t_tags_never = array();

		# @TODO, use constants for tag modifiers
		foreach( $t_tags as $t_tag_row ) {
			switch( $t_tag_row['filter'] ) {
				case 1:
					# A matched issue must always have this tag
					$t_tags_always[] = $t_tag_row;
					break;
				case 0:
					# A matched issue may have this tag
					$t_tags_any[] = $t_tag_row;
					break;
				case -1:
					# A matched must never have this tag
					$t_tags_never[] = $t_tag_row;
					break;
			}
		}

		# Consider those tags that must always match, to also be part of those that can be
		# optionally matched. This solves the scenario for an issue that matches one tag
		# from the "always" group, and none from the "any" group.
		if( !empty( $t_tags_always ) && !empty( $t_tags_any ) ) {
			$t_tags_any = array_merge( $t_tags_any, $t_tags_always );
		}

		# Add the tag id to the array, from filter field "tag_select"
		if( 0 < $c_tag_select && tag_exists( $c_tag_select ) ) {
			$t_tags_any[] = tag_get( $c_tag_select );
		}

		$t_where = array();

		if( count( $t_tags_always ) ) {
			foreach( $t_tags_always as $t_tag_row ) {
				$t_tag_alias = 'bug_tag_alias_alw_' . $t_tag_row['id'];
				$t_join_inc = 'LEFT JOIN {bug_tag} ' . $t_tag_alias . ' ON ' . $t_tag_alias . '.bug_id = {bug}.id'
					. ' AND ' . $t_tag_alias . '.tag_id = ' . $this->param( (int)$t_tag_row['id'] )
					. $t_tag_projects_clause;
				$this->add_join( $t_join_inc );
				$t_where[] = $t_tag_alias . '.tag_id IS NOT NULL';
			}
		}

		if( count( $t_tags_any ) ) {
			$t_tag_alias = 'bug_tag_alias_any';
			$t_tag_ids = $this->helper_array_map_int( array_column( $t_tags_any, 'id' ) );
			$t_join_any = 'LEFT JOIN {bug_tag} ' . $t_tag_alias . ' ON ' . $t_tag_alias . '.bug_id = {bug}.id'
				. ' AND ' . $this->sql_in( $t_tag_alias . '.tag_id', $t_tag_ids )
				. $t_tag_projects_clause;
			$this->add_join( $t_join_any );
			$t_where[] = $t_tag_alias . '.tag_id IS NOT NULL';
		}

		if( count( $t_tags_never ) ) {
			$t_tag_alias = 'bug_tag_alias_nev';
			$t_tag_ids = $this->helper_array_map_int( array_column( $t_tags_never, 'id' ) );
			$t_join_exc = 'LEFT JOIN {bug_tag} ' . $t_tag_alias . ' ON ' . $t_tag_alias . '.bug_id = {bug}.id'
				. ' AND ' . $this->sql_in(  $t_tag_alias . '.tag_id', $t_tag_ids )
				. $t_tag_projects_clause;
			$this->add_join( $t_join_exc );
			$t_where[] = $t_tag_alias . '.tag_id IS NULL';
		}

		if( !empty( $t_where ) ) {
			$this->add_where( implode( ' AND ', $t_where ) );
		}
	}

	protected function build_prop_custom_fields() {
		if( ON != config_get( 'filter_by_custom_fields' ) ) {
			log_event( LOG_FILTERING, 'filter custom fields is globally disabled, skip' );
			return;
		}

		$t_custom_fields = custom_field_get_linked_ids( $this->rt_included_projects );

		foreach( $t_custom_fields as $t_cfid ) {
			$t_field_info = custom_field_cache_row( $t_cfid, true );
			if( !$t_field_info['filter_by'] ) {
				# skip this custom field if it shouldn't be filterable
				log_event( LOG_FILTERING, 'filter custom fields, field_id=' . $t_cfid . ' is not valid for filtering');
				continue;
			}

			$t_field = $this->filter['custom_fields'][$t_cfid];
			if( filter_field_is_any( $t_field ) ) {
				# Ignore all custom filters that are not set, or that are set to '' or "any"
				continue;
			}

			$t_custom_where_clause = '';
			$t_def = custom_field_get_definition( $t_cfid );

			# skip date custom fields with value of "any"
			if( $t_def['type'] == CUSTOM_FIELD_TYPE_DATE && $t_field[0] == CUSTOM_FIELD_DATE_ANY ) {
				continue;
			}

			$t_table_name = 'cf_alias_' . $t_cfid;
			$t_cf_join_clause = 'LEFT OUTER JOIN {custom_field_string} ' . $t_table_name . ' ON {bug}.id = ' . $t_table_name . '.bug_id AND ' . $t_table_name . '.field_id = ' . $this->param( (int)$t_cfid );

			# get which projects are valid for this custom field
			$t_searchable_projects = array_intersect( $this->rt_included_projects, custom_field_get_project_ids( $t_cfid ) );
			# and for which of those projects the user have read access to this field
			$t_projects_can_view_field = access_project_array_filter( (int)$t_def['access_level_r'], $t_searchable_projects, $this->user_id );
			if( empty( $t_projects_can_view_field ) ) {
				# This field cant be viewed in any project, skip this field
				# @TODO if the field cant be viewed in any project, should still the join be made to match for META_FILTER_NONE?
				continue;
			}
			# This diff will contain those included projects that can't view this custom field
			$t_diff = array_diff( $this->rt_included_projects, $t_projects_can_view_field );
			# If not empty, it means there are some projects that can't view the field values,
			# so a project filter must be used to not include values from those projects
			if( !empty( $t_diff ) ) {
				$t_cf_join_clause .= ' AND ' . $this->sql_in( '{bug}.project_id', $t_projects_can_view_field );
			}

			$this->rt_table_alias_cf[$t_cfid] = $t_table_name;
			$this->add_join( $t_cf_join_clause );

			if( $t_def['type'] == CUSTOM_FIELD_TYPE_DATE ) {
				# Define the value field with type cast to integer
				$t_value_field = 'CAST(COALESCE(NULLIF(' . $t_table_name . '.value, \'\'), \'0\') AS DECIMAL)';
				switch( $t_field[0] ) {
					# Closing parenthesis intentionally omitted, will be added later on
					# CUSTOM_FIELD_DATE_ANY can't appear here, it was previously skipped
					case CUSTOM_FIELD_DATE_NONE:
						$t_custom_where_clause = '( ' . $t_table_name . '.bug_id IS NULL OR ' . $t_value_field . ' = 0 ';
						break;
					case CUSTOM_FIELD_DATE_BEFORE:
						$t_custom_where_clause = '( ' . $t_value_field . ' != 0 AND ' . $t_value_field . ' < ' . $this->param( $t_field[2] );
						break;
					case CUSTOM_FIELD_DATE_AFTER:
						$t_custom_where_clause = '( ' . $t_value_field . ' > ' . $this->param( $t_field[1] + 1 );
						break;
					default:
						$t_custom_where_clause = '( ' . $t_value_field . ' BETWEEN ' . $this->param( $t_field[1] ) . ' AND ' . $this->param( $t_field[2] );
						break;
				}
			} else {
				$t_filter_array = array();
				foreach( $t_field as $t_filter_member ) {
					$t_filter_member = stripslashes( $t_filter_member );
					if( filter_field_is_none( $t_filter_member ) ) {
						# coerce filter value if selecting META_FILTER_NONE so it will match empty fields
						$t_filter_member = '';

						# but also add those _not_ present in the custom field string table
						array_push( $t_filter_array, $t_table_name . '.value IS NULL' );
					}

					switch( $t_def['type'] ) {
						case CUSTOM_FIELD_TYPE_CHECKBOX:
						case CUSTOM_FIELD_TYPE_MULTILIST:
							$t_filter_array[] = $this->sql_like( $t_table_name . '.value', '%|' . $t_filter_member . '|%' );
							break;
						case CUSTOM_FIELD_TYPE_TEXTAREA:
							$t_filter_array[] = $this->sql_like( $t_table_name . '.text', '%' . $t_filter_member . '%' );
							break;
						default:
							$t_filter_array[] = $t_table_name . '.value = ' . $this->param( $t_filter_member );
					}
				}
				$t_custom_where_clause .= '(' . implode( ' OR ', $t_filter_array );
			}
			if( !is_blank( $t_custom_where_clause ) ) {
				$this->add_where( $t_custom_where_clause . ')' );
			}
		} # foreach cf
	}

	protected function build_prop_search() {
		if( is_blank( $this->filter[FILTER_PROPERTY_SEARCH] ) ) {
			return;
		}

		# break up search terms by spacing or quoting
		preg_match_all( "/-?([^'\"\s]+|\"[^\"]+\"|'[^']+')/", $this->filter[FILTER_PROPERTY_SEARCH], $t_matches, PREG_SET_ORDER );

		# organize terms without quoting, paying attention to negation
		$t_search_terms = array();
		foreach( $t_matches as $t_match ) {
			$t_search_terms[trim( $t_match[1], "\'\"" )] = ( $t_match[0][0] == '-' );
		}

		$t_bugnote_table = $this->helper_table_alias_for_bugnote();

		# build a big where-clause and param list for all search terms, including negations
		$t_first = true;
		$t_textsearch_where_clause = '( ';
		foreach( $t_search_terms as $t_search_term => $t_negate ) {
			if( !$t_first ) {
				$t_textsearch_where_clause .= ' AND ';
			}

			if( $t_negate ) {
				$t_textsearch_where_clause .= 'NOT ';
			}

			$c_search = '%' . $t_search_term . '%';
			$t_textsearch_where_clause .= '( ' . $this->sql_like( '{bug}.summary', $c_search )
					. ' OR ' . $this->sql_like( '{bug_text}.description', $c_search )
					. ' OR ' . $this->sql_like( '{bug_text}.steps_to_reproduce', $c_search )
					. ' OR ' . $this->sql_like( '{bug_text}.additional_information', $c_search )
					. ' OR ' . $this->sql_like( '{bugnote_text}.note', $c_search );

			if( is_numeric( $t_search_term ) ) {
				# Note: no need to test negative values, '-' sign has been removed
				if( $t_search_term <= DB_MAX_INT ) {
					$c_search_int = (int)$t_search_term;
					$t_textsearch_where_clause .= ' OR {bug}.id = ' . $this->param( $c_search_int );
					$t_textsearch_where_clause .= ' OR ' . $t_bugnote_table . '.id = ' . $this->param( $c_search_int );
				}
			}

			$t_textsearch_where_clause .= ' )';
			$t_first = false;
		}
		$t_textsearch_where_clause .= ' )';

		# add text query elements to arrays
		if( !$t_first ) {
			# join with bugnote table has already been created or reused
			$this->add_join( 'JOIN {bug_text} ON {bug}.bug_text_id = {bug_text}.id' );
			# Outer join required otherwise we don't retrieve issues without notes
			$this->add_join( 'LEFT JOIN {bugnote_text} ON ' . $t_bugnote_table . '.bugnote_text_id = {bugnote_text}.id' );
			$this->add_where( $t_textsearch_where_clause );
		}

	}

	protected function helper_convert_legacy_clause( $p_string, array $p_params ) {
		if( empty( $p_params ) ) {
			return $p_string;
		}
		$t_params = array_values( $p_params );
		$t_param_index = 0;
		$cb_add_param = function ( $t_matches ) use ( $t_params, &$t_param_index ) {
			return $this->param( $t_params[$t_param_index++] );
		};

		$t_new_string = preg_replace_callback( '/(?<token>\?|\$|:)(?<index>[0-9]*)/', $cb_add_param, $p_string );
		return $t_new_string;
	}

	protected function build_prop_plugin_filters() {
		$t_plugin_filters = filter_get_plugin_filters();
		foreach( $t_plugin_filters as $t_field_name => $t_filter_object ) {
			if( !filter_field_is_any( $this->filter[$t_field_name] ) || $t_filter_object->type == FILTER_TYPE_BOOLEAN ) {
				$t_filter_query = $t_filter_object->query( $this->filter[$t_field_name] );
				if( is_array( $t_filter_query ) ) {
					if( isset( $t_filter_query['join'] ) ) {
						$this->add_join( $t_filter_query['join'] );
					}
					$t_params = null;
					if( isset( $t_filter_query['params'] ) && is_array( $t_filter_query['params'] ) ) {
						$t_params = $t_filter_query['params'];
					}
					if( isset( $t_filter_query['where'] ) ) {
						$t_where = $this->helper_convert_legacy_clause( $t_filter_query['where'], $t_params );
						$this->add_where( $t_where );
					}
				}
			}
		}
	}

	protected function build_order_by() {

		# Get only the visible, and sortable, column properties
		# @TODO cproensa: this defaults to COLUMNS_TARGET_VIEW_PAGE
		# are we sure that filters are only used with the column set for view page?
		$t_sort_properties = filter_get_visible_sort_properties_array( $this->filter );
		$t_sort_fields = $t_sort_properties[FILTER_PROPERTY_SORT_FIELD_NAME];
		$t_dir_fields = $t_sort_properties[FILTER_PROPERTY_SORT_DIRECTION];

		if( gpc_string_to_bool( $this->filter[FILTER_PROPERTY_STICKY] ) && ( $this->use_sticky ) ) {
			$this->add_order( '{bug}.sticky DESC' );
		}

		$t_count = count( $t_sort_fields );
		for( $i = 0; $i < $t_count; $i++ ) {
			$c_sort = $t_sort_fields[$i];
			$c_dir = 'DESC' == $t_dir_fields[$i] ? 'DESC' : 'ASC';

			# if sorting by a custom field
			if( column_is_custom_field( $c_sort ) ) {
				$t_custom_field = column_get_custom_field_name( $c_sort );
				$t_custom_field_id = custom_field_get_id_from_name( $t_custom_field );
				$t_def = custom_field_get_definition( $t_custom_field_id );
				$t_value_field = ( $t_def['type'] == CUSTOM_FIELD_TYPE_TEXTAREA ? 'text' : 'value' );

				$t_table_name = '';
				# if the custom field was filtered, there is already a calculated join, so reuse that table alias
				# otherwise, a new join must be calculated
				if( isset( $this->rt_table_alias_cf[$t_custom_field_id] ) ) {
					$t_table_name = $this->rt_table_alias_cf[$t_custom_field_id];
				} else {
					# @TODO This code for CF visibility is the same as the custom field filter
					# It should be encapsulated and reused

					$t_searchable_projects = array_intersect( $this->rt_included_projects, custom_field_get_project_ids( $t_custom_field_id ) );
					$t_projects_can_view_field = access_project_array_filter( (int)$t_def['access_level_r'], $t_searchable_projects, $this->user_id );
					if( empty( $t_projects_can_view_field ) ) {
						continue;
					}

					$t_table_name = 'cf_sort_' . $t_custom_field_id;
					$t_cf_join_clause = 'LEFT OUTER JOIN {custom_field_string} ' . $t_table_name . ' ON {bug}.id = ' . $t_table_name . '.bug_id AND ' . $t_table_name . '.field_id = ' . $this->param( $t_custom_field_id );

					# This diff will contain those included projects that can't view this custom field
					$t_diff = array_diff( $this->rt_included_projects, $t_projects_can_view_field );
					# If not empty, it means there are some projects that can't view the field values,
					# so a project filter must be used to not include values from those projects
					if( !empty( $t_diff ) ) {
						$t_cf_join_clause .= ' AND ' . $this->sql_in( '{bug}.project_id', $t_projects_can_view_field );
					}
					$this->rt_table_alias_cf[$t_custom_field_id] = $t_table_name;
					$this->add_join( $t_cf_join_clause );
				}

				# if no join can be used (eg, no view access), skip this field from the order clause
				if( empty( $t_table_name ) ) {
					continue;
				}

				$t_field_alias = 'cf_sortfield_' . $t_custom_field_id;
				$t_sort_col = $t_table_name . '.' . $t_value_field;

				# which types need special type cast
				switch( $t_def['type'] ) {
						case CUSTOM_FIELD_TYPE_FLOAT:
							# mysql can't cast to float, use alternative syntax
							$t_sort_expr = db_is_mysql() ? $t_sort_col . '+0.0' : 'CAST(NULLIF(' . $t_sort_col . ',\'\') AS FLOAT)';
							break;
						case CUSTOM_FIELD_TYPE_DATE:
						case CUSTOM_FIELD_TYPE_NUMERIC:
							$t_sort_expr = 'CAST(NULLIF(' . $t_sort_col . ',\'\') AS DECIMAL)';
							break;
						default: # no cast needed
							$t_sort_expr = $t_sort_col;
				}

				# which types need special treatment for null sorting
				switch( $t_def['type'] ) {
					case CUSTOM_FIELD_TYPE_DATE:
					case CUSTOM_FIELD_TYPE_NUMERIC:
					case CUSTOM_FIELD_TYPE_FLOAT:
						$t_null_last = true;
						break;
					default:
						$t_null_last = false;
				}

				if( $t_null_last ) {
					$t_null_expr = 'CASE WHEN NULLIF(' . $t_sort_col . ', \'\') IS NULL THEN 1 ELSE 0 END';
					$t_clause_for_select = $t_null_expr . ' AS ' . $t_field_alias . '_null';
					$t_clause_for_select .= ', ' . $t_sort_expr . ' AS ' . $t_field_alias;
					$t_clause_for_order = $t_field_alias . '_null ASC, ' . $t_field_alias . ' ' . $c_dir;
				} else {
					$t_clause_for_select = $t_sort_expr . ' AS ' . $t_field_alias;
					$t_clause_for_order = $t_field_alias . ' ' . $c_dir;
				}

				# Note: pgsql needs the sort expression to appear as member of the "select distinct"
				$this->add_select( $t_clause_for_select );
				$this->add_order( $t_clause_for_order );

			# if sorting by plugin columns
			} else if( column_is_plugin_column( $c_sort ) ) {
				$t_plugin_columns = columns_get_plugin_columns();
				$t_column_object = $t_plugin_columns[$c_sort];

				$t_clauses = $t_column_object->sortquery( $c_dir );
				if( is_array( $t_clauses ) ) {
					if( isset( $t_clauses['join'] ) ) {
						$this->add_join( $t_clauses['join'] );
					}
					if( isset( $t_clauses['order'] ) ) {
						$this->add_order( $t_clauses['order'] );
					}
				}

			# standard column
			} else {
				$t_sort_col = '{bug}.' . $c_sort;

				# When sorting by due_date, always display undefined dates last.
				# Undefined date is defaulted as "1" in database, so add a special
				# sort clause to group and sort by this.
				if( 'due_date' == $c_sort && 'ASC' == $c_dir ) {
					$t_null_expr = 'CASE ' . $t_sort_col . ' WHEN 1 THEN 1 ELSE 0 END';
					$this->add_select( $t_null_expr . ' AS due_date_sort_null' );
					$this->add_order( 'due_date_sort_null ASC' );
				}
				# main sort clause for due date
				$this->add_order( $t_sort_col . ' ' .$c_dir );
			}
		}

		# add basic sorting if necessary
		if( !in_array( 'last_updated', $t_sort_fields ) ) {
			$this->add_order( '{bug}.last_updated DESC' );
		}
		if( !in_array( 'date_submitted', $t_sort_fields ) ) {
			$this->add_order( '{bug}.date_submitted DESC' );
		}
	}
}
