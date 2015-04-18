<?php
# MantisBT - a php based bugtracking system

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
require_once('phing/Task.php');

/**
 * The <tt>ExtractMantisBTVersion</tt> is a custom task which extracts the MantisBT version
 * as defined by the constants file.
 *  
 * @author Robert Munteanu
 */
class ExtractMantisBTVersion extends Task {

	private $file;
	
	public function setFile(PhingFile $file) {
		
		$this->file = $file;
	}

	public function main() {
		
		if ( ! isset ( $this->file ) ) {
			throw new BuildException("Missing 'file' attribute.");
		}
		
		$contents = file($this->file->getPath());
		
		foreach ( $contents as $line ) {
			if ( strstr($line, 'MANTIS_VERSION')) {
				eval($line);
				break;
			}
		}
		
		if ( !constant('MANTIS_VERSION' ) )
			throw new BuildException('No constant named MANTIS_VERSION found in ' . $this->file->getPath()); 
		
		$this->project->setProperty('mantisbtversion', MANTIS_VERSION);
	}
}
?>