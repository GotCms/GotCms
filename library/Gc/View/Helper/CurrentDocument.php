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
 * @subpackage View\Helper
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\View\Helper;

use Gc\Document\Model as DocumentModel;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceManager;

/**
 * Retrieve document from id or url_key
 *
 * @category   Gc
 * @package    Library
 * @subpackage View\Helper
 * @example In view: $this->document('mypage/mysubpage'); or $this->document(1);
 */
class CurrentDocument extends AbstractHelper
{
    /**
     * Service manager
     *
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Set current document.
     *
     * @param ServiceManager $serviceManager Service Manager
     *
     * @return void
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Returns current document.
     *
     * @return \Gc\Document\Model
     */
    public function __invoke()
    {
        return $this->serviceManager->get('CurrentDocument');
    }
}
