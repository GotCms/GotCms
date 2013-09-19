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
 * @package    Statistics
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */
namespace Statistics\Controller;

use Gc\Mvc\Controller\Action;
use Gc\User\Visitor;
use SVGGraph;

/**
 * Statistics controller
 *
 * @category   Gc_Application
 * @package    Statistics
 * @subpackage Controller
 */
class IndexController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'stats', 'permission' => 'all');

    /**
     * Display statistics for visitors
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {

        $visitorModel = new Visitor();

        $settings = array(
          'back_colour' => '#FFFFFF',
          'stroke_colour' => '#C2C2C2',
          'back_stroke_width' => 0,
          'back_stroke_colour' => '#C2C2C2',
          'axis_colour' => '#333',
          'axis_overlap' => 1,
          'axis_font' => 'Verdana',
          'axis_font_size' => 10,
          'grid_colour' => '#888888',
          'label_colour' => '#555555',
          'pad_right' => 10,
          'pad_left' => 10,
          'project_angle' => 45,
          'minimum_grid_spacing' => 40,
        );

        $graph          = new SVGGraph(600, 400, $settings);
        $graph->colours = array(array('#855D10','#855D10'));
        $data           = array();
        $array          = array('hours' => 'HOUR', 'days' => 'DAY', 'months' => 'MONTH', 'years' => 'YEAR');

        foreach ($array as $type => $sqlValue) {
            switch($type) {
                case 'hours':
                    $label = 'This day';
                    break;
                case 'days':
                    $label = 'This month';
                    break;
                case 'months':
                    $label = 'This year';
                    break;
                case 'years':
                    $label = 'All the time';
                    break;
            }

            $data[$type] = array(
                'label' => $label,
                'labels' => array(
                    'visitors' => 'Visitors',
                    'pagesviews' => 'Pages views',
                    'urlsviews' => 'Most urls views',
                    'referers' => 'Referers',
                ),
                'values' => array(
                    'visitors' => $visitorModel->getNbVisitors($sqlValue),
                    'pagesviews' => $visitorModel->getNbPagesViews($sqlValue),
                    'urlsviews' => $visitorModel->getUrlsViews($sqlValue),
                    'referers' => $visitorModel->getReferers($sqlValue),
                ),
            );
        }

        return array(
            'graph' => $graph,
            'groups' => $data,
        );
    }
}
