<?php
namespace Datatypes\Textrich;

use Application\Model\Datatype,
    Zend\Form\Element;

class Editor extends AbstractDatatpye\Editor
{
    public function save($request = null) {
        $value = $request->getParam('textrich'.$this->_property->getId());
        $this->setValue($value);
        return $this->saveValue();
    }

    public function load() {
        $textrich = new Element\Textarea('textrich'.$this->_property->getId());
        $textrich->setLabel($this->_property->getName());
        $textrich->setAttrib('id', 'textrich'.$this->_property->getId());
        $textrich->setValue($this->_property->getValue());
        $textrich->getDecorator('description')->setEscape(false)->setTag(false);
        $script = '';
        if(!Zend_Registry::isRegistered('textrich')) {
            Zend_Registry::set('textrich', '<script type="text/javascript" src="'.$this->getHelper('getSkinUrl')->getSkinUrl('js/tiny_mce/jquery.tinymce.js').'"></script>');
            $script = Zend_Registry::get('textrich');
        }
        $script .= '<script type="text/javascript">
                    $(document).ready(function() {
                        $(\'#textrich'.$this->_property->getId().'\').tinymce({
                            script_url : "'.$this->getHelper('getSkinUrl')->getSkinUrl('js/tiny_mce/tiny_mce.js').'",
                            theme : "advanced",
                            plugins : "safari,pagebreak,style,save,layer,table,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
                            theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
                            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
                            theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
                            theme_advanced_toolbar_location : "top",
                            theme_advanced_toolbar_align : "left",
                            theme_advanced_statusbar_location : "bottom",
                            theme_advanced_resizing : true,

                            content_css : "'.$this->getHelper('getSkinUrl')->getSkinUrl('css/style.css', 'default').'",

                            template_external_list_url : "lists/template_list.js",
                            external_link_list_url : "lists/link_list.js",
                            external_image_list_url : "lists/image_list.js",
                            media_external_list_url : "lists/media_list.js"
                        });
                    });
                </script>';
        $textrich->setDescription($script);
        return $textrich;
    }
}

