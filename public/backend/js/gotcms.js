/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

var Gc = (function($)
{
    'use strict';
    var $document = $(document),
    $window = $(window);

    $document.ready(function() {
        Gc.initialize();
        Gc.initializeMenu();
        Gc.notification();
    });

    return {
        initialize: function()
        {
            this._options = new Hash();
        },

        initializeMenu: function() {
            var $slideDuration = 600,
                $menuToggler = $('#menu-toggler');

            $(document).on('click', '.menu-toggle', function() {
                var $parent = $(this).parent();
                if (!$parent.hasClass('open')) {
                    $(this).next('.submenu')
                        .stop(true, true)
                        .fadeIn({ duration: $slideDuration, queue: false })
                        .slideDown($slideDuration, function() {
                            $(this).parent().toggleClass('open');
                        });
                } else {
                    $(this).next('.submenu')
                        .stop(true, true)
                        .fadeOut({ duration: $slideDuration, queue: false })
                        .slideUp($slideDuration, function() {
                            $(this).parent().toggleClass('open');
                        });
                }
            });

            $(window).on('scroll', function() {
                var $btn = $('.btn-scroll-up');
                if ($(window).scrollTop() !== 0 && $btn.is(':not(:visible)')) {
                    $btn.fadeIn();
                } else if ($(window).scrollTop() === 0) {
                    $btn.fadeOut();
                }
            });

            /*
            $(document).on('click', ':not(#menu-toggler), :not(#sidebar), :not(.navbar-toggle)', function() {
                $('#sidebar').toggleClass('show');
            });
            */

            $menuToggler.on('click', function() {
                $('#sidebar').toggleClass('show');
            });

            $('.btn-scroll-up').on('click', function() {
                $('html,body').animate({ scrollTop: 0 }, 'slow');
                return false;
            });
        },

        setOption: function($key, $value)
        {
            this._options.set($key, $value);
        },

        getOption: function($key)
        {
            return this._options.get($key);
        },

        setHtmlMessage: function($message, $class)
        {
            var $template = new Template('<div class="notification #{type}">' +
                '<a href="#" class="close">' +
                    '<img src="/backend/images/close-small.png" title="Close this notification" alt="Close">' +
                '</a>' +
                '<div>#{message}</div>' +
            '</div>');

            $('.html-message').html($template.evaluate({message: $message, type: $class}));
        },

        isEmpty: function($element)
        {
            if($element === undefined) {
                return true;
            }

            if(typeof $element === 'object') {
                return $.isEmptyObject($element);
            } else {
                return $.trim($element) === '';
            }
        },

        initDocumentType: function($addTabUrl, $addPropertyUrl, $deleteTabUrl, $deletePropertyUrl, $importTabUrl)
        {
            var $this = this,
                $originalPosition;
            $('.tabs').tabs();

            /**
             * TABS
             */
            $('#tabs').sortable({
                placeholder: 'ui-state-highlight',
                distance: 10,
                start: function(event, ui) {
                    $originalPosition = ui.item.index();
                    $(this).find('.ui-state-highlight').height(ui.item.height() - 4);
                },
                stop: function(event, ui) {
                    var $tabsNav = $('#properties-tabs-content > .ui-tabs-nav').find('.row'),
                    $newPosition = ui.item.index(),
                    $row = $tabsNav.eq($originalPosition).detach();

                    if ($newPosition === $tabsNav.length) {
                        $tabsNav.eq($newPosition - 1).after($row);
                    } else {
                        $tabsNav.eq($newPosition).before($row);
                    }
                }
            });

            $('#tabs-add').on('click', function() {
                var $name, $description;
                $name = $('#tabs-addname');
                $description = $('#tabs-adddescription');
                if($this.isEmpty($name.val()) || $this.isEmpty($description.val())) {
                    $this.setHtmlMessage(Translator.translate('Please fill all fields'), 'error');
                } else {
                    $.post($addTabUrl,
                    {name: $name.val(), description: $description.val()},
                    function($data) {
                        if($data.message !== undefined) {
                            $this.setHtmlMessage($data.message, 'error');
                        } else {
                            var $tabs, $e, $tabContent;
                            $tabs = $('#tabs');
                            $e = $('<li class="list-item"> \
    <div class="list-handle"> \
        <i class="glyphicon glyphicon-move"></i> \
        <div class="col-lg-10"> \
            <div class="col-lg-6"> \
                <input type="text" class="form-control" \
                name="tabs[tab' + $data.id + '][name]" value="' + $name.val() + '"> \
            </div> \
            <div class="col-lg-6"> \
                <input type="text" class="form-control" \
                name="tabs[tab' + $data.id + '][description]" value="' + $description.val() + '"> \
            </div> \
        </div> \
        <div class="col-lg-1"> \
            <button type="button" value="' + $data.id + '" class="delete-tab btn btn-danger"> \
                <i class="glyphicon glyphicon-remove"></i> ' + Translator.translate('Delete') + '\
            </button> \
        </div> \
    </div> \
</li>');

                            $tabs.append($e);

                            $('.select-tab').append(new Option($name.val(),$data.id));

                            if($('#properties-tabs-content').html() !== null) {
                                $tabContent = $('#properties-tabs-content');
                                $tabContent.append('<div id="tabs-properties-' + $data.id + '"></div>');
                                $tabContent.children('ul').append('<li> \
                                    <a href="#tabs-properties-' + $data.id + '"> ' + $name.val() + '</a></li>');
                                $tabContent.tabs('refresh');
                                $('#tabs-properties-' + $data.id)
                                    .append('<div class="sortable connected-sortable ui-helper-reset">');
                            }
                        }
                    });
                }
            });

            $(document).on('click', '.delete-tab', function() {
                var $button = $(this);
                $.post($deleteTabUrl, {tab: $button.val()}, function($data) {
                    $('.select-tab').find('option[value="' + $button.val() + '"]').remove();
                    var $tabs = $('#properties-tabs-content');
                    $button.closest('.list-item').remove();
                    var $tab = $tabs.find('a[href="#tabs-properties-' + $button.val() + '"]').parent();
                    var $index = $('li', $tabs).index($tab);

                    $tabs.find('.ui-tabs-nav li:eq(' + $index + ')').remove();
                    $('#tabs-properties-' + $button.val()).remove();
                    // Refresh the tabs widget
                    $tabs.tabs('refresh');
                    $this.setHtmlMessage($data.message, 'success');
                });
            });

            /**
             * Properties
             */

            $('#display-property').on('click', function() {
                $(this).next().toggleClass('hide');
                $(this).find('.glyphicon').toggleClass('glyphicon-minus');
                $(this).find('.glyphicon').toggleClass('glyphicon-plus');
            });

            var $tabs = $('#properties-tabs-content').tabs({idPrefix:'tabs-properties', panelTemplate: '<div></div>'});

            $this.setOption('accordion-option', {
                header: 'div > h3',
                collapsible: true,
                autoHeight: false,
                active: -1
            });

            $this.refreshProperties($tabs);

            $('#property-add').on('click', function() {
                var $name = $('#properties-name'),
                $identifier = $('#properties-identifier'),
                $tab = $('#properties-tab'),
                $datatype = $('#properties-datatype'),
                $description = $('#properties-description'),
                $isRequired = $('#properties-required');

                if($this.isEmpty($identifier.val()) ||
                    $this.isEmpty($name.val()) ||
                    $this.isEmpty($tab.val()) ||
                    $this.isEmpty($datatype.val())) {
                    $this.setHtmlMessage(Translator.translate('Please fill all fields'), 'error');
                } else {
                    $.post($addPropertyUrl, {
                        'name':             $name.val(),
                        'identifier':       $identifier.val(),
                        'tab':              $tab.val(),
                        'datatype':         $datatype.val(),
                        'description':      $description.val(),
                        'isRequired':       $isRequired.val()
                    },
                    function($data) {
                        if($data.success === false) {
                            $this.setHtmlMessage($data.message, 'error');
                        } else {
                            var $c = new Template('<div><h3><a href="#secion#{id}">#{name} (#{identifier})</a></h3> \
<div class="form-horizontal"> \
    <div class="form-group"> \
        <label class="required control-label col-lg-2" for="properties-name-#{tab}-#{id}"> \
        ' + Translator.translate('Name') + '</label> \
        <div class="col-lg-10"> \
            <input type="text" class="form-control" value="#{name}" id="properties-name-#{tab}-#{id}" \
            name="properties[property#{id}][name]"> \
        </div> \
    </div> \
    <div class="form-group"> \
        <label class="required control-label col-lg-2" for="properties-identifier-#{tab}-#{id}"> \
        ' + Translator.translate('Identifier') + '</label> \
        <div class="col-lg-10"> \
            <input type="text" class="form-control" value="#{identifier}" id="properties-identifier-#{tab}-#{id}" \
            name="properties[property#{id}][identifier]"> \
        </div> \
    </div> \
    <div class="form-group"> \
        <label class="required control-label col-lg-2" for="properties-datatype-#{tab}-#{id}"> \
        ' + Translator.translate('Datatype') + '</label> \
        <div class="col-lg-10"> \
            <select class="form-control select-datatype" id="properties-datatype-#{tab}-#{id}" \
            name="properties[property#{id}][datatype]"> \
            </select> \
        </div> \
    </div> \
    <div class="form-group"> \
        <label class="required control-label col-lg-2" for="properties-description-#{tab}-#{id}"> \
        ' + Translator.translate('Description') + '</label> \
        <div class="col-lg-10"> \
            <input type="text" class="form-control" value="#{description}" id="properties-description-#{tab}-#{id}" \
            name="properties[property#{id}][description]"> \
        </div> \
    </div> \
    <div class="form-group"> \
        <label class="required control-label col-lg-2" for="properties-required-#{tab}-#{id}"> \
        ' + Translator.translate('Required') + '</label> \
        <div class="col-lg-10"> \
            <div class="input-checkbox"> \
                <input type="checkbox" name="properties[property#{id}][required]" class="input-checkbox" \
            id="properties-required-#{tab}-#{id}" value="1"> \
                <label for="properties-required-#{tab}-#{id}"></label> \
            </div> \
        </div> \
    </div> \
    <div class="clearfix"> \
        <input class="property-tab-id" type="hidden" id="properties-tab-#{id}" \
            name="properties[property#{id}][tab]" value="#{tab}"> \
        <button type="button" value="#{id}" class="delete-property btn btn-danger"> \
            <i class="glyphicon glyphicon-remove"></i> ' + Translator.translate('Delete') + ' \
        </button> \
    </div> \
</div></div>');

                            $c = $($c.evaluate($data));

                            $('#tabs-properties-' + $tab.val()).children('div:first').append($c);
                            $('.connected-sortable').accordion(Gc.getOption('accordion-option'));
                            $('#properties-tab-' + $data.tab + '-' + $data.id)
                                .html($('#properties-tab')
                                .html()).val($data.tab);
                            $('#properties-datatype-' + $data.tab + '-' + $data.id)
                                .html($('#properties-datatype')
                                .html()).val($data.datatype);
                            $('#properties-required-' + $data.tab + '-' + $data.id)
                                .prop('checked', $isRequired.prop('checked'));

                            $this.refreshProperties($tabs);
                        }
                    });
                }
            });

            $(document).on('click', '.delete-property', function() {
                var $button = $(this);
                $.post($deletePropertyUrl, {
                    property:    $button.val()
                },
                function($data) {
                    if($data.success === true) {
                        $button.parent().parent().parent().remove();
                        $('.connected-sortable').accordion(Gc.getOption('accordion-option'));
                        $this.setHtmlMessage($data.message, 'success');
                    } else {
                        $this.setHtmlMessage($data.message, 'error');
                    }
                });
            });

            $('#import-tab-button').on('click', function() {
                var $tabId = $('#import-tabs').val();
                //Ajax get tab and properties
                $.post($importTabUrl, {'tab_id': $tabId}, function($data) {
                    if($data.success === true) {
                        $.ajaxSetup({async:false});
                        var $tab = $data.tab,
                        $tabName = $('#tabs-addname'),
                        $tabDescription = $('#tabs-adddescription');

                        $tabName.val($tab.name);
                        $tabDescription.val($tab.description);

                        $('#tabs-add').click();
                        $tabId = $('.select-tab').find('option:last').val();
                        $.each($tab.properties, function(key, $property)
                        {
                            var $name = $('#properties-name'),
                            $identifier = $('#properties-identifier'),
                            $tab = $('#properties-tab'),
                            $datatype = $('#properties-datatype'),
                            $description = $('#properties-description'),
                            $isRequired = $('#properties-required');

                            $name.val($property.name);
                            $identifier.val($property.identifier);
                            $tab.val($tabId);
                            $datatype.val($property.datatype);
                            $description.val($property.description);
                            $isRequired.prop('checked', $property.isRequired);
                            $('#property-add').click();
                            $name.val('');
                            $identifier.val('');
                            $tab.val('');
                            $datatype.val('');
                            $description.val('');
                            $isRequired.prop('checked', false);
                        });

                        $tabName.val('');
                        $tabDescription.val('');
                        $.ajaxSetup({async:true});
                    }
                });

                return false;
            });
        },

        refreshProperties: function($tabs)
        {
            if($('.connected-sortable').hasClass('ui-accordion')) {
                $('.connected-sortable').accordion('destroy');
            }

            $('.connected-sortable')
                .accordion(this.getOption('accordion-option'))
                .sortable({
                    placeholder: 'ui-state-highlight',
                    handle: 'h3',
                    distance: 10
                });

            var $tabItems = $('ul:first li', $tabs).droppable({
                accept: '.connected-sortable div',
                hoverClass: 'ui-state-hover',
                tolerance: 'pointer',
                drop: function(event, ui) {
                    var $item = $(this);
                    var $list = $( $item.find('a').attr('href'))
                        .find('.connected-sortable');
                    var $tabId = $item.find('a').attr('href').replace('#tabs-properties-', '');

                    ui.draggable.hide('slow', function() {
                        $tabs.tabs('option', 'active', $tabItems.index($item));
                        $(this).appendTo($list).show('slow');
                        $(this).find('.property-tab-id').val($tabId);
                        $(this).attr('style', '');
                        $('.connected-sortable').accordion('refresh');
                    });
                },
                stop: function( event, ui ) {
                    // IE doesn't register the blur when sorting
                    // so trigger focusout handlers to remove .ui-state-focus
                    ui.item.children('h3').triggerHandler('focusout');
                }
            });
        },

        sortableMenu: function($updateDocumentUrl)
        {
            $('#documents').find('ul').sortable({
                update: function() {
                    $.ajax({
                        url: $updateDocumentUrl,
                        type: 'post',
                        dataType: 'json',
                        data: {order: $(this).sortable('toArray').join()}
                    });
                },
                distance: 10
            });
        },

        initDocumentMenu: function($documentId, $updateDocumentUrl)
        {
            var $this = this,
            initialDocument = $('#document_' + $documentId).closest('li'),
            initialOpen;

            if (initialDocument.length > 0) {
                if (initialDocument.children('ul').length > 0) {
                    initialOpen = initialDocument.prop('id');
                } else {
                    initialOpen = initialDocument.parent().closest('li').prop('id');
                }
            }

            if (initialOpen === undefined) {
                initialOpen = 'documents';
            }

            $('#browser').jstree({
                'plugins' : ['themes','html_data'],
                'core' : { 'initially_open' : [ initialOpen ] },
                'themes' : {
                    'theme' : 'default',
                    'url' : '/backend/js/vendor/themes/default/style.css',
                }
            }).bind('refresh.jstree', function() {
                $this.sortableMenu($updateDocumentUrl);
            }).bind('loaded.jstree', function() {
                $this.sortableMenu($updateDocumentUrl);

                $.contextMenu({
                    selector: '#browser a',
                    build: function($trigger) {
                        var $items = {
                            'new': {name: Translator.translate('New'), icon: 'add'},
                            'edit': {name: Translator.translate('Edit'), icon: 'edit'},
                            'delete': {name: Translator.translate('Delete'), icon: 'delete'},
                            'publish': {name: Translator.translate('Publish'), icon: 'publish'},
                            'unpublish': {name: Translator.translate('Unpublish'), icon: 'unpublish'},
                            'sep1': '---------',
                            'cut': {name: Translator.translate('Cut'), icon: 'cut'},
                            'copy': {name: Translator.translate('Copy'), icon: 'copy'},
                            'paste': {name: Translator.translate('Paste'), icon: 'paste',
                                      disabled: ($this.getOption('lastAction') === 'paste' ||
                                                 $this.getOption('lastAction') === undefined)},
                            'sep2': '---------',
                            'refresh': {name: Translator.translate('Refresh'), icon: 'refresh'},
                            'quit': {name: Translator.translate('Quit'), icon: 'quit'}
                        };


                        if ($trigger.hasClass('not-published')) {
                            delete($items.unpublish);
                        } else {
                            delete($items.publish);
                        }

                        return {items: $items};
                    },
                    callback: function($action, $options) {
                        var $element = $(this),
                        $routes,
                        $url,
                        $id,
                        $displayCopyForm;

                        if($action !== 'refresh' &&
                            $action !== 'new' &&
                            $action !== 'paste' &&
                            $element.parent('li').attr('id') === 'documents') {
                            return true;
                        }

                        $routes = $this.getOption('routes');
                        $url = $routes[$action];
                        $id = 0;
                        if($element.attr('id') !== undefined) {
                            $id = $element.attr('id');
                        }

                        $url = $url.replace('itemId', $id);
                        $displayCopyForm = true;

                        switch($action) {
                        case 'refresh':
                            $this.refreshTreeview($url, $id);
                            return true;
                        case 'new':
                            if(!$this.isEmpty($id)) {
                                $url += '/parent/' + $id;
                            }
                            break;
                        case 'edit':
                            break;
                        case 'copy':
                            $displayCopyForm = false;
                            /* falls through */
                        case 'cut':
                            $this.setOption('lastAction', $action);
                            /* falls through */
                        case 'paste':
                            if($this.getOption('lastAction') === 'copy' && $displayCopyForm === true) {
                                $this.showCopyForm($url, $action, $options);
                                return true;
                            }

                            $.ajax({
                                url: $url,
                                dataType: 'json',
                                data: {},
                                success: function(data) {
                                    if(data.success === true) {
                                        if($action === 'paste' && $this.getOption('lastAction') === 'cut') {
                                            $this.refreshTreeview($routes.refresh.replace('itemId', 0), 0);
                                        }
                                    }
                                }
                            });
                            return true;

                        case 'publish':
                        case 'unpublish':
                            $.ajax({
                                url: $url,
                                dataType: 'json',
                                data: {},
                                success: function (data) {
                                    if (data.success === true) {
                                        if ($action === 'publish') {
                                            $element.removeClass('not-published');
                                        } else {
                                            $element.addClass('not-published');
                                        }
                                    }
                                }
                            });

                            return true;
                        case 'delete':
                            $this.showDeleteDialog($url, $element.parent());
                            return true;

                        case 'quit':
                        /* falls through */
                        default:
                            return true;
                        }

                        document.location.href = $url;
                    }
                });
            });
        },

        refreshTreeview: function($url, $documentId)
        {
            var $browser = $('#browser');
            $.ajax({
                url: $url,
                data: {},
                success: function(data) {
                    if($documentId === 0) {
                        $('#documents').children('ul').remove();
                        $('#documents').append(data.treeview);
                    } else {
                        $('#' + $documentId).next('ul').remove();
                        $('#' + $documentId).after(data.treeview);
                    }

                    $browser.jstree('refresh');
                }
            });
        },

        showDeleteDialog: function($url, $elementToRemove)
        {
            var $string = 'These items will be permanently deleted and cannot be recovered. Are you sure?',
            $template = '<div id="dialog" title=""> \
                    <p>' + Translator.translate($string) + '</p> \
                </div>';

            $($template).dialog({
                bgiframe :  false,
                resizable : false,
                modal :     true,
                title:      '<div class="widget-header widget-header-small"> \
                    <h4><i class="glyphicon glyphicon-warning-sign"></i> \
                    ' + Translator.translate('Delete element') + '</h4></div>',
                overlay     : {
                    backgroundColor: '#000',
                    opacity: 0.5
                },
                buttons: [
                    {
                        'text':  Translator.translate('Cancel'),
                        'class': 'btn btn-warning',
                        'click': function() {
                            $(this).dialog('close');

                            return false;
                        }
                    },
                    {
                        'text':  Translator.translate('Confirm'),
                        'class': 'btn btn-danger btn-mini',
                        'click': function() {
                            $.get($url, function(data) {
                                if(data.success === true) {
                                    $elementToRemove.remove();
                                }
                            });

                            $(this).dialog('close');

                            return true;
                        }
                    }
                ]
            });
        },

        showCopyForm: function($url, $action, $options)
        {
            var $this = this,
            $template = '<div id="copy-dialog-form" title=""> \
                <p class="validateTips">' + Translator.translate('All form fields are required.') + '</p> \
                <fieldset> \
                    <div> \
                        <label for="name">' + Translator.translate('Name') + '</label> \
                        <input type="text" name="name" id="copy-name" \
                        class="form-control ui-widget-content ui-corner-all" /> \
                    </div> \
                    <div> \
                        <label for="email">' + Translator.translate('Url key') + '</label> \
                        <input type="text" name="url-key" id="copy-url-key" value="" \
                        class="form-control ui-widget-content ui-corner-all" /> \
                    </div> \
               </fieldset> \
            </div>';

            var $buttons = {};
            $buttons[Translator.translate('Copy')] = {
                'text':  Translator.translate('Copy'),
                'class': 'btn btn-danger btn-mini',
                'click': function() {
                    var $copyName = $('#copy-name'),
                    $copyUrlKey = $('#copy-url-key');
                    if($this.isEmpty($copyName.val()) || $this.isEmpty($copyUrlKey.val())) {
                        return false;
                    }

                    $.ajax({
                        url: $url,
                        dataType: 'json',
                        data: {'name': $copyName.val(), 'url_key': $copyUrlKey.val()},
                        success: function(data) {
                            if(data.success === true) {
                                if($action === 'paste' && $this.getOption('lastAction') === 'cut') {
                                    $options.items.paste.disabled = true;
                                }

                                $this.refreshTreeview($this.getOption('routes').refresh.replace('itemId', 0), 0);
                            }
                        }
                    });

                    $(this).dialog('close');
                }
            };

            $buttons[Translator.translate('Cancel')] = {
                'text':  Translator.translate('Cancel'),
                'class': 'btn btn-warning',
                'click': function() {
                    $(this).dialog('close');

                    return false;
                }
            };

            $('#copy-dialog-form').remove();

            $($template).dialog({
                modal: true,
                title:      '<div class="widget-header widget-header-small"> \
                    <h4><i class="glyphicon glyphicon-warning-sign"></i> \
                    ' + Translator.translate('Copy document') + '</h4></div>',
                buttons: $buttons
            });
        },

        initTranslator: function()
        {
            var $idx = 1,
            $template = '<tr> \
                <td> \
                    <div> \
                        <input type="text" class="form-control" name="destination[#{id}]" size="73"> \
                    </div> \
                </td> \
                <td> \
                    <div> \
                        <select class="form-control" name="locale[#{id}]">';
            $.each(this.getOption('locale'), function(key, value) {
                $template += '<option value="' + key + '">' + value + '</option>';
            });

            $template += '</select> \
                    </div> \
                </td> \
                <td> \
                    <span class="btn btn-default add-translate"> \
                        <i class="glyphicon glyphicon-plus"></i>&nbsp; \
                        ' + Translator.translate('Add') + ' \
                    </span> \
                </td> \
            </tr>';

            $document.on('click', '.add-translate', function() {
                var $t = new Template($template),
                $tableTrad = $('#table-trad');

                $tableTrad.find('.add-translate')
                    .removeClass('add-translate')
                    .addClass('delete-translate')
                    .html('<i class="glyphicon glyphicon-minus"></i> ' + Translator.translate('Delete'));
                $tableTrad.children('tbody').append($t.evaluate({id: $idx}));
                $idx++;
            });

            $document.on('click', '.delete-translate', function() {
                $(this).parent().parent('tr').remove();
            });
        },

        initTranslationList: function()
        {
            $('#table-translation-edit > tbody > tr').on('click', function(e) {
                if($.inArray(e.target.type, ['text', 'select-one']) !== -1) {
                    return false;
                }

                $(this).find('div').toggleClass('hide');
                var $input = $(e.target).parent().find('input:first');
                $input.focus();
                var $tmp = $input.val();
                $input.val('');
                $input.val($tmp);
                $(this).find('td').each(function() {
                    var $selector = $(this).find('div:last');
                    $selector.prev('div').html($selector.children().val());
                });
            });
        },

        notification: function()
        {
            $(document).on('click', '.notification .close', function() {
                $(this).parent().fadeOut(function() {
                    $(this).remove();
                });

                return false;
            });

            setTimeout(function() {
                $('.notification').remove();
            }, 6000);
        },

        initCodeMirror: function($content)
        {
            this.initUploadLink();

            CodeMirror.fromTextArea(document.getElementById($content), {
                lineNumbers: true,
                matchBrackets: true,
                mode: 'application/x-httpd-php',
                indentUnit: 4,
                indentWithTabs: true,
                enterMode: 'keep',
                tabMode: 'spaces'
            });
        },

        initUploadLink: function()
        {
            var $buttons = {},
            $string = 'These items will be permanently updated and cannot be recovered. Are you sure?',
            $template = '<div id="dialog" title=""> \
                    <p>' + Translator.translate($string) + '</p> \
                </div>';

            $buttons[Translator.translate('Confirm')] = {
                'text':  Translator.translate('Confirm'),
                'class': 'btn btn-danger btn-mini',
                'click': function() {
                    document.location.href = $('.btn-info.update-content').prop('href');

                    $(this).dialog('close');
                }
            };

            $buttons[Translator.translate('Cancel')] = {
                'text':  Translator.translate('Cancel'),
                'class': 'btn btn-warning',
                'click': function() {
                    $(this).dialog('close');

                    return false;
                }
            };

            $('#upload-link').on('click', function() {
                $('#form-content').toggleClass('hide');
                return false;
            });
        },

        initElFinder: function($connectorUrl, $language)
        {
            $('#elfinder').elfinder({
                lang: $language,
                url : $connectorUrl
            }).elfinder('instance');
        },

        initDashBoard: function($object, $updateUrl)
        {
            var $this = this,
            $sortable,
            $notConnected,
            $dashboardNbUpdate;

            $sortable = $('.widget-column').sortable({
                connectWith: '.widget-column',
                placeholder: 'sortable-placeholder',
                helper: 'clone',
                handle: '.widget-header',
                tolerance: 'pointer',
                opacity: 0.4,
                distance: 10,
                forcePlaceholderSize: true,
                start : function() {
                    $dashboardNbUpdate = 0;
                },
                receive: function() {
                    $('.widget-column').sortable('refresh');
                },
                update: function(event, ui) {
                    var $string = $(this).sortable('toArray').join(),
                    $found = false;
                    if($this.isEmpty($object[$(this).attr('id')])) {
                        $notConnected = false;
                    } else {
                        $.each($object, function($key, $value) {
                            if(!$this.isEmpty($object[$key]) &&
                                $.inArray(ui.item.attr('id'), $value.split(',')) !== -1) {
                                $found = true;
                            }
                        });

                        if($found === false) {
                            $notConnected = true;
                        } else {
                            $notConnected = $object[$(this).attr('id')].length === $string.length;
                        }
                    }

                    $object[$(this).attr('id')] = $string;
                    $dashboardNbUpdate++;
                    if($dashboardNbUpdate === 2 || $notConnected) {
                        $.ajax({
                            url: $updateUrl,
                            type: 'post',
                            dataType: 'json',
                            data: $object
                        });
                    }
                }
            });

            if(!this.isEmpty($object)) {
                $.each($object, function(sortableId, elements) {
                    $(elements.split(',')).each(function (i, id) {
                        $('#' + id).appendTo($('#' + sortableId));
                    });
                });
            }

            $('.dashboard-close').on('click', function() {
                $.ajax({
                    url: $updateUrl,
                    type: 'post',
                    dataType: 'json',
                    data: {dashboard: true}
                });

                $('#dashboard').remove();
                return false;
            });
        },

        initTableList: function()
        {
            var $this = this;
            $('.clickable > tbody').find('td').on('click', function(e) {
                if(e.target.tagName !== 'IMG' && e.target.tagName !== 'A') {
                    $(this).parent().find('.edit-line')[0].click();
                }
            });

            $('.delete-line').on('click', function() {
                $this.showDeleteDialog($(this).attr('href'), $(this).parent().parent());
                return false;
            });
        },

        saveCommand: function()
        {
            $document.on('keydown', function(event) {
                if((event.which === 83 && event.ctrlKey) === false && event.which !== 19) {
                    return true;
                }

                event.preventDefault();
                $('#input-save').click();
            });
        },

        installModule: function()
        {
            $('#modules').find('div').hide();
            $('#module').on('change', function() {
                $('#modules').find('div').hide();
                $('#' + $(this).val()).show();
            });
        },

        initUpdate: function($confirmText)
        {
            $('#update-form').on('submit', function() {
                if(!confirm($confirmText)) {
                    return false;
                }
            });
        },

        initRoles: function()
        {
            $('#role-list').find('h3,h2').on('click', function() {
                $(this).closest('dt').nextUntil('dt').find('input[type="checkbox"]').prop('checked', true);
            });
        },

        checkDataChanged: function()
        {
            var $form = $('form.relative'),
            $originalData = $form.serialize();

            $document.on('click', '#input-save', function() {
                $(this).closest('form').data('formBeingSaved', true);
            });

            $window.on('beforeunload', function() {
                if ($('#input-save').closest('form').data('formBeingSaved') !== true) {
                    if ($form.serialize() !== $originalData) {
                        return true;
                    }
                }
            });
        },

        keepAlive: function($url) {
            setInterval(function () {
                $.get($url);
            }, 60 * 1000);
        }
    };
})(jQuery);
