<?php
	/*
	This script does the following:

	- Checks the lineterm characters of each file and reportsif the formats are NOT in UNIX format (\n)
	- Rewrites files with UNIX lineterms only.

	*/

	# -- GLOBAL VARIABLES --
	$g_flag = '';
	# - ---
	function print_result( $p_result ) {
#		switch( $p_result ) {
#		}
	}
	# - ---
	# process one file for problems
	function process_file( $p_file ) {
		global $g_flag;

		$line_arr = file( $p_file );
		$counter = 0;
		foreach ( $line_arr as $line ) {
			$line = trim( $line );
			$counter++;

			switch ( $g_flag ) {
				case '-f':
					# check for no braces for if
					$pos = strpos( $line, 'if' );
					if ( 0 === $pos ) {
						$pos2 = strpos( $line, '{' );
						if ( FALSE === $pos2 ) {
							print "$p_file : $counter\n";
						}
					}

					# check for no braces for while
					$pos = strpos( $line, 'while' );
					if ( 0 === $pos ) {
						$pos2 = strpos( $line, '{' );
						if ( FALSE === $pos2 ) {
							print "$p_file : $counter\n";
						}
					}
					break;
				case '-b':
					# check for spaces in brackets []
					$pos = strpos( $line, "[ '" );
					if ( $pos > 0 ) {
						print "$p_file : $counter\n";
					}
					$pos = strpos( $line, "[ \$" );
					if ( $pos > 0 ) {
						print "$p_file : $counter\n";
					}
					break;
				case '-p':
					# parenthesis
					break;
				case '-c':
					# check for order of comparison ( constant == variable )
					break;
				case '-m':
					# check for // comment
					$pos = strpos( $line, '//' );
					if ( FALSE !== $pos ) {
						print "$p_file : $counter\n";
					}
					break;
				case '-n':
					# check for ! with space
					$pos = strpos( $line, '! ' );
					if ( FALSE !== $pos ) {
						print "$p_file : $counter\n";
					}
					break;
			} # end switch
		}
	}
	# - ---
	# read in all files
	function process_files( $p_dir ) {
		$cwd = getcwd();
		$cwd .= '\\'.$p_dir;
		chdir( $cwd );
		if ( $handle = opendir( $cwd ) ) {
			#echo "Directory: ".getcwd()."\n";
			$file_arr = array();
		    while (false !== ( $file = readdir( $handle ) )) {
		    	$file_arr[] = $file;
			}
		    closedir( $handle );
		    foreach( $file_arr as $file ) {
		    	#echo "file: $file\n";
				if (( '.' == $file )||( '..' == $file )||( 'CVS' == $file )) {
					continue;
				}
			    if ( TRUE == is_dir( $file ) ) {
			    	# directory
					#process_files( $file );
			    } else {
			    	process_file( $file );
			    	#echo "Processing: ".getcwd()."\\".$file."\n";
			    }
		    }
		}
		chdir( '..' );
	}
	# - ---
	function print_usage() {
		echo "\nUsage:\n        php -q check_formatting.php <option> <path/folder>";
		echo "\nOptions:";
		echo "\n        -f brace check {}";
		echo "\n        -b bracket check []";
		echo "\n        -p parenthesis check ()";
		echo "\n        -c comparison check ( value == variable )";
		echo "\n        -m comment check # or //";
		echo "\n        -n not check !";
	}
	# - ---

	# -- MAIN --
	$argv = $_SERVER['argv'];
	$argc = $_SERVER['argc'];

	# too few arguments?
	if ( $argc != 3 ) {
		print_usage();
		exit;
	}

	$g_flag = $argv[1];

	process_files( $argv[2] );
?>
