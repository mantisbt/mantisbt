<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.

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
 * Base class that implements basic plugin functionality
 * and integration with Mantis. See the Mantis wiki for
 * more information.
 * @package MantisBT
 * @subpackage classes 
 */
abstract class MantisPlugin {

	public $name		= null;
	public $description	= null;
	public $page		= null;

	public $version		= null;
	public $requires	= null;

	public $author		= null;
	public $contact		= null;
	public $url			= null;

	abstract public function register();

	public function init() {}

	public function errors() {
		return array();
	}

	public function config() {
		return array();
	}

	public function events() {
		return array();
	}

	public function hooks() {
		return array();
	}

	public function schema() {
		return array();
	}

	public function install() {
		return true;
	}

	public function upgrade( $p_schema ) {
		return true;
	}

	public function uninstall() {
	}

	### Core plugin functionality ###

	public $basename	= null;
	final public function __construct( $p_basename ) {
		$this->basename = $p_basename;
		$this->register();
	}

	final public function __init() {
		plugin_config_defaults( $this->config() );
		event_declare_many( $this->events() );
		plugin_event_hook_many( $this->hooks() );

		$this->init();
	}
}

/**
 * Mantis Core Plugin
 * Used to give other plugins a permanent core plugin to 'require' for compatibility.
 * Can/should not be used as a base class.
 */
final class MantisCorePlugin extends MantisPlugin {
	function register() {
		$this->name = 'Mantis Core';
		$this->description = 'Core plugin API for the Mantis Bug Tracker.';

		$this->version = MANTIS_VERSION;

		$this->author = 'Mantis Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}
}
