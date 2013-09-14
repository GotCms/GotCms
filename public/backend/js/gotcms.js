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
    "use strict";
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
            var $slideDuration = 600;
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
                            var $tabs, $e, $tab_content;
                            $tabs = $('#tabs');
                            $e = $('<li class="list-item">' +
                                '<div class="list-handle">' +
                                    '<i class="glyphicon glyphicon-move"></i>' +
                                    '<div class="col-lg-10">' +
                                        '<div class="col-lg-6">' +
                                            '<input type="text" class="form-control" name="tabs[tab' + $data.id + '][name]" value="' + $name.val() + '">' +
                                        '</div>' +
                                        '<div class="col-lg-6">' +
                                            '<input type="text" class="form-control" name="tabs[tab' + $data.id + '][description]" value="' + $description.val() + '">' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="col-lg-1">' +
                                        '<button type="button" value="' + $data.id + '" class="delete-tab btn btn-danger">' +
                                            '<i class="glyphicon glyphicon-remove"></i> ' + Translator.translate('Delete') +
                                        '</button>' +
                                    '</div>' +
                                '</div>' +
                            '</li>');

                            $tabs.append($e);

                            $('.select-tab').append(new Option($name.val(),$data.id));

                            if($('#properties-tabs-content').html() !== null) {
                                $tab_content = $('#properties-tabs-content');
                                $tab_content.append('<div id="tabs-properties-' + $data.id + '"></div>');
                                $tab_content.children('ul').append('<li><a href="#tabs-properties-' + $data.id + '"> ' + $name.val() + '</a></li>');
                                $tab_content.tabs('refresh');
                                $('#tabs-properties-' + $data.id).append('<div class="sortable connected-sortable ui-helper-reset">');
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
                    $button.parent().remove();
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

                if($this.isEmpty($identifier.val()) || $this.isEmpty($name.val()) || $this.isEmpty($tab.val()) || $this.isEmpty($datatype.val())) {
                    $this.setHtmlMessage(Translator.translate('Please fill all fields'), 'error');
                } else {
                    $.post($addPropertyUrl, {
                        name:             $name.val(),
                        identifier:       $identifier.val(),
                        tab:              $tab.val(),
                        datatype:         $datatype.val(),
                        description:      $description.val(),
                        is_required:      $isRequired.val()
                    },
                    function($data) {
                        if($data.success === false) {
                            $this.setHtmlMessage($data.message, 'error');
                        } else {
                            var $c = new Template('<div><h3><a href="#secion#{id}">#{name} (#{identifier})</a></h3>' +
                            '<div class="form-horizontal">' +
                                '<div class="form-group">' +
                                    '<label class="required control-label col-lg-2" for="properties-name-#{tab}-#{id}">' + Translator.translate('Name') + '</label>' +
                                    '<div class="col-lg-10">' +
                                        '<input type="text" class="form-control" value="#{name}" id="properties-name-#{tab}-#{id}" name="properties[property#{id}][name]">' +
                                    '</div>' +
                                '</div>' +
                                '<div class="form-group">' +
                                    '<label class="required control-label col-lg-2" for="properties-identifier-#{tab}-#{id}">' + Translator.translate('Identifier') + '</label>' +
                                    '<div class="col-lg-10">' +
                                        '<input type="text" class="form-control" value="#{identifier}" id="properties-identifier-#{tab}-#{id}" name="properties[property#{id}][identifier]">' +
                                    '</div>' +
                                '</div>' +
                                '<div class="form-group">' +
                                    '<label class="required control-label col-lg-2" for="properties-datatype-#{tab}-#{id}">' + Translator.translate('Datatype') + '</label>' +
                                    '<div class="col-lg-10">' +
                                        '<select class="form-control select-datatype" id="properties-datatype-#{tab}-#{id}" name="properties[property#{id}][datatype]">' +
                                        '</select>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="form-group">' +
                                    '<label class="required control-label col-lg-2" for="properties-description-#{tab}-#{id}">' + Translator.translate('Description') + '</label>' +
                                    '<div class="col-lg-10">' +
                                        '<input type="text" class="form-control" value="#{description}" id="properties-description-#{tab}-#{id}" name="properties[property#{id}][description]">' +
                                    '</div>' +
                                '</div>' +
                                '<div class="form-group">' +
                                    '<label class="required control-label col-lg-2" for="properties-required-#{tab}-#{id}">' + Translator.translate('Required') + '</label>' +
                                    '<div class="col-lg-10">' +
                                        '<div class="input-checkbox">' +
                                            '<input type="checkbox" name="properties[property#{id}][required]" class="input-checkbox" id="properties-required-#{tab}-#{id}" value="1">' +
                                            '<label for="properties-required-#{tab}-#{id}"></label>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="clearfix">' +
                                    '<input class="property-tab-id" type="hidden" id="properties-tab-#{id}" name="properties[property#{id}][tab]" value="#{tab}">' +
                                    '<button type="button" value="#{id}" class="delete-property btn btn-danger">' +
                                        '<i class="glyphicon glyphicon-remove"></i> ' + Translator.translate('Delete') +
                                    '</button>' +
                                '</div>' +
                            '</div></div>');

                            $c = $($c.evaluate($data));

                            $('#tabs-properties-' + $tab.val()).children('div:first').append($c);
                            $('.connected-sortable').accordion(Gc.getOption('accordion-option'));
                            $('#properties-tab-' + $data.tab + '-' + $data.id).html($('#properties-tab').html()).val($data.tab);
                            $('#properties-datatype-' + $data.tab + '-' + $data.id).html($('#properties-datatype').html()).val($data.datatype);
                            $('#properties-required-' + $data.tab + '-' + $data.id).prop('checked', $isRequired.prop('checked'));

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
                var $tab_id = $('#import-tabs').val();
                //Ajax get tab and properties
                $.post($importTabUrl, {tab_id: $tab_id}, function($data) {
                    if($data.success === true) {
                        $.ajaxSetup({async:false});
                        var $tab = $data.tab,
                        $tab_name = $('#tabs-addname'),
                        $tab_description = $('#tabs-adddescription');

                        $tab_name.val($tab.name);
                        $tab_description.val($tab.description);

                        $('#tabs-add').click();
                        $tab_id = $('.select-tab').find('option:last').val();
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
                            $tab.val($tab_id);
                            $datatype.val($property.datatype);
                            $description.val($property.description);
                            $isRequired.val($property.is_required);
                            $('#property-add').click();
                            $name.val('');
                            $identifier.val('');
                            $tab.val('');
                            $datatype.val('');
                            $description.val('');
                            $isRequired.val('');
                        });

                        $tab_name.val('');
                        $tab_description.val('');
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

            var $tab_items = $('ul:first li', $tabs).droppable({
                accept: '.connected-sortable div',
                hoverClass: 'ui-state-hover',
                tolerance: 'pointer',
                drop: function(event, ui) {
                    var $item = $(this);
                    var $list = $( $item.find('a').attr('href'))
                        .find('.connected-sortable');
                    var $tab_id = $item.find('a').attr('href').replace('#tabs-properties-', '');

                    ui.draggable.hide('slow', function() {
                        $tabs.tabs('option', 'active', $tab_items.index($item));
                        $(this).appendTo($list).show('slow');
                        $(this).find('.property-tab-id').val($tab_id);
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

        sortableMenu: function($update_document_url)
        {
            $('#documents').find('ul').sortable({
                update: function() {
                    $.ajax({
                        url: $update_document_url,
                        type: 'post',
                        dataType: 'json',
                        data: {order: $(this).sortable('toArray').join()}
                    });
                },
                distance: 10
            });
        },

        initDocumentMenu: function($document_id, $update_document_url)
        {
            var $this = this,
            initialDocument = $('#document_' + $document_id).closest('li'),
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
                'core' : { 'initially_open' : [ initialOpen ] }
            }).bind('refresh.jstree', function() {
                $this.sortableMenu($update_document_url);
            }).bind('loaded.jstree', function() {
                $this.sortableMenu($update_document_url);

                $.contextMenu({
                    selector: '#browser a',
                    items: {
                        'new': {name: Translator.translate('New'), icon: 'add'},
                        'edit': {name: Translator.translate('Edit'), icon: 'edit'},
                        'delete': {name: Translator.translate('Delete'), icon: 'delete'},
                        'sep1': '---------',
                        'cut': {name: Translator.translate('Cut'), icon: 'cut'},
                        'copy': {name: Translator.translate('Copy'), icon: 'copy'},
                        'paste': {name: Translator.translate('Paste'), icon: 'paste', disabled: true},
                        'sep2': '---------',
                        'refresh': {name: Translator.translate('Refresh'), icon: 'refresh'},
                        'quit': {name: Translator.translate('Quit'), icon: 'quit'}
                    },
                    callback: function($action, $options) {
                        var $element = $(this),
                        $routes,
                        $url,
                        $id,
                        $display_copy_form;

                        if($action !== 'refresh' && $action !== 'new' && $action !== 'paste' && $element.parent('li').attr('id') === 'documents') {
                            return true;
                        }

                        $routes = $this.getOption('routes');
                        $url = $routes[$action];
                        $id = 0;
                        if($element.attr('id') !== undefined) {
                            $id = $element.attr('id');
                        }

                        $url = $url.replace('itemId', $id);
                        $display_copy_form = true;

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
                                $display_copy_form = false;
                            case 'cut':
                                $this.setOption('lastAction', $action);
                                $options.items.paste.disabled = false;
                            case 'paste':
                                if($this.getOption('lastAction') === 'copy' && $display_copy_form === true) {
                                    $this.showCopyForm($url, $action, $options);
                                    return true;
                                }

                                $.ajax({
                                    url: $url,
                                    dataType: 'json',
                                    data: {},
                                    success: function(data) {
                                        if(data.success === true) {
                                            if($action === 'copy' || $action === 'cut') {
                                                $options.items.paste.disabled = false;
                                            }

                                            if($action === 'paste' && $this.getOption('lastAction') === 'cut') {
                                                $options.items.paste.disabled = true;

                                                $this.refreshTreeview($routes.refresh.replace('itemId', 0), 0);
                                            }
                                        }
                                    }
                                });
                                return true;

                            case 'delete':
                                $this.showDeleteDialog($url, $element.parent());
                                return true;

                            case 'quit':
                            default:
                                return true;
                        }

                        document.location.href = $url;
                    }
                });
            });
        },

        refreshTreeview: function($url, $document_id)
        {
            var $browser = $('#browser');
            $.ajax({
                url: $url,
                data: {},
                success: function(data) {
                    if($document_id === 0) {
                        $('#documents').children('ul').remove();
                        $('#documents').append(data.treeview);
                    } else {
                        $('#' + $document_id).next('ul').remove();
                        $('#' + $document_id).after(data.treeview);
                    }

                    $browser.jstree('refresh');
                }
            });
        },

        showDeleteDialog: function($url, $element_to_remove)
        {
            $('#dialog').dialog({
                bgiframe :  false,
                resizable : false,
                modal :     true,
                title:      '<div class="widget-header widget-header-small"><h4><i class="glyphicon glyphicon-warning-sign"></i>' + Translator.translate('Delete element') + '</h4></div>',
                overlay     : {
                    backgroundColor: '#000',
                    opacity: 0.5
                },
                buttons: [
                    {
                        'text':  Translator.translate('Cancel'),
                        'class': "btn btn-warning",
                        'click': function() {
                            $(this).dialog('close');

                            return false;
                        }
                    },
                    {
                        'text':  Translator.translate('Confirm'),
                        'class': "btn btn-danger btn-mini",
                        'click': function() {
                            $.get($url, function(data) {
                                if(data.success === true) {
                                    $element_to_remove.remove();
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
            $template = '<div id="copy-dialog-form" title="' + Translator.translate('Copy document') + '">' +
                '<p class="validateTips">' + Translator.translate('All form fields are required.') + '</p>' +
                '<fieldset>' +
                    '<div>' +
                        '<label for="name">' + Translator.translate('Name') + '</label>' +
                        '<input type="text" name="name" id="copy-name" class="form-control ui-widget-content ui-corner-all" />' +
                    '</div>' +
                    '<div>' +
                        '<label for="email">' + Translator.translate('Url key') + '</label>' +
                        '<input type="text" name="url-key" id="copy-url-key" value="" class="form-control ui-widget-content ui-corner-all" />' +
                    '</div>' +
               '</fieldset>' +
            '</div>';

            var $buttons = {};
            $buttons[Translator.translate('Copy')] = function() {
                var $copy_name = $('#copy-name'),
                $copy_url_key = $('#copy-url-key');
                if($this.isEmpty($copy_name.val()) || $this.isEmpty($copy_url_key.val())) {
                    return false;
                }

                $.ajax({
                    url: $url,
                    dataType: 'json',
                    data: {name:$copy_name.val(),url_key:$copy_url_key.val()},
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
            };

            $buttons[Translator.translate('Cancel')] = function() {
                $(this).dialog('close');
            };

            $('#copy-dialog-form').remove();

            $($template).dialog({
                modal: true,
                buttons: $buttons
            });
        },

        initTranslator: function()
        {
            var $idx = 1,
            $template = '<tr>' +
                '<td>' +
                    '<div>' +
                        '<input type="text" class="form-control" name="destination[#{id}]" size="73">' +
                    '</div>' +
                '</td>' +
                '<td>' +
                    '<div>' +
                        '<select class="form-control" name="locale[#{id}]">';
                            $.each(this.getOption('locale'), function(key, value)
                            {
                                $template += '<option value="' + key + '">' + value + '</option>';
                            });

                        $template += '</select>' +
                    '</div>' +
                '</td>' +
                '<td><span class="button-add add-translate">' + Translator.translate('Add') + '</span></td>' +
            '</tr>';

            $document.on('click', '.add-translate', function() {
                var $t = new Template($template),
                $table_trad = $('#table-trad');

                $table_trad.find('.add-translate').removeClass('add-translate').addClass('delete-translate').html(Translator.translate('Delete'));
                $table_trad.children('tbody').append($t.evaluate({id: $idx}));
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
            $('#upload-link').on('click', function() {
                $('#form-content').toggleClass('hide');
                return false;
            });
        },

        initElFinder: function($connector_url, $language)
        {
            $('#elfinder').elfinder({
                lang: $language,
                url : $connector_url
            }).elfinder('instance');
        },

        initDashBoard: function($object, $update_url)
        {
            var $this = this,
            $sortable,
            $not_connected,
            $dashboard_nb_update;

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
                    $dashboard_nb_update = 0;
                },
                receive: function() {
                    $('.widget-column').sortable('refresh');
                },
                update: function(event, ui) {
                    var $string = $(this).sortable('toArray').join(),
                    $found = false;
                    if($this.isEmpty($object[$(this).attr('id')])) {
                        $not_connected = false;
                    } else {
                        $.each($object, function($key, $value) {
                            if(!$this.isEmpty($object[$key]) && $.inArray(ui.item.attr('id'), $value.split(',')) !== -1) {
                                $found = true;
                            }
                        });

                        if($found === false) {
                            $not_connected = true;
                        } else {
                            $not_connected = $object[$(this).attr('id')].length === $string.length;
                        }
                    }

                    $object[$(this).attr('id')] = $string;
                    $dashboard_nb_update++;
                    if($dashboard_nb_update === 2 || $not_connected) {
                        $.ajax({
                            url: $update_url,
                            type: 'post',
                            dataType: 'json',
                            data: $object
                        });
                    }
                }
            });

            if(!this.isEmpty($object)) {
                $.each($object, function(sortable_id, elements) {
                    $(elements.split(',')).each(function (i, id) {
                        $("#" + id).appendTo($('#' + sortable_id));
                    });
                });
            }

            $('.dashboard-close').on('click', function() {
                $.ajax({
                    url: $update_url,
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
                if(!(event.which === 83 && event.ctrlKey) && !(event.which === 19)) {
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

        initUpdate: function($confirm_text)
        {
            $('#update-form').on('submit', function() {
                if(!confirm($confirm_text)) {
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
            var $form = $('.simple-form'),
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
        }
    };
})(jQuery);
