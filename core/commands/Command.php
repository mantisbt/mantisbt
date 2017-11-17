<?php
require_once( dirname( __FILE__ ) . '/Context.php' );

/**
 * A base class for intent based commands that can accomplish a task.
 * Such commands will be used from web ui action pages, REST API, SOAP API, etc.
 * Core code and plugins can hook into such command to do pre/post-processing
 * with very clear understanding of the intent of the change, e.g. assigning an issue
 * vs. updating an issue (and checking updating fields to deduce the intent).
 *
 * The command pattern will build on top of an model that worries about database operations
 * without having to worry about business logic that triggers side effects like email messages.
 * This provides consistency of execution across different clients (UI, REST, etc) and ability to
 * understand intent by core code and plugins.
 */
abstract class Command
{
	protected $data;
	protected $context;
	protected $executionResults;
	protected $pre_exec_listeners = array();
	protected $post_exec_listeners = array();

	/**
	 * Command constructor taking in all required data to execute the command.
	 *
	 * @param array $p_data The command data.
	 * @param Context $p_context The command context.
	 */
	function __construct( array $p_data, Context $p_context ) {
		$this->data = $p_data;
		$this->context = $p_context;
	}

	/**
	 * Make sure that provided data is valid and update data with default
	 * value that are missing.
	 *
	 * @return void
	 */
	abstract protected function validate();

	/**
	 * Logging a message as a result of validating, processing, etc.
	 *
	 * @param string $p_message
	 */
	function log( $p_message ) {
		# TODO
	}

	/**
	 * Register functions that do validation before command is executed to see if it is allowed or
	 * modify the data of the command.
	 *
	 * @param $p_function Function owned by core or plugin to pre-process the command.
	 */
	function register_pre_exec( $p_function ) {
		$this->pre_exec_listeners[] = $p_function;
	}

	/**
	 * Register functions that do post processing of the command and its results.  For example,
	 * sending out an email, slack messages, etc can be a post processor.
	 *
	 * @param $p_function
	 */
	function register_post_exec( $p_function ) {
		$this->post_exec_listeners[] = $p_function;
	}

	/**
	 * The core execution of the command (e.g. assigning an issue) with no knowledge
	 * about side effects of such execution, e.g. an email will be sent via a post processor.
	 *
	 * @return array The results of the command processing that goes into $executionResults
	 */
	abstract protected function process();

	/**
	 * Execute the command.  This may throw a CommandException is execution is interrupted.
	 *
	 * @return void
	 */
	function execute() {
		# TODO: notify pre-processors and check that the command is allowed to execute, otherwise stop.

		# TODO: core execution of the command and return execution results + other information that may be
		# useful for post processors (e.g. we could capture before state or other).
		$this->executionResults = $this->process();

		# TODO: notify post-processors that would have access to data, context, and executionResults
	}
}
