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
	# Version 1.0.1 - Release Date: 31-July-2007 

	# --------------------------------------------------------
	# $Id: disposable.php,v 1.2 2007-08-01 23:10:09 zakman Exp $
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
			case 'despammed.com':
			case 'e4ward.com':
			case 'emailias.com':
			case 'hidemail.de':
			case 'kasmail.com':
			case 'mailfreeonline.com':
			case 'mailmoat.com':
			case 'mailnull.com':
			case 'mailshell.com':
			case 'mailzilla.com':
			case 'mintemail.com':
			case 'netzidiot.de':
			case 'punkass.com':
			case 'safersignup.de':
			case 'sneakemail.com':
			case 'spamslicer.com':
			case 'spamtrail.com':
			case 'temporaryforwarding.com':
			case 'xemaps.com':
			case 'xmaily.com':
			case 'fakemailz.com':
			case 'shiftmail.com':
			case '1chuan.com': 
			case '1zhuan.com': 
			case '4warding.com': 
			case '4warding.net': 
			case '4warding.org': 
			case 'imstations.com': 
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
			case 'discardmail.com':
			case 'dodgeit.com':
			case 'dontsendmespam.de':
			case 'emaildienst.de':
			case 'getonemail.com':
			case 'haltospam.com':
			case 'ipoo.org':
			case 'killmail.net':
			case 'link2mail.net':
			case 'lortemail.dk':
			case 'maileater.com':
			case 'mytrashmail.com':
			case 'nobulk.com':
			case 'noclickemail.com':
			case 'nospamfor.us':
			case 'oneoffemail.com':
			case 'oopi.org':
			case 'pookmail.com':
			case 'rejectmail.com':
			case 'shortmail.net':
			case 'sofort-mail.de':
			case 'spamavert.com':
			case 'spamfree24.com':
			case 'spamfree24.org':
			case 'spamfree24.net':
			case 'spaml.com':
			case 'tempemail.net':
			case 'temporaryinbox.com':
			case 'trashmail.net':
			case 'trash-mail.de':
			case 'twinmail.de':
			case 'willselfdestruct.com':
			case 'yopmail.com':
			case 'mailinator.com':
			case 'mailinator2.com': 
			case 'sogetthis.com': 
			case 'mailin8r.com': 
			case 'mailinator.net': 
			case 'klassmaster.com':
			case 'fastacura.com':
			case 'fastchevy.com':
			case 'fastchrysler.com': 
			case 'fastkawasaki.com':
			case 'fastmazda.com':
			case 'fastmitsubishi.com': 
			case 'fastnissan.com':
			case 'fastsubaru.com':
			case 'fastsuzuki.com':
			case 'fasttoyota.com':
			case 'fastyamaha.com':
			case '675hosting.com': 
			case '675hosting.net': 
			case '675hosting.org': 
			case '75hosting.com': 
			case '75hosting.net': 
			case '75hosting.org': 
			case 'ajaxapp.net': 
			case 'amiri.net': 
			case 'amiriindustries.com': 
			case 'emailmiser.com': 
			case 'etranquil.com': 
			case 'etranquil.net': 
			case 'etranquil.org': 
			case 'gowikibooks.com': 
			case 'gowikicampus.com': 
			case 'gowikicars.com': 
			case 'gowikifilms.com': 
			case 'gowikigames.com': 
			case 'gowikimusic.com': 
			case 'gowikinetwork.com': 
			case 'gowikitravel.com': 
			case 'gowikitv.com':
			case 'myspaceinc.com': 
			case 'myspaceinc.net': 
			case 'myspaceinc.org': 
			case 'myspacepimpedup.com': 
			case 'ourklips.com': 
			case 'pimpedupmyspace.com': 
			case 'rklips.com': 
			case 'turual.com': 
			case 'upliftnow.com': 
			case 'uplipht.com': 
			case 'viditag.com': 
			case 'viewcastmedia.com': 
			case 'viewcastmedia.net': 
			case 'viewcastmedia.org': 
			case 'wetrainbayarea.com': 
			case 'wetrainbayarea.org': 
			case 'xagloo.com': 
			case 'buyusedlibrarybooks.org':
			case 'mailquack.com':
			case 'mailslapping.com':
			case 'oneoffmail.com':
			case 'recyclemail.dk':
			case 'anonymail.dk':
			case 'trashdevil.com':
			case 'trashdevil.de':
			case 'whopy.com':
			case 'wilemail.com':
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
			case 'despam.it':
			case 'dontreg.com':
			case 'emailto.de':
			case 'getonemail.com':
			case 'guerrillamail.com':
			case 'guerrillamail.net':
			case 'haltospam.com':
			case 'jetable.com':
			case 'jetable.net':
			case 'jetable.org':
			case 'kasmail.com':
			case 'link2mail.net':
			case 'mailexpire.com':
			case 'mailzilla.com':
			case 'mintemail.com':
			case 'noclickemail.com':
			case 'oneoffemail.com':
			case 'oopi.org':
			case 'pookmail.com':
			case 'shortmail.net':
			case 'spambox.us':
			case 'spamfree24.com':
			case 'spamfree24.org':
			case 'spamfree24.net':
			case 'spamify.com':
			case 'tempemail.net':
			case 'tempinbox.com':
			case 'temporarily.de':
			case 'temporaryinbox.com':
			case 'wh4f.org':
			case 'yopmail.com':
			case 'buyusedlibrarybooks.org':
			case 'dotmsg.com':
			case 'lovemeleaveme.com':
			case 'trashdevil.com':
			case 'trashdevil.de':
			case 'walala.org':
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

		switch ( $t_domain ) {
			case 'gmail.com':
			case 'googlemail.com':
			case 'hotmail.com':
			case 'yahoo.com':
			case 'msn.com':
			case 'hotmail.de':
			case 'hotmail.fr':
			case 'hotmail.it':
			case 'hotmail.co.uk':
			case 'msn.co.uk':
			case 'pancakemail.com':
			case 'gawab.com':
			case 'yahoo.com.au':
			case 'yahoo.com.cn':
			case 'yahoo.co.uk':
			case 'yahoo.com.hk':
			case 'yahoo.com.ar':
			case 'yahoo.com.br':
			case 'yahoo.com.mx':
			case 'yahoo.com.asia':
			case 'yahoo.co.jp':
			case 'yahoo.com.malaysia':
			case 'yahoo.com.ph':
			case 'yahoo.com.sg':
			case 'yahoo.com.tw':
			case 'yahoo.com.vn':
			case 'yahoo.com.es':
			case 'yahoo.fr':
			case 'yahoo.ie':
			case 'yahoo.de':
			case 'yahoo.ca':
			case 'talk21.com':
			case 'BTinternet.com':
			case 'lycos.co.uk':
			case 'lycos.it':
			case 'lycos.es':
			case 'lycos.de':
			case 'lycos.at':
			case 'lycos.nl':
			case 'caramail.com':
			case 'mail.com':
			case 'libero.it':
			case 'iol.it':
			case 'blu.it':
			case 'aol.com':
			case 'aim.com':
			case 'netscape.com':
			case 'netscape.net':
			case 'mail.ru':
			case 'inbox.ru':
			case 'bk.ru':
			case 'list.ru':
			case 'rediffmail.com':
			case 'hanmail.net':
			case 'webmail.co.za':
			case 'exclusivemail.co.za':
			case 'executive.co.za':
			case 'homemail.co.za':
			case 'magicmail.co.za':
			case 'mailbox.co.za':
			case 'ravemail.co.za':
			case 'starmail.co.za':
			case 'thecricket.co.za':
			case 'thegolf.co.za':
			case 'thepub.co.za':
			case 'therugby.co.za':
			case 'websurfer.co.za':
			case 'workmail.co.za':
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
