<?php
/**
* This was an experiment to see how a PCRE based UTF-8 to unicode
* code point converter would perform, vs. a character by character
* converted (as in '../utf8_unicode.php'). Basically this is very
* by comparion but perhaps interesting code anyway
*/
$UTF8_MATCH =
    '([\x09\x0A\x0D\x20-\x7E])'.              # ASCII (excluding control chars)
    '|([\xC2-\xDF][\x80-\xBF]'.              # non-overlong 2-byte
    '|\xE0[\xA0-\xBF][\x80-\xBF])'.          # excluding overlongs
    '|([\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'.   # straight 3-byte
    '|\xED[\x80-\x9F][\x80-\xBF]'.          # excluding surrogates
    '|\xF0[\x90-\xBF][\x80-\xBF]{2}'.       # planes 1-3
    '|[\xF1-\xF3][\x80-\xBF]{3}'.           # planes 4-15
    '|\xF4[\x80-\x8F][\x80-\xBF]{2})';       # plane 16
    '|(.{1})';                                # catch bad bytes

function toCodePoint($matches) {
    global $points;
    if ( $matches[1] != '' ) {
        $points[]= ord($matches[1]);
    } else if ( $matches[2] != '' ) {
        $points[]= ( ( ord($matches[2][0]) % 32 ) * 64 ) + ( ord($matches[2][1]) % 64 );
    } else if ( $matches[3] != '' ) {
        $points[]= ( ( ord($matches[3][0]) % 16 ) * 4096 ) + ( ( ord($matches[3][1]) % 64 ) * 64 ) + ( ord($matches[3][2]) % 64 );
    } else if ( $matches[4] != '' ) {
        trigger_error('Invalid byte in UTF-8',E_USER_WARNING);
        return '';
    }
    return $matches[0];
}

$str = file_get_contents('../tests/data/utf8.html');
$points = array();
preg_replace_callback('/'.$UTF8_MATCH.'/S','toCodePoint',$str);
print_r($points);
