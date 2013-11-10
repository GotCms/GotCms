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
 * @category Gc_Tests
 * @package  ZfModules
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Application\Controller;

use Gc\Registry;
use Gc\Core\Config as CoreConfig;
use Gc\Datatype\Model as DatatypeModel;
use Gc\Document\Model as DocumentModel;
use Gc\DocumentType\Model as DocumentTypeModel;
use Gc\Layout\Model as LayoutModel;
use Gc\Property\Model as PropertyModel;
use Gc\Tab\Model as TabModel;
use Gc\User\Model as UserModel;
use Gc\View\Model as ViewModel;
use Gc\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-03-15 at 23:51:29.
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class IndexControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ViewModel
     */
    protected $view;

    /**
     * @var DocumentTypeModel
     */
    protected $documentType;

    /**
     * @var DatatypeModel
     */
    protected $datatype;

    /**
     * @var TabModel
     */
    protected $tabModel;

    /**
     * @var PropertyModel
     */
    protected $property;

    /**
     * @var CoreConfig
     */
    protected $config;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->init();

        $this->view = ViewModel::fromArray(
            array(
                'name' => 'View',
                'identifier' => 'ViewIdentifier',
                'description' => 'Description',
                'content' => '',
            )
        );
        $this->view->save();

        $this->layout = LayoutModel::fromArray(
            array(
                'name' => 'View',
                'identifier' => 'LayoutIdentifier',
                'description' => 'Description',
                'content' => '',
            )
        );
        $this->layout->save();

        $this->documentType = DocumentTypeModel::fromArray(
            array(
                'name' => 'DocumentType',
                'description' => 'description',
                'icon_id' => 1,
                'default_view_id' => $this->view->getId(),
                'user_id' => $this->user->getId(),
            )
        );
        $this->documentType->save();
        $this->documentType->setDependencies(array($this->documentType->getId()));
        $this->documentType->save();

        $this->datatype = DatatypeModel::fromArray(
            array(
                'name' => 'DatatypeTest',
                'model' => 'Textstring'
            )
        );
        $this->datatype->save();

        $this->tabModel = TabModel::fromArray(
            array(
                'name' => 'test',
                'description' => 'test',
                'document_type_id' => $this->documentType->getId(),
            )
        );
        $this->tabModel->save();

        $this->property = PropertyModel::fromArray(
            array(
                'name' => 'test',
                'identifier' => 'test',
                'description'=> 'test',
                'tab_id' => $this->tabModel->getId(),
                'datatype_id' => $this->datatype->getId(),
                'is_required' => true
            )
        );
        $this->property->save();

        $this->config = Registry::get('Application')->getServiceManager()->get('CoreConfig');

        $this->getApplicationServiceLocator()
            ->get('ViewTemplatePathStack')
            ->addPath(GC_TEMPLATE_PATH);

        $this->getApplicationServiceLocator()->get('Auth')->clearIdentity();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->documentType->delete();
        $this->property->delete();
        $this->tabModel->delete();
        $this->view->delete();
        $this->layout->delete();
        $this->user->delete();
        $this->datatype->delete();
        unset($this->documentType);
        unset($this->property);
        unset($this->tabModel);
        unset($this->view);
        unset($this->layout);
        unset($this->user);
        unset($this->datatype);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testIndexAction()
    {
        $document = DocumentModel::fromArray(
            array(
                'name' => 'test',
                'url_key' => '',
                'status' => DocumentModel::STATUS_ENABLE,
                'user_id' => $this->user->getId(),
                'document_type_id' => $this->documentType->getId(),
                'view_id' => $this->view->getId(),
                'layout_id' => $this->layout->getId(),
                'parent_id' => null,
            )
        );
        $document->save();
        $this->property->setDocumentId($document->getId());
        $this->property->setValue('string');
        $this->property->saveValue();


        $this->dispatch($document->getUrl());
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('Application');
        $this->assertControllerName('IndexController');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('cms');

        $document->delete();
    }

    /**
     * Test
     *
     * @return void
     */
    public function testIndexActionWithUrlKey()
    {
        $document = DocumentModel::fromArray(
            array(
                'name' => 'test',
                'url_key' => 'test',
                'status' => DocumentModel::STATUS_ENABLE,
                'user_id' => $this->user->getId(),
                'document_type_id' => $this->documentType->getId(),
                'view_id' => $this->view->getId(),
                'layout_id' => $this->layout->getId(),
                'parent_id' => null,
            )
        );
        $document->save();
        $this->property->setDocumentId($document->getId());
        $this->property->setValue('s:6:"string";');
        $this->property->saveValue();


        $this->dispatch($document->getUrl() . '/');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('Application');
        $this->assertControllerName('IndexController');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('cms');

        $document->delete();
    }

    /**
     * Test
     *
     * @return void
     */
    public function testIndexActionWithCache()
    {
        $enableCache = $this->config->setValue('cache_is_active', 1);
        $enableCache = $this->config->setValue('cache_handler', 'filesystem');
        $document    = DocumentModel::fromArray(
            array(
                'name' => 'test',
                'url_key' => 'test',
                'status' => DocumentModel::STATUS_ENABLE,
                'user_id' => $this->user->getId(),
                'document_type_id' => $this->documentType->getId(),
                'view_id' => $this->view->getId(),
                'layout_id' => $this->layout->getId(),
                'parent_id' => null,
            )
        );
        $document->save();
        $this->property->setDocumentId($document->getId());
        $this->property->setValue('string');
        $this->property->saveValue();


        $this->dispatch($document->getUrl());
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('Application');
        $this->assertControllerName('IndexController');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('cms');

        $document->delete();
    }

    /**
     * Test
     *
     * @return void
     */
    public function testIndexActionWithExistingCache()
    {
        $enableCache = $this->config->setValue('cache_is_active', 1);
        $enableCache = $this->config->setValue('cache_handler', 'filesystem');
        $document    = DocumentModel::fromArray(
            array(
                'name' => 'test',
                'url_key' => 'test',
                'status' => DocumentModel::STATUS_ENABLE,
                'user_id' => $this->user->getId(),
                'document_type_id' => $this->documentType->getId(),
                'view_id' => $this->view->getId(),
                'layout_id' => $this->layout->getId(),
                'parent_id' => null,
            )
        );
        $document->save();


        $this->dispatch($document->getUrl());
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('Application');
        $this->assertControllerName('IndexController');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('cms');

        $document->delete();
        $enableCache = $this->config->setValue('cache_is_active', 0);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testIndexActionWithUrlKeyWithPreview()
    {
        $auth = new AuthenticationService(new Storage\Session(UserModel::BACKEND_AUTH_NAMESPACE));
        $auth->clearIdentity();
        $document = DocumentModel::fromArray(
            array(
                'name' => 'test',
                'url_key' => 'test',
                'status' => DocumentModel::STATUS_ENABLE,
                'user_id' => $this->user->getId(),
                'document_type_id' => $this->documentType->getId(),
                'view_id' => $this->view->getId(),
                'layout_id' => $this->layout->getId(),
                'parent_id' => null,
            )
        );
        $document->save();


        $this->dispatch($document->getUrl() . '?preview');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('Application');
        $this->assertControllerName('IndexController');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('cms');

        $document->delete();
        $enableCache = $this->config->setValue('cache_is_active', 0);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testIndexActionWith404Page()
    {
        $this->dispatch('/404Page');
        $this->assertResponseStatusCode(404);

        $this->assertModuleName('Application');
        $this->assertControllerName('IndexController');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('cms');
    }

    /**
     * Test
     *
     * @return void
     */
    public function testIndexActionWith404PageAndNotEmptyContent()
    {
        $this->config->setValue('site_404_layout', $this->layout->getId());
        $this->layout->setContent('test');
        $this->dispatch('/404Page');
        $this->assertResponseStatusCode(404);

        $this->assertModuleName('Application');
        $this->assertControllerName('IndexController');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('cms');
    }

    /**
     * Test
     *
     * @return void
     */
    public function testIndexActionWithOfflineWebsite()
    {
        $this->config->insert(
            array(
                'identifier' => 'site_is_offline',
                'value' => 1
            )
        );
        $this->dispatch('/404Page');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('Application');
        $this->assertControllerName('IndexController');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('cms');
        $this->config->setValue('site_is_offline', 0);
    }
}
