/**
 * This source file is part of DropDown.
 *
 * DropDown is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DropDown is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with DropDown. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://pierrerambaud.com
 */

(function($) {
    'use strict';
    var specialEvents = false,
    dropDownClass     = 'wrapper-dropdown',
    htmlDropDownClass = dropDownClass + '-select',
    $document         = $(document),
    keys              = {DOWN_ARROW: 40, UP_ARROW: 38, LEFT_ARROW: 37, RIGHT_ARROW: 39, ESCAPE: 27, ENTER: 13, SHIFT: 16 , CONTROL: 17};

    var DropDown = function DropDown(element, options) {
        if (element === undefined) {
            return;
        }

        var settings      = {},
        $this = this,
        isMultiple = element.prop('multiple'),
        currentId = null,
        selectId = null;
        settings = $.extend({
            'templateOption':         '<li><span></span></li>',
            'templateOptionGroup':    '<li class="optgroup"><span></span></li>',
            'templateOptionMultiple': '<li><label><input type="checkbox"></label></li>',
            'templateLayout':         '<div><span></span><ul class="dropdown"></ul></div>',
            'multipleLabel':          '-- select --',
            'class':                  ''
        }, options);

        /**
         * Initialize dropdown
         */
        var intialize = function initialize() {
            if (element.hasClass(dropDownClass)) {
                return;
            }

            create(element);
            initEvents(element);
        };

        /**
         * Create dropdown
         */
        var create = function create() {
            var bloc = $(settings.templateLayout);
            if (element.attr('id') === undefined) {
                element.attr('id', 'select-' + String.fromCharCode(Math.floor((Math.random()*20)+97))+Math.floor(Math.random()* 1000000));
            }

            selectId = element.attr('id');
            currentId = selectId + '-dropdown';

            bloc.attr('class', htmlDropDownClass + (settings.class !== undefined ? ' ' + settings.class : ''));
            bloc.attr('id', currentId);
            element.hide();
            element.children().each(function() {
                if ($(this).get(0).nodeName === 'OPTION') {
                    var opt = $(this);
                    createChild(bloc, opt);
                } else if ($(this).get(0).nodeName === 'OPTGROUP') {
                    var optgroup = $(this),
                    content = $(settings.templateOptionGroup);
                    content.children('span').text(optgroup.prop('label'));
                    bloc.children('ul').append(content);
                    optgroup.children().each(function() {
                        var opt = $(this);
                        createChild(bloc, opt);
                    });
                }
            });

            element.after(bloc);
            element.addClass(dropDownClass);
        };


        /**
         * Create dropdown child
         */
        var createChild = function createChild(container, opt) {
            var blocOpt,
            text = (opt.text() === '' ? ' ' : opt.text());
            if (isMultiple === true) {
                blocOpt = $(settings.templateOptionMultiple);
                blocOpt.children('label').append(opt.text());
                container.children('span').text(settings.multipleLabel);
            } else {
                blocOpt = $(settings.templateOption);
                blocOpt.children('span').append(text);
                if (opt.attr('style') !== undefined) {
                    blocOpt.attr('style', opt.attr('style'));
                }
            }

            if (opt.prop('selected') === true) {
                if (isMultiple === true) {
                    blocOpt.find('input').prop('checked', true);
                } else {
                    container.children('span').text(text);
                }
            }

            if (opt.prop('disabled') === true) {
                blocOpt.addClass('disabled');
            }

            container.children('ul').append(blocOpt);
        };

        /**
         * Initialize dropdown events
         */
        var initEvents = function initEvents() {
            var obj = $('#' + currentId);

            obj.on('click', function() {
                var obj = $(this);
                $('.' + htmlDropDownClass + '.active').not(obj).removeClass('active');
                obj.toggleClass('active');
                return false;
            });

            obj.find('input').on('click',function(event) {
                $(this).closest('li').click();
                event.stopImmediatePropagation();
            });

            obj.find('li:not(.optgroup)').on('click',function(event) {
                event.stopPropagation();
                event.preventDefault();
                var opt = $(this),
                parent = $('#' + currentId),
                selectedItem = element.find('option:eq(' + opt.index() + ')'),
                value = !selectedItem.prop('selected');

                if (opt.hasClass('disabled')) {
                    return false;
                }

                selectedItem.prop('selected', value);
                if (isMultiple === false) {
                    parent.toggleClass('active');
                    parent.children('span').text((opt.text() === '' ? ' ' : opt.text()));
                } else {
                    opt.find('input').prop('checked', value);
                }

                element.triggerHandler('click');
                element.triggerHandler('change');
            });
        };

        /**
         * Initialize special event
         * like keyup, and click on document
         */
        var initSpecialEvents = function initSpecialEvents() {
            if (specialEvents) {
                return;
            }

            $document.on('click', function() {
                $('.' + htmlDropDownClass + '.active').removeClass('active');
            });

            $document.on('keyup', function(event) {
                if ($('.' + htmlDropDownClass + '.active').length === 0) {
                    return;
                }

                var obj = $('.' + htmlDropDownClass + '.active');
                switch (event.keyCode) {
                    case keys.DOWN_ARROW:
                    case keys.RIGHT_ARROW:
                        event.preventDefault();
                        event.stopPropagation();
                            $('#' + obj.attr('id').replace('-dropdown', '')).dropDown('move', 1);
                        break;
                    case keys.UP_ARROW:
                    case keys.LEFT_ARROW:
                        event.preventDefault();
                        event.stopPropagation();
                            $('#' + obj.attr('id').replace('-dropdown', '')).dropDown('move', -1);
                        break;
                    case keys.ESCAPE:
                    case keys.ENTER:
                        event.preventDefault();
                        event.stopPropagation();
                        $('#' + obj.attr('id').replace('-dropdown', '')).dropDown('close');
                        break;
                    default:
                    break;
                }

            });

            specialEvents = true;
        };

        /**
         * Public methods
         */
        /**
         * Destroy dropdown
         */
        this.destroy = function destroy(keepData) {
            if(!keepData) {
                $.data(element[0], 'DropDown', null);
            }

            $('#' + currentId).remove();
            element.removeClass(dropDownClass);
            element.show();
        };

        /**
         * Add Event
         */
        this.on = function on(type, callback) {
             $('#' + currentId).on(type, callback);
        };

        /**
         * Remove Event
         */
        this.off = function off(type) {
             $('#' + currentId).off(type);
        };

        /**
         * Close
         */
        this.close = function close() {
            $('#' + currentId).removeClass('active');
        };

        /**
         * Open
         */
        this.open = function open() {
            $('#' + currentId).addClass('active');
        };

        /**
         * Refresh
         */
        this.refresh = function refresh() {
            $this.destroy(true);
            intialize();
        };

        /**
         * Move to index
         */
        this.move = function move(index) {
            var selectedIndex = element.prop('selectedIndex'),
            position = element.prop('selectedIndex') + index,
            items = element.find('option'),
            item = $(items.get(position)),
            input;

            if (position > items.length || position < 0) {
                return false;
            }

            if (item.parent('optgroup').prop('disabled') || item.prop('disabled')) {
                return false;
            }

            if (!isMultiple) {
                $('#' + currentId).children('span').text(item.text());
            } else {
                $('#' + currentId).find('li').find('input').prop('checked', false);
                input = $('#' + currentId).find('li:eq(' + position + ')').find('input');
                input.prop('checked', !input.prop('checked'));
            }

            element.prop('selectedIndex', position);
        };

        /**
         * Values methods, can get an set values
         */
        this.values = function values(values) {
        };

        intialize();
        initSpecialEvents();
    };

    $.fn.dropDown = function(method) {
        var argv = arguments;
        return this.each(function() {
            /**
             * Method calling logic
             */
            if ($.data(this, 'DropDown') && $.data(this, 'DropDown')[method] ) {
                return $.data(this, 'DropDown')[method].apply(this, Array.prototype.slice.call(argv, 1));
            } else if (typeof method === 'object' || ! method) {
                if (!$.data(this, 'DropDown')) {
                    var myDropDown = new DropDown($(this), method);
                    $(this).data('DropDown', myDropDown);
                }
            } else {
                $.error('Method ' +  method + ' does not exist on jQuery.dropDown');
            }
        });

    };
})(jQuery);
