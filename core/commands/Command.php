<?php
/**
 * A base class for intent based commands that can accomplish a task.
 * Such commands will be used from web ui action pages, REST API, SOAP API, etc.
 * This provides consistency across such callers while being agnostic of the
 * caller.
 *
 * The command pattern will build on top of model, APIs, configurations, and
 * authorization.
 */
abstract class Command
{
	protected $data;
	protected $executionResults;

	/**
	 * Command constructor taking in all required data to execute the command.
	 *
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		$this->data = $p_data;
	}

	/**
	 * Make sure that provided data is valid and update data with default
	 * value that are missing.
	 *
	 * @return void
	 */
	abstract protected function validate();

	/**
	 * The core execution of the command (e.g. assigning an issue) with no knowledge
	 * about side effects of such execution, e.g. an email will be sent via a post processor.
	 *
	 * @return array The results of the command processing that goes into $executionResults
	 */
	abstract protected function process();

	/**
	 * Execute the command.  This may throw a CommandException is execution is interrupted.
	 * The command is expected to trigger events that are handled by plugins as part of
	 * exection.
	 *
	 * @return array Execution result
	 */
	public function execute() {
		$this->validate();
		$this->executionResults = $this->process();
		return $this->executionResults;
	}
}
