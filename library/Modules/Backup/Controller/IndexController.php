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
 * @package    Modules
 * @subpackage Backup\Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Backup\Controller;

use Gc\Module\Controller\AbstractController;
use Backup\Model;
use Zend\Http\Headers;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

/**
 * IndexController
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Backup\Controller
 */
class IndexController extends AbstractController
{
    /**
     * Index action
     *
     * @return array
     */
    public function indexAction()
    {

    }

    /**
     * Download database as gzip
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function downloadDatabaseAction()
    {
        $what = $this->params()->fromPost('what');
        if (empty($what)) {
            return $this->redirect()->toRoute('module/backup');
        }

        $configuration = $this->getServiceLocator()->get('Config');
        switch($configuration['db']['driver']) {
            case 'pdo_pgsql':
                $model = new Model\Database\Pgsql();
                break;
            case 'pdo_mysql':
                $model = new Model\Database\Mysql();
                break;
        }

        $content  = $model->export($what);
        $filename = 'database-backup-' . date('Y-m-d-H-i-s') . '.sql.gz';

        $headers = new Headers();
        $headers->addHeaderLine('Pragma', 'public')
            ->addHeaderLine('Cache-control', 'must-revalidate, post-check=0, pre-check=0')
            ->addHeaderLine('Cache-control', 'private')
            ->addHeaderLine('Expires', -1)
            ->addHeaderLine('Content-Type', 'application/download')
            ->addHeaderLine('Content-Transfer-Encoding', 'binary')
            ->addHeaderLine('Content-Length', strlen($content))
            ->addHeaderLine('Content-Disposition', 'attachment; filename=' . $filename);

        $response = $this->getResponse();
        $response->setHeaders($headers);
        $response->setContent($content);

        return $response;
    }

    /**
     * Download files as gzip
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function downloadFilesAction()
    {
        $model    = new Model\Files();
        $content  = $model->export();
        $filename = 'files-backup-' . date('Y-m-d-H-i-s') . '.zip';

        $headers = new Headers();
        $headers->addHeaderLine('Pragma', 'public')
            ->addHeaderLine('Cache-control', 'must-revalidate, post-check=0, pre-check=0')
            ->addHeaderLine('Cache-control', 'private')
            ->addHeaderLine('Expires', -1)
            ->addHeaderLine('Content-Type', 'application/download')
            ->addHeaderLine('Content-Transfer-Encoding', 'binary')
            ->addHeaderLine('Content-Length', strlen($content))
            ->addHeaderLine('Content-Disposition', 'attachment; filename=' . $filename);

        $response = $this->getResponse();
        $response->setHeaders($headers);
        $response->setContent($content);

        return $response;
    }

    /**
     * Download files as gzip
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function downloadContentAction()
    {
        $what = $this->params()->fromPost('what');
        if (empty($what) or !is_array($what)) {
            return $this->redirect()->toRoute('module/backup');
        }

        $model    = new Model\Content($this->getServiceLocator());
        $content  = $model->export($what);
        $filename = 'content-backup-' . date('Y-m-d-H-i-s') . '.xml';

        $headers = new Headers();
        $headers->addHeaderLine('Pragma', 'public')
            ->addHeaderLine('Cache-control', 'must-revalidate, post-check=0, pre-check=0')
            ->addHeaderLine('Cache-control', 'private')
            ->addHeaderLine('Expires', -1)
            ->addHeaderLine('Content-Type', 'application/download')
            ->addHeaderLine('Content-Transfer-Encoding', 'binary')
            ->addHeaderLine('Content-Length', strlen($content))
            ->addHeaderLine('Content-Disposition', 'attachment; filename=' . $filename);

        $response = $this->getResponse();
        $response->setHeaders($headers);
        $response->setContent($content);

        return $response;
    }

    /**
     * Download files as gzip
     *
     * @return \Zend\Http\Response
     */
    public function uploadContentAction()
    {
        $file = $this->params()->fromFiles('upload');

        if (empty($file) or !isset($file['error']) or $file['error'] != UPLOAD_ERR_OK) {
            return $this->redirect()->toRoute('module/backup');
        }

        $dbAdapter = GlobalAdapterFeature::getStaticAdapter();
        $model     = new Model\Content($this->getServiceLocator());
        $resource  = $dbAdapter->getDriver()->getConnection()->getResource();

        $result = $model->import(file_get_contents($file['tmp_name']));
        if ($result === false) {
            $this->flashMessenger()->addSuccessMessage('File is not an xml');
            return $this->redirect()->toRoute('module/backup');
        }

        if (is_array($result)) {
            foreach ($result as $message) {
                $this->flashMessenger()->addErrorMessage($message);
            }
        }

        $this->flashMessenger()->addSuccessMessage('Content updated!');
        return $this->redirect()->toRoute('module/backup');
    }
}
