/*
# Mantis - a php based bugtracking system

# Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright 2002 MantisBT Team   - mantisbt-dev@lists.sourceforge.net

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
 */

// Handle switching between textarea custom field type and other types
$(function() {
  "use strict";

  var type = $('#custom-field-type');
  var input = $('#custom-field-default-value, label[for=custom-field-default-value]');
  var textarea = $('#custom-field-default-value-textarea, label[for=custom-field-default-value-textarea]');

  type.on('change', function() {
    if($(this).val() === "10") {  // 10: CUSTOM_FIELD_TYPE_TEXTAREA
      input.hide();
      input.attr('disabled', 'disabled');
      textarea.show();
      textarea.removeAttr('disabled');
    } else {
      textarea.hide();
      textarea.attr('disabled', 'disabled');
      input.show();
      input.removeAttr('disabled');
    }
  });
  type.trigger('change');
});
