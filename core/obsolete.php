<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: obsolete.php,v 1.1 2002-08-27 13:55:20 vboctor Exp $
	# --------------------------------------------------------
	
	###########################################################################
	# Check that obsolete configs are not used.
	# THIS FILE ASSUMES THAT THE CONFIGURATION IS INCLUDED AS WELL AS THE
	# config_api.php.
	###########################################################################

	# Check for obsolete variables
	config_obsolete( 'g_notify_developers_on_new', 'g_notify_flags' );
	config_obsolete( 'g_notify_on_new_threshold', 'g_notify_flags' );
	config_obsolete( 'g_notify_admin_on_new', 'g_notify_flags' );
?>