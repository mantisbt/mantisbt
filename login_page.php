<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Login page POSTs results to login.php
	# Check to see if the user is already logged in via login_cookie_check()
?>
<?php
	require_once( 'core.php' );
	require_once( $g_core_path . 'obsolete.php' );

	# Check to see if the user is logged in and then validate the cookie value
	if ( !is_blank( $g_string_cookie_val ) ) {
		login_cookie_check( 'main_page.php' );
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2a() ?>

<br />
<div align="center">
<?php
	$f_error		= gpc_get_bool( 'error' );
	$f_cookie_error	= gpc_get_bool( 'cookie_error' );
	$f_return		= gpc_get_string( 'return', '' );
	$f_project_id	= gpc_get_int( 'project_id', -1 );

	# Only echo error message if error variable is set
	if ( $f_error ) {
		echo $MANTIS_ERROR[ERROR_LOGIN].'<br />';
	}
	if ( $f_cookie_error ) {
		echo $MANTIS_ERROR[ERROR_COOKIES_DISABLED].'<br />';
	}

	# Display short greeting message
	echo lang_get( 'login_page_info' );
?>
</div>

<?php # Login Form BEGIN ?>
<br />
<div align="center">
<form name="login_form" method="post" action="login.php">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title">
		<?php
			if ( !is_blank($f_return) ) {
			?>
				<input type="hidden" name="return" value="<?php echo htmlentities($f_return) ?>" />
				<?php
			}

			if ( $f_project_id > -1 ) {
			?>
				<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
				<?php
			}
			?>
		<?php echo lang_get( 'login_title' ) ?>
	</td>
	<td class="right">
	<?php
		if ( ON == config_get( 'allow_anonymous_login' ) ) {
			print_bracket_link( 'login_anon.php', lang_get( 'login_anonymously' ) );
		}
	?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo lang_get( 'username' ) ?>:
	</td>
	<td width="75%">
		<input type="text" name="username" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'password' ) ?>:
	</td>
	<td>
		<input type="password" name="password" size="16" maxlength="32" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'save_login' ) ?>:
	</td>
	<td>
		<input type="checkbox" name="perm_login" />
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'login_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Login Form END ?>

<?php print_signup_link() ?>

<script type="text/javascript" language="JavaScript">
<!--
	window.document.login_form.username.focus();
//-->
</script>

<?php print_page_bot1( __FILE__ ) ?>
