<html>
<head>
<title>Administrator checks</title>
<style type="text/css">
body { background-color: #ffffff; font-family:Verdana, Arial; font-size: 10pt }
td { font-family:Verdana, Arial; font-size: 10pt }
p { font-family:Verdana, Arial; font-size: 10pt }
address { font-family:Verdana, Arial; font-size: 8pt }
span.required { font-family:Verdana, Arial; font-size: 10pt; color: #aa0000 }
span.bugnotedelete { font-family:Verdana, Arial; font-size: 8pt }
span.small { font-family:Verdana, Arial; font-size: 8pt }
span.smallcaption { font-family:Verdana, Arial; font-size: 8pt }
</style>
</head>
<body>
<? include( "core_API.php" ) ?>
<?
	define( "BAD", 0 );
	define( "GOOD", 1 );

	function print_test_result( $p_result ) {
		if ( BAD == $p_result ) {
			PRINT "<td bgcolor=#ff0088>BAD</td>";
		}

		if ( GOOD == $p_result ) {
			PRINT "<td bgcolor=#00ff88>GOOD</td>";
		}
	}
?>
<b>Checking your installation</b>
<table width="100%" bgcolor="#888888" border="0" cellpadding="2" cellspacing="1">
<!-- PHP version -->
<tr>
	<td bgcolor="#ffffff" colspan="2">
		You are using PHP version <? echo phpversion() ?>
	</td>
</tr>
<!-- Test DATABASE part 1 -->
<tr>
	<td bgcolor="#ffffff">
		Opening connection to database on host [<? echo $g_hostname ?>] with username [<? echo $g_db_username ?>]
	</td>
	<?
		$result = mysql_connect( $g_hostname, $g_db_username, $g_db_password );
		if ( false == $result ) {
			print_test_result( BAD );
		} else {
			print_test_result( GOOD );
		}
	?>
</tr>
<!-- Test DATABASE part 2 -->
<tr>
	<td bgcolor="#ffffff">
		Selecting database [<? echo $g_database_name ?>]
	</td>
	<?
		$result = mysql_select_db( $g_database_name );
		if ( false == $result ) {
			print_test_result( BAD );
		} else {
			print_test_result( GOOD );
		}
	?>
</tr>
<!-- Absolute path check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if your $g_absolute_path variable has a trailing /
	</td>
	<?
		if ( "/" == $g_absolute_path[strlen($g_absolute_path)-1] ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- Function check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if function crypt() exists
	</td>
	<?
		if ( function_exists( "crypt" ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- Function check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if function getmxrr() exists
	</td>
	<?
		if ( function_exists( "getmxrr" ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- Function check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if function checkdnsrr() exists
	</td>
	<?
		if ( function_exists( "checkdnsrr" ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- Function check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if function wordwrap() exists
	</td>
	<?
		if ( function_exists( "wordwrap" ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- Function check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if function str_pad() exists
	</td>
	<?
		if ( function_exists( "str_pad" ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- Function check -->
<tr>
	<td bgcolor="#ffffff">
		Checking to see if function is_uploaded_file() exists
	</td>
	<?
		if ( function_exists( "is_uploaded_file" ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- PHP Setup check -->
<tr>
	<td bgcolor="#ffffff">
		register_globals should be set to On or 1
	</td>
	<?
		$test_val = get_cfg_var( "register_globals" );
		#echo $test_val;
		if ( ( 1 == $test_val ) || ( "On" === $test_val ) ) {
			print_test_result( GOOD );
		} else {
			print_test_result( BAD );
		}
	?>
</tr>
<!-- PHP Setup check -->
<tr>
	<td bgcolor="#ffffff">
		magic_quotes_gpc
	</td>
	<td bgcolor="#ffffff">
		<? echo get_cfg_var( "magic_quotes_gpc" ) ?>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		gpc_order
	</td>
	<td bgcolor="#ffffff">
		<? echo get_cfg_var( "gpc_order" ) ?>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		variables_order
	</td>
	<td bgcolor="#ffffff">
		<? echo get_cfg_var( "variables_order" ) ?>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		include_path
	</td>
	<td bgcolor="#ffffff">
		<? echo get_cfg_var( "include_path" ) ?>
	</td>
</tr>
<tr>
	<td bgcolor="#ffffff">
		short_open_tag
	</td>
	<td bgcolor="#ffffff">
		<? echo get_cfg_var( "short_open_tag" ) ?>
	</td>
</tr>
</table>

</body>
</html>