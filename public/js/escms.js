var ES = Class.create();
ES.prototype = {
    initialize: function($options)
    {
        this._options = $H($options);
    },

    setOption: function($name, $value)
    {
        this._options.set($name, $value)
    },

    setHtmlMessage: function($message)
    {
        jQuery('.html-message').html($message);
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
            return $element.hasOwnProperty;
        }
        else
        {
            return $element.replace(/^\s+|\s+$/g, '').empty();
        }
    },

    developmentForm: function($addTabUrl, $addPropertyUrl, $deleteTabUrl, $deletePropertyUrl)
    {
        var $this = this;
        jQuery('.tabs').tabs();

        //tabs
        jQuery('#tabs-add').click(function()
        {
            $name = jQuery('#tabs-addname');
            $description = jQuery('#tabs-adddescription');
            if($this.isEmpty($name.val()) || $this.isEmpty($description.val()))
            {
                $this.setHtmlMessage($this.translate('Please fill all fields'));
            }
            else
            {
                if(jQuery('#tabs').html() == null)
                {
                    jQuery(this).after('<ul class="sortable" id="tabs"></ul>');
                    jQuery('#tabs').sortable({placeholder: "ui-state-highlight"});
                }

                jQuery.post($addTabUrl, {name:$name.val(),description:$description.val()}
                , function($data, textStatus, jqXHR){
                        if($data.message != undefined)
                        {
                            $this.setHtmlMessage($data.message);
                        }
                        else
                        {
                            $tabs = jQuery('#tabs');
                            $e = '<li>'
                                +'<input type="hidden" name="tabs[name]['+$data.id+']" value="'+$name.val()+'" />'
                                +'<input type="hidden" name="tabs[description]['+$data.id+']" value="'+$description.val()+'" />'
                                +'<span>'+$name.val()+'</span><span>'+$description.val()+'</span>'
                                +'<button type="button" value="'+$data.id+'" class="delete-tab">delete</button>'
                                +'</li>';
                            $tabs.append($e);
                            jQuery('.select-tab').append(new Option($name.val(),$data.id));


                            if(jQuery('#properties-tabs-content').html() != null)
                            {
                                jQuery('#properties-tabs-content').tabs( "add", '#tabs-properties-'+$data.id, $name.val());
                            }
                            else
                            {
                                //add tab if does not exist
                                jQuery('#property_add').after('<div id="properties-tabs-content" class="tabs">'
                                        +'<ul>'
                                            +'<li><a href="#tabs-properties-1">'+$name.val()+'</a></li>'
                                        +'</ul>'
                                        +'<div id="tabs-properties-1">'
                                            +'<ul></ul>'
                                        +'</div>'
                                        +'</div>');
                                jQuery('#properties-tabs-content').tabs({idPrefix:'tabs-properties', panelTemplate: '<div><ul></ul></div>'});
                            }
                        }
                    }
                )
            }
        });

        jQuery('.delete-tab').live('click', function()
        {
            $button = jQuery(this);
            jQuery.post($deleteTabUrl, {
                        tab:    $button.val()
                    },
                    function($data)
                    {
                        jQuery('.select-tab').find('option[value="'+$button.val()+'"]').remove();
                        $tabs = jQuery('#properties-tabs-content');
                        $button.parent().remove();
                        $tab = $tabs.find('a[href="#tabs-properties-'+$button.val()+'"]').parent();
                        var $index = jQuery('li', $tabs).index($tab);
                        $tabs.tabs( "remove", $index );
                        $this.setHtmlMessage($data.message);
                    }
            );
        });
        //views

        //properties
        jQuery('#property_add').click(function()
        {
            $name = jQuery('#properties-name');
            $identifier = jQuery('#properties-identifier');
            $tab = jQuery('#properties-tab');
            $datatype = jQuery('#properties-datatype');
            $description = jQuery('#properties-description');
            $isRequired = jQuery('#properties-required');

            if($this.isEmpty($identifier.val()) || $this.isEmpty($name.val()) || $this.isEmpty($tab.val()) || $this.isEmpty($datatype.val()) || $this.isEmpty($description.val()))
            {
                $this.setHtmlMessage($this.translate('Please fill all fields'));
            }
            else
            {
                jQuery.post($addPropertyUrl, {
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

                            $t = new Template($c);
console.log($data);
                            jQuery('#tabs-properties-'+$tab.val()).find('ul').append('<li>'+$t.evaluate($data)+'</li>');
console.log('#properties-tab-'+$data.tab+'-'+$data.id);
console.log('#properties-datatype-'+$data.tab+'-'+$data.id);
console.log('#properties-required-'+$data.tab+'-'+$data.id);
                            jQuery('#properties-tab-'+$data.tab+'-'+$data.id).html(jQuery('#properties-tab').html()).val($data.tab);
                            jQuery('#properties-datatype-'+$data.tab+'-'+$data.id).html(jQuery('#properties-datatype').html()).val($data.datatype);
                            jQuery('#properties-required-'+$data.tab+'-'+$data.id).attr('checked', $isRequired.val());
                        }
                    }
                );
            }
        });

        jQuery('.delete-property').live('click', function()
        {
            $button = jQuery(this);
            jQuery.post($deletePropertyUrl, {
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
        jQuery("#browser").treeview();
        jQuery("#browser").contextMenu(
            {
                menu: 'contextMenu'
            },

            function($action, $element, $position)
            {
                switch($action){
                    case 'new':
                        if($element.attr('rel') != undefined)
                        {
                            $parentId = $element.attr('rel');
                        }
                    break;

                    case 'edit':
                    break;

                    case 'copy':
                    case 'cut':
                    break;

                    case 'paste':
                    case 'delete':
                        $this.showDialogConfirm('test', $url);
                    break;

                    case 'quit':
                    default:
                        return false;
                    break;
                }

                $routes = $this._options.get('routes');
                document.location.href = $routes[$action];
            }
        );
    },

    showDialogConfirm: function($title, $url)
    {
        jQuery('#dialog').attr('title', $title).dialog({
            bgiframe        : false,
            resizable       : false,
            height          : 150,
            modal           : true,
            overlay         :
                {
                    backgroundColor: '#000',
                    opacity: 0.5
                },
            buttons         : {
                'Confirm': function()
                {
                    document.location.href = url;
                    jQuery(this).dialog('close');

                    return true;
                },
                'Cancel': function()
                {
                    jQuery(this).dialog('close');

                    return false;
                }
            }
        });
    }
};

ES.getInstance = function()
{
    if(this.instance == null)
    {
        this.instance = new ES();
    }

    return this.instance;
}
