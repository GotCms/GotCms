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
 * @subpackage Blog\Model
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\Backup\Model\Database;

use Gc\Db\AbstractTable,
    Gc\Document\Model as DocumentModel,
    Zend\Db\Sql\Select,
    Zend\Db\Sql\Predicate\Expression;

/**
 * Blog comment table
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog\Model
 */
class Mysql extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'core_config_data';

    /**
     * Export function
     *
     * @param string $what
     * @return string
     */
    public function export($what = 'structureanddata')
    {
        if(empty($what))
        {
            $what = 'structureanddata';
        }

        $parameters = $this->getAdapter()->getDriver()->getConnection()->getConnectionParameters();

        $cmd = escapeshellcmd('/usr/bin/mysqldump');
        //Prepare command
        $cmd .= ' --user=' . escapeshellarg($parameters['username']);
        $cmd .= ' --password=' . escapeshellarg($parameters['password']);
        if(!empty($parameters['hostname']))
        {
            $cmd .= ' --host=' . escapeshellarg($parameters['hostname']);
        }

        if(!empty($parameters['port']))
        {
            $cmd .= ' --port=' . escapeshellarg($parameters['port']);
        }

        switch($what)
        {
            case 'dataonly':
                $cmd .= ' --no-create-info';
            break;

            case 'structureonly':
                $cmd .= ' --no-data';
            break;
        }

        $cmd .= ' ' . escapeshellarg($parameters['database']);

        ob_start();
        passthru($cmd . ' | gzip');

        return ob_get_clean();
    }
}
