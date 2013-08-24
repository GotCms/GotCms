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
 * @subpackage ActivityLog\Model
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace ActivityLog\Model;

use Gc\Db\AbstractTable;
use Gc\View\Renderer;
use Gc\View\Stream;
use Zend\EventManager;

/**
 * ActivityLog Event table
 *
 * @category   Gc_Library
 * @package    Modules
 * @subpackage ActivityLog\Model
 */
class Template extends AbstractTable
{
    /**
     * Renderer
     *
     * @var \Gc\View\Renderer
     */
    protected $renderer;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'activity_log_template';

    /**
     * Return all documents with Event(s)
     *
     * @param integer $id Event identifier
     *
     * @return array
     */
    public function getTemplate($id)
    {
        return $this->fetchRow($this->select(array('id' => (int) $id)));
    }

    /**
     * Return all documents with Event(s)
     *
     * @return array
     */
    public function getTemplates()
    {
        return $this->fetchAll($this->select());
    }

    /**
     * Render template from event params
     *
     * @param EventManager\Event $event    Event
     * @param string             $template Template
     *
     * @return array
     */
    public function render(EventManager\Event $event, $template)
    {
        if (empty($this->renderer)) {
            Stream::register();
            $this->renderer = new Renderer();
            $this->renderer->useStreamWrapper();
        }

        $name = 'activitylog.event';
        file_put_contents('zend.view://' . $name, $template);

        return $this->renderer->render($name, array('event' => $event));
    }
    /**
     * Render template from event params
     *
     * @param string  $content    Content
     * @param integer $templateId Template id
     * @param integer $userId     User id
     *
     * @return array
     */
    public function addEvent($content, $templateId, $userId)
    {
        $eventTable = new Event\Model;
        $eventTable->setUserId($userId);
        $eventTable->setTemplateId($templateId);
        $eventTable->setContent($content);
        $eventTable->save();
    }
}
