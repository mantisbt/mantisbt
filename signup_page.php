<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php
	# Check for invalid access to signup page
	if ( OFF == $g_allow_signup ) {
		print_header_redirect( 'login_page.php' );
		exit;
	}
?>
<?php print_page_top1() ?>
<?php
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_include_page );
?>

<br />
<div align="center">
<?php echo $s_signup_info ?>
</div>

<?php # Signup form BEGIN ?>
<br />
<div align="center">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title">
		<form method="post" action="signup.php">
		<?php echo $s_signup_title ?>
	</td>
	<td class="right">
		<?php print_bracket_link( 'login_page.php', $s_go_back ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="30%">
		<?php echo $s_username ?>:
	</td>
	<td width="70%">
		<input type="text" name="f_username" size="32" maxlength="32" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email ?>:
	</td>
	<td>
	<?php
		$t_limit_email_domain = config_get( 'limit_email_domain' );
		if ( $t_limit_email_domain ) {
			echo '<input type="text" name="f_email" size="20" maxlength="64" />'.'@'.$t_limit_email_domain;
		} else {
			echo '<input type="text" name="f_email" size="32" maxlength="64" />';
		}
	?>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_signup_button ?>" />
		</form>
	</td>
</tr>
</table>
</div>
<?php # Signup form END ?>

<?php print_page_bot1( __FILE__ ) ?>
