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
 * @package    GcDevelopment
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcDevelopment\Controller;

use Gc\Mvc\Controller\RestAction;
use GcDevelopment\Filter\Datatype as DatatypeFilter;
use GcDevelopment\Form\Datatype as DatatypeForm;
use Gc\Datatype;

/**
 * Datatype controller
 *
 * @category   Gc_Application
 * @package    GcDevelopment
 * @subpackage Controller
 */
class DatatypeRestController extends RestAction
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'development', 'permission' => 'datatype');

    /**
     * List all views
     *
     * @return \Zend\Datatype\Model\DatatypeModel
     */
    public function getList()
    {
        $datatypeCollection = new Datatype\Collection();
        $return             = array();
        foreach ($datatypeCollection->getDatatypes() as $datatype) {
            $return[] = $this->getDatatype($datatype);
        }

        return array('datatypes' => $return);
    }

    /**
     * Create Datatype
     *
     * @param array $data Datat to used
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function create($data)
    {
        $datatypeModel  = new Datatype\Model();
        $datatypeFilter = new DatatypeFilter($this->getServiceLocator()->get('DbAdapter'));
        $datatypeFilter->setData($data);
        if ($datatypeFilter->isValid()) {
            $datatypeModel->addData($datatypeFilter->getValues());
            $datatypeModel->save();

            return $this->getDatatype($datatypeModel);
        }

        return array('content' => 'Invalid data', 'errors' => $datatypeFilter->getMessages());
    }

    /**
     * Get Datatype
     *
     * @param integer $id Id of the datatype
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function get($id)
    {
        $datatypeModel = Datatype\Model::fromId($id);
        if (empty($datatypeModel)) {
            return $this->notFoundAction();
        }

        return $this->getDatatype($datatypeModel);
    }

    /**
     * Get Datatype
     *
     * @param integer $id   Id of the datatype
     * @param array   $data Datat to used
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function update($id, $data)
    {
        $datatypeModel = Datatype\Model::fromId($id);
        if (empty($datatypeModel)) {
            return $this->notFoundAction();
        }

        $datatypeFilter = new DatatypeFilter($this->getServiceLocator()->get('DbAdapter'));
        $datatypeFilter->setData($data);
        if ($datatypeFilter->isValid()) {
            if ($datatypeModel->getModel() != $datatypeFilter->getValue('model')) {
                $datatypeModel->setPrevalueValue(array());
            }

            $post = $this->getRequest()->getPost();
            $post->fromArray(array_merge($post->toArray(), $data));

            $datatype = Datatype\Model::loadDatatype($this->getServiceLocator(), $datatypeModel->getId());
            $datatypeModel->setPrevalueValue(Datatype\Model::savePrevalueEditor($datatype));
            $datatypeModel->addData($datatypeFilter->getValues());
            $datatypeModel->save();

            return $this->getDatatype($datatypeModel);
        }

        return array('content' => 'Invalid data', 'errors' => $datatypeFilter->getMessages());
    }


    /**
     * Get Datatype informations
     *
     * @param Datatype\Model $datatypeModel Datatype Model
     *
     * @return array
     */
    protected function getDatatype(Datatype\Model $datatypeModel)
    {
        $datatype = Datatype\Model::loadDatatype($this->getServiceLocator(), $datatypeModel->getId());
        $renderer = $this->getServiceLocator()->get('ViewRenderer');

        $datatypeForm = new DatatypeForm();
        DatatypeForm::addContent($datatypeForm, Datatype\Model::loadPrevalueEditor($datatype));

        return array(
            'datatype' => $datatypeModel->toArray(),
            'infos' => $datatypeModel->getInfos(),
            'prevalue_editor' => $renderer->render(
                'gc-development/datatype/display',
                array('form' => $datatypeForm)
            )
        );
    }
}
