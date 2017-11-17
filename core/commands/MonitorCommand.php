<?php
# TODO: Do autoload for commands.
require_once( dirname( __FILE__ ) . '/Command.php' );

class MonitorCommand extends Command {
	function __construct( array $p_data, Context $p_context ) {
		parent::__construct( $p_data, $p_context );
	}

	function validate() {
		# Validate issue id
		if( !isset( $this->data['issue_id'] ) ) {
			throw new CommandException( HTTP_STATUS_BAD_REQUEST, 'issue_id missing', ERROR_GPC_VAR_NOT_FOUND );
		}

		if( !is_numeric( $this->data['issue_id'] ) || (int)$this->data['issue_id'] < 1 ) {
			throw new CommandException( HTTP_STATUS_BAD_REQUEST, 'issue_id must be a valid issue id', ERROR_GPC_NOT_NUMBER );
		}

		if( !bug_exists( (int) $this->data['issue_id'] ) ) {
			throw new CommandException( HTTP_STATUS_NOT_FOUND, "Issue id {$this->data['issue_id']} not found", ERROR_BUG_NOT_FOUND );
		}

		# Validate user id (if specified), otherwise set from context
		if( !isset( $this->data['user_id'] ) ) {
			if( !$this->context->userAuthenticated() ) {
				throw new CommandException( HTTP_STATUS_BAD_REQUEST, 'user_id missing', ERROR_GPC_VAR_NOT_FOUND );
			}

			$this->data['user_id'] = $this->context->getUserId();
		}
	}

	function process() {
		# TODO: handle an array of user ids
		# TODO: trigger exceptions from within the method or if method returns false.
		bug_monitor( $this->data['issue_id'], $this->data['user_id'] );
	}

}

