<?php
	/*
	This script does the following:

	- Checks for missing strings
	- Checks for duplicate strings
	- Orders strings according to the English template.
	- Fills in missing strings with English strings.

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
	# parse the string and grab the variable
	function get_key( $p_string ) {
		$p_string = trim( $p_string );
		if ( '$' != $p_string[0] ) {
			return '';
		}
		$p_string = str_replace( ' ', '', $p_string );
		$p_array = explode('=', $p_string);
		return trim($p_array[0]);
	}
	# - ---
	function check_missing( $p_file ) {
		$strings = file( $p_file );
		$lang_strings = array();
		foreach( $strings as $string ) {
			$string = trim( $string );
			$key = get_key( $string );
			if ( strlen( $key ) > 0 ) {
				$lang_strings[$key] = $string;
			}
		}
		$fp = fopen( $p_file, 'wb' );

		# print out header
		foreach( $strings as $string ) {
			$p_string = trim( $string );
			if ( '?>' === $p_string ) {
				fwrite( $fp, rtrim($string)."\n" );
				break;
			}
			fwrite( $fp, rtrim($string)."\n" );
		}

		# grab english strings
		$english_strings = file( 'strings_english.txt' );
		$skip = 0;
		foreach ( $english_strings as $english_string ) {
			$english_string = trim( $english_string );
			$p_english_string = trim( $english_string );
			# ignore header section
			if ( 0 == $skip ) {
				if ( '?>' == $p_english_string ) {
					$skip = 1;
				}
			} else {
				# grab the key
				# if found then use the lang string
				$key = get_key( $english_string );
				if ( array_key_exists( $key, $lang_strings ) ) {
					fwrite( $fp, $lang_strings[$key]."\n" );
				# else use the english string
				} else {
					fwrite( $fp, $english_string."\n" );
				}
			}
		}
		fclose( $fp );
	}
	# - ---
	function check_duplicates( $p_file ) {
		$strings = file( $p_file );
		$lang_strings = array();
		$counter = 0;
		foreach( $strings as $string ) {
			$string = trim( $string );
			$key = get_key( $string );
			$counter++;
			if ( strlen( $key ) > 0 ) {
				if ( array_key_exists( $key, $lang_strings ) ) {
					echo "DUPLICATE: $key (line: $counter)\n";
				} else {
					$lang_strings[$key] = $string;
				}
			}
		}
	}
	# - ---
	function print_usage() {
		echo "\nUsage:\n        php -q check_lang.php <path/folder>\n";
	}
	# - ---

	# -- MAIN --
	$argv = $_SERVER['argv'];
	$argc = $_SERVER['argc'];

	# too few arguments?
	if ( $argc < 2 ) {
		print_usage();
		exit;
	} else if ( !is_dir( $argv[1] ) ) {
		print_usage();
		exit;
	}

	grab_lang_files();

	foreach( $lang_files as $file ) {
		if ( 'strings_english.txt' != $file ) {
			echo "Processing: $file\n";
			check_missing( $file );
			check_duplicates( $file );
		}
	}
?>
