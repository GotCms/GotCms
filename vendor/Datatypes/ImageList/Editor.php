<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Gc
 * @package  Datatype
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Datatypes\ImageList;

use Gc\Datatype\AbstractDatatype\AbstractEditor,
    Zend\Form\Element;

class Editor extends AbstractEditor
{

    public function save() {
        //sauvegarde des informations
    }

    public function load() {
        $textrich = new Element\Textarea('textrich-'.$this->_property->getId());
        $textrich->setLabel($this->_property->getName());
        $textrich->setAttrib('id', 'textrich-'.$this->_property->getId());
        $textrich->setValue($this->_property->getValue());
        $textrich->getDecorator('description')->setEscape(false)->setTag(false);
        $script = '';
        if(!Zend_Registry::isRegistered('textrich')) {
            Zend_Registry::set('textrich', '<script type="text/javascript" src="/skin/admin/js/tiny_mce/jquery.tinymce.js"></script>');
            $script = Zend_Registry::get('textrich');
        }
        $script .= '<script type="text/javascript">
                    $(function() {
                        $(\'#textrich-'.$this->_property->getId().'\').tinymce({
                            theme : "advanced",
                            plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
                            theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
                            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
                            theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
                            theme_advanced_toolbar_location : "top",
                            theme_advanced_toolbar_align : "left",
                            theme_advanced_statusbar_location : "bottom",
                            theme_advanced_resizing : true,
                            content_css : "css/content.css",
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

