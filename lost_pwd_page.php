<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: lost_pwd_page.php,v 1.1 2004-08-14 15:26:20 thraxisp Exp $
	# --------------------------------------------------------

	# --------------------------------------------------------
	# 2004 by Marcello Scata' (marcello@marcelloscata.com) - ITALY
	# --------------------------------------------------------

	require_once( 'core.php' );

	# lost password feature disabled or reset password via email disabled -> stop here!
	if( OFF == config_get( 'lost_password_feature' ) ||
		OFF == config_get( 'send_reset_password' ) ) {
		trigger_error( ERROR_LOST_PASSWORD_NOT_ENABLED, ERROR );
	}

	html_page_top1();
	html_page_top2a();

	echo "<br />";
?>
<br />
<div align="center">
<form name="lost_password_form" method="post" action="lost_pwd.php">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan=2>
		<?php echo lang_get( 'lost_password_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="username" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="category" width="25%">
		<?php echo lang_get( 'email' ) ?>
	</td>
	<td width="75%">
		<?php print_email_input( 'email', '' ) ?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<br/>
		<?php echo lang_get( 'lost_password_info' ) ?>
		<br/><br/>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'submit_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php
	PRINT '<br /><div align="center">';
	print_login_link();
	PRINT '&nbsp;';
	print_signup_link();
	PRINT '</div>';

	if ( ON == config_get( 'use_javascript' ) ) {
?>
<!-- Autofocus JS -->
<script type="text/javascript" language="JavaScript">
window.document.lost_password_form.username.focus();
</script>

<?php
	}

	html_page_bottom1a( __FILE__ );
?>