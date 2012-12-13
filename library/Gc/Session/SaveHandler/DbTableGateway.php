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
 * @category   Gc
 * @package    Library
 * @subpackage Session\SaveHandler
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Session\SaveHandler;

use Zend\Session\SaveHandler\DbTableGateway as ZendDbTableGateway;

/**
 * DB Table Gateway session save handler
 *
 * @category   Gc
 * @package    Library
 * @subpackage Session\SaveHandler
 */
class DbTableGateway extends ZendDbTableGateway
{
    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $rows = $this->tableGateway->select(array(
            $this->options->getIdColumn()   => $id,
            $this->options->getNameColumn() => $this->sessionName,
        ));

        if($row = $rows->current())
        {
            if($row->{$this->options->getModifiedColumn()} + $row->{$this->options->getLifetimeColumn()} > time())
            {
                return base64_decode($row->{$this->options->getDataColumn()});
            }

            $this->destroy($id);
        }

        return '';
    }

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return boolean
     */
    public function write($id, $data)
    {
        $data = array(
            $this->options->getModifiedColumn() => time(),
            $this->options->getDataColumn()     => base64_encode((string) $data),
        );

        $rows = $this->tableGateway->select(array(
            $this->options->getIdColumn()   => $id,
            $this->options->getNameColumn() => $this->sessionName,
        ));

        if($row = $rows->current())
        {
            return (bool) $this->tableGateway->update($data, array(
                $this->options->getIdColumn()   => $id,
                $this->options->getNameColumn() => $this->sessionName,
            ));
        }

        $data[$this->options->getLifetimeColumn()] = (int)$this->lifetime;
        $data[$this->options->getIdColumn()]       = $id;
        $data[$this->options->getNameColumn()]     = $this->sessionName;

        return (bool) $this->tableGateway->insert($data);
    }
}
