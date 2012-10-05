/* Author:
Pierre Rambaud
*/

var Gc = (function($)
{
    var $document = $(document),
    $window = $(window);

    $document.ready(function()
    {
        Gc.initialize();
        Gc.notification();
    });

    return {
        initialize: function()
        {
            this._options = new Hash();
        },

        setOption: function($key, $value)
        {
            this._options.set($key, $value);
        },

        getOption: function($key)
        {
            return this._options.get($key);
        },

        setHtmlMessage: function($message)
        {
            $('.html-message').html($message);
        },

        isEmpty: function($element)
        {
            if($element == undefined)
            {
                return true;
            }

            if(typeof $element == 'object')
            {
                return $.isEmptyObject($element)
            }
            else
            {
                return $.trim($element) == '';
            }
        },

        initDocumentType: function($addTabUrl, $addPropertyUrl, $deleteTabUrl, $deletePropertyUrl, $importTabUrl)
        {
            var $this = this;
            $('.tabs').tabs();

            /**
             * TABS
             */
            $('#tabs').sortable({
                placeholder: 'ui-state-highlight',
                distance: 10
            });

            $('#tabs-add').on('click', function()
            {
                $name = $('#tabs-addname');
                $description = $('#tabs-adddescription');
                if($this.isEmpty($name.val()) || $this.isEmpty($description.val()))
                {
                    $this.setHtmlMessage(Translator.translate('Please fill all fields'));
                }
                else
                {
                    $.post($addTabUrl, {name:$name.val(),description:$description.val()}
                    , function($data, textStatus, jqXHR)
                    {
                        if($data.message != undefined)
                        {
                            $this.setHtmlMessage($data.message);
                        }
                        else
                        {
                            $tabs = $('#tabs');
                            $e = '<li>'
                                + '<div class="hide floatL">'
                                + '<input type="text" name="tabs[tab'+$data.id+'][name]" value="'+$name.val()+'">'
                                + '<input type="text" name="tabs[tab'+$data.id+'][description]" value="'+$description.val()+'">'
                                + '</div>'
                                + '<div class="floatL">'
                                + '<span>'+$name.val()+'</span> <span>'+$description.val()+'</span>'
                                + '</div>'
                                + '<button type="button" value="'+$data.id+'" class="delete-tab floatR input-submit">'+Translator.translate('Delete')+'</button>'
                                + '</li>';
                            $tabs.append($e);

                            $('.select-tab').append(new Option($name.val(),$data.id));

                            if($('#properties-tabs-content').html() != null)
                            {
                                $('#properties-tabs-content').tabs('add', '#tabs-properties-'+$data.id, $name.val());
                                $('#tabs-properties-'+$data.id).append('<div class="sortable connected-sortable ui-helper-reset">');
                            }
                        }
                    })
                }
            });

            $(document).on('click', '#tabs > li', function(event)
            {
                if(event.target.type == 'text')
                {
                    return false;
                }

                $(this).find('div').toggleClass('hide').height('auto');
            });

            $(document).on('click', '.delete-tab', function()
            {
                $button = $(this);
                $.post($deleteTabUrl, {tab: $button.val()}, function($data)
                {
                    $('.select-tab').find('option[value="'+$button.val()+'"]').remove();
                    $tabs = $('#properties-tabs-content');
                    $button.parent().remove();
                    $tab = $tabs.find('a[href="#tabs-properties-'+$button.val()+'"]').parent();
                    var $index = $('li', $tabs).index($tab);
                    $tabs.tabs('remove', $index );
                    $this.setHtmlMessage($data.message);
                });
            });

            /**
             * Properties
             */
            var $tabs = $('#properties-tabs-content').tabs({idPrefix:'tabs-properties', panelTemplate: '<div></div>'});

            $this.setOption('accordion-option', {
                header: 'div > h3',
                collapsible: true,
                autoHeight: false,
                active: -1,
            });

            $this.refreshProperties($tabs);

            $('#property-add').on('click', function()
            {
                $name = $('#properties-name');
                $identifier = $('#properties-identifier');
                $tab = $('#properties-tab');
                $datatype = $('#properties-datatype');
                $description = $('#properties-description');
                $isRequired = $('#properties-required');

                if($this.isEmpty($identifier.val()) || $this.isEmpty($name.val()) || $this.isEmpty($tab.val()) || $this.isEmpty($datatype.val()) || $this.isEmpty($description.val()))
                {
                    $this.setHtmlMessage(Translator.translate('Please fill all fields'));
                }
                else
                {
                    $.post($addPropertyUrl,
                    {
                        name:               $name.val()
                        , identifier:       $identifier.val()
                        , tab:              $tab.val()
                        , datatype:         $datatype.val()
                        , description:      $description.val()
                        , is_required:      $isRequired.val()
                    },
                    function($data)
                    {
                        if($data.success == false)
                        {
                            $this.setHtmlMessage($data.message);
                        }
                        else
                        {
                            $this.setHtmlMessage('');
                            $c = new Template('<div><h3><a href="#secion#{id}">#{name} (#{identifier})</a></h3>'
                                + '<dl>'
                                +'<dt id="name-label-#{tab}-#{id}">'
                                    +'<label class="optional" for="properties-name-#{tab}-#{id}">'+Translator.translate('Name')+'</label>'
                                +'</dt>'
                                +'<dd id="name-element-#{tab}-#{id}">'
                                    +'<input type="text" value="#{name}" id="properties-name-#{tab}-#{id}" name="properties[property#{id}][name]">'
                                +'</dd>'
                                +'<dt id="identifier-label-#{tab}-#{id}">'
                                    +'<label class="optional" for="properties-identifier-#{tab}-#{id}">'+Translator.translate('Identifier')+'</label>'
                                +'</dt>'
                                +'<dd id="identifier-element-#{tab}-#{id}">'
                                    +'<input type="text" value="#{identifier}" id="properties-identifier-#{tab}-#{id}" name="properties[property#{id}][identifier]">'
                                +'</dd>'
                                +'<dt id="datatype-label-#{tab}-#{id}">'
                                    +'<label class="optional" for="properties-datatype-#{tab}-#{id}">'+Translator.translate('Datatype')+'</label>'
                                +'</dt>'
                                +'<dd id="datatype-element-#{tab}-#{id}">'
                                    +'<select class="select-datatype" id="properties-datatype-#{tab}-#{id}" name="properties[property#{id}][datatype]">'
                                    +'</select>'
                                +'</dd>'
                                +'<dt id="description-label-#{tab}-#{id}">'
                                    +'<label class="optional" for="properties-description-#{tab}-#{id}">'+Translator.translate('Description')+'</label>'
                                +'</dt>'
                                +'<dd id="description-element-#{tab}-#{id}">'
                                    +'<input type="text" value="#{description}" id="properties-description-#{tab}-#{id}" name="properties[property#{id}][description]">'
                                +'</dd>'
                                +'<dt id="required-label-#{tab}-#{id}">'
                                    +'<label class="optional" for="properties-required-#{tab}-#{id}">'+Translator.translate('Required')+'</label>'
                                +'</dt>'
                                +'<dd id="required-element-#{tab}-#{id}">'
                                    +'<input type="checkbox" value="1" id="properties-required-#{tab}-#{id}" name="properties[property#{id}][required]">'
                                +'</dd>'
                                +'<dd id="required-element-#{tab}-#{id}">'
                                    +'<input class="property-tab-id" type="hidden" id="properties-tab-#{id}" name="properties[property#{id}][tab]" value="#{tab}">'
                                    +'<button type="button" value="#{id}" class="delete-property input-submit">'+Translator.translate('Delete')+'</button>'
                                +'</dd>'
                            +'</dl></div>');

                            $c = $c.evaluate($data);

                            $('#tabs-properties-'+$tab.val()).children('div:first').append($c);
                            $('.connected-sortable').accordion(Gc.getOption('accordion-option'))
                            $('#properties-tab-'+$data.tab+'-'+$data.id).html($('#properties-tab').html()).val($data.tab);
                            $('#properties-datatype-'+$data.tab+'-'+$data.id).html($('#properties-datatype').html()).val($data.datatype);
                            $('#properties-required-'+$data.tab+'-'+$data.id).prop('checked', $isRequired.prop('checked'));

                            $this.refreshProperties($tabs);
                        }
                    });
                }
            });

            $(document).on('click', '.delete-property', function()
            {
                $button = $(this);
                $.post($deletePropertyUrl,
                {
                    property:    $button.val()
                },
                function($data)
                {
                    if($data.success == true)
                    {
                        $button.parent().parent().parent().remove();
                        $('.connected-sortable').accordion(Gc.getOption('accordion-option'))
                    }

                    $this.setHtmlMessage($data.message);
                });
            });

            $('#import-tab-button').on('click', function()
            {
                $tab_id = $('#import-tabs').val();
                //Ajax get tab and properties
                $.post($importTabUrl, {tab_id: $tab_id}, function($data)
                {
                    if($data.success == true)
                    {
                        $.ajaxSetup({async:false});
                        $tab = $data.tab;
                        $tab_name = $('#tabs-addname');
                        $tab_description = $('#tabs-adddescription');
                        $tab_name.val($tab.name);
                        $tab_description.val($tab.description);

                        $('#tabs-add').click();
                        $tab_id = $('.select-tab').find('option:last').val();
                        $.each($tab.properties, function(key, $property)
                        {
                            $name = $('#properties-name');
                            $identifier = $('#properties-identifier');
                            $tab = $('#properties-tab');
                            $datatype = $('#properties-datatype');
                            $description = $('#properties-description');
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
            $('.connected-sortable')
                .accordion('destroy')
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
                drop: function( event, ui )
                {
                    var $item = $(this);
                    var $list = $( $item.find('a').attr('href'))
                        .find('.connected-sortable');
                    var $tab_id = $item.find('a').attr('href').replace('#tabs-properties-', '');

                    ui.draggable.hide('slow', function() {
                        $tabs.tabs('select', $tab_items.index( $item ) );
                        $(this).appendTo( $list ).show('slow');
                        $(this).find('.property-tab-id').val($tab_id);
                    });
                },
                stop: function( event, ui ) {
                    // IE doesn't register the blur when sorting
                    // so trigger focusout handlers to remove .ui-state-focus
                    ui.item.children('h3').triggerHandler('focusout');
                }
            });
        },

        initDocumentMenu: function($document_id, $update_document_url)
        {
            $this = this;
            $('#browser').jstree({
                'plugins' : ['themes','html_data'],
                'core' : { 'initially_open' : [ $('#document_' + $document_id).parent().parent('li').prop('id') ] }
            }).bind('loaded.jstree', function (event, data)
            {
                $has_cut_action = false;
                $('#documents').find('ul').sortable({
                    update: function(e, ui)
                    {
                        $data = {order: $(this).sortable('toArray').join()};
                        $.ajax({
                            url: $update_document_url,
                            type: 'post',
                            dataType: 'json',
                            data: $data
                        });
                    },
                    distance: 10
                });

                $.contextMenu(
                {
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
                    callback: function($action, $options)
                    {
                        $element = $(this);
                        if($action != 'refresh' && $action != 'new' && $action != 'paste' && $element.parent('li').attr('id') == 'documents')
                        {
                            return true;
                        }

                        $routes = $this.getOption('routes');
                        $url = $routes[$action];
                        $id = 0;
                        if($element.attr('id') != undefined)
                        {
                            $id = $element.attr('id');
                        }

                        $url = $url.replace('itemId', $id);
                        $display_copy_form = true;

                        switch($action)
                        {
                            case 'refresh':
                                $this.refreshTreeview($url, $id);
                                return true;
                            break;
                            case 'new':
                                if(!$this.isEmpty($id))
                                {
                                    $url += '/parent/'+$id;
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
                                if($this.getOption('lastAction') == 'copy' && $display_copy_form == true)
                                {
                                    $this.showCopyForm($url, $action, $options);
                                    return true;
                                }

                                $.ajax({
                                    url: $url,
                                    dataType: 'json',
                                    data: {},
                                    success: function(data)
                                    {
                                        if(data.success == true)
                                        {
                                            if($action == 'copy' || $action == 'cut')
                                            {
                                                $options.items.paste.disabled = false;
                                            }

                                            if($action == 'paste' && $this.getOption('lastAction') == 'cut')
                                            {
                                                $options.items.paste.disabled = true;

                                                $this.refreshTreeview($routes['refresh'].replace('itemId', 0), 0);
                                            }
                                        }
                                    }
                                });
                                return true;
                            break;

                            case 'delete':
                                $this.showDialogConfirm($url);
                                return true;
                            break;

                            case 'quit':
                            default:
                                return true;
                            break;
                        }

                        document.location.href = $url;
                    }
                });
            });
        },

        refreshTreeview: function($url, $document_id)
        {
            $this = this;
            $browser = $('#browser');
            $.ajax({
                url: $url,
                data: {},
                success: function(data)
                {
                    if($document_id == 0)
                    {
                        $('#documents').children('ul').remove();
                        $('#documents').append(data.treeview);
                    }
                    else
                    {
                        $('#'+$document_id).next('ul').remove()
                        $('#'+$document_id).after(data.treeview);
                    }

                    $browser.jstree('refresh');
                }
            });
        },

        showDialogConfirm: function($url)
        {
            var $buttons = {};
            $buttons[Translator.translate('Confirm')] = function()
            {
                document.location.href = $url;
                $(this).dialog('close');

                return true;
            };
            $buttons[Translator.translate('Cancel')] = function()
            {
                $(this).dialog('close');

                return false;
            };

            $('#dialog').attr('title', Translator.translate('Delete element')).dialog(
            {
                bgiframe        : false,
                resizable       : false,
                modal           : true,
                overlay         :
                {
                    backgroundColor: '#000',
                    opacity: 0.5
                },
                buttons         :$buttons
            });
        },

        showCopyForm: function($url, $action, $options)
        {
            $this = this;
            $template = '<div title="'+Translator.translate('Copy document')+'">'
                +'<p class="validateTips">'+Translator.translate('All form fields are required.')+'</p>'
                +'<fieldset>'
                    +'<div>'
                        +'<label for="name">'+Translator.translate('Name')+'</label>'
                        +'<input type="text" name="name" id="copy-name" class="text ui-widget-content ui-corner-all" />'
                    +'</div>'
                    +'<div>'
                        +'<label for="email">'+Translator.translate('Url key')+'</label>'
                        +'<input type="text" name="url-key" id="copy-url-key" value="" class="text ui-widget-content ui-corner-all" />'
                    +'</div>'
               +'</fieldset>'
            +'</div>';

            var $buttons = {};
            $buttons[Translator.translate('Copy')] = function()
            {
                $copy_name = $('#copy-name');
                $copy_url_key = $('#copy-url-key');
                if($this.isEmpty($copy_name.val()) || $this.isEmpty($copy_url_key.val()))
                {
                    return false;
                }

                $.ajax({
                    url: $url,
                    dataType: 'json',
                    data: {name:$copy_name.val(),url_key:$copy_url_key.val()},
                    success: function(data)
                    {
                        if(data.success == true)
                        {
                            if($action == 'paste' && $this.getOption('lastAction') == 'cut')
                            {
                                $options.items.paste.disabled = true;
                            }

                            $this.refreshTreeview($routes['refresh'].replace('itemId', 0), 0);
                        }
                    }
                });

                $(this).dialog('close');
            };

            $buttons[Translator.translate('Cancel')] = function()
            {
                $(this).dialog('close');
            };

            $($template).dialog({
                modal: true,
                buttons: $buttons
            });
        },

        initTranslator: function()
        {
            $idx = 1;
            $template = '<tr>' +
                '<td>' +
                    '<div>' +
                        '<input type="text" name="destination[#{id}]" size="73">' +
                    '</div>' +
                '</td>' +
                '<td>' +
                    '<div>' +
                        '<select name="locale[#{id}]">';
                            $.each(this.getOption('locale'), function(key, value)
                            {
                                $template += '<option value="'+key+'">'+value+'</option>';
                            });

                        $template += '</select>' +
                    '</div>' +
                '</td>' +
                '<td><span class="button-add add-translate">'+Translator.translate('Add')+'</span></td>' +
            '</tr>';

            $document.on('click', '.add-translate', function()
            {
                $t = new Template($template);
                $table_trad = $('#table-trad');
                $table_trad.find('.add-translate').removeClass('add-translate').addClass('delete-translate').html(Translator.translate('Delete'));
                $table_trad.children('tbody').append($t.evaluate({id: $idx}));
                $idx++;
            });

            $document.on('click', '.delete-translate', function()
            {
                $(this).parent().parent('tr').remove();
            });
        },

        initTraslationList: function(e)
        {
            $('#table-translation-edit > tbody > tr').on('click', function(e)
            {
                if($.inArray(e.target.type, ['text', 'select-one']) !=-1)
                {
                    return false;
                }

                $(this).find('div').toggleClass('hide');
                $input = $(e.target).parent().find('input:first');
                $input.focus();
                $tmp = $input.val();
                $input.val('');
                $input.val($tmp);
                $(this).find('td').each(function()
                {
                    $selector = $(this).find('div:last');
                    $selector.prev('div').html($selector.children().val());
                });
            });
        },

        notification: function()
        {
            $(document).on('click', '.notification .close', function()
            {
                $(this).parent().fadeOut(function()
                {
                    $(this).remove();
                });

                return false;
            });

            setTimeout(function()
            {
                $('.notification').remove();
            }, 6000);
        },

        initCodeMirror: function($content)
        {
            $('#upload-link').on('click', function()
            {
                $('#form-content').toggle();
                return false;
            });

            var myCodeMirror = CodeMirror.fromTextArea(document.getElementById($content), {
                lineNumbers: true,
                matchBrackets: true,
                mode: 'application/x-httpd-php',
                indentUnit: 4,
                indentWithTabs: true,
                enterMode: 'keep',
                tabMode: 'spaces'
            });
        },

        initElFinder: function($connector_url, $language)
        {
            var elf = $('#elfinder').elfinder({
                lang: $language,
                url : $connector_url
            }).elfinder('instance');
        },

        initDashBoard: function($object, $update_url)
        {
            $this = this;
            $sortable = $('.widget-column').sortable({
                connectWith: '.widget-column',
                placeholder: 'sortable-placeholder',
                helper: 'clone',
                handle: 'h3',
                tolerance: 'pointer',
                opacity: 0.4,
                distance: 10,
                forcePlaceholderSize: true,
                start : function(e, ui)
                {
                    $dashboardNbUpdate = 0;
                },
                receive: function(e, ui)
                {
                    $('.widget-column').sortable('sortupdate');
                },
                update: function(e, ui)
                {
                    $string = $(this).sortable('toArray').join();
                    if($this.isEmpty($object[$(this).attr('id')]))
                    {
                        $not_connected = false;
                    }
                    else
                    {
                        $not_connected = $object[$(this).attr('id')].length == $string.length;
                    }

                    $object[$(this).attr('id')] = $string;
                    $dashboardNbUpdate++;
                    if($dashboardNbUpdate == 2 || $not_connected)
                    {
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
                $.each($object, function(sortable_id, elements)
                {
                    $(elements.split(',')).each(function (i, id) {
                        $("#" + id).appendTo($('#'+sortable_id));
                    });
                });
            }

            $('.dashboard-close').on('click', function()
            {
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
            $this = this;
            $('.table-list > tbody').find('td').on('click', function(e)
            {
                if(e.target.tagName != 'IMG' && e.target.tagName != 'A')
                {
                    $(this).parent().find('.edit-line')[0].click();
                }
            });

            $('.delete-line').on('click', function()
            {
                $this.showDialogConfirm($(this).attr('href'));
                return false;
            })
        },

        saveCommand: function()
        {
            $(window).on('keypress', function(event)
            {
                if(!(event.which == 115 && event.ctrlKey) && !(event.which == 19))
                {
                    return true;
                }

                event.preventDefault();
                $('#input-save').click();
            });
        },

        installModule: function()
        {
            $('#modules').find('div').hide();
            $('#module').on('change', function()
            {
                $('#modules').find('div').hide();
                $('#' + $(this).val()).show();
            });
        }
    };
})(jQuery);

