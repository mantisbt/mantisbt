<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Crypto API
 *
 * @package CoreAPI
 * @subpackage CryptoAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses utility_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'utility_api.php' );

/**
 * Initialise the CryptoAPI subsystem. This function checks whether the master
 * salt is specified correctly within the configuration. If not, a fatal error
 * is produced to protect against invalid configuration impacting the security
 * of the MantisBT installation.
 * @return void
 */
function crypto_init() {
	if( !defined( 'MANTIS_MAINTENANCE_MODE' ) ) {
		if( strlen( config_get_global( 'crypto_master_salt' ) ) < 16 ) {
			trigger_error( ERROR_CRYPTO_MASTER_SALT_INVALID, ERROR );
		}
	}
	return;
}

/**
 * Generate a random string (raw binary output) for cryptographic purposes such
 * as nonces, IVs, default passwords, etc. This function will attempt to
 * generate strong randomness but can optionally be used to generate weaker
 * randomness if less security is needed or a strong source of randomness isn't
 * available. The use of weak randomness for cryptographic purposes is strongly
 * discouraged because it contains low entropy and is predictable.
 *
 * @param integer $p_bytes                    Number of bytes of randomness required.
 * @param boolean $p_require_strong_generator Whether or not a weak source of randomness can be used by this function.
 * @return string|null Raw binary string containing the requested number of bytes of random output or null if the output couldn't be created
 */
function crypto_generate_random_string( $p_bytes, $p_require_strong_generator = true ) {
	# First we attempt to use the secure PRNG provided by OpenSSL in PHP
	if( function_exists( 'openssl_random_pseudo_bytes' ) ) {
		$t_random_bytes = openssl_random_pseudo_bytes( $p_bytes, $t_strong );
		if( $t_random_bytes !== false ) {
			if( $p_require_strong_generator && $t_strong === true ) {
				$t_random_string = $t_random_bytes;
			} else if( !$p_require_strong_generator ) {
				$t_random_string = $t_random_bytes;
			}
		}
	}

	# Attempt to use mcrypt_create_iv - this is built into newer versions of php on windows
	# if the mcrypt extension is enabled on Linux, it takes random data from /dev/urandom
	if( !isset( $t_random_string ) ) {
		if( function_exists( 'mcrypt_create_iv' )
			&& ( version_compare( PHP_VERSION, '5.3.7' ) >= 0 || !is_windows_server() )
		) {
			$t_random_bytes = mcrypt_create_iv( $p_bytes, MCRYPT_DEV_URANDOM );
			if( $t_random_bytes !== false && strlen( $t_random_bytes ) === $p_bytes ) {
				$t_random_string = $t_random_bytes;
			}
		}
	}

	# Next we try to use the /dev/urandom PRNG provided on Linux systems. This
	# is nowhere near as secure as /dev/random but it is still satisfactory for
	# the needs of MantisBT, especially given the fact that we don't want this
	# function to block while waiting for the system to generate more entropy.
	if( !isset( $t_random_string ) ) {
		if( !is_windows_server() ) {
			$t_urandom_fp = @fopen( '/dev/urandom', 'rb' );
			if( $t_urandom_fp !== false ) {
				$t_random_bytes = @fread( $t_urandom_fp, $p_bytes );
				if( $t_random_bytes !== false ) {
					$t_random_string = $t_random_bytes;
				}
				@fclose( $t_urandom_fp );
			}
		}
	}

	# At this point we've run out of possibilities for generating randomness
	# from a strong source. Unless weak output is specifically allowed by the
	# $p_require_strong_generator argument, we should return null as we've
	# failed to generate randomness to a satisfactory security level.
	if( !isset( $t_random_string ) && $p_require_strong_generator ) {
		return null;
	}

	# As a last resort we have to fall back to using the insecure Mersenne
	# Twister pseudo random number generator provided in PHP. This DOES NOT
	# produce cryptographically secure randomness and thus the output of the
	# PRNG is easily guessable. In an attempt to make it harder to guess the
	# internal state of the PRNG, we salt the PRNG output with a known secret
	# and hash it.
	if( !isset( $t_random_string ) ) {
		$t_secret_key = 'prng' . config_get_global( 'crypto_master_salt' );
		$t_random_bytes = '';
		for( $i = 0; $i < $p_bytes; $i += 64 ) {
			$t_random_segment = '';
			for( $j = 0; $j < 64; $j++ ) {
				$t_random_segment .= base_convert( mt_rand(), 10, 36 );
			}
			$t_random_segment .= $i;
			$t_random_segment .= $t_secret_key;
			$t_random_bytes .= hash( 'whirlpool', $t_random_segment, true );
		}
		$t_random_string = substr( $t_random_bytes, 0, $p_bytes );
		if( $t_random_string === false ) {
			return null; # Unexpected error
		}
	}

	return $t_random_string;
}

/**
 * Generate a strong random string (raw binary output) for cryptographic
 * purposes such as nonces, IVs, default passwords, etc. If a strong source
 * of randomness is not available, this function will fail and produce an
 * error. Strong randomness is different from weak randomness in that a strong
 * randomness generator doesn't produce predictable output and has much higher
 * entropy. Where randomness is being used for cryptographic purposes, a strong
 * source of randomness should always be used.
 * @param integer $p_bytes Number of bytes of strong randomness required.
 * @return string Raw binary string containing the requested number of bytes of random output
 */
function crypto_generate_strong_random_string( $p_bytes ) {
	$t_random_string = crypto_generate_random_string( $p_bytes, true );
	if( $t_random_string === null ) {
		trigger_error( ERROR_CRYPTO_CAN_NOT_GENERATE_STRONG_RANDOMNESS, ERROR );
	}
	return $t_random_string;
}

/**
 * Generate a nonce encoded using the base64 with URI safe alphabet approach
 * described in RFC4648. Note that the minimum length is rounded up to the next
 * number with a factor of 4 so that padding is never added to the end of the
 * base64 output. This means the '=' padding character is never present in the
 * output. Due to the reduced character set of base64 encoding, the actual
 * amount of entropy produced by this function for a given output string length
 * is 3/4 (0.75) that of raw unencoded output produced with the
 * crypto_generate_strong_random_string( $p_bytes ) function.
 * @param integer $p_minimum_length Minimum number of characters required for the nonce.
 * @return string Nonce encoded according to the base64 with URI safe alphabet approach described in RFC4648
 */
function crypto_generate_uri_safe_nonce( $p_minimum_length ) {
	$t_length_mod4 = $p_minimum_length % 4;
	$t_adjusted_length = $p_minimum_length + 4 - ($t_length_mod4 ? $t_length_mod4 : 4);
	$t_raw_bytes_required = ( $t_adjusted_length / 4 ) * 3;
	if( !is_windows_server() ) {
		$t_random_bytes = crypto_generate_strong_random_string( $t_raw_bytes_required );
	} else {
		# It's currently not possible to generate strong random numbers
		# with PHP on Windows so we have to resort to using PHP's
		# built-in insecure PRNG.
		$t_random_bytes = crypto_generate_random_string( $t_raw_bytes_required, false );
	}
	$t_base64_encoded = base64_encode( $t_random_bytes );
	# Note: no need to translate trailing = padding characters because our
	# length rounding ensures that padding is never required.
	$t_random_nonce = strtr( $t_base64_encoded, '+/', '-_' );
	return $t_random_nonce;
}
