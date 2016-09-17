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
(function(){
    if( !('ace' in window) ) window['ace'] = {}

    ace.config = {
        storage_method: 0, //0 means use localStorage if available otherwise cookies, 1 means localStorage, 2 means cookies
        cookie_expiry : 604800, //(cookie only) 1 week duration for saved settings
        cookie_path: ''//(cookie only)
    }
    if( !('vars' in window['ace']) ) window['ace'].vars = {}
    ace.vars['very_old_ie']	= !('querySelector' in document.documentElement);

    ace.settings = {
        saveState : function(element, attrName, attrVal, append) {
            if( !element || (typeof element == 'string' && !(element = document.getElementById(element))) || !element.hasAttribute('id') ) return false;
            if( !ace.hasClass(element, 'ace-save-state') ) return false;

            var attrName = attrName || 'class';
            var id = element.getAttribute('id');

            var attrList = ace.data.get('state', 'id-'+id) || {};
            if(typeof attrList == 'string') {
                try {
                    attrList = JSON.parse(attrList);
                }
                catch(e) {
                    attrList = {}
                }
            }

            var newVal, hasCustomVal = typeof attrVal !== 'undefined', $delete = false;

            var re1 = /class/i
            var re2 = /checked|disabled|readonly|value/i

            if(re2.test(attrName)) newVal = hasCustomVal ? attrVal : element[attrName];
            else {
                if(element.hasAttribute(attrName)) {
                    newVal = hasCustomVal ? attrVal : element.getAttribute(attrName);
                }
                else if(!hasCustomVal) $delete = true;
                //delete this, because element has no such attribute and we haven't given a custom value! (no attrVal)
            }


            if($delete) {
                delete attrList[attrName];
            }
            else {
                //save class names as an object which indicated which classes should be included or excluded (true/false)
                if( re1.test(attrName) ) {//class


                    if( !attrList.hasOwnProperty(attrName) ) attrList[attrName] = {}
                    if(append === true) {
                        //append to previous value
                        attrList[attrName][newVal] = 1;
                    }
                    else if(append === false) {
                        //remove from previous value
                        attrList[attrName][newVal] = -1;
                    }
                    else {
                        attrList[attrName]['className'] = newVal;
                    }
                }

                else {
                    attrList[attrName] = newVal;
                }
            }

            ace.data.set('state', 'id-'+id , JSON.stringify(attrList));
        },

        loadState : function(element, attrName) {
            if( !element || (typeof element == 'string' && !(element = document.getElementById(element))) || !element.hasAttribute('id') ) return false;

            var id = element.getAttribute('id');
            var attrList = ace.data.get('state', 'id-'+id) || {};
            if(typeof attrList == 'string') {
                try {
                    attrList = JSON.parse(attrList);
                }
                catch(e) {
                    attrList = {}
                }
            }

            var setAttr = function(element, attr, val) {
                var re1 = /class/i
                var re2 = /checked|disabled|readonly|value/i

                if(re1.test(attr)) {
                    if(typeof val === 'object') {
                        if('className' in val) element.setAttribute('class', val['className']);
                        for(var key in val) if(val.hasOwnProperty(key)) {
                            var append = val[key];
                            if(append == 1) ace.addClass(element, key);
                            else if(append == -1) ace.removeClass(element, key);
                        }
                    }
                    //else if(typeof ace.addClass(element, val);
                }
                else if(re2.test(attr)) element[attr] = val;
                else element.setAttribute(attr, val);
            }

            if(attrName !== undefined) {
                if(attrList.hasOwnProperty(attrName) && attrList[attrName] !== null) setAttr(element, attrName, attrList[attrName]);
            }
            else {
                for(var name in attrList) {
                    if(attrList.hasOwnProperty(name) && attrList[name] !== null) setAttr(element, name, attrList[name]);
                }
            }
        },

        clearState : function(element) {
            var id = null;
            if(typeof element === 'string') {
                id = element;
            }
            else if('hasAttribute' in element && element.hasAttribute('id')) {
                id = element.getAttribute('id');
            }
            if(id) ace.data.remove('state', 'id-'+id);
        }
    };




    (function() {
        //detect if it is supported
        //https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Animations/Detecting_CSS_animation_support
        var animationSupport = function() {
            var animation = false,
                animationstring = 'animation',
                keyframeprefix = '',
                domPrefixes = 'Webkit Moz O ms Khtml'.split(' '),
                pfx  = '',
                elm = document.createElement('div');

            if( elm.style.animationName !== undefined ) { animation = true; }

            if( animation === false ) {
                for( var i = 0; i < domPrefixes.length; i++ ) {
                    if( elm.style[ domPrefixes[i] + 'AnimationName' ] !== undefined ) {
                        pfx = domPrefixes[ i ];
                        animationstring = pfx + 'Animation';
                        keyframeprefix = '-' + pfx.toLowerCase() + '-';
                        animation = true;
                        break;
                    }
                }
            }

            return animation;
        }

        ace.vars['animation'] = animationSupport();
        if( ace.vars['animation'] ) {
            //based on http://www.backalleycoder.com/2012/04/25/i-want-a-damnodeinserted/

            var animationCSS = "@keyframes nodeInserted{from{outline-color:#fff}to{outline-color:#000}}@-moz-keyframes nodeInserted{from{outline-color:#fff}to{outline-color:#000}}@-webkit-keyframes nodeInserted{from{outline-color:#fff}to{outline-color:#000}}@-ms-keyframes nodeInserted{from{outline-color:#fff}to{outline-color:#000}}@-o-keyframes nodeInserted{from{outline-color:#fff}to{outline-color:#000}}.ace-save-state{animation-duration:10ms;-o-animation-duration:10ms;-ms-animation-duration:10ms;-moz-animation-duration:10ms;-webkit-animation-duration:10ms;animation-delay:0s;-o-animation-delay:0s;-ms-animation-delay:0s;-moz-animation-delay:0s;-webkit-animation-delay:0s;animation-name:nodeInserted;-o-animation-name:nodeInserted;-ms-animation-name:nodeInserted;-moz-animation-name:nodeInserted;-webkit-animation-name:nodeInserted}";
            var animationNode = document.createElement('style');
            animationNode.innerHTML = animationCSS;
            document.head.appendChild(animationNode);

            var domInsertEvent = function(event) {
                var element = event.target;
                if( !element || !ace.hasClass(element, 'ace-save-state') ) return;

                ace.settings.loadState(element);
            }

            document.addEventListener('animationstart', domInsertEvent, false);
            document.addEventListener('MSAnimationStart', domInsertEvent, false);
            document.addEventListener('webkitAnimationStart', domInsertEvent, false);
        }
        else {
            //if animation events are not supported, wait for document ready event
            var documentReady = function() {
                var list = document.querySelectorAll('.ace-save-state');
                for(var i = 0 ; i < list.length ; i++) ace.settings.loadState(list[i]);
            }

            if(document.readyState == 'complete') documentReady();
            else if(document.addEventListener) document.addEventListener('DOMContentLoaded', documentReady, false);
            else if(document.attachEvent) document.attachEvent('onreadystatechange', function(){
                if (document.readyState == 'complete') documentReady();
            });
        }
    })();






//save/retrieve data using localStorage or cookie
//method == 1, use localStorage
//method == 2, use cookies
//method not specified, use localStorage if available, otherwise cookies
    ace.data_storage = function(method, undefined) {
        var prefix = 'ace_';

        var storage = null;
        var type = 0;

        if((method == 1 || method === undefined || method == 0) && 'localStorage' in window && window['localStorage'] !== null) {
            storage = ace.storage;
            type = 1;
        }
        else if(storage == null && (method == 2 || method === undefined) && 'cookie' in document && document['cookie'] !== null) {
            storage = ace.cookie;
            type = 2;
        }


        this.set = function(namespace, key, value, path, is_obj, undefined) {
            if(!storage) return;

            if(value === undefined) {//no namespace here?
                value = key;
                key = namespace;

                if(value == null) storage.remove(prefix+key)
                else {
                    if(type == 1)
                        storage.set(prefix+key, value)
                    else if(type == 2)
                        storage.set(prefix+key, value, ace.config.cookie_expiry, path || ace.config.cookie_path)
                }
            }
            else {
                if(type == 1) {//localStorage
                    if(value == null) storage.remove(prefix+namespace+'_'+key)
                    else {
                        if(is_obj && typeof value == 'object') {
                            value = JSON.stringify(value);
                        }
                        storage.set(prefix+namespace+'_'+key, value);
                    }
                }
                else if(type == 2) {//cookie
                    var val = storage.get(prefix+namespace);
                    var tmp = val ? JSON.parse(val) : {};

                    if(value == null) {
                        delete tmp[key];//remove
                        if(ace.sizeof(tmp) == 0) {//no other elements in this cookie, so delete it
                            storage.remove(prefix+namespace);
                            return;
                        }
                    }

                    else {
                        tmp[key] = value;
                    }

                    storage.set(prefix+namespace , JSON.stringify(tmp), ace.config.cookie_expiry, path || ace.config.cookie_path)
                }
            }
        }

        this.get = function(namespace, key, is_obj, undefined) {
            if(!storage) return null;

            if(key === undefined) {//no namespace here?
                key = namespace;
                return storage.get(prefix+key);
            }
            else {
                if(type == 1) {//localStorage
                    var value = storage.get(prefix+namespace+'_'+key);
                    if(is_obj && value) {
                        try { value = JSON.parse(value) } catch(e) {}
                    }
                    return value;
                }
                else if(type == 2) {//cookie
                    var val = storage.get(prefix+namespace);
                    var tmp = val ? JSON.parse(val) : {};
                    return key in tmp ? tmp[key] : null;
                }
            }
        }


        this.remove = function(namespace, key, undefined) {
            if(!storage) return;

            if(key === undefined) {
                key = namespace
                this.set(key, null);
            }
            else {
                this.set(namespace, key, null);
            }
        }
    }





//cookie storage
    ace.cookie = {
        // The following settingFunction are from Cookie.js class in TinyMCE, Moxiecode, used under LGPL.

        /**
         * Get a cookie.
         */
        get : function(name) {
            var cookie = document.cookie, e, p = name + "=", b;

            if ( !cookie )
                return;

            b = cookie.indexOf("; " + p);

            if ( b == -1 ) {
                b = cookie.indexOf(p);

                if ( b != 0 )
                    return null;

            } else {
                b += 2;
            }

            e = cookie.indexOf(";", b);

            if ( e == -1 )
                e = cookie.length;

            return decodeURIComponent( cookie.substring(b + p.length, e) );
        },

        /**
         * Set a cookie.
         *
         * The 'expires' arg can be either a JS Date() object set to the expiration date (back-compat)
         * or the number of seconds until expiration
         */
        set : function(name, value, expires, path, domain, secure) {
            var d = new Date();

            if ( typeof(expires) == 'object' && expires.toGMTString ) {
                expires = expires.toGMTString();
            } else if ( parseInt(expires, 10) ) {
                d.setTime( d.getTime() + ( parseInt(expires, 10) * 1000 ) ); // time must be in miliseconds
                expires = d.toGMTString();
            } else {
                expires = '';
            }

            document.cookie = name + "=" + encodeURIComponent(value) +
                ((expires) ? "; expires=" + expires : "") +
                ((path) ? "; path=" + path : "") +
                ((domain) ? "; domain=" + domain : "") +
                ((secure) ? "; secure" : "");
        },

        /**
         * Remove a cookie.
         *
         * This is done by setting it to an empty value and setting the expiration time in the past.
         */
        remove : function(name, path) {
            this.set(name, '', -1000, path);
        }
    };


//local storage
    ace.storage = {
        get: function(key) {
            return window['localStorage'].getItem(key);
        },
        set: function(key, value) {
            window['localStorage'].setItem(key , value);
        },
        remove: function(key) {
            window['localStorage'].removeItem(key);
        }
    };






//count the number of properties in an object
//useful for getting the number of elements in an associative array
    ace.sizeof = function(obj) {
        var size = 0;
        for(var key in obj) if(obj.hasOwnProperty(key)) size++;
        return size;
    }

//because jQuery may not be loaded at this stage, we use our own toggleClass
    ace.hasClass = function(elem, className) {	return (" " + elem.className + " ").indexOf(" " + className + " ") > -1; }

    ace.addClass = function(elem, className) {
        var parts = className.split(/\s+/);
        for(var p = 0; p < parts.length; p++) {
            if ( parts[p].length > 0 && !ace.hasClass(elem, parts[p]) ) {
                var currentClass = elem.className;
                elem.className = currentClass + (currentClass.length ? " " : "") + parts[p];
            }
        }
    }

    ace.removeClass = function(elem, className) {
        var parts = className.split(/\s+/);
        for(var p = 0; p < parts.length; p++) {
            if( parts[p].length > 0 ) ace.replaceClass(elem, parts[p]);
        }
        ace.replaceClass(elem, className);
    }

    ace.replaceClass = function(elem, className, newClass) {
        var classToRemove = new RegExp(("(^|\\s)" + className + "(\\s|$)"), "i");
        elem.className = elem.className.replace(classToRemove, function (match, p1, p2) {
            return newClass ? (p1 + newClass + p2) : " ";
        }).replace(/^\s+|\s+$/g, "");
    }


    ace.toggleClass = function(elem, className) {
        if(ace.hasClass(elem, className))
            ace.removeClass(elem, className);
        else ace.addClass(elem, className);
    }

    ace.isHTMlElement = function(elem) {
        return window.HTMLElement ? elem instanceof HTMLElement : ('nodeType' in elem ? elem.nodeType == 1 : false);
    }


    //data_storage instance used inside ace.settings etc
    ace.data = new ace.data_storage(ace.config.storage_method);

})();
