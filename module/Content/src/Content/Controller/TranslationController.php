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

use Content\Form;
use Gc\Core\Translator;
use Zend\Http\Headers;
use Exception;
use ZipArchive;
use SplFileObject;

/**
 * Translation controller
 *
 * @category   Gc_Application
 * @package    Content
 * @subpackage Controller
 */
class TranslationController extends AbstractController
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'content', 'permission' => 'translation');

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

            $translator->generateCache();

            $this->flashMessenger()->addSuccessMessage('Translation saved !');
            return $this->redirect()->toRoute('content/translation');
        }

        $translator = new Translator();
        return array('form' => $translationForm, 'values' => $translator->getValues());
    }

    /**
     * Upload a file to the server
     *
     * @return \Zend\Http\Response
     */
    public function uploadAction()
    {
        if (empty($_FILES['upload'])) {
            $this->flashMessenger()->addErrorMessage('Can not upload translations');
            return $this->redirect()->toRoute('content/translation');
        }

        $translator = new Translator();
        foreach ($_FILES['upload']['tmp_name'] as $idx => $tmpName) {
            if ($_FILES['upload']['error'][$idx] != UPLOAD_ERR_OK) {
                continue;
            }

            $fileName = $_FILES['upload']['name'][$idx];
            switch ($_FILES['upload']['type'][$idx]) {
                case 'text/csv':
                    try {
                        $locale = str_replace('.csv', '', $fileName);

                        $file = new SplFileObject($tmpName);
                        $file->setFlags(SplFileObject::READ_CSV);
                        foreach ($file as $row) {
                            if (empty($row[0])) {
                                continue;
                            }

                            list($source, $value) = $row;
                            $this->saveTranslation($translator, $locale, $source, $value);
                        }
                    } catch (Exception $e) {
                        $this->flashMessenger()->addErrorMessage($e->getMessage());
                        return $this->redirect()->toRoute('content/translation');
                    }

                    $this->flashMessenger()->addSuccessMessage(sprintf('Translations in %s are updated', $fileName));

                    break;
                case 'text/php':
                case 'application/x-php':
                    try {
                        $locale  = str_replace('.php', '', $fileName);
                        $content = str_replace(
                            array(
                                '<?php',
                                '<?',
                                '?>'
                            ),
                            array(
                                '',
                                '',
                                '',
                            ),
                            file_get_contents($tmpName)
                        );
                        if (!$data = @eval($content) or !is_array($data)) {
                            throw new Exception(sprintf('File %s cannot be read', $fileName));
                        }

                        foreach ($data as $source => $value) {
                            $this->saveTranslation($translator, $locale, $source, $value);
                        }
                    } catch (Exception $e) {
                        $this->flashMessenger()->addErrorMessage($e->getMessage());
                        return $this->redirect()->toRoute('content/translation');
                    }

                    $translator->generateCache();
                    $this->flashMessenger()->addSuccessMessage(sprintf('Translations in %s are updated', $fileName));
                    break;
            }
        }

        return $this->redirect()->toRoute('content/translation');
    }

    /**
     * Save translation
     *
     * @param Translator $translator Translator
     * @param string     $locale     Locale
     * @param string     $source     Source
     * @param string     $value      Value
     *
     * @return boolean
     */
    protected function saveTranslation(Translator $translator, $locale, $source, $value)
    {
        return $translator->setValue(
            $source,
            array(
                array(
                    'locale' => $locale,
                    'value' => $value
                )
            )
        );
    }

    /**
     * Send a file to the browser
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function downloadAction()
    {
        $translator  = new Translator();
        $values      = $translator->getValues();
        $zip         = new ZipArchive;
        $tmpFilename = tempnam(sys_get_temp_dir(), 'zip');
        $res         = $zip->open($tmpFilename, ZipArchive::CREATE);
        if ($res === true) {
            $locales = array();
            foreach ($values as $value) {
                if (!isset($locales[$value['locale']])) {
                    $locales[$value['locale']] = array();
                }

                $locales[$value['locale']][] = sprintf(
                    '"%s","%s"',
                    str_replace('"', '"""', $value['source']),
                    str_replace('"', '"""', $value['destination'])
                );
            }

            foreach ($locales as $locale => $content) {
                $zip->addFromString($locale . '.csv', implode(PHP_EOL, $content));
            }

            $zip->close();
            $content  = file_get_contents($tmpFilename);
            $filename = 'translations.zip';
            unlink($tmpFilename);
        }

        if (empty($content) or empty($filename)) {
            $this->flashMessenger()->addErrorMessage('Can not save translations');
            return $this->redirect()->toRoute('content/translation');
        }

        $headers = new Headers();
        $headers->addHeaderLine('Pragma', 'public')
            ->addHeaderLine('Cache-control', 'must-revalidate, post-check=0, pre-check=0')
            ->addHeaderLine('Cache-control', 'private')
            ->addHeaderLine('Expires', -1)
            ->addHeaderLine('Content-Type', 'application/octet-stream')
            ->addHeaderLine('Content-Transfer-Encoding', 'binary')
            ->addHeaderLine('Content-Length', strlen($content))
            ->addHeaderLine('Content-Disposition', 'attachment; filename=' . $filename);

        $response = $this->getResponse();
        $response->setHeaders($headers);
        $response->setContent($content);

        return $response;
    }
}
