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

require_once 'SVGGraphPointGraph.php';
require_once 'SVGGraphMultiGraph.php';

/**
 * MultiLineGraph - joined line, with axes and grid
 */
class MultiLineGraph extends PointGraph {

  protected $multi_graph;
  private $line_styles = array();
  private $fill_styles = array();

  public function Draw()
  {
    $body = $this->Grid() . $this->Guidelines(SVGG_GUIDELINE_BELOW);

    $plots = '';
    $y_axis_pos = $this->height - $this->pad_bottom - $this->y0;
    $y_bottom = min($y_axis_pos, $this->height - $this->pad_bottom);

    $ccount = count($this->colours);
    $chunk_count = count($this->values);
    if(!$this->AssociativeKeys())
      sort($this->multi_graph->all_keys, SORT_NUMERIC);
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


      foreach($this->multi_graph->all_keys as $key) {
        $value = $this->multi_graph->GetValue($key, $i);
        $point_pos = $this->GridPosition($key, $bnum);
        if(!is_null($value) && !is_null($point_pos)) {
          $x = $point_pos;
          $y = $y_axis_pos - ($value * $this->bar_unit_height);

          if($fill && $path == '')
            $fillpath = "M$x {$y_bottom}L";
          $path .= "$cmd$x $y ";
          $fillpath .= "$x $y ";

          // no need to repeat same L command
          $cmd = $cmd == 'M' ? 'L' : '';
          $this->AddMarker($x, $y, $key, $value, NULL, $i);
        }
        ++$bnum;
      }

      if($path != '') {
        $attr['d'] = $path;
        $attr['stroke'] = $this->GetColour($i % $ccount, true);
        $graph_line = $this->Element('path', $attr);
        $fill_style = null;

        if($fill) {
          $opacity = $this->multi_graph->Option($this->fill_opacity, $i);
          $fillpath .= "L$x {$y_bottom}z";
          $fill_style = array(
            'fill' => $this->GetColour($i % $ccount),
            'd' => $fillpath,
            'stroke' => $attr['fill'],
          );
          if($opacity < 1)
            $fill_style['opacity'] = $opacity;
          $graph_line = $this->Element('path', $fill_style) . $graph_line;
        }
        $plots .= $graph_line;

        unset($attr['d']);
        $this->AddLineStyle($attr, $fill_style);
      }
    }

    $group = array();
    $this->ClipGrid($group);
    $body .= $this->Element('g', $group, NULL, $plots);
    $body .= $this->Guidelines(SVGG_GUIDELINE_ABOVE);
    $body .= $this->Axes();
    $body .= $this->CrossHairs();
    $body .= $this->DrawMarkers();
    return $body;
  }

  /**
   * Requires at least two values
   */
  protected function CheckValues(&$values)
  {
    parent::CheckValues($values);

    if($this->GetHorizontalCount() < 2)
      throw new Exception('Not enough values for line graph');
  }

  /**
   * Return line and marker for legend
   */
  protected function DrawLegendEntry($set, $x, $y, $w, $h)
  {
    if(!array_key_exists($set, $this->line_styles))
      return '';

    $marker = parent::DrawLegendEntry($set, $x, $y, $w, $h);

    $h1 = $h/2;
    $y += $h1;
    $line = $this->line_styles[$set];
    $line['d'] = "M$x {$y}l$w 0";
    $graph_line = $this->Element('path', $line);
    if($this->fill_under) {
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

  protected function AddLineStyle($style, $fill_style = null)
  {
    $this->line_styles[] = $style;
    $this->fill_styles[] = $fill_style;
  }
}

