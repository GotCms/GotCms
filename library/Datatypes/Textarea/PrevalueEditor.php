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
 * @subpackage Textarea
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Datatypes\Textarea;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor;
use Zend\Form\Element;

/**
 * Prevalue Editor for Textarea datatype
 *
 * @category   Gc_Library
 * @package    Datatypes
 * @subpackage Textarea
 */
class PrevalueEditor extends AbstractPrevalueEditor
{
    /**
     * Save Textarea prevalue editor
     *
     * @return void
     */
    public function save()
    {
        //Save prevalue in column Datatypes\prevalue_value
        $post = $this->getRequest()->getPost();
        $rows = $post->get('rows', null);
        $cols = $post->get('cols', null);
        $wrap = $post->get('wrap', null);

        $this->setConfig(
            array(
                'cols' => $cols,
                'rows' => $rows,
                'wrap' => $wrap,
            )
        );
    }

    /**
     * Load Textarea prevalue editor
     * - cols:   Number of caracters display per line
     * - rows:   Define the number of line visible in text zone
     * - wrap:   Possible values are : hard / off / soft
     *                       define if line returns are automatic (hard / soft)
     *                       or if the horizontal display if exceeded (off)
     *
     * @return mixed
     */
    public function load()
    {
        $config = $this->getConfig();

        $cols = new Element\Text('cols');
        $cols->setAttributes(
            array(
                'label' => 'Cols',
                'value' => isset($config['cols']) ? $config['cols'] : '',
                'class' => 'form-control',
            )
        );

        $rows = new Element\Text('rows');
        $rows->setAttributes(
            array(
                'label' => 'Rows',
                'value' => isset($config['rows']) ? $config['rows'] : '',
                'class' => 'form-control',
            )
        );

        $wrap = new Element\Select('wrap');
        $wrap->setAttributes(
            array(
                'label' => 'Wrap',
                'class' => 'input-select',
                'options' => array('hard' => 'hard', 'off' => 'off', 'soft' => 'soft'),
                'value' => isset($config['wrap']) ? $config['wrap'] : '',
            )
        );

        return array($cols, $rows, $wrap);
    }
}
