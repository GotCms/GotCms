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
namespace GcContent\Controller;

use GcContent\Filter\Translation as TranslationFilter;
use Gc\Core\Translator;
use Gc\Mvc\Controller\RestAction;

/**
 * Content controller
 *
 * @category   Gc_Application
 * @package    Content
 * @subpackage Controller
 */
class TranslationRestController extends RestAction
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'content', 'permission' => 'translation');

    /**
     * Get all translations
     *
     * @return array
     */
    public function getList()
    {
        $translator = new Translator();
        return array('translations' => $translator->getValues());
    }

    /**
     * Create translation
     *
     * @param array $data Data to used to create translation
     *
     * @return array
     */
    public function create($data)
    {
        $translationFilter = new TranslationFilter($this->getServiceLocator()->get('Config'));
        $translationFilter->setData($data);
        if ($translationFilter->isValid()) {
            $source = $translationFilter->getValue('source');
            $data   = array();
            foreach ($translationFilter->getValue('destination') as $destinationId => $destination) {
                if (empty($destination)) {
                    continue;
                }

                $data[$destinationId] = array('value' => $destination);
            }

            foreach ($translationFilter->getValue('locale') as $localeId => $locale) {
                if (empty($data[$localeId])) {
                    continue;
                }

                if (empty($locale)) {
                    unset($data[$localeId]);
                    continue;
                }

                $data[$localeId]['locale'] = $locale;
            }

            $this->flashMessenger()->addSuccessMessage('Translation saved !');
            $translator = new Translator();
            return array('translation' => $translator->setValue($source, $data));
        }

        return array('content' => 'Invalid data', 'errors' => $translationFilter->getMessages());
    }
}
