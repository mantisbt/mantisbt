# Converting mantis language files from 1.3.x back to 1.2.x format

# removing array definition
/^.s_messages/d
/^\);/d

# comments
s/^\t#/#/
/\t'missing_error_string'/ {
	i/**\
 * ERROR strings\
 */
	a
}

# remove last blank line
${/^$/d}

# language strings
/^\t'(\w+)' =>/ {
	:loopstr
		s/^\t'(\w+)' => (.*),$/\$s_\1 = \2;/
		t endstr
		N
		bloopstr
	:endstr
}

# error messages
/^\tERROR.* =>/ {
	:looperr
		s/^\t(ERROR.*) => (.*),$/\$MANTIS_ERROR[\1] = \2;/
		tenderr
		N
		blooperr
	:enderr
}
