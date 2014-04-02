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
 * @subpackage Markdown
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\Markdown;

use Gc\Datatype\AbstractDatatype\AbstractEditor;
use Zend\Form\Element;
use Zend\Validator\Uri;
use Parsedown\Parsedown;

/**
 * Editor for Markdown datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage Markdown
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
        $value = $this->getRequest()->getPost()->get($this->getName());
        $data  = array();
        if (!empty($value)) {
            $parsedown        = new Parsedown();
            $data['markdown'] = $parsedown->parse($value);
            $data['source']   = $value;
        }

        $this->setValue(serialize($data));
    }

    /**
     * Load upload editor
     *
     * @return mixed
     */
    public function load()
    {
        $data     = unserialize($this->getValue());
        $property = $this->getProperty();
        $textarea = new Element\Textarea($this->getName());
        $textarea->setAttribute('id', $this->getName());
        $textarea->setAttribute('class', 'form-control');
        $textarea->setAttribute('required', $property->isRequired());
        $textarea->setAttribute('description', $property->getDescription());
        $textarea->setLabel($this->getProperty()->getName());
        $textarea->setValue(!empty($data['source']) ? $data['source'] : '');

        return $this->addPath(__DIR__)->render(
            'markdown-editor.phtml',
            array(
                'textarea' => $textarea,
                'id'       => $this->getName(),
                'value'    => $this->getValue(),
            )
        );
    }
}
