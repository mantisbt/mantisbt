<? include( "core_API.php" ) ?>
<?
	if ( !isset( $f_action ) ) {
		$res = setcookie( "testcookie", "blah" );
	} else if ( $f_action=="test" ) {
		$cookieval = $HTTP_COOKIE_VARS["testcookie"];
	}
?>
<h2>This file will try to identify the possible problems that windows users are having with not being able to login.</h2>
<p>
<? if ( $f_action=="test" ) { ?>
	An attempt was made to set a cookie.
	<p>
	The value is :<b><? echo $cookieval ?></b>:.  Should be :<b>blah</b>:.
	<p>
	Result is:
	<?
		if ($cookieval=="blah") {
			PRINT "<b>PASSED</b>";
		} else {
			PRINT "<b>FAILED</b>";
		}
	?>
	<p>
	If the test is failed then your browser may have cookies turned off.  Additionally, your webserver or PHP may be configured incorrectly.
<? } ### end f_action if ?>
<p>
<a href="admin_cookiecheck.php3?f_action=test">Click here</a> to reload the page and see if the value was set correctly.
