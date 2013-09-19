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
 * @package    Library
 * @subpackage View\Helper
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\View\Helper;

use Gc\User\Model as UserModel;
use Zend\Authentication\AuthenticationService;
use Zend\View\Helper\AbstractHelper;

/**
 * Generate url with specific base path for cdn frontend stored in database.
 *
 * @category   Gc_Library
 * @package    Library
 * @subpackage View\Helper
 * @example In view: $this->cdn('path/to/file');
 */
class Admin extends AbstractHelper
{
    /**
     * Constructor
     *
     * @param AuthenticationService $auth Authentication service
     *
     * @return void
     */
    public function __construct(AuthenticationService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Retrieve the current admin
     *
     * @return UserModel|boolean
     */
    public function __invoke()
    {
        if ($this->auth->hasIdentity()) {
            return $this->auth->getIdentity();
        }

        return false;
    }
}
