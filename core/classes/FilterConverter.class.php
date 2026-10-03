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
 * FilterConverter class.
 * @copyright Copyright 2014 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

global $g_absolute_path;
$t_soap_dir = $g_absolute_path . 'api/soap/';
require_once( $t_soap_dir . 'mc_api.php' );
require_once( $t_soap_dir . 'mc_account_api.php' );
require_once( $t_soap_dir . 'mc_enum_api.php' );
require_once( $t_soap_dir . 'mc_project_api.php' );

require_api( 'custom_field_api.php' );
require_api( 'filter_api.php' );

/**
 * FilterConverter class
 *
 * @package MantisBT
 * @subpackage classes
 */
class FilterConverter {
	/**
	 * @var int The logged in user id.
	 */
	private $user_id;

	/**
	 * @var string The logged in user's language
	 */
	private $lang;

	/**
	 * Constructor
	 *
	 * @param integer $p_user_id The logged in user id.
	 * @param string $p_lang The logged in user language.
	 */
	public function __construct( $p_user_id, $p_lang ) {
		$this->user_id = (int)$p_user_id;
		$this->lang = $p_lang;
	}

	/**
	 * Convert filter from internal format to API format.
	 *
	 * @param array $p_filter The filter definition.
	 * @return array The filter in API format ready to be converted to JSON.
	 */
	public function filterToJson( array $p_filter ) {
		$t_filter = array();
		$t_filter['id'] = (int)$p_filter['id'];
		$t_filter['name'] = $p_filter['name'];

		# Only include owner if it is different than user retrieving the filters list.
		if( (int)$p_filter['user_id'] != $this->user_id ) {
			$t_filter['owner'] = mci_account_get_array_by_id( $p_filter['user_id'] );
		}

		$t_filter['public'] = $p_filter['is_public'] == '1' ? true : false;
		$t_filter['project'] = mci_project_as_array_by_id( $p_filter['project_id'] );
		$t_filter['criteria'] = $this->filterCriteriaToJson( $p_filter['criteria'], (int)$p_filter['project_id'] );
		$t_filter['url'] = $p_filter['url'];

		return $t_filter;
	}

	/**
	 * Convert filter criteria from internal format to API format.
	 *
	 * @param array $p_criteria The criteria to convert.
	 * @param integer $p_project_id The project id the filter is saved under.
	 * @return array The converted criteria.
	 */
	private function filterCriteriaToJson( $p_criteria, $p_project_id ) {
		# Relative date descriptors are resolved up front so the dates reported are
		# the window as it stands now, not the snapshot the y/m/d slots were left
		# holding by whatever last wrote the filter. The descriptors themselves are
		# internal storage and are dropped below, alongside the fields they feed.
		$t_criteria = filter_resolve_relative_dates( $p_criteria );
	
		$this->renameField( $t_criteria, FILTER_PROPERTY_HANDLER_ID, 'handler' );
		$this->renameField( $t_criteria, FILTER_PROPERTY_REPORTER_ID, 'reporter' );
		$this->renameField( $t_criteria, FILTER_PROPERTY_MONITOR_USER_ID, 'monitored' );
		$this->renameField( $t_criteria, FILTER_PROPERTY_NOTE_USER_ID, 'commented' );
		$this->renameField( $t_criteria, FILTER_PROPERTY_CATEGORY_ID, 'category' );
		$this->renameField( $t_criteria, FILTER_PROPERTY_PROJECT_ID, 'project' );
		$this->renameField( $t_criteria, FILTER_PROPERTY_PROFILE_ID, 'profile' );

		$this->convertDateFieldsToJson( $t_criteria );

		# Do conversion in order of how they should appear in criteria json.
		$this->convertTypeToJson( $t_criteria );
		$this->convertMatchTypeToJson( $t_criteria );
		$this->convertProjectArrayToJson( $t_criteria );
		$this->convertCategoryArrayToJson( $t_criteria );

		$this->convertUserArrayToJson( $t_criteria, 'reporter' );
		$this->convertUserArrayToJson( $t_criteria, 'handler' );
		$this->convertUserArrayToJson( $t_criteria, 'monitored' );
		$this->convertUserArrayToJson( $t_criteria, 'commented' );

		$this->convertEnumToJson( $t_criteria, FILTER_PROPERTY_STATUS, 'status' );
		$this->convertHideStatusToJson( $t_criteria );

		$this->convertEnumToJson( $t_criteria, FILTER_PROPERTY_PRIORITY, 'priority' );
		$this->convertEnumToJson( $t_criteria, FILTER_PROPERTY_SEVERITY, 'severity' );
		$this->convertEnumToJson( $t_criteria, FILTER_PROPERTY_RESOLUTION, 'resolution' );
		$this->convertEnumToJson( $t_criteria, FILTER_PROPERTY_VIEW_STATE, 'view_state' );
		$this->convertEnumToJson( $t_criteria, FILTER_PROPERTY_PROJECTION, 'projection' );

		$this->convertVersionArrayToJson( $t_criteria, FILTER_PROPERTY_VERSION, $p_project_id );
		$this->convertVersionArrayToJson( $t_criteria, FILTER_PROPERTY_FIXED_IN_VERSION, $p_project_id );
		$this->convertVersionArrayToJson( $t_criteria, FILTER_PROPERTY_TARGET_VERSION, $p_project_id );
		
		$this->convertProfileToJson( $t_criteria );
		$this->convertStringArrayToJson( $t_criteria, FILTER_PROPERTY_PLATFORM );
		$this->convertStringArrayToJson( $t_criteria, FILTER_PROPERTY_BUILD );
		$this->convertStringArrayToJson( $t_criteria, FILTER_PROPERTY_OS );
		$this->convertStringArrayToJson( $t_criteria, FILTER_PROPERTY_OS_BUILD );

		$this->convertTagsToJson( $t_criteria );
		$this->convertCustomFieldsArrayToJson( $t_criteria );
		$this->convertTextSearchToJson( $t_criteria );
		$this->convertRelationshipToJson( $t_criteria );

		$this->convertViewOptionsToJson( $t_criteria );
		$this->convertSortOrder( $t_criteria );
		
		return $t_criteria;
	}

	/**
	 * Convert enum from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @param string $p_field     The enum field name in the criteria array.
	 * @param string $p_enum_name The enum name as defined in enum strings.
	 * @return void
	 */
	private function convertEnumToJson( &$p_criteria, $p_field, $p_enum_name ) {
		if( isset( $p_criteria[$p_field] ) ) {
			if( !is_array( $p_criteria[$p_field] ) ) {
				$p_criteria[$p_field] = array( $p_criteria[$p_field] );
			}
	
			$t_result = array();
			foreach( $p_criteria[$p_field] as $t_enum_code ) {
				switch( $t_enum_code ) {
					case META_FILTER_ANY:
						// $t_result[] = array( 'id' => '[any]' );
						// break;
						unset( $p_criteria[$p_field] );
						return;
					default:
						$t_result[] = mci_enum_get_array_by_id( $t_enum_code, $p_enum_name, $this->lang );
						break;
				}
			}

			unset( $p_criteria[$p_field] );
			$p_criteria[$p_field] = $t_result;
		}
	}

	/**
	 * Convert fields with value of array of strings from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @param string $p_field     The field name in the criteria array.
	 * @return void
	 */
	private function convertStringArrayToJson( &$p_criteria, $p_field ) {
		$t_result = array();

		if( isset( $p_criteria[$p_field] ) ) {
			foreach( $p_criteria[$p_field] as $t_value ) {
				switch( $t_value ) {
					case META_FILTER_ANY:
						unset( $p_criteria[$p_field] );
						return;
					case META_FILTER_NONE:
						$t_result[] = array( 'id' => '[none]' );
						break;
					default:
						$t_result[] = array( 'name' => $t_value );
						break;
				}
			}
		}

		unset( $p_criteria[$p_field] );
		$p_criteria[$p_field] = $t_result;
	}

	/**
	 * Convert fields with value of user array from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @param string $p_field     The field name in the criteria array.
	 * @return void
	 */
	private function convertUserArrayToJson( &$p_criteria, $p_field ) {
		if( isset( $p_criteria[$p_field] ) ) {
			$t_result = array();

			foreach( $p_criteria[$p_field] as $t_user ) {
				switch( $t_user ) {
					case META_FILTER_ANY:
						unset( $p_criteria[$p_field] );
						return;
					case META_FILTER_NONE:
						$t_result[] = array( 'id' => '[none]' );
						break;
					case META_FILTER_MYSELF:
						$t_result[] = array( 'id' => '[myself]' );
						break;
					default:
						$t_result[] = mci_account_get_array_by_id( $t_user );
						break;
				}
			}

			unset( $p_criteria[$p_field] );
			$p_criteria[$p_field] = $t_result;
		}
	}

	/**
	 * Rename a field in the criteria
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @param string $p_old_name  The old name.
	 * @param string $p_new_name  The new name.
	 * @return void
	 */
	private function renameField( &$p_criteria, $p_old_name, $p_new_name ) {
		if( isset( $p_criteria[$p_old_name] ) ) {
			$p_criteria[$p_new_name] = $p_criteria[$p_old_name];
			unset( $p_criteria[$p_old_name] );
		}
	}

	/**
	 * Convert custom fields from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertCustomFieldsArrayToJson( &$p_criteria ) {
		# The descriptors have already been resolved into the timestamps below by
		# filterCriteriaToJson(); they are read here only to publish each relative
		# bound alongside the timestamp it resolved to, and the internal property
		# itself never reaches the payload.
		$t_relative = isset( $p_criteria[FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE] )
			? $p_criteria[FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE]
			: array();
		unset( $p_criteria[FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE] );

		$t_field = 'custom_fields';
		if( isset( $p_criteria[$t_field] ) ) {
			$t_result = array();

			foreach( $p_criteria[$t_field] as $t_cf_id => $t_cf_values ) {
				$t_values = array();

				foreach( $t_cf_values as $t_value ) {
					if( $t_value === (int)META_FILTER_ANY || $t_value === (string)META_FILTER_ANY ) {
						$t_values = array();
						break;
					} else if( $t_value === (int)META_FILTER_NONE || $t_value === (string)META_FILTER_NONE ) {
						$t_values[] = '[none]';
					} else {
						$t_values[] = $t_value;
					}

					$t_def = custom_field_get_definition( $t_cf_id );
					$t_cf = array(
						'field' => array( 'id' => (int) $t_cf_id, 'name' => $t_def['name'] ),
						'value' => $t_values );
				}

				if( !empty( $t_values ) && isset( $t_relative[$t_cf_id] ) ) {
					$t_cf['value'] = $this->customFieldDateBoundsToJson( $t_values, $t_relative[$t_cf_id] );
				}

				if( !empty( $t_values ) ) {
					$t_result[] = $t_cf;
				}
			}

			if( empty( $t_result ) ) {
				unset( $p_criteria[$t_field] );
			} else {
				unset( $p_criteria[$t_field] );
				$p_criteria[$t_field] = $t_result;
			}
		}
	}

	/**
	 * Convert projects from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertProjectArrayToJson( &$p_criteria ) {
		$t_field = 'project';
		if( isset( $p_criteria[$t_field] ) ) {
			$t_result = array();
			$t_count = count( $p_criteria[$t_field] );
			foreach( $p_criteria[$t_field] as $t_project_id ) {
				switch( $t_project_id ) {
					case META_FILTER_CURRENT:
						if( $t_count == 1 ) {
							unset( $p_criteria[$t_field] );
							return;
						}

						$t_result[] = array( 'id' => '[current]' );
						break;
					default:
						$t_result[] = mci_project_as_array_by_id( $t_project_id );
						break;
				}
			}

			unset( $p_criteria[$t_field] );
			$p_criteria[$t_field] = $t_result;
		}
	}

	/**
	 * Convert category from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertCategoryArrayToJson( &$p_criteria ) {
		$t_field = 'category';
		if( isset( $p_criteria[$t_field] ) ) {
			$t_result = array();

			foreach( $p_criteria[$t_field] as $t_value ) {
				switch( $t_value ) {
					case META_FILTER_ANY:
						// $t_result[] = array( 'id' => '[any]' );
						// break;
						unset( $p_criteria[$t_field] );
						return;
					default:
						$t_result[] = mci_get_category( $t_value );
						break;
				}
			}

			unset( $p_criteria[$t_field] );
			$p_criteria[$t_field] = $t_result;
		}
	}

	/**
	 * Convert fields with versions from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @param string $p_field     The field name in the criteria array.
	 * @param integer $p_project_id Integer representing project id.
	 * @return void
	 */
	private function convertVersionArrayToJson( &$p_criteria, $p_field, $p_project_id ) {
		if( isset( $p_criteria[$p_field] ) ) {
			$t_result = array();

			foreach( $p_criteria[$p_field] as $t_version_string ) {
				switch( $t_version_string ) {
					case META_FILTER_ANY:
						// $t_result[] = array( 'id' => '[any]' );
						// break;
						unset( $p_criteria[$p_field] );
						return;
					default:
						$t_result[] = mci_get_version( $t_version_string, $p_project_id );
						break;
				}
			}

			unset( $p_criteria[$p_field] );
			$p_criteria[$p_field] = $t_result;
		}
	}

	/**
	 * Convert sort order from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertSortOrder( &$p_criteria ) {
		$t_result = array();

		if( isset( $p_criteria[FILTER_PROPERTY_STICKY] ) ) {
			$t_show_sticky_default = config_get_global( 'show_sticky_issues' );
			$t_show_sticky = $p_criteria[FILTER_PROPERTY_STICKY] == 'on';
			if( $t_show_sticky != $t_show_sticky_default ) {
				$t_result[FILTER_PROPERTY_STICKY] = $t_show_sticky;
			}

			unset( $p_criteria[FILTER_PROPERTY_STICKY] );
		}

		if( isset( $p_criteria['sort'] ) ) {
			$t_sort_entry = array();
			$t_sort_entry['field'] = array( 'name' => $p_criteria['sort'] );
			unset( $p_criteria['sort'] );

			if( isset( $p_criteria['dir'] ) ) {
				$t_sort_entry['dir'] = $p_criteria['dir'];
				unset( $p_criteria['dir'] );
			}

			if( $t_sort_entry['field']['name'] != 'last_updated' || $t_sort_entry['dir'] != 'DESC' ) {
				$t_result['fields'] = array( $t_sort_entry );
			}
		}

		if( !empty( $t_result ) ) {
			$p_criteria['order_by'] = $t_result;
		}
	}

	/**
	 * Convert tags from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertTagsToJson( &$p_criteria ) {
		# TODO: not sure what this field is used for
		if( isset( $p_criteria['tag_select'] ) ) {
			unset( $p_criteria['tag_select'] );
		}

		$t_field = 'tag_string';
		if( isset( $p_criteria[$t_field] ) ) {
			if( empty( $p_criteria[$t_field ] ) ) {
				unset( $p_criteria[$t_field] );
				return;
			}

			$t_elements = explode( ',', $p_criteria[$t_field] );
			$t_result = array();
			foreach( $t_elements as $t_element ) {
				$t_element = trim( $t_element );
				$t_tag_row = tag_get_by_name( $t_element );
				$t_result[] = array(
					'id' => $t_tag_row['id'],
					'name' => $t_tag_row['name'],
					'owner' => mci_account_get_array_by_id( $t_tag_row['user_id'] ) );
			}

			unset( $p_criteria[$t_field ] );
			$p_criteria['tags'] = $t_result;
		}
	}

	/**
	 * Convert profile from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertProfileToJson( &$p_criteria ) {
		$t_field = 'profile';

		if( isset( $p_criteria[$t_field] ) ) {
			$t_result = array();

			foreach( $p_criteria[$t_field] as $t_profile_id ) {
				switch( $t_profile_id ) {
					case META_FILTER_ANY:
						unset( $p_criteria[$t_field] );
						return;
					default:
						$t_result[] = mci_profile_as_array_by_id( $t_profile_id );
						break;
				}
			}

			unset( $p_criteria[$t_field] );
			$p_criteria[$t_field] = $t_result;
		}
	}

	/**
	 * Convert matching type from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertMatchTypeToJson( &$p_criteria ) {
		$t_field = 'match_type';

		if( isset( $p_criteria[$t_field] ) ) {
			switch( $p_criteria[$t_field] ) {
				case FILTER_MATCH_ALL:
					unset( $p_criteria[$t_field] );
					return;
				case FILTER_MATCH_ANY:
					$t_result = 'any';
					break;
			}

			unset( $p_criteria[$t_field] );
			$p_criteria[$t_field] = $t_result;
		}
	}

	/**
	 * Convert relationship from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertRelationshipToJson( &$p_criteria ) {
		if( isset( $p_criteria['relationship_type'] ) ) {
			$t_issue_id = (int)$p_criteria['relationship_bug'];
			if( $t_issue_id != 0 ) {
				$t_result = array(
					'type' => relationship_get_name_for_api( $p_criteria['relationship_type'] ),
					'issue' => array( 'id' => $t_issue_id )
				);
	
				$p_criteria['relationship'] = $t_result;				
			}

			unset( $p_criteria['relationship_type'] );
			unset( $p_criteria['relationship_bug'] );
		}
	}

	/**
	 * Convert view options from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertViewOptionsToJson( &$p_criteria ) {
		$t_result = array();

		$t_page_size = (int)config_get_global( 'default_limit_view' );
		if( isset( $p_criteria['per_page'] ) ) {
			if( (int)$p_criteria['per_page'] != $t_page_size ) {
				$t_result['page_size'] = (int)$p_criteria['per_page'];
			}

			unset( $p_criteria['per_page'] );
		}

		$t_highlight_changed = (int)config_get_global( 'default_show_changed' );
		if( isset( $p_criteria['highlight_changed'] ) ) {
			if( (int)$p_criteria['highlight_changed'] != $t_highlight_changed ) {
				$t_result['highlight_changed'] = (int)$p_criteria['highlight_changed'];
			}

			unset( $p_criteria['highlight_changed'] );
		}

		if( !empty( $t_result ) ) {
			$p_criteria['view_options'] = $t_result;
		}
	}

	/**
	 * Convert hide status from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertHideStatusToJson( &$p_criteria ) {
		$t_field = 'hide_status';
		if( isset( $p_criteria[$t_field] ) ) {
			if( $p_criteria[$t_field][0] == META_FILTER_NONE ) {
				unset( $p_criteria[$t_field] );
			} else {
				$t_status_code = $p_criteria[$t_field][0];
				unset( $p_criteria[$t_field] );
				$p_criteria[$t_field] = mci_enum_get_array_by_id( $t_status_code, 'status', $this->lang );
			}
		}
	}

	/**
	 * Convert free text search field from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertTextSearchToJson( &$p_criteria ) {
		$t_field = 'search';
		if( isset( $p_criteria[$t_field] ) ) {
			if( empty( $p_criteria[$t_field] ) ) {
				unset( $p_criteria[$t_field] );
			}
		}
	}

	/**
	 * Convert filter version and type info from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertTypeToJson( &$p_criteria ) {
		unset( $p_criteria['_version'] );
		unset( $p_criteria['_source_query_id'] );
		unset( $p_criteria['_view_type'] );
		unset( $p_criteria['_filter_id'] );
	}

	/**
	 * Convert date fields from internal to API format.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function convertDateFieldsToJson( &$p_criteria ) {
		if( !$p_criteria[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] ) {
			$this->removeDateSubmitted( $p_criteria );
		} else {
			$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] = (int)$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY];
			$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] = (int)$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH];
			$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] = (int)$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR];
			$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] = (int)$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY];
			$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] = (int)$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH];
			$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] = (int)$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR];
		}

		if( !$p_criteria[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] ) {
			$this->remoteDateLastUpdated( $p_criteria );
		} else {
			$p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_DAY] = (int)$p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_DAY];
			$p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] = (int)$p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_MONTH];
			$p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] = (int)$p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_YEAR];
			$p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_DAY] = (int)$p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_DAY];
			$p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] = (int)$p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_MONTH];
			$p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] = (int)$p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_YEAR];
		}
	
		if( isset( $p_criteria[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] ) ) {
			$t_start_date = sprintf( '%04d-%02d-%02d', 
				$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR],
				$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH],
				$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] );

			$t_end_date = sprintf( '%04d-%02d-%02d', 
				$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR],
				$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH],
				$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] );
			
				$p_criteria['created_at'] = array(
					'from' => $this->dateBoundToJson( $t_start_date,
						$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE] ?? null ),
					'to' => $this->dateBoundToJson( $t_end_date,
						$p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE] ?? null ) );
				$this->removeDateSubmitted( $p_criteria );
		}

		if( isset( $p_criteria[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] ) ) {
			$t_start_date = sprintf( '%04d-%02d-%02d', 
				$p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_YEAR],
				$p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_MONTH],
				$p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_DAY] );

			$t_end_date = sprintf( '%04d-%02d-%02d', 
				$p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_YEAR],
				$p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_MONTH],
				$p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_DAY] );
			
			$p_criteria['updated_at'] = array(
				'from' => $this->dateBoundToJson( $t_start_date,
					$p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE] ?? null ),
				'to' => $this->dateBoundToJson( $t_end_date,
					$p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE] ?? null ) );
			$this->remoteDateLastUpdated( $p_criteria );
		}
	}

	/**
	 * Render one endpoint of a date range.
	 *
	 * A fixed endpoint is the date itself, "2026-09-01". A relative one is the
	 * descriptor that produced it, carrying the resolved date alongside:
	 *
	 *   { "anchor": "today", "offset": -7, "unit": "day", "date": "2026-09-01" }
	 *
	 * The two forms are exclusive on purpose. Publishing both would let a client
	 * read only the date and write back a filter that has silently become fixed;
	 * as it is, a client that does not know descriptors sees an object where it
	 * expected a string and fails visibly. Only relative filters are affected - a
	 * fixed one renders exactly as before.
	 *
	 * The descriptor is authoritative; "date" is derived from it as of this
	 * request, so a client can show the window without redoing the arithmetic.
	 *
	 * @param string     $p_date       Resolved date, YYYY-MM-DD.
	 * @param array|null $p_descriptor Relative date descriptor, or null if fixed.
	 * @return string|array The endpoint in API format.
	 */
	private function dateBoundToJson( $p_date, $p_descriptor ) {
		if( empty( $p_descriptor ) || !is_array( $p_descriptor ) ) {
			return $p_date;
		}

		# Normalized, so what is published is the descriptor that was actually
		# applied rather than whatever the stored value happened to say.
		$t_descriptor = filter_relative_descriptor_normalize( $p_descriptor );
		$t_descriptor['date'] = $p_date;

		return $t_descriptor;
	}

	/**
	 * Render a date custom field's stored value with each relative bound replaced
	 * by the descriptor that produced it.
	 *
	 * The value stays the triple it has always been, and a fixed bound stays the
	 * plain timestamp. A relative one carries the timestamp it resolved to:
	 *
	 *   [
	 *    2,
	 *    { "anchor": "start_of_month", "offset": -1, "unit": "month", "timestamp": 1785542400 },
	 *    { "anchor": "today", "offset": 0, "unit": "day", "timestamp": 1788911998 }
	 *   ]
	 *
	 * The resolved value is a timestamp, not a date string, so a bound reads the
	 * same whichever form it takes. dateBoundToJson()'s reasoning about the forms
	 * being exclusive applies here unchanged.
	 *
	 * Which descriptor produced which timestamp depends on the date control, so
	 * the mapping comes from filter_custom_field_date_endpoint_sources() rather
	 * than by position. A slot the control fills with an open-ended sentinel has
	 * no descriptor behind it and is left alone.
	 *
	 * @param array $p_values      Stored triple, array( date control, start timestamp, end timestamp ).
	 * @param array $p_descriptors array( 'start' => descriptor or null, 'end' => descriptor or null ),
	 *                             as stored for this field under custom_fields_relative.
	 * @return array The triple, with relative bounds rendered as descriptors.
	 */
	private function customFieldDateBoundsToJson( array $p_values, array $p_descriptors ) {
		if( count( $p_values ) != 3 ) {
			return $p_values;
		}

		$t_sources = filter_custom_field_date_endpoint_sources( $p_values[0] );

		foreach( array( 1, 2 ) as $t_slot ) {
			$t_source = $t_sources[$t_slot - 1];
			if( null === $t_source || empty( $p_descriptors[$t_source] ) ) {
				continue;
			}

			# Normalized, so what is published is the descriptor that was actually
			# applied rather than whatever the stored value happened to say.
			$t_descriptor = filter_relative_descriptor_normalize( $p_descriptors[$t_source] );
			$t_descriptor['timestamp'] = $p_values[$t_slot];
			$p_values[$t_slot] = $t_descriptor;
		}

		return $p_values;
	}

	/**
	 * Remove internal date submitted filtering fields.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function removeDateSubmitted( &$p_criteria ) {
		unset( $p_criteria[FILTER_PROPERTY_FILTER_BY_DATE] );
		unset( $p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE] );
		unset( $p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE] );
		unset( $p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] );
		unset( $p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] );
		unset( $p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] );
		unset( $p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] );
		unset( $p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] );
		unset( $p_criteria[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] );
	}

	/**
	 * Remove internal date last updated filtering fields.
	 *
	 * @param array  $p_criteria  The criteria to be updated.
	 * @return void
	 */
	private function remoteDateLastUpdated( &$p_criteria ) {
		unset( $p_criteria[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] );
		unset( $p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE] );
		unset( $p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE] );
		unset( $p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_DAY] );
		unset( $p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] );
		unset( $p_criteria[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] );
		unset( $p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_DAY] );
		unset( $p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] );
		unset( $p_criteria[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] );
	}
}