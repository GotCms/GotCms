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
 * @category Controller
 * @package  Statistics\Controller
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Statistics\Controller;

require_once('vendor/SVGGraph/SVGGraph.php');

use Gc\Mvc\Controller\Action,
    Gc\User\Visitor;

class IndexController extends Action
{
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $visitor_model = new Visitor();

        $settings = array(
          'back_colour' => '#FFF',  'stroke_colour' => '#000',
          'back_stroke_width' => 0, 'back_stroke_colour' => '#000',
          'axis_colour' => '#333',  'axis_overlap' => 1,
          'axis_font' => 'Verdana', 'axis_font_size' => 10,
          'grid_colour' => '#666',  'label_colour' => '#000',
          'pad_right' => 10,        'pad_left' => 10,
          'project_angle' => 45,    'minimum_grid_spacing' => 40
        );

        $colours = array(array('#656565','#959595'));

        $graph = new \SVGGraph(600, 400, $settings);
        $graph->colours = $colours;

        $visists_hours = $visitor_model->getVisitStats('HOUR');
        $visists_days = $visitor_model->getVisitStats('DAY');
        $visists_months = $visitor_model->getVisitStats('MONTH');

        $visistors_hours = $visitor_model->getVisitorStats('HOUR');
        $visistors_days = $visitor_model->getVisitorStats('DAY');
        $visistors_months = $visitor_model->getVisitorStats('MONTH');

        return array(
            'graph' => $graph,
            'visistsHours' => $visists_hours,
            'visistsDays' => $visists_days,
            'visistsMonths' => $visists_months,
            'visistorsHours' => $visistors_hours,
            'visistorsDays' => $visistors_days,
            'visistorsMonths' => $visistors_months,
        );
    }
}
