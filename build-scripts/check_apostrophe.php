<?php
	/*
	This script does the following:

	- Checks for unescaped single quotes in the middle of strings.  User must edit by hand.

	*/

	# -- GLOBAL VARIABLES --
	$lang_files = array();
	$english_strings = array();
	# - ---
	# read in all language files
	function grab_lang_files() {
		global $lang_files;

		if ($handle = opendir('.')) {
		    while (false !== ($file = readdir($handle))) {
			    if (strpos($file,'.txt')>0) {
			    	$lang_files[] = $file;
			    }
		    }
		    closedir($handle);
		}
	}
	# - ---
	function found( $p_haystack, $p_needle ) {
		if ( strpos( $p_haystack, $p_needle ) > 0 ) {
			return true;
		} else {
			return false;
		}
	}
	# - ---
	function check_apostrophes( $p_file ) {
		$strings = file( $p_file );
		$lang_strings = array();
		$counter = 0;
		foreach( $strings as $string ) {
			$counter++;
			$string = trim( $string );
			$apostrophe_count = substr_count( $string, "'" );
			$apostrophe_escaped_count = substr_count( $string, "\'" );
			$diff = $apostrophe_count -  $apostrophe_escaped_count;
			if ( ( $diff ) > 2 ) {
				echo "$counter: $diff\n";
			}
		}
	}
	# - ---
	function print_usage() {
		echo "\nUsage:\n        php -q check_apostrophe.php <lang file>\n";
	}
	# - ---

	# -- MAIN --
	$argv = $_SERVER['argv'];
	$argc = $_SERVER['argc'];

	# too few arguments?
	if ( $argc < 2 ) {
		print_usage();
		exit;
	} else if ( is_dir( $argv[1] ) ) {
		print_usage();
		exit;
	}

	echo "Processing: ".$argv[1]."\n";
	check_apostrophes( $argv[1] );
?>
