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
 * @category    Gc
 * @package     Library
 * @subpackage  User
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\User;

use Gc\Db\AbstractTable,
    Gc\Component\IterableInterface,
    Zend\Db\Sql\Select;
/**
 * Collection of User Model
 */
class Collection extends AbstractTable implements IterableInterface
{
    /**
     * Table name
     * @var string
     */
    protected $_name = 'user';

    /**
     * Initiliaze User collection
     * @return void
     */
    public function init()
    {
        $this->setUsers();
    }

    /**
     * Get users
     * @return array of Gc\User\Model
     */
    public function getUsers()
    {
        return $this->getData('users');
    }

    /**
     * Set users collection
     * @return void
     */
    private function setUsers()
    {
        $select = $this->select(function(Select $select)
        {
            $select->order('lastname');
        });

        $rows = $this->fetchAll($select);
        $users = array();
        foreach($rows as $row)
        {
            $users[] = Model::fromArray((array)$row);
        }

        $this->setData('users', $users);
    }

    /** (non-PHPdoc)
     * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getName()
     */
    public function getName()
    {
        return 'Users';
    }

    /** (non-PHPdoc)
     * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getChildren()
     */
    public function getChildren()
    {
        return $this->getUsers();
    }
    /** (non-PHPdoc)
     * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getParent()
     */
    public function getParent()
    {
        return FALSE;
    }

    /** (non-PHPdoc)
     * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getId()
     */
    public function getId()
    {
        return FALSE;
    }

    /** (non-PHPdoc)
     * @see include/Es/Interfaces/Gc\Component\IterableInterfaces#getTreeViewId()
     */
    public function getIterableId()
    {
        return 'users';
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getUrl()
     */
    public function getUrl()
    {
        return '';
    }

    /** (non-PHPdoc)
     * @see include \Gc\Component\IterableInterface#getIcon()
     */
    public function getIcon()
    {
        return 'folder';
    }
}
