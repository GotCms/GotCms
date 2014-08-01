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
 * @package    GcConfig
 * @subpackage Filter
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcConfig\Filter;

use Gc\InputFilter\AbstractInputFilter;
use Zend\Db\Adapter\Adapter;

/**
 * User form
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Filter
 */
class User extends AbstractInputFilter
{
    /**
     * Initialize User filter
     *
     * @param Adapter $dbAdapter Database Adapter
     *
     * @return void
     */
    public function __construct(Adapter $dbAdapter)
    {
        $this->add(
            array(
                'name' => 'email',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                    array('name' => 'email_address'),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'login',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                    array(
                        'name' => 'db\\no_record_exists',
                        'options' => array(
                            'table' => 'user',
                            'field' => 'login',
                            'adapter' => $dbAdapter,
                        ),
                    ),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'lastname',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'firstname',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'user_acl_role_id',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'active',
                'allow_empty' => true,
            )
        );
    }

    /**
     * Set if yes or no password is required when user click on Save
     *
     * @return User
     */
    public function passwordRequired()
    {
        $this->add(
            array(
                'name' => 'password',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            )
        );

        $this->add(
            array(
                'name' => 'password_confirm',
                'required' => true,
                'validators' => array(
                    array('name' => 'not_empty'),
                ),
            )
        );

        return $this;
    }
}
