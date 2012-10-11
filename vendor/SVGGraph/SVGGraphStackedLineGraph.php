<?php
/**
 * Copyright (C) 2012 Graham Breach
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

require_once 'SVGGraphPointGraph.php';
require_once 'SVGGraphMultiGraph.php';
require_once 'SVGGraphMultiLineGraph.php';

/**
 * StackedLineGraph - multiple joined lines with values added together
 */
class StackedLineGraph extends MultiLineGraph {

  protected $legend_reverse = true;

  public function Draw()
  {
    $body = $this->Grid() . $this->Guidelines(SVGG_GUIDELINE_BELOW);

    $plots = array();
    $y_axis_pos = $this->height - $this->pad_bottom - $this->y0;
    $y_bottom = min($y_axis_pos, $this->height - $this->pad_bottom);

    $ccount = count($this->colours);
    $chunk_count = count($this->values);
    if(!$this->AssociativeKeys())
      sort($this->multi_graph->all_keys, SORT_NUMERIC);
    $stack = array();
    for($i = 0; $i < $chunk_count; ++$i) {
      $bnum = 0;
      $cmd = 'M';
      $path = $fillpath = '';
      $attr = array('fill' => 'none');
      $fill = $this->multi_graph->Option($this->fill_under, $i);
      $dash = $this->multi_graph->Option($this->line_dash, $i);
      $stroke_width = 
        $this->multi_graph->Option($this->line_stroke_width, $i);
      if(!empty($dash))
        $attr['stroke-dasharray'] = $dash;
      $attr['stroke-width'] = $stroke_width <= 0 ? 1 : $stroke_width;

      $bottom = array();
      $point_count = 0;
      foreach($this->multi_graph->all_keys as $key) {
        $value = $this->multi_graph->GetValue($key, $i);
        $point_pos = $this->GridPosition($key, $bnum);
        if(!isset($stack[$key]))
          $stack[$key] = 0;
        if(!is_null($point_pos)) {
          $bottom[$point_pos] = $stack[$key];
          $x = $point_pos;
          $y_size = ($stack[$key] + $value) * $this->bar_unit_height;

          $y = $y_axis_pos - $y_size;
          $stack[$key] += $value;

          $path .= "$cmd$x $y ";
          if($fill && $fillpath == '')
            $fillpath = "M$x {$y}L";
          else
            $fillpath .= "$x $y ";

          // no need to repeat same L command
          $cmd = $cmd == 'M' ? 'L' : '';
          if(!is_null($value)) {
            $this->AddMarker($x, $y, $key, $value, NULL, $i);
            ++$point_count;
          }
        }
        ++$bnum;
      }

      if($point_count > 0) {
        $attr['d'] = $path;
        $attr['stroke'] = $this->GetColour($i % $ccount, true);
        $graph_line = $this->Element('path', $attr);
        $fill_style = null;

        if($fill) {
          // complete the fill area with the previous stack total
          $cmd = 'L';
          $opacity = $this->multi_graph->Option($this->fill_opacity, $i);
          $bpoints = array_reverse($bottom, TRUE);
          foreach($bpoints as $x => $pos) {
            $y = $y_axis_pos - ($pos * $this->bar_unit_height);
            $fillpath .= "$x $y ";
          }
          $fillpath .= 'z';
          $fill_style = array(
            'fill' => $this->GetColour($i % $ccount),
            'd' => $fillpath,
            'stroke' => $attr['fill'],
          );
          if($opacity < 1)
            $fill_style['opacity'] = $opacity;
          $graph_line = $this->Element('path', $fill_style) . $graph_line;
        }

        $plots[] = $graph_line;
        unset($attr['d']);
        $this->AddLineStyle($attr, $fill_style);
      }
    }

    $group = array();
    $this->ClipGrid($group);

    $plots = array_reverse($plots);
    $body .= $this->Element('g', $group, NULL, implode($plots));
    $body .= $this->Guidelines(SVGG_GUIDELINE_ABOVE);
    $body .= $this->Axes();
    $body .= $this->CrossHairs();
    $body .= $this->DrawMarkers();
    return $body;
  }


  /**
   * Returns the maximum value
   */
  protected function GetMaxValue()
  {
    return $this->multi_graph->GetMaxSumValue();
  }

  /**
   * Returns the minimum value
   */
  protected function GetMinValue()
  {
    return $this->multi_graph->GetMinSumValue();
  }

}

