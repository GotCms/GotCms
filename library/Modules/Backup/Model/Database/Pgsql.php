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
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Modules\Backup\Model\Database;

use Gc\Db\AbstractTable;
use Gc\Document\Model as DocumentModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate\Expression;

/**
 * Blog comment table
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage Blog\Model
 */
class Pgsql extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'core_config_data';

    /**
     * Export function
     *
     * @param string $what Action
     *
     * @return string
     */
    public function export($what = 'structureanddata')
    {
        if (empty($what)) {
            $what = 'structureanddata';
        }

        $exe        = escapeshellcmd('/usr/bin/pg_dump');
        $parameters = $this->getAdapter()->getDriver()->getConnection()->getConnectionParameters();

        // Set environmental variables that pg_dump uses
        putenv('PGPASSWORD=' . $parameters['password']);
        putenv('PGUSER=' . $parameters['username']);
        putenv('PGDATABASE=' . $parameters['database']);
        if (!empty($parameters['hostname'])) {
            putenv('PGHOST=' . $parameters['hostname']);
        }

        if (!empty($parameters['port'])) {
            putenv('PGPORT=' . $parameters['port']);
        }

        //Prepare command
        $cmd  = $exe;
        $cmd .= ' --compress 9 --no-owner --disable-triggers';

        switch ($what) {
            case 'dataonly':
                $cmd .= ' --data-only';
                $cmd .= ' --column-inserts';
                break;
            case 'structureonly':
                $cmd .= ' --schema-only';
                $cmd .= ' --clean';
                break;
            case 'structureanddata':
                $cmd .= ' --column-inserts';
                $cmd .= ' --clean';
                break;
        }

        // Execute command and return the output
        ob_start();
        passthru($cmd . ' 2>/dev/null');

        return ob_get_clean();
    }
}
