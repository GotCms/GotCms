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
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Content\Controller;

use Gc\Mvc\Controller\Action;
use Gc\Document\Collection as DocumentCollection;
use Content\Form;
use Gc\Component;
use Gc\Core\Translator;
use Zend\Json\Json;

/**
 * Translation controller
 *
 * @category   Gc_Application
 * @package    Content
 * @subpackage Controller
 */
class TranslationController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array $aclPage
     */
    protected $aclPage = array('resource' => 'Content', 'permission' => 'translation');

    /**
     * Initialize Media Controller
     *
     * @return void
     */
    public function init()
    {
        $documents = new DocumentCollection();
        $documents->load(0);

        $this->layout()->setVariable('treeview', Component\TreeView::render(array($documents)));

        $routes = array(
            'edit' => 'content/document/edit',
            'new' => 'content/document/create',
            'delete' => 'content/document/delete',
            'copy' => 'content/document/copy',
            'cut' => 'content/document/cut',
            'paste' => 'content/document/paste',
            'refresh' => 'content/document/refresh-treeview',
        );

        $arrayRoutes = array();
        foreach ($routes as $key => $route) {
            $arrayRoutes[$key] = $this->url()->fromRoute($route, array('id' => 'itemId'));
        }

        $this->layout()->setVariable('routes', Json::encode($arrayRoutes));
    }

    /**
     * Create Translation
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function createAction()
    {
        $translationForm = new Form\Translation();
        $translationForm->prepareForm($this->getServiceLocator()->get('Config'));
        $translationForm->setAttribute('action', $this->url()->fromRoute('content/translation/create'));

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $translationForm->setData($post->toArray());
            if (!$translationForm->isValid()) {
                $this->flashMessenger()->addErrorMessage('Invalid data sent !');
                $this->useFlashMessenger();
            } else {
                $source = $post->get('source');
                $data   = array();
                foreach ($post->get('destination') as $destinationId => $destination) {
                    $data[$destinationId] = array('value' => $destination);
                }

                foreach ($post->get('locale') as $localeId => $locale) {
                    if (empty($data[$localeId])) {
                        continue;
                    }

                    $data[$localeId]['locale'] = $locale;
                }

                $this->flashMessenger()->addSuccessMessage('Translation saved !');
                $translator = new Translator();
                $translator->setValue($source, $data);
                return $this->redirect()->toRoute('content/translation/create');
            }
        }

        return array('form' => $translationForm);
    }

    /**
     * List and edit translation
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $translationForm = new Form\Translation();
        $translationForm->prepareForm($this->getServiceLocator()->get('Config'));
        $translationForm->setAttribute('action', $this->url()->fromRoute('content/translation'));
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if (empty($post['source']) or empty($post['destination'])) {
                return $this->redirect()->toRoute('content/translation');
            }

            $translator = new Translator();
            foreach ($post['source'] as $sourceId => $source) {
                $translator->update(array('source' => $source), sprintf('id = %d', $sourceId));
                if (!empty($post['destination'][$sourceId])) {
                    $translator->setValue($sourceId, $post['destination'][$sourceId]);
                }
            }

            $this->generateCache();

            $this->flashMessenger()->addSuccessMessage('Translation saved !');
            return $this->redirect()->toRoute('content/translation');
        }

        $translator = new Translator();
        return array('form' => $translationForm, 'values' => $translator->getValues());
    }

    /**
     * Generate php array file as cache
     *
     * @return void
     */
    protected function generateCache()
    {
        $translator = new Translator();
        $values     = $translator->getValues();
        $data       = array();
        foreach ($values as $value) {
            if (empty($data[$value['locale']])) {
                $data[$value['locale']] = array();
            }

            $data[$value['locale']][$value['source']] = $value['destination'];
        }

        $translatePath   = GC_APPLICATION_PATH . '/data/translation/%s.php';
        $templateContent = file_get_contents(GC_APPLICATION_PATH . '/data/install/tpl/language.tpl.php');

        foreach ($data as $locale => $values) {
            file_put_contents(sprintf($translatePath, $locale), sprintf($templateContent, var_export($values, true)));
        }
    }
}
