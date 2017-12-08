<?php
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'constant_inc.php' );
require_api( 'config_api.php' );
require_api( 'user_api.php' );

use Mantis\Exceptions;

class MonitorCommand extends Command {
	private $projectId;
	private $loggedInUserId;
	private $userIdsToAdd;

	/**
	 * Data is expected to contain:
	 * - issue_id
	 * - users (array of users) each user as
	 *   - an array having a key value for id or name or real_name or name_or_realname.
	 *     id takes first priority, name second, real_name third, name_or_realname fourth.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data );
	}

	/**
	 * Validate the data.
	 */
	function validate() {
		# Validate issue id
		if( !isset( $this->data['issue_id'] ) ) {
			throw new ClientException( 'issue_id missing', ERROR_GPC_VAR_NOT_FOUND );
		}

		if( !is_numeric( $this->data['issue_id'] ) ) {
			throw new ClientException( 'issue_id must be numeric', ERROR_GPC_VAR_NOT_FOUND );
		}
		
		$t_issue_id = (int)$this->data['issue_id'];

		if( !bug_exists( $t_issue_id ) ) {
			throw new ClientException( "Issue id {$t_issue_id} not found", ERROR_BUG_NOT_FOUND, ERROR_GPC_VAR_NOT_FOUND );
		}

		$this->projectId = bug_get_field( $t_issue_id, 'project_id' );
		$t_logged_in_user = auth_get_current_user_id();

		# Validate user id (if specified), otherwise set from context
		if( !isset( $this->data['users'] ) ) {
			if( !auth_is_user_authenticated() ) {
				throw new ClientException( 'user_id missing', ERROR_GPC_VAR_NOT_FOUND );
			}

			$this->data['users'] = array( 'id' => auth_get_current_user_id() );
		}

		# Normalize user objects
		$t_user_ids = array();
		foreach( $this->data['users'] as $t_user ) {
			$t_user_id = $this->getIdForUser( $t_user );
			
			# TODO: If we throw exception above, then this check will not be necessary
			if( $t_user_id ) {
				$t_user_ids[] = $t_user_id;
			}
		}

		$this->userIdsToAdd = array();
		foreach( $t_user_ids as $t_user_id ) {
			if( user_is_anonymous( $t_user_id ) ) {
				throw new ClientException( "anonymous account can't monitor issues", ERROR_PROTECTED_ACCOUNT );
			}
		
			if( $t_logged_in_user == $t_user_id ) {
				$t_access_level_config = 'monitor_bug_threshold';
			} else {
				$t_access_level_config = 'monitor_add_others_bug_threshold';
			}

			$t_access_level = config_get(
				$t_access_level_config,
				/* default */ null,
				/* user */ null,
				$this->projectId );

			if( !access_has_bug_level( $t_access_level, $t_issue_id ) ) {
				throw new ClientException( 'access denied', ERROR_ACCESS_DENIED );
			}

			$this->userIdsToAdd[] = $t_user_id;
		}
	}

	/**
	 * Process the command.
	 * 
	 * @returns null No output from this command.
	 */
	protected function process() {
		if( $this->projectId != helper_get_current_project() ) {
			# in case the current project is not the same project of the bug we are
			# viewing, override the current project. This to avoid problems with
			# categories and handlers lists etc.
			$g_project_override = $this->projectId;
		}
		
		foreach( $this->userIdsToAdd as $t_user_id ) {
			bug_monitor( $this->data['issue_id'], $t_user_id );
		}

		return null;
	}

	/**
	 * A helper method that takes an array that describes a user and returns
	 * the corresponding id or false if not found.
	 * 
	 * @return integer|boolean The user id or false if not found.
	 */
	private function getIdForUser( array $p_user ) {
		# TODO: move to a common utility method that replaced this method
		# and mci_get_user_id()

		$t_identifier = '';
		if( isset( $p_user['id'] ) ) {
			$t_user_id = $p_user['id'];
		} else if( isset( $p_user['name'] ) ) {
			$t_identifier = $p_user['name'];
			$t_user_id = user_get_id_by_name( $p_user['name'] );
		} else if( isset( $p_user['real_name'] ) ) {
			$t_identifier = $p_user['real_name'];
			$t_user_id = user_get_id_by_realname( $p_user['real_name'] );
		} else if( isset( $p_user['name_or_realname' ] ) ) {
			$t_identifier = $p_user['name_or_realname'];
			$t_user_id = user_get_id_by_name( $p_user['name_or_realname'] );
			if( !$t_user_id ) {
				$t_user_id = user_get_id_by_realname( $p_user['name_or_realname'] );
			}	
		}

		if( !$t_user_id ) {
			# TODO: throw exception equivalent to below error
			# error_parameters( $t_identifier );
			# trigger_error( ERROR_USER_BY_NAME_NOT_FOUND, E_USER_ERROR );
			return false;
		}

		if( !user_exists( $t_user_id ) ) {
			# TODO: trigger error
			return false;
		}

		return $t_user_id;
	}
}

