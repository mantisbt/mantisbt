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
 * Profile API
 *
 * @package CoreAPI
 * @subpackage ProfileAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * System Profile information
 *
 * @property int $id
 * @property int $user_id
 * @property string $platform
 * @property string $os
 * @property string $os_build
 * @property string $description
 */
class ProfileData {
	/** @var int Profile Id */
	protected int $id;

	/** @var int User Id */
	protected int $user_id;

	/** @var string Platform */
	protected string $platform;

	/** @var string Operating System */
	protected string $os;

	/** @var string Operating System Build */
	protected string $os_build;

	/** @var string Profile description */
	protected string $description;

	/**
	 * Class constructor
	 *
	 * @param int $p_profile_id Id of Profile to initialize object with.
	 *
	 * @throws ClientException if the Profile Id does not exist.
	 */
	public function __construct( int $p_profile_id ) {
		$t_row = $this->load_row( $p_profile_id );

		$this->id = $t_row['id'];
		$this->user_id = $t_row['user_id'];
		$this->platform = $t_row['platform'];
		$this->os = $t_row['os'];
		$this->os_build = $t_row['os_build'];
		$this->description = $t_row['description'];
	}

	/**
	 * Load and return a profile row from the database.
	 *
	 * @param int $p_profile_id A profile identifier.
	 *
	 * @return array
	 * @throws ClientException if the Profile Id does not exist
	 */
	public static function load_row( int $p_profile_id ): array {
		$t_query = new DbQuery();
		$t_query->sql( 'SELECT * FROM {user_profile} WHERE id=:profile_id' );
		$t_query->bind( 'profile_id',  $p_profile_id );
		$t_query->execute();

		$t_row = $t_query->fetch();
		if( !$t_row ) {
			throw new ClientException(
				"Profile #$p_profile_id not found",
				ERROR_USER_PROFILE_NOT_FOUND,
				array( $p_profile_id )
			);
		}

		return $t_row;
	}

	/**
	 * Allow direct, read-only access to class properties.
	 *
	 * @param string $p_property Property name.
	 *
	 * @return int|string Property value.
	 * @throws ClientException if property does not exist
	 */
	public function __get( string $p_property ) {
		if( !property_exists( $this, $p_property ) ) {
			throw new ClientException( "Unknown property '$p_property'", ERROR_GENERIC );
		}
		return $this->$p_property;
	}

	/**
	 * Return the profile's name as concatenation of platform, os and build.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return "$this->platform $this->os $this->os_build";
	}

	/**
	 * Returns true if the specified profile is global.
	 *
	 * @return bool
	 */
	public function is_global(): bool {
		return $this->user_id == ALL_USERS;
	}

	/**
	 * Check if user is allowed to update or delete the given Profile.
	 *
	 * @param int|null $p_user_id User id or null for current user (default).
	 *
	 * @return bool True if allowed, false otherwise.
	 */
	function can_update( ?int $p_user_id = null ): bool {
		$t_user_id = $p_user_id === null ? auth_get_current_user_id() : $p_user_id;

		# Global profile ?
		if( $this->is_global()) {
			return access_has_global_level( config_get( 'manage_global_profile_threshold' ) );
		}

		return $this->user_id == $t_user_id;
	}

	/**
	 * Throws Access Denied error if user is not allowed to update the Profile.
	 *
	 * @param int|null $p_user_id User id or null for current user (default).
	 */
	function ensure_can_update( ?int $p_user_id = null ) {
		if( !$this->can_update( $p_user_id ) ) {
			access_denied();
		}
	}

}

/**
 * Create a new profile for the user, return the ID of the new profile.
 *
 * @param int    $p_user_id     A valid user identifier.
 * @param string $p_platform    Value for profile platform.
 * @param string $p_os          Value for profile operating system.
 * @param string $p_os_build    Value for profile operation system build.
 * @param string $p_description Description of profile.
 *
 * @return int
 * @throws ClientException if user is protected
 */
function profile_create( $p_user_id, $p_platform, $p_os, $p_os_build, $p_description ) {
	$p_user_id = (int)$p_user_id;

	profile_validate_before_update( $p_user_id, $p_platform, $p_os, $p_os_build, $p_description );

	# Add profile
	$t_query = new DbQuery();
	$t_query->sql( 'INSERT INTO {user_profile}
		( user_id, platform, os, os_build, description )
		VALUES
		( :user_id, :platform, :os, :os_build, :description )'
	);
	$t_query->bind( 'user_id',  $p_user_id );
	$t_query->bind( 'platform',  $p_platform );
	$t_query->bind( 'os',  $p_os );
	$t_query->bind( 'os_build',  $p_os_build );
	$t_query->bind( 'description',  $p_description );
	$t_query->execute();

	return db_insert_id( db_get_table( 'user_profile' ) );
}

/**
 * Delete a profile for the user.
 *
 * Note that although profile IDs are currently globally unique, the existing
 * code included the user_id in the query and I have chosen to keep that for
 * this API as it hides the details of id implementation from users of the API
 *
 * @param int $p_user_id    A valid user identifier.
 * @param int $p_profile_id A profile identifier.
 *
 * @return void
 * @throws ClientException if user is protected
 */
function profile_delete( $p_user_id, $p_profile_id ) {
	if( ALL_USERS != $p_user_id ) {
		user_ensure_unprotected( $p_user_id );
	}

	# Delete the profile
	$t_query = new DbQuery();
	$t_query->sql( 'DELETE FROM {user_profile} WHERE id=:profile_id AND user_id=:user_id' );
	$t_query->bind( 'profile_id',  $p_profile_id );
	$t_query->bind( 'user_id',  $p_user_id );
	$t_query->execute();
}

/**
 * Update a profile for the user.
 *
 * @param int    $p_user_id     A valid user identifier.
 * @param int    $p_profile_id  A profile identifier.
 * @param string $p_platform    Value for profile platform.
 * @param string $p_os          Value for profile operating system.
 * @param string $p_os_build    Value for profile operation system build.
 * @param string $p_description Description of profile.
 *
 * @return void
 * @throws ClientException if user is protected
 */
function profile_update( $p_user_id, $p_profile_id, $p_platform, $p_os, $p_os_build, $p_description ) {
	profile_validate_before_update( $p_user_id, $p_platform, $p_os, $p_os_build, $p_description );

	# Update profile
	$t_query = new DbQuery();
	$t_query->sql( 'UPDATE {user_profile}
		SET platform=:platform, 
			os=:os,
			os_build=:os_build,
			description=:description
		WHERE id=:profile_id AND user_id=:user_id'
	);
	$t_query->bind( 'platform',  $p_platform );
	$t_query->bind( 'os',  $p_os );
	$t_query->bind( 'os_build',  $p_os_build );
	$t_query->bind( 'description',  $p_description );
	$t_query->bind( 'profile_id',  $p_profile_id );
	$t_query->bind( 'user_id',  $p_user_id );
	$t_query->execute();
}

/**
 * Validates that the given profile data is valid, throw errors if not.
 *
 * @param int    $p_user_id     A valid user identifier.
 * @param string $p_platform    Value for profile platform.
 * @param string $p_os          Value for profile operating system.
 * @param string $p_os_build    Value for profile operation system build.
 * @param string $p_description Value for profile description.
 *
 * @throws ClientException
 * @internal
 */
function profile_validate_before_update( $p_user_id, $p_platform, $p_os, $p_os_build, $p_description ) {
	if( ALL_USERS != $p_user_id ) {
		user_ensure_unprotected( $p_user_id );
	}

	# platform cannot be blank
	if( is_blank( $p_platform ) ) {
		error_parameters( lang_get( 'platform' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# os cannot be blank
	if( is_blank( $p_os ) ) {
		error_parameters( lang_get( 'os' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# os_build cannot be blank
	if( is_blank( $p_os_build ) ) {
		error_parameters( lang_get( 'version' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# Description length
	helper_ensure_longtext_length_valid( $p_description, 'profile_description' );
}

/**
 * Return a profile row from the database.
 *
 * @param int $p_profile_id A profile identifier.
 *
 * @return array
 * @throws ClientException if the profile ID does not exist
 *
 * @deprecated 2.28.0 Use {@see ProfileData::load_row()} instead.
 */
function profile_get_row( $p_profile_id ) {
	error_parameters( __FUNCTION__ . '()', 'ProfileData::load_row()' );
	trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );

	return ProfileData::load_row( $p_profile_id );
}

/**
 * Return the profile's name as concatenation of platform, os and build.
 *
 * @param int $p_profile_id
 *
 * @return string
 * @throws ClientException if the profile ID does not exist
 *
 * @deprecated 2.28.0 Use {@see ProfileData::get_name()} instead.
 */
function profile_get_name( $p_profile_id ) {
	error_parameters( __FUNCTION__ . '()', 'ProfileData::get_name()' );
	trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );

	$t_profile = new ProfileData( $p_profile_id );
	return $t_profile->get_name();
}

/**
 * Return an array containing all rows for a given user.
 *
 * @param int  $p_user_id   A valid user identifier.
 * @param bool $p_all_users Include profiles for all users.
 *
 * @return array
 */
function profile_get_all_rows( $p_user_id, $p_all_users = false ) {
	$t_query = new DbQuery();
	$t_query->sql( 'SELECT * FROM {user_profile} WHERE user_id=:user_id' );
	if( $p_all_users && ALL_USERS != $p_user_id ) {
		$t_query->append_sql( ' OR user_id=' . ALL_USERS );
	}
	$t_query->append_sql( ' ORDER BY platform, os, os_build' );

	$t_query->bind( 'user_id',  $p_user_id );
	$t_query->execute();

	return $t_query->fetch_all();
}

/**
 * Return an array containing all profiles for a given user,
 * including global profiles.
 *
 * @param int $p_user_id A valid user identifier.
 *
 * @return array
 */
function profile_get_all_for_user( $p_user_id ) {
	return profile_get_all_rows( $p_user_id, $p_user_id != ALL_USERS );
}

/**
 * Return an array of strings containing unique values for the specified field
 * based on private and public profiles accessible to the specified user.
 *
 * @param string $p_field   Field name of the profile to retrieve.
 * @param int    $p_user_id A valid user identifier.
 *
 * @return array
 */
function profile_get_field_all_for_user( $p_field, $p_user_id = null ) {
	$c_user_id = ( $p_user_id === null ) ? auth_get_current_user_id() : $p_user_id;

	switch( $p_field ) {
		case 'id':
		case 'user_id':
		case 'platform':
		case 'os':
		case 'os_build':
		case 'description':
			$c_field = $p_field;
			break;
		default:
			trigger_error( ERROR_GENERIC, ERROR );
	}

	$t_query = new DbQuery();
	/** @noinspection PhpUndefinedVariableInspection */
	$t_query->sql( "SELECT DISTINCT $c_field 
		FROM {user_profile}
		WHERE user_id=:user_id OR user_id=" . ALL_USERS . "
		ORDER BY $c_field"
	);
	$t_query->bind( 'user_id',  $c_user_id );
	$t_query->execute();

	return array_column( $t_query->fetch_all(), $p_field );
}

/**
 * Return an array containing all profiles used in a given project.
 *
 * @param int $p_project_id A valid project identifier.
 *
 * @return array
 */
function profile_get_all_for_project( $p_project_id ) {
	$t_query = new DbQuery();
	$t_query->sql( 'SELECT DISTINCT(up.id), up.user_id, up.platform, up.os, up.os_build
		FROM {user_profile} up
		JOIN {bug} b ON b.profile_id = up.id
		WHERE ' . helper_project_specific_where( $p_project_id ) . '
		ORDER BY up.platform, up.os, up.os_build'
	);
	$t_query->execute();

	return $t_query->fetch_all();
}

/**
 * Returns the user's default profile.
 *
 * @param int $p_user_id A valid user identifier.
 *
 * @return int
 */
function profile_get_default( $p_user_id ) {
	return (int)user_pref_get_pref( $p_user_id, 'default_profile' );
}

/**
 * Returns true if the specified profile is global.
 *
 * @param int $p_profile_id A valid profile identifier.
 *
 * @return bool
 * @throws ClientException if the profile ID does not exist
 *
 * @deprecated 2.28.0 Use {@see ProfileData::is_global()} instead.
 */
function profile_is_global( $p_profile_id ) {
	error_parameters( __FUNCTION__ . '()', 'ProfileData::is_global()' );
	trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );

	$t_profile = new ProfileData( $p_profile_id );
	return $t_profile->is_global();
}
