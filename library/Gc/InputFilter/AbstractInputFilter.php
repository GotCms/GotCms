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
 * @category   Gc
 * @package    Library
 * @subpackage InputFilter
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\InputFilter;

use Gc\Db\AbstractTable;
use Zend\Validator\Db\NoRecordExists;
use Zend\InputFilter\InputFilter;

/**
 * Abstract InputFilter overload Zend\InputFilter\InputFilter
 * This is better to initialize somes values, retrieve adapter
 * add dynamic content, etc...
 *
 * @category   Gc
 * @package    Library
 * @subpackage InputFilter
 */
abstract class AbstractInputFilter extends InputFilter
{
    /**
     * Load values
     *
     * @param AbstractTable $table Table
     *
     * @return AbstractInputFilter
     */
    public function loadValues(AbstractTable $table)
    {
        $data = $table->getData();
        if (is_array($data)) {
            foreach ($data as $name => $value) {
                if ($this->has($name)) {
                    $this->get($name)->setValue($value);
                    $validators = $this->get($name)->getValidatorChain()->getValidators();
                    foreach ($validators as $validator) {
                        if ($validator['instance'] instanceof NoRecordExists) {
                            $validator['instance']->setExclude(array('field' => 'id', 'value' => $table->getId()));
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Add data
     *
     * @param array $data Array of data
     *
     * @return AbstractInputFilter
     */
    public function addData(array $data)
    {
        foreach ($data as $name => $value) {
            if ($this->has($name)) {
                $this->get($name)->setValue($value);
            }
        }

        return $this;
    }
}
