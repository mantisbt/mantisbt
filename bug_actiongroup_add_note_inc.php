<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_actiongroup_add_note_inc.php,v 1.1 2007-04-13 13:41:53 vboctor Exp $
	# --------------------------------------------------------

	/**
	 * Prints the title for the custom action page.	 
	 */
	function action_add_note_print_title() {
		echo lang_get( 'add_bugnote_title' );
	}

	/**
	 * Prints the field within the custom action form.  This has an entry for
	 * every field the user need to supply + the submit button.  The fields are
	 * added as rows in a table that is already created by the calling code.
	 * A row has two columns.         	 
	 */
	function action_add_note_print_fields() {
		echo '<tr><td><center><textarea name="bugnote_text" cols="80" rows="10" wrap="virtual"></textarea></center></td></tr>';
		echo '<tr><td><center><input type="submit" class="button" value="' . lang_get( 'add_bugnote_button' ) . ' " /></center></td></tr>';
	}

	/**
	 * Validates the action on the specified bug id.
	 * 
	 * @returns true    Action can be applied.
	 * @returns array( bug_id => reason for failure )	 
	 */
	function action_add_note_validate( $p_bug_id ) {
		$f_bugnote_text = gpc_get_string( 'bugnote_text' );

		if ( is_blank( $f_bugnote_text ) ) {
			error_parameters( lang_get( 'bugnote' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		$t_failed_validation_ids = array();
		$t_add_bugnote_threshold = config_get( 'add_bugnote_threshold' );
		$t_bug_id = $p_bug_id;

		if ( bug_is_readonly( $t_bug_id ) ) {
			$t_failed_validation_ids[$t_bug_id] = lang_get( 'actiongroup_error_issue_is_readonly' );
			return $t_failed_validation_ids;
		}

		if ( !access_has_bug_level( $t_add_bugnote_threshold, $t_bug_id ) ) {
			$t_failed_validation_ids[$t_bug_id] = lang_get( 'access_denied' );
			return $t_failed_validation_ids;
		}

		return true;
	}

	/**
	 * Executes the custom action on the specified bug id.
	 * 
	 * @param $p_bug_id  The bug id to execute the custom action on.
	 * 
	 * @returns true   Action executed successfully.
	 * @returns array( bug_id => reason for failure )               	 
	 */
	function action_add_note_process( $p_bug_id ) {
		$f_bugnote_text = gpc_get_string( 'bugnote_text' );
		bugnote_add ( $p_bug_id, $f_bugnote_text, '0:00', /* $p_private = */ false );
        return true;
    }
?>
