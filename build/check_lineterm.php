<?php
	/*
	This script does the following:

	- Checks the lineterm characters of each file and reportsif the formats are NOT in UNIX format (\n)
	- Rewrites files with UNIX lineterms only.

	*/

	# -- GLOBAL VARIABLES --
	$lang_files = array();
	$english_strings = array();
	# - ---
	define( 'LF_ONLY',        0 );
	define( 'CR_ONLY',        1 );
	define( 'CRLF_ONLY',      2 );
	define( 'LFCR_MIXED',     3 );
	define( 'LFCRLF_MIXED',   4 );
	define( 'CRLFCR_MIXED',   5 );
	define( 'CRLFCRLF_MIXED', 6 );
	# - ---
	function print_result( $p_result ) {
		switch( $p_result ) {
			case CRLF_ONLY:
				$format = "*** Windows";
				break;
			case CR_ONLY:
				$format = "*** Mac";
				break;
			case LF_ONLY:
				$format =  "UNIX";
				break;
			case LFCR_MIXED:
				$format =  "### Mixed (LF and CR)";
				break;
			case LFCRLF_MIXED:
				$format =  "### Mixed (LF and CRLF)";
				break;
			case CRLFCR_MIXED:
				$format =  "### Mixed (CRLF and CR)";
				break;
			case CRLFCRLF_MIXED:
				$format =  "### Mixed (CRLF and CR and LF)";
				break;
		}
		echo "$format Format";
	}

	# - ---
	# read in all files
	function process_files( $p_dir, $p_rewrite = false ) {
		$back = $cwd = getcwd();
		$cwd .= DIRECTORY_SEPARATOR . $p_dir;
		chdir( $cwd );

		if ( $handle = opendir( $cwd ) ) {

			# Get directory's contents
			#echo "Directory: " . getcwd() . "\n";
			$file_arr = array();
			while (false !== ( $file = readdir( $handle ) )) {
				$file_arr[] = $file;
			}
			closedir( $handle );

			# Process the files
			foreach( $file_arr as $file ) {
				#echo "file: $file\n";

				# Exclusions - continue to next file if match
				switch( $file ) {

					# File names
					case '.':
					case '..':
					case '.git':
						continue 2;

					default:
						# File extensions
						if( preg_match( '/\.(jpg|gif|png|zip|wav|ttf|woff|woff2)$/', $file ) ) {
							continue 2;
						}
				}

				if( is_dir( $file ) ) {
					# Recurse
					process_files( $file, $p_rewrite );
				} else {
					$filepath = $cwd . DIRECTORY_SEPARATOR . $file;
					$result = check_lineterm( $filepath );
					if( LF_ONLY != $result ) {
						echo $filepath . ": ";
						print_result( $result );
						if( $p_rewrite ) {
							echo " - ";
							rewrite_file( $filepath );
						}
						echo "\n";
					}
				}
			}
		}
		chdir( $back );
	}

	# - ---
	function rewrite_file( $p_file ) {
		echo "rewriting";
		$strings = file( $p_file );
		$fp = fopen( $p_file, 'wb' );
		foreach( $strings as $string ) {
			fwrite( $fp, rtrim($string) . "\n" );
		}
		fclose( $fp );
	}
	# - ---
	# reports if the line terms are LF, CR, CRLF, or a mix
	function check_lineterm( $p_file ) {
		$lf_count = 0;
		$cr_count = 0;
		$crlf_count = 0;

		$strings = file( $p_file );
		$counter = 0;
		foreach( $strings as $string ) {
			$counter++;
			$lf = strpos( $string, "\n" );
			$cr = strpos( $string, "\r" );
			$crlf = strpos( $string, "\r\n" );
			$strlen = strlen( $string )."\n";
			if ($crlf>0) {
				if ( $strlen-2 == $crlf ) {
					$crlf_count++;
				}
			} else if ($lf>0) {
				if ( $strlen-1 == $lf ) {
					$lf_count++;
				}
			} else if ($cr>0) {
				if ( $strlen-1 == $cr ) {
					$cr_count++;
				}
			}
		}
		# Windows is CRLF
		if ((0==$lf_count)&&(0==$cr_count)&&($crlf_count>0)) {
			return CRLF_ONLY;
		}
		# Mac is CR
		if ((0==$lf_count)&&($cr_count>0)&&(0==$crlf_count)) {
			return CR_ONLY;
		}
		# Unix is LF
		if (($lf_count>0)&&(0==$cr_count)&&(0==$crlf_count)) {
			return LF_ONLY;
		}
		if (($lf_count>0)&&($cr_count>0)) {
			return LFCR_MIXED;
		}
		if (($lf_count>0)&&($crlf_count>0)) {
			return LFCRLF_MIXED;
		}
		if (($crlf_count>0)&&($cr_count>0)) {
			return CRLFCR_MIXED;
		}
		if (($crlf_count>0)&&($cr_count>0)&&($lf_count>0)) {
			return CRLFCRLF_MIXED;
		}
	}

	# - ---
	function print_usage() {
		echo "\nUsage:\n        php -q check_lineterm.php <option> <path/folder>\n        -c check files\n        -f fix file\n        -a fix all files\n";
	}
	# - ---

	# -- MAIN --
	$argv = $_SERVER['argv'];
	$argc = $_SERVER['argc'];

	# too few arguments?
	if ( $argc < 2 ) {
		print_usage();
		exit;
	}

	$path = isset( $argv[2] ) ? $argv[2] : '.';

	echo "Processing, please wait...\n";
	switch( $argv[1] ) {
		case '-c':
			process_files( $path );
			break;
		case '-a':
			process_files( $path, true );
			break;
		case '-f':
			if( $path != '.' ) {
				echo realpath( $argv[2] ) . ": ";
				rewrite_file( $argv[2] );
				echo "\n";
				break;
			}
		default:
			print_usage();
			exit;
	}
	echo "Done\n";
?>
