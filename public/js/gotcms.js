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
    });

    return {
        initialize: function()
        {
            this._options = new Array();
        },

        setOption: function($key, $value)
        {
            this._options[$key] = $value;
        },

        getOption: function($key)
        {
            return this._options[$key];
        },

        setHtmlMessage: function($message)
        {
            $('.html-message').html($message);
        },

        translate: function($message)
        {
            return $message;
            //@TODO set translator
        },

        isEmpty: function($element)
        {
            if($element == undefined)
            {
                return true;
            }

            if(typeof $element == "object")
            {
                return $.isEmptyObject($element)
            }
            else
            {
                return $element.trim() == '';
            }
        },

        developmentForm: function($addTabUrl, $addPropertyUrl, $deleteTabUrl, $deletePropertyUrl)
        {
            var $this = this;
            $('.tabs').tabs();

            //tabs
            $('#tabs-add').click(function()
            {
                $name = $('#tabs-addname');
                $description = $('#tabs-adddescription');
                if($this.isEmpty($name.val()) || $this.isEmpty($description.val()))
                {
                    $this.setHtmlMessage($this.translate('Please fill all fields'));
                }
                else
                {
                    if($('#tabs').html() == null)
                    {
                        $(this).after('<ul class="sortable" id="tabs"></ul>');
                        $('#tabs').sortable({placeholder: "ui-state-highlight"});
                    }

                    $.post($addTabUrl, {name:$name.val(),description:$description.val()}
                    , function($data, textStatus, jqXHR){
                            if($data.message != undefined)
                            {
                                $this.setHtmlMessage($data.message);
                            }
                            else
                            {
                                $tabs = $('#tabs');
                                $e = '<li>'
                                    +'<input type="hidden" name="tabs[name]['+$data.id+']" value="'+$name.val()+'" />'
                                    +'<input type="hidden" name="tabs[description]['+$data.id+']" value="'+$description.val()+'" />'
                                    +'<span>'+$name.val()+'</span><span>'+$description.val()+'</span>'
                                    +'<button type="button" value="'+$data.id+'" class="delete-tab">delete</button>'
                                    +'</li>';
                                $tabs.append($e);
                                $('.select-tab').append(new Option($name.val(),$data.id));


                                if($('#properties-tabs-content').html() != null)
                                {
                                    $('#properties-tabs-content').tabs( "add", '#tabs-properties-'+$data.id, $name.val());
                                }
                                else
                                {
                                    //add tab if does not exist
                                    $('#property_add').after('<div id="properties-tabs-content" class="tabs">'
                                            +'<ul>'
                                                +'<li><a href="#tabs-properties-1">'+$name.val()+'</a></li>'
                                            +'</ul>'
                                            +'<div id="tabs-properties-1">'
                                                +'<ul></ul>'
                                            +'</div>'
                                            +'</div>');
                                    $('#properties-tabs-content').tabs({idPrefix:'tabs-properties', panelTemplate: '<div><ul></ul></div>'});
                                }
                            }
                        }
                    )
                }
            });

            $('.delete-tab').live('click', function()
            {
                $button = $(this);
                $.post($deleteTabUrl, {
                            tab:    $button.val()
                        },
                        function($data)
                        {
                            $('.select-tab').find('option[value="'+$button.val()+'"]').remove();
                            $tabs = $('#properties-tabs-content');
                            $button.parent().remove();
                            $tab = $tabs.find('a[href="#tabs-properties-'+$button.val()+'"]').parent();
                            var $index = $('li', $tabs).index($tab);
                            $tabs.tabs( "remove", $index );
                            $this.setHtmlMessage($data.message);
                        }
                );
            });
            //views

            //properties
            $('#property_add').click(function()
            {
                $name = $('#properties-name');
                $identifier = $('#properties-identifier');
                $tab = $('#properties-tab');
                $datatype = $('#properties-datatype');
                $description = $('#properties-description');
                $isRequired = $('#properties-required');

                if($this.isEmpty($identifier.val()) || $this.isEmpty($name.val()) || $this.isEmpty($tab.val()) || $this.isEmpty($datatype.val()) || $this.isEmpty($description.val()))
                {
                    $this.setHtmlMessage($this.translate('Please fill all fields'));
                }
                else
                {
                    $.post($addPropertyUrl, {
                            name:            $name.val()
                            , identifier:    $identifier.val()
                            , tab:            $tab.val()
                            , datatype:    $datatype.val()
                            , description:    $description.val()
                            , is_required:    $isRequired.val()
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
                                $c = '<dl>'
                                        +'<dt id="name-label-#{tab}-#{id}">'
                                            +'<label class="optional" for="properties-name-#{tab}-#{id}">Name</label>'
                                        +'</dt>'
                                        +'<dd id="name-element-#{tab}-#{id}">'
                                            +'<input type="text" value="#{name}" id="properties-name-#{tab}-#{id}" name="properties[name][]">'
                                        +'</dd>'
                                        +'<dt id="identifier-label-#{tab}-#{id}">'
                                            +'<label class="optional" for="properties-identifier-#{tab}-#{id}">Identifier</label>'
                                        +'</dt>'
                                        +'<dd id="identifier-element-#{tab}-#{id}">'
                                            +'<input type="text" value="#{identifier}" id="properties-identifier-#{tab}-#{id}" name="properties[identifier][]">'
                                        +'</dd>'
                                        +'<dt id="tabs-label-#{tab}-#{id}">'
                                            +'<label class="optional" for="properties-tab-#{tab}-#{id}">Tab</label>'
                                        +'</dt>'
                                        +'<dd id="tabs-element-#{tab}-#{id}">'
                                        +'<select class="select-tab" id="properties-tab-#{tab}-#{id}" name="properties[tab][]">'
                                        +'</select>'
                                        +'</dd>'
                                        +'<dt id="datatype-label-#{tab}-#{id}">'
                                            +'<label class="optional" for="properties-datatype-#{tab}-#{id}">datatype</label>'
                                        +'</dt>'
                                        +'<dd id="datatype-element-#{tab}-#{id}">'
                                            +'<select class="select-datatype" id="properties-datatype-#{tab}-#{id}" name="properties[datatype][]">'
                                            +'</select>'
                                        +'</dd>'
                                        +'<dt id="description-label-#{tab}-#{id}">'
                                            +'<label class="optional" for="properties-description-#{tab}-#{id}">Description</label>'
                                        +'</dt>'
                                        +'<dd id="description-element-#{tab}-#{id}">'
                                            +'<input type="text" value="#{description}" id="properties-description-#{tab}-#{id}" name="properties[description][]">'
                                        +'</dd>'
                                        +'<dt id="required-label-#{tab}-#{id}">'
                                            +'<label class="optional" for="properties-required-#{tab}-#{id}">Required</label>'
                                        +'</dt>'
                                        +'<dd id="required-element-#{tab}-#{id}">'
                                            +'<input type="checkbox" value="1" id="properties-required-#{tab}-#{id}" name="properties[required][]">'
                                        +'</dd>'
                                        +'<dt id="delete-#{tab}-#{id}">'
                                            +'<label class="optional" for="properties-delete-#{tab}-#{id}">Required</label>'
                                        +'</dt>'
                                        +'<dd id="required-element-#{tab}-#{id}">'
                                            +'<button type="button" value="'+$data.id+'" class="delete-property">delete</button>'
                                        +'</dd>'
                                    +'</dl>';

                                jQuery.each($data, function($key, $value)
                                {
                                    $regexp = new RegExp('#{'+$key+'}', 'ig');
                                    $c = $c.replace($regexp, $value);
                                });

                                $('#tabs-properties-'+$tab.val()).find('ul').append('<li>'+$c+'</li>');
                                $('#properties-tab-'+$data.tab+'-'+$data.id).html($('#properties-tab').html()).val($data.tab);
                                $('#properties-datatype-'+$data.tab+'-'+$data.id).html($('#properties-datatype').html()).val($data.datatype);
                                $('#properties-required-'+$data.tab+'-'+$data.id).attr('checked', $isRequired.val());
                            }
                        }
                    );
                }
            });

            $('.delete-property').live('click', function()
            {
                $button = $(this);
                $.post($deletePropertyUrl, {
                            property:    $button.val()
                        },
                        function($data)
                        {
                            if($data.success == true)
                            {
                                $button.parent().parent().parent().remove();
                            }

                            $this.setHtmlMessage($data.message);
                        }
                );
            });
        },

        initDocumentMenu: function($routes)
        {
            $this = this;
            $("#browser").treeview();
            $("#browser a").contextMenu(
                {
                    menu: 'contextMenu'
                },

                function($action, $element, $position)
                {
                    $routes = $this._options.get('routes');
                    $url = $routes[$action];

                    if($element.attr('rel') != undefined)
                    {
                        $id = $element.attr('rel');
                        $url = $url.replace('itemId', $id);
                    }

                    switch($action){
                        case 'new':
                        case 'edit':
                        break;

                        case 'copy':
                        case 'cut':
                        break;

                        case 'paste':
                        break;

                        case 'delete':
                            $this.showDialogConfirm('Delete element', $url);
                            return false;
                        break;

                        case 'quit':
                        default:
                            return false;
                        break;
                    }

                    document.location.href = $url;
                }
            );
        },

        showDialogConfirm: function($title, $url)
        {
            $('#dialog').attr('title', $title).dialog({
                bgiframe        : false,
                resizable       : false,
                height          : 150,
                modal           : true,
                overlay         :
                {
                    backgroundColor: '#000',
                    opacity: 0.5
                },
                buttons         :
                {
                    'Confirm': function()
                    {
                        document.location.href = $url;
                        $(this).dialog('close');

                        return true;
                    },
                    'Cancel': function()
                    {
                        $(this).dialog('close');

                        return false;
                    }
                }
            });
        }
    };
})(jQuery);

