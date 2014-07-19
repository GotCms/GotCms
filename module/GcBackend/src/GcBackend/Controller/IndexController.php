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
 * @package    GcBackend
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcBackend\Controller;

use Gc\Mvc\Controller\Action;
use Gc\Document\Collection;
use Gc\User\Visitor;
use Gc\Version;
use Zend\Json\Json;
use Zend\View\Model\ViewModel;

/**
 * Index controller for admin module
 *
 * @category   Gc_Application
 * @package    GcBackend
 * @subpackage Controller
 */
class IndexController extends Action
{
    /**
     * Translator js action
     *
     * @TODO
     *
     * @return ViewModel
     */
    public function translatorAction()
    {
        $translator = $this->getServiceLocator()->get('MvcTranslator');

        return array(
            'Please fill all fields' => $translator->translate('Please fill all fields'),
            'Delete' => $translator->translate('Delete'),
            'Name' => $translator->translate('Name'),
            'Identifier' => $translator->translate('Identifier'),
            'Datatype' => $translator->translate('Datatype'),
            'Description' => $translator->translate('Description'),
            'Required' => $translator->translate('Required'),
            'Delete' => $translator->translate('Delete'),
            'New' => $translator->translate('New'),
            'Edit' => $translator->translate('Edit'),
            'Cut' => $translator->translate('Cut'),
            'Copy' => $translator->translate('Copy'),
            'Paste' => $translator->translate('Paste'),
            'Refresh' => $translator->translate('Refresh'),
            'Quit' => $translator->translate('Quit'),
            'These items will be permanently deleted and cannot be recovered. Are you sure?' => $translator->translate(
                'These items will be permanently deleted and cannot be recovered. Are you sure?'
            ),
            'Delete element' => $translator->translate('Delete element'),
            'Cancel' => $translator->translate('Cancel'),
            'Confirm' => $translator->translate('Confirm'),
            'All form fields are required' => $translator->translate('All form fields are required'),
            'Url key' => $translator->translate('Url key'),
            'Copy document' => $translator->translate('Copy document'),
            'Add' => $translator->translate('Add'),
            'These items will be permanently updated and cannot be recovered. Are you sure?' => $translator->translate(
                'These items will be permanently updated and cannot be recovered. Are you sure?'
            ),
            'Update content' => $translator->translate('Update content')
        );
    }
}
