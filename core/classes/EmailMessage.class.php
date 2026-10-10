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
 * A class that captures all data related to formatted email message.
 */
class EmailMessage {
	# Headers including from address and reply-to.
	public $headers = [];

	# To Recipients
	public $to = [];

	# CC Recipients
	public $cc = [];

	# BCC Recipients
	public $bcc = [];

	# Subject
	public string $subject = '';

	# Text Body
	public string $text = '';

	# Language
	public string $lang = 'english';

	# Hostname
	public string $hostname = '';

	# Charset
	public string $charset = 'UTF-8';
}
