<?php
	# Disposable Email Checker - a static php based check for spam emails
	# Copyright (C) 2007-2008 Victor Boctor
	
	# This program is distributed under the terms and conditions of the LGPL
	# See the README and LICENSE files for details

/**
 * A class that checks an email address and provides some facts about whether
 * it is a disposable, free web mail, etc.  The data that is used to make
 * such decision is static as part of the class implementation, hence
 * avoiding a round trip to a remote service.  This makes the class much
 * more efficient in scenarios where performance is an issue.
 */
class DisposableEmailChecker
{
	static $forwarding_domains_array = array(
		'1chuan.com',
		'1zhuan.com',
		'4warding.com',
		'4warding.net',
		'4warding.org',
		'despammed.com',
		'e4ward.com',
		'emailias.com',
		'fakemailz.com',
		'gishpuppy.com',
		'hidemail.de',
		'imstations.com',
		'jetable.org',
		'kasmail.com',
		'mailfreeonline.com',
		'mailmoat.com',
		'mailnull.com',
		'mailshell.com',
		'mailzilla.com',
		'mintemail.com',
		'netzidiot.de',
		'punkass.com',
		'safersignup.de',
		'shiftmail.com',
		'sneakemail.com',
		'spambob.net',
		'spamex.com',
		'spamgourmet.com',
		'spamhole.com',
		'spammotel.com',
		'spamslicer.com',
		'spamtrail.com',
		'temporaryforwarding.com',
		'trashmail.net',
		'xemaps.com',
		'xmaily.com'
	);

	static $trash_domains_array = array(
		'10minutemail.com',
		'675hosting.com',
		'675hosting.net',
		'675hosting.org',
		'75hosting.com',
		'75hosting.net',
		'75hosting.org',
		'ajaxapp.net',
		'amilegit.com',
		'amiri.net',
		'amiriindustries.com',
		'anonymail.dk',
		'anonymbox.com',
		'bspamfree.org',
		'bugmenot.com',
		'buyusedlibrarybooks.org',
		'deadaddress.com',
		'discardmail.com',
		'disposeamail.com',
		'dispostable.com',
		'dodgeit.com',
		'dontsendmespam.de',
		'emaildienst.de',
		'emailmiser.com',
		'emailthe.net',
		'etranquil.com',
		'etranquil.net',
		'etranquil.org',
		'fakeinbox.com',
		'fastacura.com',
		'fastchevy.com',
		'fastchrysler.com',
		'fastkawasaki.com',
		'fastmazda.com',
		'fastmitsubishi.com',
		'fastnissan.com',
		'fastsubaru.com',
		'fastsuzuki.com',
		'fasttoyota.com',
		'fastyamaha.com',
		'getonemail.com',
		'gowikibooks.com',
		'gowikicampus.com',
		'gowikicars.com',
		'gowikifilms.com',
		'gowikigames.com',
		'gowikimusic.com',
		'gowikinetwork.com',
		'gowikitravel.com',
		'gowikitv.com',
		'haltospam.com',
		'ichimail.com',
		'incognitomail.org',
		'ipoo.org',
		'killmail.net',
		'klassmaster.com',
		'link2mail.net',
		'lortemail.dk',
		'mailcatch.com',
		'maileater.com',
		'mailin8r.com',
		'mailinator.com',
		'mailinator.net',
		'mailinator2.com',
		'mailmetrash.com',
		'mailnesia.com',
		'mailquack.com',
		'mailslapping.com',
		'meltmail.com',
		'mmmmail.com',
		'mt2009.com',
		'myspaceinc.com',
		'myspaceinc.net',
		'myspaceinc.org',
		'myspacepimpedup.com',
		'mytrashmail.com',
		'nepwk.com',
		'no-spam.hu',
		'nobulk.com',
		'noclickemail.com',
		'nospamfor.us',
		'nowmymail.com',
		'oneoffemail.com',
		'oneoffmail.com',
		'oopi.org',
		'ourklips.com',
		'pimpedupmyspace.com',
		'pookmail.com',
		'recyclemail.dk',
		'rejectmail.com',
		'rklips.com',
		'sharklasers.com',
		'shortmail.net',
		'sofort-mail.de',
		'sogetthis.com',
		'spam.la',
		'spamavert.com',
		'spambob.com',
		'spambog.com',
		'spambox.us',
		'spamfree24.com',
		'spamfree24.net',
		'spamfree24.org',
		'spaml.com',
		'tempemail.net',
		'tempinbox.com',
		'tempomail.fr',
		'temporaryinbox.com',
		'thankyou2010.com',
		'thisisnotmyrealemail.com',
		'trash-mail.de',
		'trash2009.com',
		'trashdevil.com',
		'trashdevil.de',
		'trashmail.net',
		'trashymail.com',
		'turual.com',
		'twinmail.de',
		'upliftnow.com',
		'uplipht.com',
		'viditag.com',
		'viewcastmedia.com',
		'viewcastmedia.net',
		'viewcastmedia.org',
		'wetrainbayarea.com',
		'wetrainbayarea.org',
		'whopy.com',
		'whyspam.me',
		'wilemail.com',
		'willselfdestruct.com',
		'xagloo.com',
		'yopmail.com'
	);

	static $shredder_domains_array = array(
		'spambob.org'
	);

	static $time_bound_domains_array = array(
		'10minutemail.com',
		'bugmenot.com',
		'buyusedlibrarybooks.org',
		'despam.it',
		'dontreg.com',
		'dotmsg.com',
		'emailto.de',
		'getonemail.com',
		'guerrillamail.com',
		'guerrillamail.net',
		'haltospam.com',
		'jetable.com',
		'jetable.net',
		'jetable.org',
		'kasmail.com',
		'link2mail.net',
		'lovemeleaveme.com',
		'mailexpire.com',
		'mailzilla.com',
		'mintemail.com',
		'no-spam.hu',
		'noclickemail.com',
		'oneoffemail.com',
		'oopi.org',
		'pookmail.com',
		'shortmail.net',
		'spambox.us',
		'spamfree24.com',
		'spamfree24.org',
		'spamfree24.net',
		'spamhole.com',
		'spamify.com',
		'tempemail.net',
		'tempinbox.com',
		'temporaryinbox.com',
		'temporarily.de',
		'trashdevil.com',
		'trashdevil.de',
		'trashmail.net',
		'walala.org',
		'wh4f.org',
		'yopmail.com'
	);

	static $open_domains_array = array(
		'aim.com',
		'aol.com',
		'bk.ru',
		'blu.it',
		'BTinternet.com',
		'caramail.com',
		'exclusivemail.co.za',
		'executive.co.za',
		'free.fr',
		'freemail.hu',
		'gawab.com',
		'gmail.com',
		'gmx.at',
		'gmx.de',
		'gmx.net',
		'googlemail.com',
		'hanmail.net',
		'homemail.co.za',
		'hotmail.co.uk',
		'hotmail.com',
		'hotmail.de',
		'hotmail.fr',
		'hotmail.it',
		'inbox.ru',
		'iol.it',
		'libero.it',
		'list.ru',
		'lycos.at',
		'lycos.co.uk',
		'lycos.de',
		'lycos.es',
		'lycos.it',
		'lycos.nl',
		'magicmail.co.za',
		'mail.com',
		'mail.ru',
		'mailbox.co.za',
		'msn.co.uk',
		'msn.com',
		'netscape.com',
		'netscape.net',
		'o2.pl',
		'pancakemail.com',
		'ravemail.co.za',
		'rediffmail.com',
		'sapo.pt',
		'starmail.co.za',
		'talk21.com',
		'thecricket.co.za',
		'thegolf.co.za',
		'thepub.co.za',
		'therugby.co.za',
		'ukr.net',
		'yahoo.ca',
		'yahoo.co.in',
		'yahoo.co.jp',
		'yahoo.co.uk',
		'yahoo.com',
		'yahoo.com.ar',
		'yahoo.com.asia',
		'yahoo.com.au',
		'yahoo.com.br',
		'yahoo.com.cn',
		'yahoo.com.es',
		'yahoo.com.hk',
		'yahoo.com.malaysia',
		'yahoo.com.mx',
		'yahoo.com.ph',
		'yahoo.com.sg',
		'yahoo.com.tw',
		'yahoo.com.vn',
		'yahoo.de',
		'yahoo.dk',
		'yahoo.es',
		'yahoo.fr',
		'yahoo.gr',
		'yahoo.ie',
		'yahoo.it',
		'yahoo.se',
		'web.de',
		'webmail.co.za',
		'websurfer.co.za',
		'workmail.co.za'
	);

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
		return in_array( $t_domain, DisposableEmailChecker::$forwarding_domains_array );
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
		return in_array( $t_domain, DisposableEmailChecker::$trash_domains_array );
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
		return in_array( $t_domain, DisposableEmailChecker::$shredder_domains_array );
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
		return in_array( $t_domain, DisposableEmailChecker::$time_bound_domains_array );
	}

	/**
	 * See is_open_domain() for details.
	 */
	function is_free_email( $p_email ) {
		return $this->is_open_email( $p_email );
	}

	/**
	 * Determines if the email address is an email address in an open domain.  These are
	 * addresses that users can sign up for, typically free.  They then has to login to
	 * these address to get the emails.  These are not considered to be
	 * disposable emails, however, if the application is providing a free
	 * trial for an expensive server, then users can signup for more accounts
	 * to get further trials.
	 *
	 * If applications are to block these addresses, it is important to be aware
	 * that some users use open webmail as their primary email and that such
	 * service providers include hotmail, gmail, and yahoo.
	 *
	 * @param $p_email  The email address to check.
	 * @returns true: open domain email, false: otherwise.
	 */
	function is_open_email( $p_email ) {
		$t_domain = DisposableEmailChecker::_get_domain_from_address( $p_email );
		return in_array( $t_domain, DisposableEmailChecker::$open_domains_array );
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

	/**
	 * A debugging function that outputs some statistics about the number of domains in
	 * each category.
	 */
	function echo_stats() {
		echo 'Forwarding Domains: ' . count( DisposableEmailChecker::$forwarding_domains_array ) . '<br />';
		echo 'Free Domains: ' . count( DisposableEmailChecker::$open_domains_array ) . '<br />';
		echo 'Shredded Domains: ' . count( DisposableEmailChecker::$shredder_domains_array ) . '<br />';
		echo 'Time Bound: ' . count( DisposableEmailChecker::$time_bound_domains_array ) . '<br />';
		echo 'Trash Domains: ' . count( DisposableEmailChecker::$trash_domains_array ) . '<br />';
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