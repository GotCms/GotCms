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
 * @subpackage Core
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Core\Updater\Adapter;

/**
 * Get and set config data
 *
 * @category   Gc
 * @package    Library
 * @subpackage Core
 */
class Git extends AbstractAdapter
{
    /**
     * Update
     *
     * @return boolean
     */
    public function update()
    {
        putenv('PATH=' . getenv('PATH') . ':' . GC_APPLICATION_PATH);
        exec('git fetch --tags 2>&1', $output);
        $this->addMessage(implode(PHP_EOL, $output));

        return true;
    }

    /**
     * Upgrade
     *
     * @return boolean
     */
    public function upgrade()
    {
        putenv('PATH=' . getenv('PATH') . ':' . GC_APPLICATION_PATH);
        exec('git checkout ' . $this->getLatestVersion() . ' 2>&1', $output);
        $this->addMessage(implode(PHP_EOL, $output));

        return true;
    }

    /**
     * Upgrade
     *
     * @param string $version Version to checkout
     *
     * @return boolean
     */
    public function rollback($version)
    {
        putenv('PATH=' . getenv('PATH') . ':' . GC_APPLICATION_PATH);
        exec('git checkout ' . $version . ' 2>&1', $output);
        $this->addMessage(implode(PHP_EOL, $output));

        return true;
    }
}
