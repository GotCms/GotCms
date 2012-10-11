<?php
/**
 * Copyright (C) 2011-2012 Graham Breach
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * For more information, please contact <graham@goat1000.com>
 */

require_once 'SVGGraphRadarGraph.php';
require_once 'SVGGraphMultiGraph.php';

/**
 * MultiRadarGraph - multiple radar graphs on one plot
 */
class MultiRadarGraph extends RadarGraph {

  protected $multi_graph;
  private $line_styles = array();
  private $fill_styles = array();

  public function Draw()
  {
    $body = $this->Grid();

    $plots = '';
    $ccount = count($this->colours);
    $chunk_count = count($this->values);
    if(!$this->AssociativeKeys())
      sort($this->multi_graph->all_keys, SORT_NUMERIC);
    for($i = 0; $i < $chunk_count; ++$i) {
      $bnum = 0;
      $cmd = 'M';
      $path = '';
      $attr = array('fill' => 'none');
      $fill = $this->multi_graph->Option($this->fill_under, $i);
      $dash = $this->multi_graph->Option($this->line_dash, $i);
      $stroke_width = 
        $this->multi_graph->Option($this->line_stroke_width, $i);
      $fill_style = null;
      if($fill) {
        $attr['fill'] = $this->GetColour($i % $ccount);
        $fill_style = array('fill' => $attr['fill']);
        $opacity = $this->multi_graph->Option($this->fill_opacity, $i);
        if($opacity < 1.0) {
          $attr['fill-opacity'] = $opacity;
          $fill_style['fill-opacity'] = $opacity;
        }
      }
      if(!is_null($dash))
        $attr['stroke-dasharray'] = $dash;
      $attr['stroke-width'] = $stroke_width <= 0 ? 1 : $stroke_width;


      foreach($this->multi_graph->all_keys as $key) {
        $value = $this->multi_graph->GetValue($key, $i);
        $point_pos = $this->GridPosition($key, $bnum);
        if(!is_null($value) && !is_null($point_pos)) {
          $val = $this->y0 + $value * $this->bar_unit_height;
          $angle = $this->arad + $point_pos / $this->g_height;
          $x = $this->xc + ($val * sin($angle));
          $y = $this->yc + ($val * cos($angle));

          $path .= "$cmd$x $y ";

          // no need to repeat same L command
          $cmd = $cmd == 'M' ? 'L' : '';
          $this->AddMarker($x, $y, $key, $value, NULL, $i);
        }
        ++$bnum;
      }

      if($path != '') {
        $path .= "z";
        $attr['d'] = $path;
        $attr['stroke'] = $this->GetColour($i % $ccount, true);
        $plots .= $this->Element('path', $attr);
        unset($attr['d']);
        $this->AddLineStyle($attr, $fill_style);
      }
    }

    $group = array();
    $this->ClipGrid($group);
    $body .= $this->Element('g', $group, NULL, $plots);
    $body .= $this->Axes();
    $body .= $this->CrossHairs();
    $body .= $this->DrawMarkers();
    return $body;
  }

  /**
   * Slightly less restrictive than the radar graph
   */
  protected function CheckValues(&$values)
  {
    GridGraph::CheckValues($values);

    if($this->GetHorizontalCount() < 2)
      throw new Exception('Not enough values for line graph');
    if($this->multi_graph->GetMinValue() < 0)
      throw new Exception('Negative value for radar graph');
  }

  /**
   * Return line and marker for legend
   */
  protected function DrawLegendEntry($set, $x, $y, $w, $h)
  {
    if(!array_key_exists($set, $this->line_styles))
      return '';

    $marker = PointGraph::DrawLegendEntry($set, $x, $y, $w, $h);

    $h1 = $h/2;
    $y += $h1;
    $line = $this->line_styles[$set];
    $line['d'] = "M$x {$y}l$w 0";
    $graph_line = $this->Element('path', $line);
    if($this->fill_under && !empty($this->fill_styles[$set])) {
      $fill = $this->fill_styles[$set];
      $fill['d'] = "M$x {$y}l$w 0 0 $h1 -$w 0z";
      $graph_line = $this->Element('path', $fill) . $graph_line;
    }

    return $graph_line . $marker;
  }

  /**
   * construct multigraph
   */
  public function Values($values)
  {
    parent::Values($values);
    $this->multi_graph = new MultiGraph($this->values, $this->force_assoc);
  }

  /**
   * The horizontal count is reduced by one
   */
  protected function GetHorizontalCount()
  {
    return $this->multi_graph->KeyCount();
  }

  /**
   * Returns the maximum value
   */
  protected function GetMaxValue()
  {
    return $this->multi_graph->GetMaxValue();
  }

  /**
   * Returns the minimum value
   */
  protected function GetMinValue()
  {
    return $this->multi_graph->GetMinValue();
  }

  /**
   * Returns the key from the MultiGraph
   */
  protected function GetKey($index)
  {
    return $this->multi_graph->GetKey($index);
  }

  protected function GetMaxKey()
  {
    return $this->multi_graph->GetMaxKey();
  }

  protected function GetMinKey()
  {
    return $this->multi_graph->GetMinKey();
  }

  protected function AddLineStyle($style, $fill_style)
  {
    $this->line_styles[] = $style;
    $this->fill_styles[] = $fill_style;
  }
}

