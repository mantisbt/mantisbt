<?php
	# Disposable Email Checker - a static php based check for spam emails
	# Copyright (C) 2007 Victor Boctor
	#
	#     http://www.futureware.biz/disposable
	#     http://www.mantisbt.org/
	#
	# This program is distributed under the terms and conditions of the LGPL
	# See the README and LICENSE files for details
	#
	# Version 1.0.0 - Release Date: 16-Jun-2007 

	# --------------------------------------------------------
	# $Id: disposable.php,v 1.1 2007-06-16 23:04:35 vboctor Exp $
	# --------------------------------------------------------

/**
 * A class that checks an email address and provides some facts about whether
 * it is a disposable, free web mail, etc.  The data that is used to make 
 * such decision is static as part of the class implementation, hence
 * avoiding a round trip to a remote service.  This makes the class much
 * more efficient in scenarios where performance is an issue.
 */      
class DisposableEmailChecker
{
	/**
	 * Determines if the email address is disposable.
	 * 
	 * @param $p_email  The email address to validate.
	 * @returns true: disposable, false: non-disposable.
	 */	 	 	 	 	
	function is_disposable_email( $p_email ) {
		return (
			DisposableEmailChecker::is_forwarding_email( $p_email ) ||
			DisposableEmailChecker::is_trash_email( $p_email ) ||
			DisposableEmailChecker::is_time_bound_email( $p_email ) ||
			DisposableEmailChecker::is_shredder_email( $p_email ) );
	}

	/**
	 * Determines if the email address is disposable email that forwards to
	 * users' email address.  This is one of the best kind of disposable 
	 * addresses since emails end up in the user's inbox unless the user
	 * cancel the address.
	 * 
	 * @param $p_email  The email address to check.
	 * @returns true: disposable forwarding, false: otherwise.
	 */	 	 
	function is_forwarding_email( $p_email ) {
		$t_domain = DisposableEmailChecker::_get_domain_from_address( $p_email );

		switch ( $t_domain ) {
			case 'gishpuppy.com':
			case 'jetable.org':
			case 'spambob.net':
			case 'spamex.com':
			case 'spamgourmet.com':
			case 'spamhole.com':
			case 'spammotel.com':
			case 'trashmail.net':
				return true;
		}

		return false;
	}

	/**
	 * Determines if the email address is trash email that doesn't forward to
	 * user's email address.  This kind of address can be checked using a 
	 * web page and no password is required for such check.  Hence, data sent
	 * to such address is not protected.  Typically users use these addresses
	 * to signup for a service, and then they never check it again.
	 * 
	 * @param $p_email  The email address to check.
	 * @returns true: disposable trash mail, false: otherwise.
	 */	 	 	 	 	 	 	 	 	
	function is_trash_email( $p_email ) {
		$t_domain = DisposableEmailChecker::_get_domain_from_address( $p_email );

		switch ( $t_domain ) {
			case '10minutemail.com':
			case 'bspamfree.org':
			case 'dodgeit.com':
			case 'ichimail.com':
			case 'mailinator.com':
			case 'no-spam.hu':
			case 'pookmail.com':
			case 'spambob.com':
			case 'spambog.com':
			case 'spam.la':
			case 'tempinbox.com':
				return true;
		}

		return false;
	}

	/**
	 * Determines if the email address is a shredder email address.  Shredder
	 * email address delete all received emails without forwarding them or 
	 * making them available for a user to check.
	 * 
	 * @param $p_email  The email address to check.
	 * @returns true: shredded disposable email, false: otherwise.
	 */	 	 	 	 	 	 	
	function is_shredder_email( $p_email ) {
		$t_domain = DisposableEmailChecker::_get_domain_from_address( $p_email );

		switch ( $t_domain ) {
			case 'spambob.org':
				return true;
		}

		return false;
	}

	/**
	 * Determines if the email address is time bound, these are the disposable
	 * addresses that auto expire after a pre-configured time.  For example,
	 * 10 minutes, 1 hour, 2 hours, 1 day, 1 month, etc.  These address can
	 * also be trash emails or forwarding emails.
	 * 
	 * @param $p_email  The email address to check.
	 * @returns true: time bound disposable email, false: otherwise.
	 */	 	 	 	 	 	 	 	
	function is_time_bound_email( $p_email ) {
		$t_domain = DisposableEmailChecker::_get_domain_from_address( $p_email );

		switch ( $t_domain ) {
			case '10minutemail.com':
			case 'jetable.org':
			case 'no-spam.hu':
			case 'spamhole.com':
			case 'trashmail.net':
				return true;
		}

		return false;
	}

	/**
	 * Determines if the email address is a free email address.  These are
	 * addresses that users can sign up for free.  They then has to login to
	 * these address to get the emails.  These are not considered to be 
	 * disposable emails, however, if the application is providing a free
	 * trial for an expensive server, then users can signup for more accounts
	 * to get further trials.
	 * 
	 * If applications are to block these addresses, it is important to be aware
	 * that some users use free webmail as their primary email and that such
	 * service providers include hotmail, gmail, and yahoo.
	 */	 	 	 	 	  	 	 	 	 	
	function is_free_email( $p_email ) {
		$t_domain = DisposableEmailChecker::_get_domain_from_address( $p_email );

		# @@@ use regular expressions to handle potentially yahoo.com, yahoo.com.au, etc.
		switch ( $t_domain ) {
			case 'gmail.com':
			case 'hotmail.com':
			case 'yahoo.com':
				return true;
		}

		return false;
	}

	/**
	 * A debugging function that takes in an email address and dumps out the
	 * details for such email.
	 * 
	 * @param $p_email  The email address to echo results for.  This must be a 
	 *                  safe script (i.e. no javascript, etc).                	 	 
	 */	 	 	 	 	
	function echo_results( $p_email ) {
		echo 'email address = ', htmlspecialchars( $p_email ), '<br />';
		echo 'is_disposable_email = ', DisposableEmailChecker::is_disposable_email( $p_email ), '<br />'; 
		echo 'is_forwarding_email = ', DisposableEmailChecker::is_forwarding_email( $p_email ), '<br />'; 
		echo 'is_trash_email = ', DisposableEmailChecker::is_trash_email( $p_email ), '<br />'; 
		echo 'is_time_bound_email = ', DisposableEmailChecker::is_time_bound_email( $p_email ), '<br />'; 
		echo 'is_shredder_email = ', DisposableEmailChecker::is_shredder_email( $p_email ), '<br />'; 
		echo 'is_free_email = ', DisposableEmailChecker::is_free_email( $p_email ), '<br />'; 
	}

	//
	// Private functions, shouldn't be called from outside the class
	//

	/**
	 * A helper function that takes in an email address and returns a lower case
	 * domain.
	 *
	 * @param $p_email  The email address to extra the domain from.
	 * @returns The lower case domain or empty string if email not valid.
	 */	 	 	 	 	 	
	function _get_domain_from_address( $p_email ) {
		$t_domain_pos = strpos( $p_email, '@' );
		if ( $t_domain_pos === false ) {
			return '';
		}

		return strtolower( substr( $p_email, $t_domain_pos + 1 ) );
	}
}
?>
