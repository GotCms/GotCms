<?php
/**
 * Copyright (C) 2009-2012 Graham Breach
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

/**
 * LineGraph - joined line, with axes and grid
 */
class LineGraph extends PointGraph {

  private $line_style;
  private $fill_style;

  public function Draw()
  {
    $body = $this->Grid() . $this->Guidelines(SVGG_GUIDELINE_BELOW);

    $attr = array('stroke' => $this->stroke_colour, 'fill' => 'none');
    $dash = is_array($this->line_dash) ?
      $this->line_dash[0] : $this->line_dash;
    $stroke_width = is_array($this->line_stroke_width) ?
      $this->line_stroke_width[0] : $this->line_stroke_width;
    if(!empty($dash))
      $attr['stroke-dasharray'] = $dash;
    $attr['stroke-width'] = $stroke_width <= 0 ? 1 : $stroke_width;

    $bnum = 0;
    $cmd = 'M';
    $y_axis_pos = $this->height - $this->pad_bottom - $this->y0;
    $y_bottom = min($y_axis_pos, $this->height - $this->pad_bottom);

    $path = $fillpath = '';
    $values = $this->GetValues();
    foreach($values as $key => $value) {
      $point_pos = $this->GridPosition($key, $bnum);
      if(!is_null($value) && !is_null($point_pos)) {
        $x = $point_pos;
        $y = $y_axis_pos - ($value * $this->bar_unit_height);

        if($this->fill_under && $path == '')
          $fillpath = "M$x {$y_bottom}L";
        $path .= "$cmd$x $y ";
        $fillpath .= "$x $y ";

        // no need to repeat same L command
        $cmd = $cmd == 'M' ? 'L' : '';
        $this->AddMarker($x, $y, $key, $value);
      }
      ++$bnum;
    }

    $this->line_style = $attr;
    $attr['d'] = $path;
    $graph_line = $this->Element('path', $attr);

    if($this->fill_under) {
      $attr['fill'] = $this->GetColour(0);
      if($this->fill_opacity < 1.0)
        $attr['fill-opacity'] = $this->fill_opacity;
      $fillpath .= "L$x {$y_bottom}z";
      $attr['d'] = $fillpath;
      $attr['stroke'] = 'none';
      unset($attr['stroke-dasharray'], $attr['stroke-width']);
      $this->fill_style = $attr;
      $graph_line = $this->Element('path', $attr) . $graph_line;
    }

    $group = array();
    $this->ClipGrid($group);
    $body .= $this->Element('g', $group, NULL, $graph_line);

    $body .= $this->Guidelines(SVGG_GUIDELINE_ABOVE);
    $body .= $this->Axes();
    $body .= $this->CrossHairs();
    $body .= $this->DrawMarkers();
    return $body;
  }

  protected function CheckValues(&$values)
  {
    parent::CheckValues($values);

    if(count($values[0]) <= 1)
      throw new Exception('Not enough values for line graph');
  }

  /**
   * Return line and marker for legend
   */
  protected function DrawLegendEntry($set, $x, $y, $w, $h)
  {
    // single line graph only supports one set
    if($set > 0)
      return '';

    $marker = parent::DrawLegendEntry($set, $x, $y, $w, $h);

    $h1 = $h/2;
    $y += $h1;
    $line = $this->line_style;
    $line['d'] = "M$x {$y}l$w 0";
    $graph_line = $this->Element('path', $line);
    if($this->fill_under) {
      $fill = $this->fill_style;
      $fill['d'] = "M$x {$y}l$w 0 0 $h1 -$w 0z";
      $graph_line = $this->Element('path', $fill) . $graph_line;
    }
    return $graph_line . $marker;
  }

}

