<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: lost_pwd.php,v 1.3 2004-10-25 19:45:04 marcelloscata Exp $
	# --------------------------------------------------------

	# ======================================================================
	# Author: Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
	# ======================================================================

	require_once( 'core.php' );

	# lost password feature disabled or reset password via email disabled -> stop here!
	if( OFF == config_get( 'lost_password_feature' ) ||
		OFF == config_get( 'send_reset_password' ) ) {
		trigger_error( ERROR_LOST_PASSWORD_NOT_ENABLED, ERROR );
	}

	$f_username = gpc_get_string('username');
	$f_email = gpc_get_string('email');

	$f_email = email_append_domain( $f_email );
	email_ensure_valid( $f_email );

	$t_user_table = config_get( 'mantis_user_table' );

	$query = 'SELECT id FROM ' . $t_user_table . ' WHERE username = \'' . $f_username . '\' and email = \'' . $f_email . '\'';
	$result = db_query( $query );

	if ( 0 == db_num_rows( $result ) ) {
		trigger_error( ERROR_LOST_PASSWORD_NOT_MATCHING_DATA, ERROR );
	}

	if( is_blank( $f_email ) ) {
		trigger_error( ERROR_LOST_PASSWORD_NO_EMAIL_SPECIFIED, ERROR );
	}

	$row = db_fetch_array( $result );
	$t_user_id = $row['id'];

	if( user_is_protected( $t_user_id ) ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}

	if( !user_is_lost_password_request_allowed( $t_user_id ) ) {
		trigger_error( ERROR_LOST_PASSWORD_MAX_IN_PROGRESS_ATTEMPTS_REACHED, ERROR );
	}

	$t_confirm_hash = auth_generate_confirm_hash( $t_user_id );
	email_send_confirm_hash_url( $t_user_id, $t_confirm_hash );

	user_increment_lost_password_in_progress_count( $t_user_id );

	$t_redirect_url = 'login_page.php';

	html_page_top1();
	html_page_top2();
?>

<br />
<div align="center">
<table class="width50" cellspacing="1">
<tr>
	<td class="center">
		<b><?php echo lang_get( 'lost_password_done_title' ) ?></b>
	</td>
</tr>
<tr>
	<td>
		<br/>
		<?php echo lang_get( 'reset_request_in_progress_msg' ) ?>
		<br/><br/>
	</td>
</tr>
</table>
<br />
<?php print_bracket_link( 'login_page.php', lang_get( 'proceed' ) ); ?>
</div>

<?php html_page_bottom1a( __FILE__ ) ?>
