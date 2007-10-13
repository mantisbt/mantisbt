<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: print_all_bug_options_page.php,v 1.7.42.1 2007-10-13 22:34:13 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	# This page allows the user to set the fields of the bugs he wants to print
	# Update is POSTed to acount_prefs_update.php3
	# Reset is POSTed to acount_prefs_reset.php3
?>
<?php require_once( 'core.php' ) ?>
<?php require( 'print_all_bug_options_inc.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	html_page_top1();
	html_page_top2();
	edit_printing_prefs();
	html_page_bottom1( __FILE__ );
?>
