<?php

class MantisDatabaseException extends MantisException {
    public function __construct($code = 0, $parameters, Exception $previous = null)
    {
		/* if we have some form of database exception, assume that the database don't want to treat
		 * the database as connected in the exception handler anymore
		 */
		global $g_db_connected;
		$g_db_connected = false;
		
		parent::__construct($code, $parameters, $previous);
	}
}