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
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\Backup\Controller;

use Gc\Module\Controller\AbstractController;
use Gc\Document\Model as DocumentModel;
use Gc\Registry;
use Modules\Backup\Model;
use Zend\Http\Headers;

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
     * Index action, list all documents with comments
     *
     * @return array
     */
    public function indexAction()
    {

    }

    /**
     * Download database as gzip
     *
     * @return array
     */
    public function downloadDatabaseAction()
    {
        $configuration = Registry::get('Configuration');
        switch($configuration['db']['driver']) {
            case 'pdo_pgsql':
                $model = new Model\Database\Pgsql();
                break;
            case 'pdo_mysql':
                $model = new Model\Database\Mysql();
                break;
        }

        $content  = $model->export($this->getRequest()->getPost()->get('what'));
        $filename = 'database-backup-' . date('Y-m-d') . '.sql.gz';

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
     * @return array
     */
    public function downloadFilesAction()
    {
        $model    = new Model\Files();
        $content  = $model->export();
        $filename = 'frontend-backup-' . date('Y-m-d') . '.zip';

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
}
