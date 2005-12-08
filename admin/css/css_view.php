<?php include( "core.php" ); ?>
<p style="font-family: courier new, courier, fixed; font-size: 10pt; white-space: nowrap;">
<?php
	ob_start();

	include( 'css_inc.php' );
	$contents = ob_get_contents ();

	ob_end_clean();

	$contents = nl2br( string_html_specialchars( $contents ) );
	#$contents = str_replace( "\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $contents );
	#$contents = str_replace( " ", "&nbsp;", $contents );
	echo $contents;
?>
</p>
