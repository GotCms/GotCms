<?php
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
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage TinyUrl
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\TinyUrl;

use Gc\Datatype\AbstractDatatype\AbstractEditor;
use Zend\Form\Element;
use Zend\Validator\Uri;

/**
 * Editor for TinyUrl datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage TinyUrl
 */
class Editor extends AbstractEditor
{
    /**
     * Save upload editor
     *
     * @return void
     */
    public function save()
    {
        $url   = $this->getRequest()->getPost()->get($this->getName());
        $value = array();
        if (!empty($url)) {
            $validator = new Uri(
                array(
                    'allowRelative' => false,
                )
            );
            if ($validator->isValid($url)) {
                $tinyUrl = file_get_contents(
                    'http://tinyurl.com/api-create.php?url=' . rawurlencode($url)
                );

                $value = array(
                    $url,
                    $tinyUrl
                );
            }
        }

        $this->setValue(serialize($value));
    }

    /**
     * Load upload editor
     *
     * @return mixed
     */
    public function load()
    {
        $property    = $this->getProperty();
        $value       = @unserialize($this->getValue());
        $originalUrl = !empty($value[0]) ? $value[0] : '';
        $tinyUrl     = !empty($value[1]) ? $value[1] : '';

        $textstring = new Element\Url($this->getName());
        $textstring->setAttribute('class', 'form-control');
        $textstring->setAttribute('description', $property->getDescription());
        $textstring->setLabel($property->getName());
        $textstring->setValue($originalUrl);
        $textstring->setAttribute('required', $property->isRequired());

        $data = array($textstring);
        if (!empty($tinyUrl)) {
            $html   = '<div class="col-lg-2">&nbsp;</div><div class="col-lg-10 bg-warning">';
            $html  .= sprintf('<a href="%s" target="_blank" class="btn btn-link">', $tinyUrl);
            $html  .= $tinyUrl;
            $html  .= '</a></div>';
            $data[] = $html;

        }

        return $data;
    }
}
