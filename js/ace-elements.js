/*
 Ace Admin Theme v1.4
 Copyright (c) 2016 Mohsen - (twitter.com/responsiweb)

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/* Ace v1.4.0v*/

if (typeof jQuery === 'undefined') { throw new Error('Ace\'s JavaScript requires jQuery') }

/**
 <b>Ace custom scroller</b>. It is not as feature-rich as plugins such as NiceScroll but it's good enough for most cases.
 */
(function($ , undefined) {
    var Ace_Scroll = function(element , _settings) {
        var self = this;

        var attrib_values = ace.helper.getAttrSettings(element, $.fn.ace_scroll.defaults);
        var settings = $.extend({}, $.fn.ace_scroll.defaults, _settings, attrib_values);

        this.size = 0;
        this.lock = false;
        this.lock_anyway = false;

        this.$element = $(element);
        this.element = element;

        var vertical = true;

        var disabled = false;
        var active = false;
        var created = false;


        var $content_wrap = null, content_wrap = null;
        var $track = null, $bar = null, track = null, bar = null;
        var bar_style = null;

        var bar_size = 0, bar_pos = 0, bar_max_pos = 0, bar_size_2 = 0, move_bar = true;
        var reset_once = false;

        var styleClass = '';
        var trackFlip = false;//vertical on left or horizontal on top
        var trackSize = 0;

        var css_pos,
            css_size,
            max_css_size,
            client_size,
            scroll_direction,
            scroll_size;

        var ratio = 1;
        var inline_style = false;
        var mouse_track = false;
        var mouse_release_target = 'onmouseup' in window ? window : 'html';
        var dragEvent = settings.dragEvent || false;

        var trigger_scroll = _settings.scrollEvent || false;


        var detached = settings.detached || false;//when detached, hideOnIdle as well?
        var updatePos = settings.updatePos || false;//default is true

        var hideOnIdle = settings.hideOnIdle || false;
        var hideDelay = settings.hideDelay || 1500;
        var insideTrack = false;//used to hide scroll track when mouse is up and outside of track
        var observeContent = settings.observeContent || false;
        var prevContentSize = 0;

        var is_dirty = true;//to prevent consecutive 'reset' calls

        this.ref = function() {
            return this;
        }

        this.create = function(_settings) {
            if(created) return;

            if(_settings) settings = $.extend({}, $.fn.ace_scroll.defaults, _settings);

            this.size = parseInt(this.$element.attr('data-size')) || settings.size || 200;
            vertical = !settings['horizontal'];

            css_pos = vertical ? 'top' : 'left';//'left' for horizontal
            css_size = vertical ? 'height' : 'width';//'width' for horizontal
            max_css_size = vertical ? 'maxHeight' : 'maxWidth';

            client_size = vertical ? 'clientHeight' : 'clientWidth';
            scroll_direction = vertical ? 'scrollTop' : 'scrollLeft';
            scroll_size = vertical ? 'scrollHeight' : 'scrollWidth';



            this.$element.addClass('ace-scroll');
            if(this.$element.css('position') == 'static') {
                inline_style = this.element.style.position;
                this.element.style.position = 'relative';
            } else inline_style = false;

            var scroll_bar = null;
            if(!detached) {
                this.$element.wrapInner('<div class="scroll-content" />');
                this.$element.prepend('<div class="scroll-track"><div class="scroll-bar"></div></div>');
            }
            else {
                scroll_bar = $('<div class="scroll-track scroll-detached"><div class="scroll-bar"></div></div>').appendTo('body');
            }


            $content_wrap = this.$element;
            if(!detached) $content_wrap = this.$element.find('.scroll-content').eq(0);

            if(!vertical) $content_wrap.wrapInner('<div />');

            content_wrap = $content_wrap.get(0);
            if(detached) {
                //set position for detached scrollbar
                $track = scroll_bar;
                setTrackPos();
            }
            else $track = this.$element.find('.scroll-track').eq(0);

            $bar = $track.find('.scroll-bar').eq(0);
            track = $track.get(0);
            bar = $bar.get(0);
            bar_style = bar.style;

            //add styling classes and horizontalness
            if(!vertical) $track.addClass('scroll-hz');
            if(settings.styleClass) {
                styleClass = settings.styleClass;
                $track.addClass(styleClass);
                trackFlip = !!styleClass.match(/scroll\-left|scroll\-top/);
            }

            //calculate size of track!
            if(trackSize == 0) {
                $track.show();
                getTrackSize();
            }

            $track.hide();


            //if(!touchDrag) {
            $track.on('mousedown', mouse_down_track);
            $bar.on('mousedown', mouse_down_bar);
            //}

            $content_wrap.on('scroll', function() {
                if(move_bar) {
                    bar_pos = parseInt(Math.round(this[scroll_direction] * ratio));
                    bar_style[css_pos] = bar_pos + 'px';
                }
                move_bar = false;
                if(trigger_scroll) this.$element.trigger('scroll', [content_wrap]);
            })


            if(settings.mouseWheel) {
                this.lock = settings.mouseWheelLock;
                this.lock_anyway = settings.lockAnyway;

                //mousewheel library available?
                this.$element.on(!!$.event.special.mousewheel ? 'mousewheel.ace_scroll' : 'mousewheel.ace_scroll DOMMouseScroll.ace_scroll', function(event) {
                    if(disabled) return;
                    checkContentChanges(true);

                    if(!active) return !self.lock_anyway;

                    if(mouse_track) {
                        mouse_track = false;
                        $('html').off('.ace_scroll')
                        $(mouse_release_target).off('.ace_scroll');
                        if(dragEvent) self.$element.trigger('drag.end');
                    }


                    event.deltaY = event.deltaY || 0;
                    var delta = (event.deltaY > 0 || event.originalEvent.detail < 0 || event.originalEvent.wheelDelta > 0) ? 1 : -1
                    var scrollEnd = false//have we reached the end of scrolling?

                    var clientSize = content_wrap[client_size], scrollAmount = content_wrap[scroll_direction];
                    if( !self.lock ) {
                        if(delta == -1)	scrollEnd = (content_wrap[scroll_size] <= scrollAmount + clientSize);
                        else scrollEnd = (scrollAmount == 0);
                    }

                    self.move_bar(true);

                    //var step = parseInt( Math.min(Math.max(parseInt(clientSize / 8) , 80) , self.size) ) + 1;
                    var step = parseInt(clientSize / 8);
                    if(step < 80) step = 80;
                    if(step > self.size) step = self.size;
                    step += 1;

                    content_wrap[scroll_direction] = scrollAmount - (delta * step);


                    return scrollEnd && !self.lock_anyway;
                })
            }


            //swipe not available yet
            var touchDrag = ace.vars['touch'] && 'ace_drag' in $.event.special && settings.touchDrag //&& !settings.touchSwipe;
            //add drag event for touch devices to scroll
            if(touchDrag/** || ($.fn.swipe && settings.touchSwipe)*/) {
                var dir = '', event_name = touchDrag ? 'ace_drag' : 'swipe';
                this.$element.on(event_name + '.ace_scroll', function(event) {
                    if(disabled) {
                        event.retval.cancel = true;
                        return;
                    }
                    checkContentChanges(true);

                    if(!active) {
                        event.retval.cancel = this.lock_anyway;
                        return;
                    }

                    dir = event.direction;
                    if( (vertical && (dir == 'up' || dir == 'down'))
                        ||
                        (!vertical && (dir == 'left' || dir == 'right'))
                    )
                    {
                        var distance = vertical ? event.dy : event.dx;

                        if(distance != 0) {
                            if(Math.abs(distance) > 20 && touchDrag) distance = distance * 2;

                            self.move_bar(true);
                            content_wrap[scroll_direction] = content_wrap[scroll_direction] + distance;
                        }
                    }

                })
            }


            /////////////////////////////////

            if(hideOnIdle) {
                $track.addClass('idle-hide');
            }
            if(observeContent) {
                $track.on('mouseenter.ace_scroll', function() {
                    insideTrack = true;
                    checkContentChanges(false);
                }).on('mouseleave.ace_scroll', function() {
                    insideTrack = false;
                    if(mouse_track == false) hideScrollbars();
                });
            }



            //some mobile browsers don't have mouseenter
            this.$element.on('mouseenter.ace_scroll touchstart.ace_scroll', function(e) {
                is_dirty = true;
                if(observeContent) checkContentChanges(true);
                else if(settings.hoverReset) self.reset(true);

                $track.addClass('scroll-hover');
            }).on('mouseleave.ace_scroll touchend.ace_scroll', function() {
                $track.removeClass('scroll-hover');
            });
            //

            if(!vertical) $content_wrap.children(0).css(css_size, this.size);//the extra wrapper
            $content_wrap.css(max_css_size , this.size);

            disabled = false;
            created = true;
        }
        this.is_active = function() {
            return active;
        }
        this.is_enabled = function() {
            return !disabled;
        }
        this.move_bar = function($move) {
            move_bar = $move;
        }

        this.get_track = function() {
            return track;
        }

        this.reset = function(innert_call) {
            if(disabled) return;// this;
            if(!created) this.create();
            /////////////////////
            var size = this.size;

            if(innert_call && !is_dirty) {
                return;
            }
            is_dirty = false;

            if(detached) {
                var border_size = parseInt(Math.round( (parseInt($content_wrap.css('border-top-width')) + parseInt($content_wrap.css('border-bottom-width'))) / 2.5 ));//(2.5 from trial?!)
                size -= border_size;//only if detached
            }

            var content_size   = vertical ? content_wrap[scroll_size] : size;
            if( (vertical && content_size == 0) || (!vertical && this.element.scrollWidth == 0) ) {
                //element is hidden
                //this.$element.addClass('scroll-hidden');
                $track.removeClass('scroll-active')
                return;// this;
            }

            var available_space = vertical ? size : content_wrap.clientWidth;

            if(!vertical) $content_wrap.children(0).css(css_size, size);//the extra wrapper
            $content_wrap.css(max_css_size , this.size);


            if(content_size > available_space) {
                active = true;
                $track.css(css_size, available_space).show();

                ratio = parseFloat((available_space / content_size).toFixed(5))

                bar_size = parseInt(Math.round(available_space * ratio));
                bar_size_2 = parseInt(Math.round(bar_size / 2));

                bar_max_pos = available_space - bar_size;
                bar_pos = parseInt(Math.round(content_wrap[scroll_direction] * ratio));

                bar_style[css_size] = bar_size + 'px';
                bar_style[css_pos] = bar_pos + 'px';

                $track.addClass('scroll-active');

                if(trackSize == 0) {
                    getTrackSize();
                }

                if(!reset_once) {
                    //this.$element.removeClass('scroll-hidden');
                    if(settings.reset) {
                        //reset scrollbar to zero position at first
                        content_wrap[scroll_direction] = 0;
                        bar_style[css_pos] = 0;
                    }
                    reset_once = true;
                }

                if(detached) setTrackPos();
            } else {
                active = false;
                $track.hide();
                $track.removeClass('scroll-active');
                $content_wrap.css(max_css_size , '');
            }

            return;// this;
        }
        this.disable = function() {
            content_wrap[scroll_direction] = 0;
            bar_style[css_pos] = 0;

            disabled = true;
            active = false;
            $track.hide();

            this.$element.addClass('scroll-disabled');

            $track.removeClass('scroll-active');
            $content_wrap.css(max_css_size , '');
        }
        this.enable = function() {
            disabled = false;
            this.$element.removeClass('scroll-disabled');
        }
        this.destroy = function() {
            active = false;
            disabled = false;
            created = false;

            this.$element.removeClass('ace-scroll scroll-disabled scroll-active');
            this.$element.off('.ace_scroll')

            if(!detached) {
                if(!vertical) {
                    //remove the extra wrapping div
                    $content_wrap.find('> div').children().unwrap();
                }
                $content_wrap.children().unwrap();
                $content_wrap.remove();
            }

            $track.remove();

            if(inline_style !== false) this.element.style.position = inline_style;

            if(idleTimer != null) {
                clearTimeout(idleTimer);
                idleTimer = null;
            }
        }
        this.modify = function(_settings) {
            if(_settings) settings = $.extend({}, settings, _settings);

            this.destroy();
            this.create();
            is_dirty = true;
            this.reset(true);
        }
        this.update = function(_settings) {
            if(_settings) settings = $.extend({}, settings, _settings);

            this.size = settings.size || this.size;

            this.lock = settings.mouseWheelLock || this.lock;
            this.lock_anyway = settings.lockAnyway || this.lock_anyway;

            hideOnIdle = settings.hideOnIdle || hideOnIdle;
            hideDelay = settings.hideDelay || hideDelay;
            observeContent = settings.observeContent || false;

            dragEvent = settings.dragEvent || false;

            if(typeof _settings.styleClass !== 'undefined') {
                if(styleClass) $track.removeClass(styleClass);
                styleClass = _settings.styleClass;
                if(styleClass) $track.addClass(styleClass);
                trackFlip = !!styleClass.match(/scroll\-left|scroll\-top/);
            }
        }

        this.start = function() {
            content_wrap[scroll_direction] = 0;
        }
        this.end = function() {
            content_wrap[scroll_direction] = content_wrap[scroll_size];
        }

        this.hide = function() {
            $track.hide();
        }
        this.show = function() {
            $track.show();
        }


        this.update_scroll = function() {
            move_bar = false;
            bar_style[css_pos] = bar_pos + 'px';
            content_wrap[scroll_direction] = parseInt(Math.round(bar_pos / ratio));
        }

        function mouse_down_track(e) {
            e.preventDefault();
            e.stopPropagation();

            var track_offset = $track.offset();
            var track_pos = track_offset[css_pos];//top for vertical, left for horizontal
            var mouse_pos = vertical ? e.pageY : e.pageX;

            if(mouse_pos > track_pos + bar_pos) {
                bar_pos = mouse_pos - track_pos - bar_size + bar_size_2;
                if(bar_pos > bar_max_pos) {
                    bar_pos = bar_max_pos;
                }
            }
            else {
                bar_pos = mouse_pos - track_pos - bar_size_2;
                if(bar_pos < 0) bar_pos = 0;
            }

            self.update_scroll()
        }

        var mouse_pos1 = -1, mouse_pos2 = -1;
        function mouse_down_bar(e) {
            e.preventDefault();
            e.stopPropagation();

            if(vertical) {
                mouse_pos2 = mouse_pos1 = e.pageY;
            } else {
                mouse_pos2 = mouse_pos1 = e.pageX;
            }

            mouse_track = true;
            $('html').off('mousemove.ace_scroll').on('mousemove.ace_scroll', mouse_move_bar)
            $(mouse_release_target).off('mouseup.ace_scroll').on('mouseup.ace_scroll', mouse_up_bar);

            $track.addClass('active');
            if(dragEvent) self.$element.trigger('drag.start');
        }
        function mouse_move_bar(e) {
            e.preventDefault();
            e.stopPropagation();

            if(vertical) {
                mouse_pos2 = e.pageY;
            } else {
                mouse_pos2 = e.pageX;
            }


            if(mouse_pos2 - mouse_pos1 + bar_pos > bar_max_pos) {
                mouse_pos2 = mouse_pos1 + bar_max_pos - bar_pos;
            } else if(mouse_pos2 - mouse_pos1 + bar_pos < 0) {
                mouse_pos2 = mouse_pos1 - bar_pos;
            }
            bar_pos = bar_pos + (mouse_pos2 - mouse_pos1);

            mouse_pos1 = mouse_pos2;

            if(bar_pos < 0) {
                bar_pos = 0;
            }
            else if(bar_pos > bar_max_pos) {
                bar_pos = bar_max_pos;
            }

            self.update_scroll()
        }
        function mouse_up_bar(e) {
            e.preventDefault();
            e.stopPropagation();

            mouse_track = false;
            $('html').off('.ace_scroll')
            $(mouse_release_target).off('.ace_scroll');

            $track.removeClass('active');
            if(dragEvent) self.$element.trigger('drag.end');

            if(active && hideOnIdle && !insideTrack) hideScrollbars();
        }


        var idleTimer = null;
        var prevCheckTime = 0;
        function checkContentChanges(hideSoon) {
            //check if content size has been modified since last time?
            //and with at least 1s delay
            var newCheck = +new Date();
            if(observeContent && newCheck - prevCheckTime > 1000) {
                var newSize = content_wrap[scroll_size];
                if(prevContentSize != newSize) {
                    prevContentSize = newSize;
                    is_dirty = true;
                    self.reset(true);
                }
                prevCheckTime = newCheck;
            }

            //show scrollbars when not idle anymore i.e. triggered by mousewheel, dragging, etc
            if(active && hideOnIdle) {
                if(idleTimer != null) {
                    clearTimeout(idleTimer);
                    idleTimer = null;
                }
                $track.addClass('not-idle');

                if(!insideTrack && hideSoon == true) {
                    //hideSoon is false when mouse enters track
                    hideScrollbars();
                }
            }
        }

        function hideScrollbars() {
            if(idleTimer != null) {
                clearTimeout(idleTimer);
                idleTimer = null;
            }
            idleTimer = setTimeout(function() {
                idleTimer = null;
                $track.removeClass('not-idle');
            } , hideDelay);
        }

        //for detached scrollbars
        function getTrackSize() {
            $track.css('visibility', 'hidden').addClass('scroll-hover');
            if(vertical) trackSize = parseInt($track.outerWidth()) || 0;
            else trackSize = parseInt($track.outerHeight()) || 0;
            $track.css('visibility', '').removeClass('scroll-hover');
        }
        this.track_size = function() {
            if(trackSize == 0) getTrackSize();
            return trackSize;
        }

        //for detached scrollbars
        function setTrackPos() {
            if(updatePos === false) return;

            var off = $content_wrap.offset();//because we want it relative to parent not document
            var left = off.left;
            var top = off.top;

            if(vertical) {
                if(!trackFlip) {
                    left += ($content_wrap.outerWidth() - trackSize)
                }
            }
            else {
                if(!trackFlip) {
                    top += ($content_wrap.outerHeight() - trackSize)
                }
            }

            if(updatePos === true) $track.css({top: parseInt(top), left: parseInt(left)});
            else if(updatePos === 'left') $track.css('left', parseInt(left));
            else if(updatePos === 'top') $track.css('top', parseInt(top));
        }



        this.create();
        is_dirty = true;
        this.reset(true);
        prevContentSize = content_wrap[scroll_size];

        return this;
    }


    $.fn.ace_scroll = function (option,value) {
        var retval;

        var $set = this.each(function () {
            var $this = $(this);
            var data = $this.data('ace_scroll');
            var options = typeof option === 'object' && option;

            if (!data) $this.data('ace_scroll', (data = new Ace_Scroll(this, options)));
            //else if(typeof options == 'object') data['modify'](options);
            if (typeof option === 'string') retval = data[option](value);
        });

        return (retval === undefined) ? $set : retval;
    };


    $.fn.ace_scroll.defaults = {
        'size' : 200,
        'horizontal': false,
        'mouseWheel': true,
        'mouseWheelLock': false,
        'lockAnyway': false,
        'styleClass' : false,

        'observeContent': false,
        'hideOnIdle': false,
        'hideDelay': 1500,

        'hoverReset': true //reset scrollbar sizes on mouse hover because of possible sizing changes
        ,
        'reset': false //true= set scrollTop = 0
        ,
        'dragEvent': false
        ,
        'touchDrag': true
        ,
        'touchSwipe': false
        ,
        'scrollEvent': false //trigger scroll event

        ,
        'detached': false
        ,
        'updatePos': true
        /**
         ,
         'track' : true,
         'show' : false,
         'dark': false,
         'alwaysVisible': false,
         'margin': false,
         'thin': false,
         'position': 'right'
         */
    }

    /**
     $(document).on('ace.settings.ace_scroll', function(e, name) {
		if(name == 'sidebar_collapsed') $('.ace-scroll').scroller('reset');
	});
     $(window).on('resize.ace_scroll', function() {
		$('.ace-scroll').scroller('reset');
	});
     */

})(window.jQuery);;/**
 <b>Custom color picker element</b>. Converts html select elements to a dropdown color picker.
 */
(function($ , undefined) {
    var Ace_Colorpicker = function(element, _options) {

        var attrib_values = ace.helper.getAttrSettings(element, $.fn.ace_colorpicker.defaults);
        var options = $.extend({}, $.fn.ace_colorpicker.defaults, _options, attrib_values);


        var $element = $(element);
        var color_list = '';
        var color_selected = '';
        var selection = null;
        var color_array = [];

        $element.addClass('hide').find('option').each(function() {
            var $class = 'colorpick-btn';
            var color = this.value.replace(/[^\w\s,#\(\)\.]/g, '');
            if(color.indexOf('number') >= 0) return;//ng
            if(this.value != color) this.value = color;
            if(this.selected) {
                $class += ' selected';
                color_selected = color;
            }
            color_array.push(color)
            color_list += '<li><a class="'+$class+'" style="background-color:'+color+';" data-color="'+color+'"></a></li>';
        }).
            end()
            .on('change.color', function(){
                $element.next().find('.btn-colorpicker').css('background-color', this.value);
            })
            .after('<div class="dropdown dropdown-colorpicker">\
		<a data-toggle="dropdown" class="dropdown-toggle" '+(options.auto_pos ? 'data-position="auto"' : '')+'><span class="btn-colorpicker" style="background-color:'+color_selected+'"></span></a><ul class="dropdown-menu'+(options.caret? ' dropdown-caret' : '')+(options.pull_right ? ' dropdown-menu-right' : '')+'">'+color_list+'</ul></div>')


        var dropdown = $element.next().find('.dropdown-menu')
        dropdown.on(ace.click_event, function(e, inner_call) {
            var a = $(e.target);
            if(!a.is('.colorpick-btn')) return false;

            if(selection) selection.removeClass('selected');
            selection = a;
            selection.addClass('selected');
            var color = selection.data('color');

            if(inner_call !== true) $element.val(color).trigger('change');
            $element.next().find('.btn-colorpicker').css('background-color', color);

            e.preventDefault();
            return true;//to hide dropdown
        })
        selection = $element.next().find('a.selected');

        this.pick = function(index, insert) {
            if(typeof index === 'number') {
                if(index >= color_array.length) return;
                element.selectedIndex = index;
                dropdown.find('a:eq('+index+')').trigger(ace.click_event, [true]);
            }
            else if(typeof index === 'string') {
                var color = index.replace(/[^\w\s,#\(\)\.]/g, '');
                index = color_array.indexOf(color);

                //add this color if it doesn't exist
                if(index == -1 && insert === true) {
                    color_array.push(color);

                    $('<option />')
                        .appendTo($element)
                        .val(color);

                    $('<li><a class="colorpick-btn"></a></li>')
                        .appendTo(dropdown)
                        .find('a')
                        .css('background-color', color)
                        .data('color', color);

                    index = color_array.length - 1;
                }

                if(index == -1) return;
                dropdown.find('a:eq('+index+')').trigger(ace.click_event, [true]);
            }
        }

        this.destroy = function() {
            $element.removeClass('hide')//.off('change.color')
                .next().remove();
            color_array = [];
        }
    }


    $.fn.ace_colorpicker = function(option, value) {
        var retval;

        var $set = this.each(function () {
            var $this = $(this);
            var data = $this.data('ace_colorpicker');
            var options = typeof option === 'object' && option;

            if (!data) $this.data('ace_colorpicker', (data = new Ace_Colorpicker(this, options)));
            if (typeof option === 'string') retval = data[option](value);
        });

        return (retval === undefined) ? $set : retval;
    }

    $.fn.ace_colorpicker.defaults = {
        'pull_right' : false,
        'caret': true,
        'auto_pos': true
    }

})(window.jQuery);;/**
 <b>Ace file input element</b>. Custom, simple file input element to style browser's default file input.
 */
(function($ , undefined) {
    var multiplible = 'multiple' in document.createElement('INPUT');
    var hasFileList = 'FileList' in window;//file list enabled in modern browsers
    var hasFileReader = 'FileReader' in window;
    var hasFile = 'File' in window;

    var Ace_File_Input = function(element , settings) {
        var self = this;

        var attrib_values = ace.helper.getAttrSettings(element, $.fn.ace_file_input.defaults);
        this.settings = $.extend({}, $.fn.ace_file_input.defaults, settings, attrib_values);

        this.$element = $(element);
        this.element = element;
        this.disabled = false;
        this.can_reset = true;


        this.$element
            .off('change.ace_inner_call')
            .on('change.ace_inner_call', function(e , ace_inner_call){
                if(self.disabled) return;

                if(ace_inner_call === true) return;//this change event is called from above drop event and extra checkings are taken care of there
                return handle_on_change.call(self);
                //if(ret === false) e.preventDefault();
            });

        var parent_label = this.$element.closest('label').css({'display':'block'})
        var tagName = parent_label.length == 0 ? 'label' : 'span';//if not inside a "LABEL" tag, use "LABEL" tag, otherwise use "SPAN"
        this.$element.wrap('<'+tagName+' class="ace-file-input" />');

        this.apply_settings();
        this.reset_input_field();//for firefox as it keeps selected file after refresh
    }
    Ace_File_Input.error = {
        'FILE_LOAD_FAILED' : 1,
        'IMAGE_LOAD_FAILED' : 2,
        'THUMBNAIL_FAILED' : 3
    };


    Ace_File_Input.prototype.apply_settings = function() {
        var self = this;

        this.multi = this.$element.attr('multiple') && multiplible;
        this.well_style = this.settings.style == 'well';

        if(this.well_style) {
            if( !this.settings.thumbnail ) this.settings.thumbnail = 'small';
            this.$element.parent().addClass('ace-file-multiple');
        }
        else {
            this.$element.parent().removeClass('ace-file-multiple');
        }


        this.$element.parent().find(':not(input[type=file])').remove();//remove all except our input, good for when changing settings
        this.$element.after('<span class="ace-file-container" data-title="'+this.settings.btn_choose+'"><span class="ace-file-name" data-title="'+this.settings.no_file+'">'+(this.settings.no_icon ? '<i class="'+ ace.vars['icon'] + this.settings.no_icon+'"></i>' : '')+'</span></span>');
        this.$label = this.$element.next();
        this.$container = this.$element.closest('.ace-file-input');

        var remove_btn = !!this.settings.icon_remove;
        if(remove_btn) {
            var btn =
                $('<a class="remove" href="#"><i class="'+ ace.vars['icon'] + this.settings.icon_remove+'"></i></a>')
                    .appendTo(this.$element.parent());

            btn.on(ace.click_event, function(e){
                e.preventDefault();
                if( !self.can_reset ) return false;

                var ret = true;
                if(self.settings.before_remove) ret = self.settings.before_remove.call(self.element);
                if(!ret) return false;

                var r = self.reset_input();
                return false;
            });
        }


        if(this.settings.droppable && hasFileList) {
            enable_drop_functionality.call(this);
        }
    }

    Ace_File_Input.prototype.show_file_list = function($files , inner_call) {
        var files = typeof $files === "undefined" ? this.$element.data('ace_input_files') : $files;
        if(!files || files.length == 0) return;

        //////////////////////////////////////////////////////////////////

        if(this.well_style) {
            this.$label.find('.ace-file-name').remove();
            if(!this.settings.btn_change) this.$label.addClass('hide-placeholder');
        }
        this.$label.attr('data-title', this.settings.btn_change).addClass('selected');

        for (var i = 0; i < files.length; i++) {
            var filename = '', format = false;
            if(typeof files[i] === "string") filename = files[i];
            else if(hasFile && files[i] instanceof File) filename = $.trim( files[i].name );
            else if(files[i] instanceof Object && files[i].hasOwnProperty('name')) {
                //format & name specified by user (pre-displaying name, etc)
                filename = files[i].name;
                if(files[i].hasOwnProperty('type')) format = files[i].type;
                if(!files[i].hasOwnProperty('path')) files[i].path = files[i].name;
            }
            else continue;

            var index = filename.lastIndexOf("\\") + 1;
            if(index == 0)index = filename.lastIndexOf("/") + 1;
            filename = filename.substr(index);

            if(format == false) {
                if((/\.(jpe?g|png|gif|svg|bmp|tiff?)$/i).test(filename)) {
                    format = 'image';
                }
                else if((/\.(mpe?g|flv|mov|avi|swf|mp4|mkv|webm|wmv|3gp)$/i).test(filename)) {
                    format = 'video';
                }
                else if((/\.(mp3|ogg|wav|wma|amr|aac)$/i).test(filename)) {
                    format = 'audio';
                }
                else format = 'file';
            }

            var fileIcons = {
                'file' : 'fa fa-file',
                'image' : 'fa fa-picture-o file-image',
                'video' : 'fa fa-film file-video',
                'audio' : 'fa fa-music file-audio'
            };
            var fileIcon = fileIcons[format];


            if(!this.well_style) this.$label.find('.ace-file-name').attr({'data-title':filename}).find(ace.vars['.icon']).attr('class', ace.vars['icon'] + fileIcon);
            else {
                this.$label.append('<span class="ace-file-name" data-title="'+filename+'"><i class="'+ ace.vars['icon'] + fileIcon+'"></i></span>');
                var type = (inner_call === true && hasFile && files[i] instanceof File) ? $.trim(files[i].type) : '';
                var can_preview = hasFileReader && this.settings.thumbnail
                    &&
                    ( (type.length > 0 && type.match('image')) || (type.length == 0 && format == 'image') )//the second one is for older Android's default browser which gives an empty text for file.type
                if(can_preview) {
                    var self = this;
                    $.when(preview_image.call(this, files[i])).fail(function(result){
                        //called on failure to load preview
                        if(self.settings.preview_error) self.settings.preview_error.call(self, filename, result.code);
                    })
                }
            }
        }

        return true;
    }

    Ace_File_Input.prototype.reset_input = function() {
        this.reset_input_ui();
        this.reset_input_field();
    }

    Ace_File_Input.prototype.reset_input_ui = function() {
        this.$label.attr({'data-title':this.settings.btn_choose, 'class':'ace-file-container'})
            .find('.ace-file-name:first').attr({'data-title':this.settings.no_file , 'class':'ace-file-name'})
            .find(ace.vars['.icon']).attr('class', ace.vars['icon'] + this.settings.no_icon)
            .prev('img').remove();
        if(!this.settings.no_icon) this.$label.find(ace.vars['.icon']).remove();

        this.$label.find('.ace-file-name').not(':first').remove();

        this.reset_input_data();

        //if(ace.vars['old_ie']) ace.helper.redraw(this.$container[0]);
    }
    Ace_File_Input.prototype.reset_input_field = function() {
        //http://stackoverflow.com/questions/1043957/clearing-input-type-file-using-jquery/13351234#13351234
        this.$element.wrap('<form>').parent().get(0).reset();
        this.$element.unwrap();

        //strangely when reset is called on this temporary inner form
        //only **IE9/10** trigger 'reset' on the outer form as well
        //and as we have mentioned to reset input on outer form reset
        //it causes infinite recusrsion by coming back to reset_input_field
        //thus calling reset again and again and again
        //so because when "reset" button of outer form is hit, file input is automatically reset
        //we just reset_input_ui to avoid recursion
    }
    Ace_File_Input.prototype.reset_input_data = function() {
        if(this.$element.data('ace_input_files')) {
            this.$element.removeData('ace_input_files');
            this.$element.removeData('ace_input_method');
        }
    }

    Ace_File_Input.prototype.enable_reset = function(can_reset) {
        this.can_reset = can_reset;
    }

    Ace_File_Input.prototype.disable = function() {
        this.disabled = true;
        this.$element.attr('disabled', 'disabled').addClass('disabled');
    }
    Ace_File_Input.prototype.enable = function() {
        this.disabled = false;
        this.$element.removeAttr('disabled').removeClass('disabled');
    }

    Ace_File_Input.prototype.files = function() {
        return $(this).data('ace_input_files') || null;
    }
    Ace_File_Input.prototype.method = function() {
        return $(this).data('ace_input_method') || '';
    }

    Ace_File_Input.prototype.update_settings = function(new_settings) {
        this.settings = $.extend({}, this.settings, new_settings);
        this.apply_settings();
    }

    Ace_File_Input.prototype.loading = function(is_loading) {
        if(is_loading === false) {
            this.$container.find('.ace-file-overlay').remove();
            this.element.removeAttribute('readonly');
        }
        else {
            var inside = typeof is_loading === 'string' ? is_loading : '<i class="overlay-content fa fa-spin fa-spinner orange2 fa-2x"></i>';
            var loader = this.$container.find('.ace-file-overlay');
            if(loader.length == 0) {
                loader = $('<div class="ace-file-overlay"></div>').appendTo(this.$container);
                loader.on('click tap', function(e) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    return false;
                });

                this.element.setAttribute('readonly' , 'true');//for IE
            }
            loader.empty().append(inside);
        }
    }



    var enable_drop_functionality = function() {
        var self = this;

        var dropbox = this.$element.parent();
        dropbox
            .off('dragenter')
            .on('dragenter', function(e){
                e.preventDefault();
                e.stopPropagation();
            })
            .off('dragover')
            .on('dragover', function(e){
                e.preventDefault();
                e.stopPropagation();
            })
            .off('drop')
            .on('drop', function(e){
                e.preventDefault();
                e.stopPropagation();

                if(self.disabled) return;

                var dt = e.originalEvent.dataTransfer;
                var file_list = dt.files;
                if(!self.multi && file_list.length > 1) {//single file upload, but dragged multiple files
                    var tmpfiles = [];
                    tmpfiles.push(file_list[0]);
                    file_list = tmpfiles;//keep only first file
                }


                file_list = processFiles.call(self, file_list, true);//true means files have been selected, not dropped
                if(file_list === false) return false;

                self.$element.data('ace_input_method', 'drop');
                self.$element.data('ace_input_files', file_list);//save files data to be used later by user

                self.show_file_list(file_list , true);

                self.$element.triggerHandler('change' , [true]);//true means ace_inner_call
                return true;
            });
    }


    var handle_on_change = function() {
        var file_list = this.element.files || [this.element.value];/** make it an array */

        file_list = processFiles.call(this, file_list, false);//false means files have been selected, not dropped
        if(file_list === false) return false;

        this.$element.data('ace_input_method', 'select');
        this.$element.data('ace_input_files', file_list);

        this.show_file_list(file_list , true);

        return true;
    }



    var preview_image = function(file) {
        var self = this;
        var $span = self.$label.find('.ace-file-name:last');//it should be out of onload, otherwise all onloads may target the same span because of delays

        var deferred = new $.Deferred;

        var getImage = function(src, $file) {
            $span.prepend("<img class='middle' style='display:none;' />");
            var img = $span.find('img:last').get(0);

            $(img).one('load', function() {
                imgLoaded.call(null, img, $file);
            }).one('error', function() {
                imgFailed.call(null, img);
            });

            img.src = src;
        }
        var imgLoaded = function(img, $file) {
            //if image loaded successfully

            var size = self.settings['previewSize'];

            if(!size) {
                if(self.settings['previewWidth'] || self.settings['previewHeight']) {
                    size = {previewWidth: self.settings['previewWidth'], previewHeight: self.settings['previewHeight']}
                }
                else {
                    size = 50;
                    if(self.settings.thumbnail == 'large') size = 150;
                }
            }
            if(self.settings.thumbnail == 'fit') size = $span.width();
            else if(typeof size == 'number') size = parseInt(Math.min(size, $span.width()));


            var thumb = get_thumbnail(img, size/**, file.type*/);
            if(thumb == null) {
                //if making thumbnail fails
                $(this).remove();
                deferred.reject({code:Ace_File_Input.error['THUMBNAIL_FAILED']});
                return;
            }


            var showPreview = true;
            //add width/height info to "file" and trigger preview finished event for each image!
            if($file && $file instanceof File) {
                $file.width = thumb.width;
                $file.height = thumb.height;
                self.$element.trigger('file.preview.ace', {'file': $file});

                var event
                self.$element.trigger( event = new $.Event('file.preview.ace'), {'file': $file} );
                if ( event.isDefaultPrevented() ) showPreview = false;
            }


            if(showPreview) {
                var w = thumb.previewWidth, h = thumb.previewHeight;
                if(self.settings.thumbnail == 'small') {w=h=parseInt(Math.max(w,h))}
                else $span.addClass('large');

                $(img).css({'background-image':'url('+thumb.src+')' , width:w, height:h})
                    .data('thumb', thumb.src)
                    .attr({src:'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQImWNgYGBgAAAABQABh6FO1AAAAABJRU5ErkJggg=='})
                    .show()
            }

            ///////////////////
            deferred.resolve();
        }
        var imgFailed = function(img) {
            //for example when a file has image extenstion, but format is something else
            $span.find('img').remove();
            deferred.reject({code:Ace_File_Input.error['IMAGE_LOAD_FAILED']});
        }

        if(hasFile && file instanceof File) {
            var reader = new FileReader();
            reader.onload = function (e) {
                getImage(e.target.result, file);
            }
            reader.onerror = function (e) {
                deferred.reject({code:Ace_File_Input.error['FILE_LOAD_FAILED']});
            }
            reader.readAsDataURL(file);
        }
        else {
            if(file instanceof Object && file.hasOwnProperty('path')) {
                getImage(file.path, null);//file is a file name (path) --- this is used to pre-show user-selected image
            }
        }

        return deferred.promise();
    }

    var get_thumbnail = function(img, size, type) {
        var imgWidth = img.width, imgHeight = img.height;

        //**IE10** is not giving correct width using img.width so we use $(img).width()
        imgWidth = imgWidth > 0 ? imgWidth : $(img).width()
        imgHeight = imgHeight > 0 ? imgHeight : $(img).height()

        var previewSize = false, previewHeight = false, previewWidth = false;
        if(typeof size == 'number') previewSize = size;
        else if(size instanceof Object) {
            if(size['previewWidth'] && !size['previewHeight']) previewWidth = size['previewWidth'];
            else if(size['previewHeight'] && !size['previewWidth']) previewHeight = size['previewHeight'];
            else if(size['previewWidth'] && size['previewHeight']) {
                previewWidth = size['previewWidth'];
                previewHeight = size['previewHeight'];
            }
        }

        if(previewSize) {
            if(imgWidth > imgHeight) {
                previewWidth = previewSize;
                previewHeight = parseInt(imgHeight/imgWidth * previewWidth);
            } else {
                previewHeight = previewSize;
                previewWidth = parseInt(imgWidth/imgHeight * previewHeight);
            }
        }
        else {
            if(!previewHeight && previewWidth) {
                previewHeight = parseInt(imgHeight/imgWidth * previewWidth);
            }
            else if(previewHeight && !previewWidth) {
                previewWidth = parseInt(imgWidth/imgHeight * previewHeight);
            }
        }



        var dataURL
        try {
            var canvas = document.createElement('canvas');
            canvas.width = previewWidth; canvas.height = previewHeight;
            var context = canvas.getContext('2d');
            context.drawImage(img, 0, 0, imgWidth, imgHeight, 0, 0, previewWidth, previewHeight);
            dataURL = canvas.toDataURL(/*type == 'image/jpeg' ? type : 'image/png', 10*/)
        } catch(e) {
            dataURL = null;
        }
        if(! dataURL) return null;


        //there was only one image that failed in firefox completely randomly! so let's double check things
        if( !( /^data\:image\/(png|jpe?g|gif);base64,[0-9A-Za-z\+\/\=]+$/.test(dataURL)) ) dataURL = null;
        if(! dataURL) return null;


        return {src: dataURL, previewWidth: previewWidth, previewHeight: previewHeight, width: imgWidth, height: imgHeight};
    }



    var processFiles = function(file_list, dropped) {
        var ret = checkFileList.call(this, file_list, dropped);
        if(ret === -1) {
            this.reset_input();
            return false;
        }
        if( !ret || ret.length == 0 ) {
            if( !this.$element.data('ace_input_files') ) this.reset_input();
            //if nothing selected before, reset because of the newly unacceptable (ret=false||length=0) selection
            //otherwise leave the previous selection intact?!!!
            return false;
        }
        if (ret instanceof Array || (hasFileList && ret instanceof FileList)) file_list = ret;


        ret = true;
        if(this.settings.before_change) ret = this.settings.before_change.call(this.element, file_list, dropped);
        if(ret === -1) {
            this.reset_input();
            return false;
        }
        if(!ret || ret.length == 0) {
            if( !this.$element.data('ace_input_files') ) this.reset_input();
            return false;
        }

        //inside before_change you can return a modified File Array as result
        if (ret instanceof Array || (hasFileList && ret instanceof FileList)) file_list = ret;

        return file_list;
    }


    var getExtRegex = function(ext) {
        if(!ext) return null;
        if(typeof ext === 'string') ext = [ext];
        if(ext.length == 0) return null;
        return new RegExp("\.(?:"+ext.join('|')+")$", "i");
    }
    var getMimeRegex = function(mime) {
        if(!mime) return null;
        if(typeof mime === 'string') mime = [mime];
        if(mime.length == 0) return null;
        return new RegExp("^(?:"+mime.join('|').replace(/\//g, "\\/")+")$", "i");
    }
    var checkFileList = function(files, dropped) {
        var allowExt   = getExtRegex(this.settings.allowExt);

        var denyExt    = getExtRegex(this.settings.denyExt);

        var allowMime  = getMimeRegex(this.settings.allowMime);

        var denyMime   = getMimeRegex(this.settings.denyMime);

        var maxSize    = this.settings.maxSize || false;

        if( !(allowExt || denyExt || allowMime || denyMime || maxSize) ) return true;//no checking required


        var safe_files = [];
        var error_list = {}
        for(var f = 0; f < files.length; f++) {
            var file = files[f];

            //file is either a string(file name) or a File object
            var filename = !hasFile ? file : file.name;
            if( allowExt && !allowExt.test(filename) ) {
                //extension not matching whitelist, so drop it
                if(!('ext' in error_list)) error_list['ext'] = [];
                error_list['ext'].push(filename);

                continue;
            } else if( denyExt && denyExt.test(filename) ) {
                //extension is matching blacklist, so drop it
                if(!('ext' in error_list)) error_list['ext'] = [];
                error_list['ext'].push(filename);

                continue;
            }

            var type;
            if( !hasFile ) {
                //in browsers that don't support FileReader API
                safe_files.push(file);
                continue;
            }
            else if((type = $.trim(file.type)).length > 0) {
                //there is a mimetype for file so let's check against are rules
                if( allowMime && !allowMime.test(type) ) {
                    //mimeType is not matching whitelist, so drop it
                    if(!('mime' in error_list)) error_list['mime'] = [];
                    error_list['mime'].push(filename);
                    continue;
                }
                else if( denyMime && denyMime.test(type) ) {
                    //mimeType is matching blacklist, so drop it
                    if(!('mime' in error_list)) error_list['mime'] = [];
                    error_list['mime'].push(filename);
                    continue;
                }
            }

            if( maxSize && file.size > maxSize ) {
                //file size is not acceptable
                if(!('size' in error_list)) error_list['size'] = [];
                error_list['size'].push(filename);
                continue;
            }

            safe_files.push(file)
        }



        if(safe_files.length == files.length) return files;//return original file list if all are valid

        /////////
        var error_count = {'ext': 0, 'mime': 0, 'size': 0}
        if( 'ext' in error_list ) error_count['ext'] = error_list['ext'].length;
        if( 'mime' in error_list ) error_count['mime'] = error_list['mime'].length;
        if( 'size' in error_list ) error_count['size'] = error_list['size'].length;

        var event
        this.$element.trigger(
            event = new $.Event('file.error.ace'),
            {
                'file_count': files.length,
                'invalid_count' : files.length - safe_files.length,
                'error_list' : error_list,
                'error_count' : error_count,
                'dropped': dropped
            }
        );
        if ( event.isDefaultPrevented() ) return -1;//it will reset input
        //////////

        return safe_files;//return safe_files
    }



    ///////////////////////////////////////////
    $.fn.aceFileInput = $.fn.ace_file_input = function (option,value) {
        var retval;

        var $set = this.each(function () {
            var $this = $(this);
            var data = $this.data('ace_file_input');
            var options = typeof option === 'object' && option;

            if (!data) $this.data('ace_file_input', (data = new Ace_File_Input(this, options)));
            if (typeof option === 'string') retval = data[option](value);
        });

        return (retval === undefined) ? $set : retval;
    };


    $.fn.ace_file_input.defaults = {
        style: false,
        no_file: 'No File ...',
        no_icon: 'fa fa-upload',
        btn_choose: 'Choose',
        btn_change: 'Change',
        icon_remove: 'fa fa-times',
        droppable: false,
        thumbnail: false,//large, fit, small

        allowExt: null,
        denyExt: null,
        allowMime: null,
        denyMime: null,
        maxSize: false,

        previewSize: false,
        previewWidth: false,
        previewHeight: false,

        //callbacks
        before_change: null,
        before_remove: null,
        preview_error: null
    }


})(window.jQuery);
;/**
 <b>Bootstrap 2 typeahead plugin.</b> With Bootstrap <u>3</u> it's been dropped in favor of a more advanced separate plugin.
 Pretty good for simple cases such as autocomplete feature of the search box and required for <u class="text-danger">Tag input</u> plugin.
 */

/* =============================================================
 * bootstrap-typeahead.js v2.3.2
 * http://twitter.github.com/bootstrap/javascript.html#typeahead
 * =============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function($){

    "use strict"; // jshint ;_;


    /* TYPEAHEAD PUBLIC CLASS DEFINITION
     * ================================= */

    var Typeahead = function (element, options) {
        this.$element = $(element)
        this.options = $.extend({}, $.fn.bs_typeahead.defaults, options)
        this.matcher = this.options.matcher || this.matcher
        this.sorter = this.options.sorter || this.sorter
        this.highlighter = this.options.highlighter || this.highlighter
        this.updater = this.options.updater || this.updater
        this.source = this.options.source
        this.$menu = $(this.options.menu)
        this.shown = false
        this.listen()
    }

    Typeahead.prototype = {

        constructor: Typeahead

        , select: function () {
            var val = this.$menu.find('.active').attr('data-value')
            this.$element
                .val(this.updater(val))
                .change()
            return this.hide()
        }

        , updater: function (item) {
            return item
        }

        , show: function () {
            var pos = $.extend({}, this.$element.position(), {
                height: this.$element[0].offsetHeight
            })

            this.$menu
                .insertAfter(this.$element)
                .css({
                    top: pos.top + pos.height
                    , left: pos.left
                })
                .show()

            this.shown = true
            return this
        }

        , hide: function () {
            this.$menu.hide()
            this.shown = false
            return this
        }

        , lookup: function (event) {
            var items

            this.query = this.$element.val()

            if (!this.query || this.query.length < this.options.minLength) {
                return this.shown ? this.hide() : this
            }

            items = $.isFunction(this.source) ? this.source(this.query, $.proxy(this.process, this)) : this.source

            return items ? this.process(items) : this
        }

        , process: function (items) {
            var that = this

            items = $.grep(items, function (item) {
                return that.matcher(item)
            })

            items = this.sorter(items)

            if (!items.length) {
                return this.shown ? this.hide() : this
            }

            return this.render(items.slice(0, this.options.items)).show()
        }

        , matcher: function (item) {
            return ~item.toLowerCase().indexOf(this.query.toLowerCase())
        }

        , sorter: function (items) {
            var beginswith = []
                , caseSensitive = []
                , caseInsensitive = []
                , item

            while (item = items.shift()) {
                if (!item.toLowerCase().indexOf(this.query.toLowerCase())) beginswith.push(item)
                else if (~item.indexOf(this.query)) caseSensitive.push(item)
                else caseInsensitive.push(item)
            }

            return beginswith.concat(caseSensitive, caseInsensitive)
        }

        , highlighter: function (item) {
            var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
            return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
                return '<strong>' + match + '</strong>'
            })
        }

        , render: function (items) {
            var that = this

            items = $(items).map(function (i, item) {
                i = $(that.options.item).attr('data-value', item)
                i.find('a').html(that.highlighter(item))
                return i[0]
            })

            items.first().addClass('active')
            this.$menu.html(items)
            return this
        }

        , next: function (event) {
            var active = this.$menu.find('.active').removeClass('active')
                , next = active.next()

            if (!next.length) {
                next = $(this.$menu.find('li')[0])
            }

            next.addClass('active')
        }

        , prev: function (event) {
            var active = this.$menu.find('.active').removeClass('active')
                , prev = active.prev()

            if (!prev.length) {
                prev = this.$menu.find('li').last()
            }

            prev.addClass('active')
        }

        , listen: function () {
            this.$element
                .on('focus',    $.proxy(this.focus, this))
                .on('blur',     $.proxy(this.blur, this))
                .on('keypress', $.proxy(this.keypress, this))
                .on('keyup',    $.proxy(this.keyup, this))

            if (this.eventSupported('keydown')) {
                this.$element.on('keydown', $.proxy(this.keydown, this))
            }

            this.$menu
                .on('click', $.proxy(this.click, this))
                .on('mouseenter', 'li', $.proxy(this.mouseenter, this))
                .on('mouseleave', 'li', $.proxy(this.mouseleave, this))
        }

        , eventSupported: function(eventName) {
            var isSupported = eventName in this.$element
            if (!isSupported) {
                this.$element.setAttribute(eventName, 'return;')
                isSupported = typeof this.$element[eventName] === 'function'
            }
            return isSupported
        }

        , move: function (e) {
            if (!this.shown) return

            switch(e.keyCode) {
                case 9: // tab
                case 13: // enter
                case 27: // escape
                    e.preventDefault()
                    break

                case 38: // up arrow
                    e.preventDefault()
                    this.prev()
                    break

                case 40: // down arrow
                    e.preventDefault()
                    this.next()
                    break
            }

            e.stopPropagation()
        }

        , keydown: function (e) {
            this.suppressKeyPressRepeat = ~$.inArray(e.keyCode, [40,38,9,13,27])
            this.move(e)
        }

        , keypress: function (e) {
            if (this.suppressKeyPressRepeat) return
            this.move(e)
        }

        , keyup: function (e) {
            switch(e.keyCode) {
                case 40: // down arrow
                case 38: // up arrow
                case 16: // shift
                case 17: // ctrl
                case 18: // alt
                    break

                case 9: // tab
                case 13: // enter
                    if (!this.shown) return
                    this.select()
                    break

                case 27: // escape
                    if (!this.shown) return
                    this.hide()
                    break

                default:
                    this.lookup()
            }

            e.stopPropagation()
            e.preventDefault()
        }

        , focus: function (e) {
            this.focused = true
        }

        , blur: function (e) {
            this.focused = false
            if (!this.mousedover && this.shown) this.hide()
        }

        , click: function (e) {
            e.stopPropagation()
            e.preventDefault()
            this.select()
            this.$element.focus()
        }

        , mouseenter: function (e) {
            this.mousedover = true
            this.$menu.find('.active').removeClass('active')
            $(e.currentTarget).addClass('active')
        }

        , mouseleave: function (e) {
            this.mousedover = false
            if (!this.focused && this.shown) this.hide()
        }

    }


    /* TYPEAHEAD PLUGIN DEFINITION
     * =========================== */

    var old = $.fn.bs_typeahead

    $.fn.bs_typeahead = function (option) {
        return this.each(function () {
            var $this = $(this)
                , data = $this.data('bs_typeahead')
                , options = typeof option == 'object' && option
            if (!data) $this.data('bs_typeahead', (data = new Typeahead(this, options)))
            if (typeof option == 'string') data[option]()
        })
    }

    $.fn.bs_typeahead.defaults = {
        source: []
        , items: 8
        , menu: '<ul class="typeahead dropdown-menu"></ul>'
        , item: '<li><a href="#"></a></li>'
        , minLength: 1
    }

    $.fn.bs_typeahead.Constructor = Typeahead


    /* TYPEAHEAD NO CONFLICT
     * =================== */

    $.fn.bs_typeahead.noConflict = function () {
        $.fn.bs_typeahead = old
        return this
    }


    /* TYPEAHEAD DATA-API
     * ================== */

    $(document).on('focus.bs_typeahead.data-api', '[data-provide="bs_typeahead"]', function (e) {
        var $this = $(this)
        if ($this.data('bs_typeahead')) return
        $this.bs_typeahead($this.data())
    })

}(window.jQuery);;/**
 <b>Wysiwyg</b>. A wrapper for Bootstrap wyswiwyg plugin.
 It's just a wrapper so you still need to include Bootstrap wysiwyg script first.
 */
(function($ , undefined) {
    $.fn.ace_wysiwyg = function($options , undefined) {
        var options = $.extend( {
            speech_button:true,
            wysiwyg:{}
        }, $options);

        var color_values = [
            '#ac725e','#d06b64','#f83a22','#fa573c','#ff7537','#ffad46',
            '#42d692','#16a765','#7bd148','#b3dc6c','#fbe983','#fad165',
            '#92e1c0','#9fe1e7','#9fc6e7','#4986e7','#9a9cff','#b99aff',
            '#c2c2c2','#cabdbf','#cca6ac','#f691b2','#cd74e6','#a47ae2',
            '#444444'
        ]

        var button_defaults =
        {
            'font' : {
                values:['Arial', 'Courier', 'Comic Sans MS', 'Helvetica', 'Open Sans', 'Tahoma', 'Verdana'],
                icon:'fa fa-font',
                title:'Font'
            },
            'fontSize' : {
                values:{5:'Huge', 3:'Normal', 1:'Small'},
                icon:'fa fa-text-height',
                title:'Font Size'
            },
            'bold' : {
                icon : 'fa fa-bold',
                title : 'Bold (Ctrl/Cmd+B)'
            },
            'italic' : {
                icon : 'fa fa-italic',
                title : 'Italic (Ctrl/Cmd+I)'
            },
            'strikethrough' : {
                icon : 'fa fa-strikethrough',
                title : 'Strikethrough'
            },
            'underline' : {
                icon : 'fa fa-underline',
                title : 'Underline'
            },
            'insertunorderedlist' : {
                icon : 'fa fa-list-ul',
                title : 'Bullet list'
            },
            'insertorderedlist' : {
                icon : 'fa fa-list-ol',
                title : 'Number list'
            },
            'outdent' : {
                icon : 'fa fa-outdent',
                title : 'Reduce indent (Shift+Tab)'
            },
            'indent' : {
                icon : 'fa fa-indent',
                title : 'Indent (Tab)'
            },
            'justifyleft' : {
                icon : 'fa fa-align-left',
                title : 'Align Left (Ctrl/Cmd+L)'
            },
            'justifycenter' : {
                icon : 'fa fa-align-center',
                title : 'Center (Ctrl/Cmd+E)'
            },
            'justifyright' : {
                icon : 'fa fa-align-right',
                title : 'Align Right (Ctrl/Cmd+R)'
            },
            'justifyfull' : {
                icon : 'fa fa-align-justify',
                title : 'Justify (Ctrl/Cmd+J)'
            },
            'createLink' : {
                icon : 'fa fa-link',
                title : 'Hyperlink',
                button_text : 'Add',
                placeholder : 'URL',
                button_class : 'btn-primary'
            },
            'unlink' : {
                icon : 'fa fa-chain-broken',
                title : 'Remove Hyperlink'
            },
            'insertImage' : {
                icon : 'fa fa-picture-o',
                title : 'Insert picture',
                button_text : '<i class="'+ ace.vars['icon'] + 'fa fa-file"></i> Choose Image &hellip;',
                placeholder : 'Image URL',
                button_insert : 'Insert',
                button_class : 'btn-success',
                button_insert_class : 'btn-primary',
                choose_file: true //show the choose file button?
            },
            'foreColor' : {
                values : color_values,
                title : 'Change Color'
            },
            'backColor' : {
                values : color_values,
                title : 'Change Background Color'
            },
            'undo' : {
                icon : 'fa fa-undo',
                title : 'Undo (Ctrl/Cmd+Z)'
            },
            'redo' : {
                icon : 'fa fa-repeat',
                title : 'Redo (Ctrl/Cmd+Y)'
            },
            'viewSource' : {
                icon : 'fa fa-code',
                title : 'View Source'
            }
        }

        var toolbar_buttons =
            options.toolbar ||
            [
                'font',
                null,
                'fontSize',
                null,
                'bold',
                'italic',
                'strikethrough',
                'underline',
                null,
                'insertunorderedlist',
                'insertorderedlist',
                'outdent',
                'indent',
                null,
                'justifyleft',
                'justifycenter',
                'justifyright',
                'justifyfull',
                null,
                'createLink',
                'unlink',
                null,
                'insertImage',
                null,
                'foreColor',
                null,
                'undo',
                'redo',
                null,
                'viewSource'
            ]


        this.each(function() {
            var toolbar = ' <div class="wysiwyg-toolbar btn-toolbar center"> <div class="btn-group"> ';

            for(var tb in toolbar_buttons) if(toolbar_buttons.hasOwnProperty(tb)) {
                var button = toolbar_buttons[tb];
                if(button === null){
                    toolbar += ' </div> <div class="btn-group"> ';
                    continue;
                }

                if(typeof button == "string" && button in button_defaults) {
                    button = button_defaults[button];
                    button.name = toolbar_buttons[tb];
                } else if(typeof button == "object" && button.name in button_defaults) {
                    button = $.extend(button_defaults[button.name] , button);
                }
                else continue;

                var className = "className" in button ? button.className : 'btn-default';
                switch(button.name) {
                    case 'font':
                        toolbar += ' <a class="btn btn-sm '+className+' dropdown-toggle" data-toggle="dropdown" title="'+button.title+'"><i class="'+ ace.vars['icon'] + button.icon+'"></i><i class="' + ace.vars['icon'] + 'fa fa-angle-down icon-on-right"></i></a> ';
                        toolbar += ' <ul class="dropdown-menu dropdown-light dropdown-caret">';
                        for(var font in button.values)
                            if(button.values.hasOwnProperty(font))
                                toolbar += ' <li><a data-edit="fontName ' + button.values[font] +'" style="font-family:\''+ button.values[font]  +'\'">'+button.values[font]  + '</a></li> '
                        toolbar += ' </ul>';
                        break;

                    case 'fontSize':
                        toolbar += ' <a class="btn btn-sm '+className+' dropdown-toggle" data-toggle="dropdown" title="'+button.title+'"><i class="'+ ace.vars['icon'] + button.icon+'"></i>&nbsp;<i class="'+ ace.vars['icon'] + 'fa fa-angle-down icon-on-right"></i></a> ';
                        toolbar += ' <ul class="dropdown-menu dropdown-light dropdown-caret"> ';
                        for(var size in button.values)
                            if(button.values.hasOwnProperty(size))
                                toolbar += ' <li><a data-edit="fontSize '+size+'"><font size="'+size+'">'+ button.values[size] +'</font></a></li> '
                        toolbar += ' </ul> ';
                        break;

                    case 'createLink':
                        toolbar += ' <div class="btn-group"> <a class="btn btn-sm '+className+' dropdown-toggle" data-toggle="dropdown" title="'+button.title+'"><i class="'+ ace.vars['icon'] + button.icon+'"></i></a> ';
                        toolbar += ' <div class="dropdown-menu dropdown-caret dropdown-menu-right">\
							 <div class="input-group">\
								<input class="form-control" placeholder="'+button.placeholder+'" type="text" data-edit="'+button.name+'" />\
								<span class="input-group-btn">\
									<button class="btn btn-sm '+button.button_class+'" type="button">'+button.button_text+'</button>\
								</span>\
							 </div>\
						</div> </div>';
                        break;

                    case 'insertImage':
                        toolbar += ' <div class="btn-group"> <a class="btn btn-sm '+className+' dropdown-toggle" data-toggle="dropdown" title="'+button.title+'"><i class="'+ ace.vars['icon'] + button.icon+'"></i></a> ';
                        toolbar += ' <div class="dropdown-menu dropdown-caret dropdown-menu-right">\
							 <div class="input-group">\
								<input class="form-control" placeholder="'+button.placeholder+'" type="text" data-edit="'+button.name+'" />\
								<span class="input-group-btn">\
									<button class="btn btn-sm '+button.button_insert_class+'" type="button">'+button.button_insert+'</button>\
								</span>\
							 </div>';
                        if( button.choose_file && 'FileReader' in window ) toolbar +=
                            '<div class="space-2"></div>\
                            <label class="center block no-margin-bottom">\
                               <button class="btn btn-sm '+button.button_class+' wysiwyg-choose-file" type="button">'+button.button_text+'</button>\
								<input type="file" data-edit="'+button.name+'" />\
							  </label>'
                        toolbar += ' </div> </div>';
                        break;

                    case 'foreColor':
                    case 'backColor':
                        toolbar += ' <select class="hide wysiwyg_colorpicker" title="'+button.title+'"> ';
                        $.each(button.values, function (_, color) {
                            toolbar += ' <option value="' + color + '">' + color + '</option> ';
                        });
                        toolbar += ' </select> ';
                        toolbar += ' <input style="display:none;" disabled class="hide" type="text" data-edit="'+button.name+'" /> ';
                        break;

                    case 'viewSource':
                        toolbar += ' <a class="btn btn-sm '+className+'" data-view="source" title="'+button.title+'"><i class="'+ ace.vars['icon'] + button.icon+'"></i></a> ';
                        break;
                    default:
                        toolbar += ' <a class="btn btn-sm '+className+'" data-edit="'+button.name+'" title="'+button.title+'"><i class="'+ ace.vars['icon'] + button.icon+'"></i></a> ';
                        break;
                }
            }
            toolbar += ' </div> ';
            ////////////
            var speech_input;
            if (options.speech_button && 'onwebkitspeechchange' in (speech_input = document.createElement('input'))) {
                toolbar += ' <input class="wysiwyg-speech-input" type="text" data-edit="inserttext" x-webkit-speech />';
            }
            speech_input = null;
            ////////////
            toolbar += ' </div> ';


            //if we have a function to decide where to put the toolbar, then call that
            if(options.toolbar_place) toolbar = options.toolbar_place.call(this, toolbar);
            //otherwise put it just before our DIV
            else toolbar = $(this).before(toolbar).prev();

            if($.fn.tooltip) toolbar.find('a[title]').tooltip({animation:false, container:'body'});

            toolbar.find('.dropdown-menu input[type=text]').on('click', function() {return false})
                .on('change', function() {$(this).closest('.dropdown-menu').siblings('.dropdown-toggle').dropdown('toggle')})
                .on('keydown', function (e) {
                    if(e.which == 27) {
                        this.value = '';
                        $(this).change();
                    }
                    else if(e.which == 13) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).change();
                    }
                });

            toolbar.find('input[type=file]').prev().on(ace.click_event, function (e) {
                $(this).next().click();
            });
            toolbar.find('.wysiwyg_colorpicker').each(function() {
                var el = $(this).ace_colorpicker({pull_right:true}).change(function(){
                    $(this).nextAll('input').eq(0).val(this.value).change();
                }).next().find('.btn-colorpicker')

                if($.fn.tooltip) el.tooltip({title: this.title, animation:false, container:'body'})
            });


            var self = $(this);
            //view source
            var view_source = false;
            toolbar.find('a[data-view=source]').on('click', function(e){
                e.preventDefault();

                if(!view_source) {
                    $('<textarea />')
                        .css({'width':self.outerWidth(), 'height':self.outerHeight()})
                        .val(self.html())
                        .insertAfter(self)
                    self.hide();

                    $(this).addClass('active');
                }
                else {
                    var textarea = self.next();
                    self.html(textarea.val()).show();
                    textarea.remove();

                    $(this).removeClass('active');
                }

                view_source = !view_source;
            });


            var $options = $.extend({}, { activeToolbarClass: 'active' , toolbarSelector : toolbar }, options.wysiwyg || {})
            $(this).wysiwyg( $options );
        });

        return this;
    }


})(window.jQuery);

;/**
 <b>Spinner</b>. A wrapper for FuelUX spinner element.
 It's just a wrapper so you still need to include FuelUX spinner script first.
 */
(function($ , undefined) {
    //a wrapper for fuelux spinner
    function Ace_Spinner(element , _options) {
        var attrib_values = ace.helper.getAttrSettings(element, $.fn.ace_spinner.defaults);
        var options = $.extend({}, $.fn.ace_spinner.defaults, _options, attrib_values);

        var max = options.max
        max = (''+max).length
        var width = parseInt(Math.max((max * 20 + 40) , 90))

        var $element = $(element);

        var btn_class = 'btn-sm';//default
        var sizing = 2;
        if($element.hasClass('input-sm')) {
            btn_class = 'btn-xs';
            sizing = 1;
        }
        else if($element.hasClass('input-lg')) {
            btn_class = 'btn-lg';
            sizing = 3;
        }

        if(sizing == 2) width += 25;
        else if(sizing == 3) width += 50;

        $element.addClass('spinbox-input form-control text-center').wrap('<div class="ace-spinner middle">')

        var $parent_div = $element.closest('.ace-spinner').spinbox(options).wrapInner("<div class='input-group'></div>")
        var $spinner = $parent_div.data('fu.spinbox');

        if(options.on_sides)
        {
            $element
                .before('<div class="spinbox-buttons input-group-btn">\
					<button type="button" class="btn spinbox-down '+btn_class+' '+options.btn_down_class+'">\
						<i class="icon-only '+ ace.vars['icon'] + options.icon_down+'"></i>\
					</button>\
				</div>')
                .after('<div class="spinbox-buttons input-group-btn">\
					<button type="button" class="btn spinbox-up '+btn_class+' '+options.btn_up_class+'">\
						<i class="icon-only '+ ace.vars['icon'] + options.icon_up+'"></i>\
					</button>\
				</div>');

            $parent_div.addClass('touch-spinner')
            $parent_div.css('width' , width+'px')
        }
        else {
            $element
                .after('<div class="spinbox-buttons input-group-btn">\
					<button type="button" class="btn spinbox-up '+btn_class+' '+options.btn_up_class+'">\
						<i class="icon-only '+ ace.vars['icon'] + options.icon_up+'"></i>\
					</button>\
					<button type="button" class="btn spinbox-down '+btn_class+' '+options.btn_down_class+'">\
						<i class="icon-only '+ ace.vars['icon'] + options.icon_down+'"></i>\
					</button>\
				</div>')

            if(ace.vars['touch'] || options.touch_spinner) {
                $parent_div.addClass('touch-spinner')
                $parent_div.css('width' , width+'px')
            }
            else {
                $element.next().addClass('btn-group-vertical');
                $parent_div.css('width' , width+'px')
            }
        }

        $parent_div.on('changed', function(){
            $element.trigger('change')//trigger the input's change event
        });

        this._call = function(name, arg) {
            $spinner[name](arg);
        }
    }


    $.fn.ace_spinner = function(option, value) {
        var retval;

        var $set = this.each(function() {
            var $this = $(this);
            var data = $this.data('ace_spinner');
            var options = typeof option === 'object' && option;

            if (!data) {
                options = $.extend({}, $.fn.ace_spinner.defaults, option);
                $this.data('ace_spinner', (data = new Ace_Spinner(this, options)));
            }
            if (typeof option === 'string') retval = data._call(option, value);
        });

        return (retval === undefined) ? $set : retval;
    }

    $.fn.ace_spinner.defaults = {
        'icon_up' : 'fa fa-chevron-up',
        'icon_down': 'fa fa-chevron-down',

        'on_sides': false,
        'btn_up_class': '',
        'btn_down_class' : '',

        'max' : 999,
        'touch_spinner': false
    }


})(window.jQuery);
;/**
 <b>Treeview</b>. A wrapper for FuelUX treeview element.
 It's just a wrapper so you still need to include FuelUX treeview script first.
 */
(function($ , undefined) {

    $.fn.aceTree = $.fn.ace_tree = function(options) {
        var $defaults = {
            'open-icon' : ace.vars['icon'] + 'fa fa-folder-open',
            'close-icon' : ace.vars['icon'] + 'fa fa-folder',
            'toggle-icon': ace.vars['icon'] + 'fa fa-play',
            'selected-icon' : ace.vars['icon'] + 'fa fa-check',
            'unselected-icon' : ace.vars['icon'] + 'fa fa-times',
            'base-icon' : ace.vars['icon'] + 'fa',
            'folder-open-icon' : 'fa fa-plus-square-o',
            'folder-close-icon' : 'fa fa-plus-minus-o',
            'loadingHTML': 'Loading...'
        }

        this.each(function() {

            var attrib_values = ace.helper.getAttrSettings(this, $defaults);
            var $options = $.extend({}, $defaults, options, attrib_values);

            var $this = $(this);
            $this.addClass('tree').attr('role', 'tree');
            $this.html(
                '<li class="tree-branch hide" data-template="treebranch" role="treeitem" aria-expanded="false">\
                    '+($options['folderSelect'] ? '<i class="icon-caret '+$options['folder-open-icon']+'"></i>&nbsp;' : '')+'\
				<div class="tree-branch-header">\
					<span class="tree-branch-name">\
						<i class="icon-folder '+$options['close-icon']+'"></i>\
						<span class="tree-label"></span>\
					</span>\
				</div>\
				<ul class="tree-branch-children" role="group"></ul>\
				<div class="tree-loader" role="alert">'+$options['loadingHTML']+'</div>\
			</li>\
			<li class="tree-item hide" data-template="treeitem" role="treeitem">\
				<span class="tree-item-name">\
				  '+($options['unselected-icon'] == null ? '' : '<i class="icon-item '+$options['unselected-icon']+'"></i>')+'\
				  <span class="tree-label"></span>\
				</span>\
			</li>');

            $this.addClass($options['selectable'] == true ? 'tree-selectable' : 'tree-unselectable');

            $this.tree($options);
        });

        return this;
    }

})(window.jQuery);
;/**
 <b>Wizard</b>. A wrapper for FuelUX wizard element.
 It's just a wrapper so you still need to include FuelUX wizard script first.
 */
(function($ , undefined) {
    $.fn.aceWizard = $.fn.ace_wizard = function(options) {

        this.each(function() {
            var $this = $(this);
            $this.wizard();

            if(ace.vars['old_ie']) $this.find('ul.steps > li').last().addClass('last-child');

            var buttons = (options && options['buttons']) ? $(options['buttons']) : $this.siblings('.wizard-actions').eq(0);
            var $wizard = $this.data('fu.wizard');
            $wizard.$prevBtn.remove();
            $wizard.$nextBtn.remove();

            $wizard.$prevBtn = buttons.find('.btn-prev').eq(0).on(ace.click_event,  function(){
                $wizard.previous();
            }).attr('disabled', 'disabled');
            $wizard.$nextBtn = buttons.find('.btn-next').eq(0).on(ace.click_event,  function(){
                $wizard.next();
            }).removeAttr('disabled');
            $wizard.nextText = $wizard.$nextBtn.text();

            var step = options && ((options.selectedItem && options.selectedItem.step) || options.step);
            if(step) {
                $wizard.currentStep = step;
                $wizard.setState();
            }
        });

        return this;
    }

})(window.jQuery);
;/**
 <b>Content Slider</b>. with custom content and elements based on Bootstrap modals.
 */
(function($ , undefined) {
    var $window = $(window);

    function Aside(modal, settings) {
        var self = this;

        var $modal = $(modal);
        var placement = 'right', vertical = false;
        var hasFade = $modal.hasClass('fade');//Bootstrap enables transition only when modal is ".fade"

        var attrib_values = ace.helper.getAttrSettings(modal, $.fn.ace_aside.defaults);
        this.settings = $.extend({}, $.fn.ace_aside.defaults, settings, attrib_values);

        //if no scroll style specified and modal has dark background, let's make scrollbars 'white'
        if(this.settings.background && !settings.scroll_style && !attrib_values.scroll_style) {
            this.settings.scroll_style = 'scroll-white no-track';
        }


        this.container = this.settings.container;
        if(this.container) {
            try {
                if( $(this.container).get(0) == document.body ) this.container = null;
            } catch(e) {}
        }
        if(this.container) {
            this.settings.backdrop = false;//no backdrop when inside another element?
            $modal.addClass('aside-contained');
        }


        var dialog = $modal.find('.modal-dialog');
        var content = $modal.find('.modal-content');
        var delay = 300;

        this.initiate = function() {
            modal.className = modal.className.replace(/(\s|^)aside\-(right|top|left|bottom)(\s|$)/ig , '$1$3');

            placement = this.settings.placement;
            if(placement) placement = $.trim(placement.toLowerCase());
            if(!placement || !(/right|top|left|bottom/.test(placement))) placement = 'right';

            $modal.attr('data-placement', placement);
            $modal.addClass('aside-' + placement);

            if( /right|left/.test(placement) ) {
                vertical = true;
                $modal.addClass('aside-vc');//vertical
            }
            else $modal.addClass('aside-hz');//horizontal

            if( this.settings.fixed ) $modal.addClass('aside-fixed');
            if( this.settings.background ) $modal.addClass('aside-dark');
            if( this.settings.offset ) $modal.addClass('navbar-offset');

            if( !this.settings.transition ) $modal.addClass('transition-off');

            $modal.addClass('aside-hidden');

            this.insideContainer();

            /////////////////////////////

            dialog = $modal.find('.modal-dialog');
            content = $modal.find('.modal-content');

            if(!this.settings.body_scroll) {
                //don't allow body scroll when modal is open
                $modal.on('mousewheel.aside DOMMouseScroll.aside touchmove.aside pointermove.aside', function(e) {
                    if( !$.contains(content[0], e.target) ) {
                        e.preventDefault();
                        return false;
                    }
                })
            }

            if( this.settings.backdrop == false ) {
                $modal.addClass('no-backdrop');
            }
        }


        this.show = function() {
            if(this.settings.backdrop == false) {
                try {
                    $modal.data('bs.modal').$backdrop.remove();
                } catch(e){}
            }

            if(this.container) $(this.container).addClass('overflow-hidden');
            else $modal.css('position', 'fixed')

            $modal.removeClass('aside-hidden');
        }

        this.hide = function() {
            if(this.container) {
                this.container.addClass('overflow-hidden');

                if(ace.vars['firefox']) {
                    //firefox needs a bit of forcing re-calculation
                    modal.offsetHeight;
                }
            }

            toggleButton();

            if(ace.vars['transition'] && !hasFade) {
                $modal.one('bsTransitionEnd', function() {
                    $modal.addClass('aside-hidden');
                    $modal.css('position', '');

                    if(self.container) self.container.removeClass('overflow-hidden');
                }).emulateTransitionEnd(delay);
            }
        }

        this.shown = function() {
            toggleButton();
            $('body').removeClass('modal-open').css('padding-right', '');

            if( this.settings.backdrop == 'invisible' ) {
                try {
                    $modal.data('bs.modal').$backdrop.css('opacity', 0);
                } catch(e){}
            }

            var size = !vertical ? dialog.height() : content.height();
            if(!ace.vars['touch']) {
                if(!content.hasClass('ace-scroll')) {
                    content.ace_scroll({
                        size: size,
                        reset: true,
                        mouseWheelLock: true,
                        lockAnyway: !this.settings.body_scroll,
                        styleClass: this.settings.scroll_style,
                        'observeContent': true,
                        'hideOnIdle': !ace.vars['old_ie'],
                        'hideDelay': 1500
                    })
                }
            }
            else {
                content.addClass('overflow-scroll').css('max-height', size+'px');
            }

            $window
                .off('resize.modal.aside')
                .on('resize.modal.aside', function() {
                    if(!ace.vars['touch']) {
                        content.ace_scroll('disable');//to get correct size when going from small window size to large size
                        var size = !vertical ? dialog.height() : content.height();
                        content
                            .ace_scroll('update', {'size': size})
                            .ace_scroll('enable')
                            .ace_scroll('reset');
                    }
                    else content.css('max-height', (!vertical ? dialog.height() : content.height())+'px');
                }).triggerHandler('resize.modal.aside');


            ///////////////////////////////////////////////////////////////////////////
            if(self.container && ace.vars['transition'] && !hasFade) {
                $modal.one('bsTransitionEnd', function() {
                    self.container.removeClass('overflow-hidden')
                }).emulateTransitionEnd(delay);
            }
        }


        this.hidden = function() {
            $window.off('.aside')
            //$modal.off('.aside')
            //
            if( !ace.vars['transition'] || hasFade ) {
                $modal.addClass('aside-hidden');
                $modal.css('position', '');
            }
        }


        this.insideContainer = function() {
            var container = $('.main-container');

            var dialog = $modal.find('.modal-dialog');
            dialog.css({'right': '', 'left': ''});
            if( container.hasClass('container') ) {
                var flag = false;
                if(vertical == true) {
                    dialog.css( placement, parseInt(($window.width() - container.width()) / 2) );
                    flag = true;
                }

                //strange firefox issue, not redrawing properly on window resize (zoom in/out)!!!!
                //--- firefix is still having issue!
                if(flag && ace.vars['firefox']) {
                    ace.helper.redraw(container[0]);
                }
            }
        }

        this.flip = function() {
            var flipSides = {right : 'left', left : 'right', top: 'bottom', bottom: 'top'};
            $modal.removeClass('aside-'+placement).addClass('aside-'+flipSides[placement]);
            placement = flipSides[placement];
        }

        var toggleButton = function() {
            var btn = $modal.find('.aside-trigger');
            if(btn.length == 0) return;
            btn.toggleClass('open');

            var icon = btn.find(ace.vars['.icon']);
            if(icon.length == 0) return;
            icon.toggleClass(icon.attr('data-icon1') + " " + icon.attr('data-icon2'));
        }


        this.initiate();

        if(this.container) this.container = $(this.container);
        $modal.appendTo(this.container || 'body');
    }


    $(document)
        .on('show.bs.modal', '.modal.aside', function(e) {
            $('.aside.in').modal('hide');//??? hide previous open ones?
            $(this).ace_aside('show');
        })
        .on('hide.bs.modal', '.modal.aside', function(e) {
            $(this).ace_aside('hide');
        })
        .on('shown.bs.modal', '.modal.aside', function(e) {
            $(this).ace_aside('shown');
        })
        .on('hidden.bs.modal', '.modal.aside', function(e) {
            $(this).ace_aside('hidden');
        })




    $(window).on('resize.aside_container', function() {
        $('.modal.aside').ace_aside('insideContainer');
    });
    $(document).on('settings.ace.aside', function(e, event_name) {
        if(event_name == 'main_container_fixed') $('.modal.aside').ace_aside('insideContainer');
    });

    $.fn.aceAside = $.fn.ace_aside = function (option, value) {
        var method_call;

        var $set = this.each(function () {
            var $this = $(this);
            var data = $this.data('ace_aside');
            var options = typeof option === 'object' && option;

            if (!data) $this.data('ace_aside', (data = new Aside(this, options)));
            if (typeof option === 'string' && typeof data[option] === 'function') {
                if(value instanceof Array) method_call = data[option].apply(data, value);
                else method_call = data[option](value);
            }
        });

        return (method_call === undefined) ? $set : method_call;
    }

    $.fn.ace_aside.defaults = {
        fixed: false,
        background: false,
        offset: false,
        body_scroll: false,
        transition: true,
        scroll_style: 'scroll-dark no-track',
        container: null,
        backdrop: false,
        placement: 'right'
    }

})(window.jQuery);