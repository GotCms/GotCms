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
    Gc\Media\File,
    Zend\Form\Element;

class Editor extends AbstractEditor
{

    public function save()
    {
        $file_class = new File();
        $file_class->init($this->getProperty(), $this->getDatatype()->getDocument());

        $post = $this->getRequest()->getPost();
        $values = $post->get($this->getName(), array());
        $parameters = $this->getConfig();
        $options  = $parameters['options'];
        $array_values = array();
        $return_values = NULL;
        if(!empty($values))
        {
            $i = 0;
            foreach($values as $idx => $value)
            {
                if(empty($value['name']))
                {
                    continue;
                }

                $file = $file_class->getDirectory() . '/' . $value['name'];
                if(!empty($value) && file_exists($file))
                {
                    $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
                    $finfo = finfo_open($const); // return mimetype extension
                    if(!in_array(finfo_file($finfo, $file), $parameters['mime_list']))
                    {
                        unlink($file);
                    }
                    else
                    {
                        $array_values[$i] = array();
                        $array_values[$i]['name'] = $value['name'];
                        if(!empty($options['title']))
                        {
                            $title = empty($value['title']) ? '' : $value['title'];
                            $array_values[$i]['title'] = $title;
                        }

                        if(!empty($options['content']))
                        {
                            $content = empty($value['content']) ? '' : $value['content'];
                            $array_values[$i]['content'] = $content;
                        }

                        $i++;
                    }

                    finfo_close($finfo);
                }
            }

            $return_values = serialize($array_values);
        }

        $this->setValue($return_values);
    }

    public function load()
    {
        $parameters = $this->getConfig();
        $options  = $parameters['options'];
        $maxNumberOfFiles = empty($options['maxNumberOfFiles']) ? FALSE : TRUE;
        $title = empty($options['title']) ? FALSE : TRUE;
        $content = empty($options['content']) ? FALSE : TRUE;

        $this->initScript();
        $file_list = array();
        $files = unserialize($this->getValue());
        if(!empty($files))
        {
            $file_class = new File();
            $file_class->init($this->getProperty(), $this->getDatatype()->getDocument());
            foreach($files as $file_data)
            {
                $file_object = new \StdClass();
                $file_object->name = $file_data['name'];
                $file_object->filename = $file_data['name'];
                $file_object->size = empty($file_data['size']) ? '' : $file_data['size'];
                $file_object->type = empty($file_data['type']) ? '' : $file_data['type'];

                if(!empty($options['title']))
                {
                    $file_object->title = empty($file_data['title']) ? '' : $file_data['title'];
                }

                if(!empty($options['content']))
                {
                    $file_object->content = empty($file_data['content']) ? '' : $file_data['content'];
                }

                //$fileclass->error = 'null';
                $file_object->thumbnail_url = str_replace(GC_APPLICATION_PATH . '/public', '', $file_class->getDirectory()) . '/' . $file_data['name'];

                $router = $GLOBALS['application']->getMvcEvent()->getRouter();
                $file_object->delete_url = $router->assemble(array(
                    'document_id' => $this->getDatatype()->getDocument()->getId(),
                    'property_id' => $this->getProperty()->getId(),
                    'file' => $file_data['name'])
                , array('name' => 'documentRemoveMedia'));
                $file_object->delete_type = 'DELETE';
                $file_list[] = $file_object;
            }
        }

        return $this->addPath(__DIR__)->render('upload-editor.phtml', array(
            'property' => $this->getProperty(),
            'uploadUrl' => $this->getUploadUrl(),
            'name' => $this->getName(),
            'files' => json_encode($file_list),
            'options' => $options
        ));
    }


    protected function initScript()
    {
        $headscript = $this->getHelper('HeadScript');
        $headscript
            ->appendFile('/js/jfileupload/load-image.min.js', 'text/javascript')
            ->appendFile('/js/jfileupload/canvas-to-blob.min.js', 'text/javascript')
            ->appendFile('/js/jfileupload/jquery.iframe-transport.js', 'text/javascript')
            ->appendFile('/js/jfileupload/jquery.fileupload.js', 'text/javascript')
            ->appendFile('/js/jfileupload/jquery.fileupload-fp.js', 'text/javascript')
            ->appendFile('/js/jfileupload/jquery.fileupload-ui.js', 'text/javascript')
            ->appendFile('/js/jfileupload/locale.js', 'text/javascript');

        $headlink = $this->getHelper('HeadLink');
        $headlink->appendStylesheet('/css/jquery.fileupload-ui.css')
            ->appendStylesheet('/css/jfileupload-bootstrap.css');
    }

    /**
     * @param mixte $key
     * @param mixte $value
     * @param mixte $default
     * @return unknown
     */
    protected function getParam($key, $value, $default)
    {
        $array = $this->getRequest()->getPost()->get($key);

        return is_array($array) && isset($array[$value]) ? $array[$value] : $default;
    }

}
