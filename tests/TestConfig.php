<?php
# Mantis - a php based bugtracking system

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

/**
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/*
 * Start output buffering
 */
ob_start();

/*
 * Include PHPUnit dependencies ; insure compatibility with 3.5 and 3.6
 */
@include_once 'PHPUnit/Framework.php';
/*
 * Set error reporting to the level to which Zend Framework code must comply.
 */
error_reporting( E_ALL | E_STRICT );

/*
 * Determine the root, library, and tests directories of the framework
 * distribution.
 */
$mantisRoot = dirname(__FILE__) . '/..';
$mantisCore = "$mantisRoot/core";
$mantisLibrary = "$mantisRoot/library";
$mantisClasses = "$mantisRoot/core/classes";
$mantisTests = "$mantisRoot/tests";


/*
 * Prepend the application/ and tests/ directories to the
 * include_path.  
 */
$path = array(
    $mantisCore,
    $mantisLibrary,
    $mantisClasses,
    get_include_path()
    );
set_include_path( implode( PATH_SEPARATOR, $path ) );


/*
 * Unset global variables that are no longer needed.
 */
unset($mantisRoot, $mantisLibrary, $mantisTests, $path);
