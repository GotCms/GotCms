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

namespace Datatypes\Textarea;

use Gc\Datatype\AbstractDatatype\AbstractEditor,
    Zend\Form\Element;

class Editor extends AbstractEditor
{
    /**
     * Save textarea editor
     * @return mixte
     */
    public function save()
    {
        $value = $this->getRequest()->getPost()->get($this->getName());
        $this->setValue($value);
    }

    /**
     * Load textarea editor
     * @return mixte
     */
    public function load()
    {
        $config = $this->getConfig();
        $textarea = new Element($this->getName());
        $textarea->setAttribute('type', 'textarea');
        $textarea->setAttribute('label', $this->getProperty()->getName());
        $textarea->setAttribute('value', $this->getProperty()->getValue());

        foreach($config as $key => $value)
        {
            if(!empty($value))
            {
                $textarea->setAttribute($key, $value);
            }
        }

        return $textarea;
    }
}
