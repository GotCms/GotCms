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
 * @category   Gc_Application
 * @package    Content
 * @subpackage Form
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Content\Form;

use Gc\Document\Model as DocumentModel;
use Gc\DocumentType;
use Gc\Form\AbstractForm;
use Gc\Layout;
use Gc\View;
use Zend\Validator;
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFilterFactory;

/**
 * Translation form
 *
 * @category   Gc_Application
 * @package    Content
 * @subpackage Form
 */
class Translation extends AbstractForm
{
    /**
     * Initialize Form
     *
     * @return void
     */
    public function init()
    {
        $inputFilterFactory = new InputFilterFactory();
        $inputFilter        = $inputFilterFactory->createInputFilter(
            array(
                'source' => array(
                    'name' => 'source',
                    'required' => true,
                    'validators' => array(
                        array('name' => 'not_empty'),
                    ),
                ),
            )
        );

        $this->setInputFilter($inputFilter);

        $source = new Element\Text('source');
        $source->setAttribute('label', 'Name')
            ->setAttribute('class', 'input-text');

        $localeList = array(
            'fr_FR' => 'Français',
            'en_GB' => 'English',
        );

        $locale = new Element\Select('locale');
        $locale->setAttribute('label', 'Url key')
            ->setValueOptions($localeList);

        $this->add($source);
        $this->add($locale);
    }
}
