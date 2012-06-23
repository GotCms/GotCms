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

namespace Datatypes\Upload;

use Gc\Datatype\AbstractDatatype\AbstractEditor,
    Zend\Form\Element;

class Editor extends AbstractEditor
{

    public function save()
    {
        $post = $this->getRequest()->post();
        $value_ids = $post->get('upload-file-id-'.$this->getProperty()->getId(), array());
        $parameters = $this->getParameters();
        $options  = $parameters['options'];
        $arrayValues = array();
        if(is_array($value_ids) && count($value_ids) > 1 && isset($options['content']) && $options['content'] === true) {
            $i = 0;
            foreach($value_ids as $value_id) {
                $value = $this->getParam('upload-file-link-'.$this->getProperty()->getId().'', $value_id, '');
                $content = $this->getParam('upload-file-content-'.$this->getProperty()->getId().'', $value_id, '');
                $title = $this->getParam('upload-file-title-'.$this->getProperty()->getId().'', $value_id, '');
                $file = '..'.$value;// .. to go to parent directory
                if(!empty($value) && is_file($file)) {
                    $arrayValues[$i] = array();
                    $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
                    $finfo = finfo_open($const); // return mimetype extension
                    if(!in_array(finfo_file($finfo, $file), $parameters['mime_list'])){
                        unlink($file);
                        $value = '';
                    }else {
                        $arrayValues[$i]['value'] = $value;
                        $arrayValues[$i]['title'] = $title;
                        $arrayValues[$i]['content'] = $content;
                        $i++;
                    }
                    finfo_close($finfo);
                }
            }
            $returnValue = serialize($arrayValues);
        } else {
            $value_id = $value_ids[0];

            $value = $this->getParam('upload-file-link-'.$this->getProperty()->getId().'', $value_id, '');

            if((isset($options['content'] )&& $options['content'] === true) or (isset($options['title']) && $options['title'] === true)) {
                if($options['content'] === true) {
                    $content = $this->getParam('upload-file-title-'.$this->getProperty()->getId().'', $value_id, '');
                    $arrayValues['content'] = $content;
                }
                if($options['title'] === true){
                    $title = $this->getParam('upload-file-content-'.$this->getProperty()->getId().'', $value_id, '');
                    $arrayValues['title'] = $title;
                }
                $arrayValues['value'] = $value;
                $returnValue = serialize($arrayValues);
            }else {
                $returnValue = $value;
            }
        }
        $this->setValue($returnValue);
        return $this->saveValue();
    }

    public function load() {
        $parameters = $this->getParameters();
        $options  = $parameters['options'];
        $multiple = false;
        $title = false;
        $content = false;
        if(isset($options['multiple'])) {
            $multiple = $options['multiple'];
        }
        if(isset($options['title'])) {
            $title = $options['title'];
        }
        if(isset($options['content'])) {
            $content = $options['content'];
        }

        $upload = $this->addForm($title, $content, $multiple);
        $this->addScript($upload);

        return array($upload);
    }

    /**
     * @param boolean $title
     * @param boolean $content
     * @param boolean $multiple
     * @return string
     */
    protected function addForm($title, $content, $multiple) {
        $values = $this->getValue();
        if($multiple === true) {
            $values = unserialize($values);
            $templateHtml = '<div id="multiple-'.$this->getProperty()->getId().'">
                <ul class="multiple-ul-'.$this->getProperty()->getId().'">';
            if(!empty($values)):
                $nbValues = count($values);
                $index = 1;
                foreach($values as $key=>$value):
                    $templateHtml .= '<li>
                        <input type="hidden" value="'.$key.'" name="upload-file-id-'.$this->getProperty()->getId().'[]" class="upload-file-id" />
                        <input type="hidden" value="'.(isset($value['value']) ? $value['value'] : '').'" name="upload-file-link-'.$this->getProperty()->getId().'['.$key.']" id="upload-file-link-'.$this->getProperty()->getId().'-'.$key.'" />
                        <div class="upload-file-'.$this->getProperty()->getId().'">
                            <span id="spanButton'.$this->getProperty()->getId().'-'.$key.'"></span>
                        </div>
                        <div id="contentProgress'.$this->getProperty()->getId().'-'.$key.'"></div>';
                    $templateHtml .= $title === true ? ($this->addTitle(isset($value['title']) ? $value['title'] : '', $key)) :'';
                    $templateHtml .= $content === true ? ($this->addContent(isset($value['content']) ? $value['content'] : '', $key)) : '';
                    if($nbValues == $index) {
                        $templateHtml .= '<button class="button-add">Add</button></li>';
                    } else {
                        $templateHtml .= '<button class="button-delete">Delete</button></li>';
                    }
                    $index++;
                endforeach;
            else:
                $key = 0;
                $templateHtml .= '<li>
                    <input type="hidden" value="'.$key.'" name="upload-file-id-'.$this->getProperty()->getId().'[]" class="upload-file-id" />
                    <input type="hidden" value="" name="upload-file-link-'.$this->getProperty()->getId().'['.$key.']" id="upload-file-link-'.$this->getProperty()->getId().'-'.$key.'" value="" />
                    <div class="upload-file-'.$this->getProperty()->getId().'">
                        <span id="spanButton'.$this->getProperty()->getId().'-'.$key.'"></span>
                    </div>
                    <div id="contentProgress-'.$this->getProperty()->getId().'-'.$key.'"></div>';

                $templateHtml .= $title === true ? ($this->addTitle('', $key)) :'';
                $templateHtml .= $content === true ? ($this->addContent('', $key)) :'';

                $templateHtml .= '<button class="button-add">Add</button></li>';

            endif;
            $templateHtml .= '
                </ul>
            </div>
                <script type="text/javascript">
                        var key = '.$key.';
                        function setButtons() {
                            $(".button-add > .ui-button-text").each(function() {
                                $(this).parent().html($(this).html());
                            });
                            $(".button-delete > .ui-button-text").each(function() {
                                $(this).parent().html($(this).html());
                            });

                            $(".button-add").button({
                                icons: {
                                    primary: "ui-icon-circle-plus"
                                },
                                text: false
                            });
                            $(".button-delete").button({
                                icons: {
                                    primary: "ui-icon-circle-minus"
                                },
                                text: false
                            });
                        }
                        $(".button-add").live("click", function() {
                            key+=1;
                            var newImage = \'<li><input type="hidden" value="\'+key+\'" name="upload-file-id-'.$this->getProperty()->getId().'[]" class="upload-file-id" /><input type="hidden" value="" name="upload-file-link-'.$this->getProperty()->getId().'[\'+key+\']" id="upload-file-link-'.$this->getProperty()->getId().'-\'+key+\'" value="" /><div class="upload-file-'.$this->getProperty()->getId().'">    <span id="spanButton'.$this->getProperty()->getId().'-\'+key+\'"></span></div><div id="contentProgress-'.$this->getProperty()->getId().'-\'+key+\'"></div>'.$this->addTitle('', '\'+key+\'').$this->addContent('', '\'+key+\'').'<button class="button-add">Add</button></li>\';
                            $(".multiple-ul-'.$this->getProperty()->getId().'").append(newImage);
                            $(this).removeClass("ui-state-focus");
                            $(this).removeClass("button-add").addClass("button-delete");
                            $(this).find(".ui-button-text").html("Delete");
                            setButtons();
                            swfUpload'.$this->getProperty()->getId().'();
                            return false;
                        });
                        $(".button-delete").live("click", function() {
                            $(this).removeClass("ui-state-focus");
                            $(this).parent().remove();
                            return false;
                        });
                        //init buttons add and delete elements
                        setButtons();
                </script>
            ';
        } else {
            $key = 0;
            $value = $values;
            $templateHtml = '
                <input type="hidden" value="'.$key.'" name="upload-file-id-'.$this->getProperty()->getId().'[]" class="upload-file-id" />
                <input type="hidden" value="'.($title === true || $content === true ? $value['value'] : $value).'" name="upload-file-link-'.$this->getProperty()->getId().'['.$key.']" id="upload-file-link-'.$this->getProperty()->getId().'-'.$key.'" />
                <div class="upload-file-'.$this->getProperty()->getId().'">
                    <span id="spanButton'.$this->getProperty()->getId().'-'.$key.'"></span>
                </div>
                <div id="contentProgress-'.$this->getProperty()->getId().'-'.$key.'"></div>';
            $templateHtml .= $title === true ? ($this->addTitle(isset($value['title']) ? $value['title'] : '', $key)) :'';
            $templateHtml .= $content === true ? ($this->addContent(isset($value['content']) ? $value['content'] : '', $key)) : '';
        }
        return $templateHtml;
    }

    protected function addScript(&$string) {
        if(!Zend_Registry::isRegistered('upload')) {
            Zend_Registry::set('upload', '<script type="text/javascript" src="'.$this->getHelper('getSkinUrl')->getSkinUrl('js/swfupload/swfupload.js').'"></script>
                                            <script type="text/javascript" src="'.$this->getHelper('getSkinUrl')->getSkinUrl('js/swfupload/jquery.swfupload.js').'"></script>');

            $string .= Zend_Registry::get('upload');
        }
        $string .='
            <script type="text/javascript">
                function swfUpload'.$this->getProperty()->getId().'() {
                    $(".upload-file-'.$this->getProperty()->getId().'").each(function() {

                        var elementId = $(this).parent().find(\'.upload-file-id\').val();
                        endOfName = "";
                        if(elementId != "") {
                            endOfName = "-"+elementId;
                        }
                        $(this).swfupload({
                            // Backend Settings
                            upload_url                : "'.$this->getUploadUrl().'/id/"+elementId,    // Relative to the SWF file (or you can use absolute paths)

                            // File Upload Settings
                            file_size_limit            : "25mb", // megabytes
                            file_types                 : "*.*",
                            file_types_description     : "All Files",
                            file_upload_limit         : "10",
                            file_queue_limit         : "0",

                            // Button Settings
                            button_image_url         : "'.$this->getHelper('getSkinUrl')->getSkinUrl('js/swfupload/button.png').'", // Relative to the SWF file
                            button_placeholder_id     : $(this).children(\'span\').attr(\'id\'),
                            button_width            : 61,
                            button_height            : 22,

                            // Flash Settings
                            flash_url                 : "'.$this->getHelper('getSkinUrl')->getSkinUrl('js/swfupload/swfupload.swf').'",
                            // Debug Settings
                            post_params                :{sid: "'.Zend_Session::getId().'"}

                        });
                        // assign our event handlers
                        $(this).bind("fileQueued", function(event, file){
                            // start the upload once a file is queued
                            $(this).swfupload("startUpload");
                        })
                        .bind("uploadError", function(file, error, message){
                            var elementId = $(this).parent().find(\'.upload-file-id\').val();
                            endOfName = "";
                            if(elementId != "") {
                                endOfName = "-"+elementId;
                            }
                            $("#upload-file-link-'.$this->getProperty()->getId().'"+endOfName).val("");
                            $("#contentProgress-'.$this->getProperty()->getId().'"+endOfName).html("Upload Error").attr("class", "");
                        })
                        .bind("uploadSuccess", function(file, data, response){
                            var elementId = $(this).parent().find(\'.upload-file-id\').val();
                            endOfName = "";
                            if(elementId != "") {
                                endOfName = "-"+elementId;
                            }
                            $("#upload-file-link-'.$this->getProperty()->getId().'"+endOfName).val(response);
                            $("#contentProgress-'.$this->getProperty()->getId().'"+endOfName).html("Upload Success").attr("class", "");
                        })
                        .bind("uploadStart", function(file){
                            var elementId = $(this).parent().find(\'.upload-file-id\').val();
                            endOfName = "";
                            if(elementId != "") {
                                endOfName = "-"+elementId;
                            }
                            $("#contentProgress-'.$this->getProperty()->getId().'"+endOfName).html("<div id=\"progressbar-'.$this->getProperty()->getId().'"+endOfName+"\"></div>");
                            $("#progressbar-'.$this->getProperty()->getId().'"+endOfName).progressbar({
                                value: 0
                            });
                        })
                        .bind("uploadProgress", function(file, bytesTotal, bytesLoaded){
                            var elementId = $(this).parent().find(\'.upload-file-id\').val();
                            endOfName = "";
                            if(elementId != "") {
                                endOfName = "-"+elementId;
                            }
                            value = (bytesLoaded * 100) / bytesTotal.size;
                            $("#progressbar-'.$this->getProperty()->getId().'"+endOfName).progressbar({
                                value: parseInt(value)
                            });
                        })
                        .bind("uploadComplete", function(event, file){
                            $(this).swfupload("startUpload");
                        });
                    });
                }
                setTimeout("swfUpload'.$this->getProperty()->getId().'()", 1000);
            </script>';
    }

    /**
     * Add title
     * @param string $value
     * @param integer $key
     * @return string
     */
    protected function addTitle($value, $key)
    {
        return 'Title: <input type="text" value="'.$value.'" name="upload-file-title-'.$this->getProperty()->getId().'['.$key.']" />';
    }

    /**
     * Add content description
     * @param string $value
     * @param integer $key
     * @return string
     */
    protected function addContent($value, $key)
    {
        return 'Content: <textarea name="upload-file-content-'.$this->getProperty()->getId().'['.$key.']">'.$value.'</textarea>';
    }

    /**
     * @param mixte $key
     * @param mixte $value
     * @param mixte $default
     * @return unknown
     */
    protected function getParam($key, $value, $default)
    {
        $array = $this->getRequest()->post()->get($key);

        return is_array($array) && isset($array[$value]) ? $array[$value] : $default;
    }

}
