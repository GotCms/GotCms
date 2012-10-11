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

require_once 'SVGGraphGridGraph.php';

abstract class ThreeDGraph extends GridGraph {

  // Number of data ranges
  private $depth = 1;
  private $depth_unit = 1;

  /**
   * Returns the projection angle in radians
   */
  protected function AngleRadians()
  {
    return deg2rad($this->project_angle);
  }

  /**
   * Converts x,y,z coordinates into flat x,y
   */
  protected function Project($x, $y, $z)
  {
    $a = $this->AngleRadians();
    $xp = $z * cos($a);
    $yp = $z * sin($a);
    return array($x + $xp, $y - $yp);
  }

  /**
   * Adjust axes for block spacing, returning the depth unit
   */
  private function Adjust3DAxes($count, &$x_len, &$y_len)
  {
    $a = $this->AngleRadians();

    $d = $this->depth;
    $u = $x_len / ($count + $d * cos($a));
    $c = $u * $d * cos($a);
    $d = $u * $d * sin($a);
    $x_len -= $c;
    $y_len -= $d;
    return $u;
  }

  /**
   * Adjust the axis sizes to account for the block depth
   */
  protected function GetAxes($ends, &$x_len, &$y_len)
  {
    if(!isset($this->g_width)) {
      $count = $ends['k_max'] - $ends['k_min'] + 1;
      $this->Adjust3DAxes($count, $x_len, $y_len);
    }
    return parent::GetAxes($ends, $x_len, $y_len);
  }

  /**
   * Calculates the sizes of the 3D axes and grid
   */
  protected function CalcAxes()
  {
    // calculate bar 
    $ends = $this->GetAxisEnds();
    $count = $ends['k_max'] - $ends['k_min'] + 1;
    $a = $this->AngleRadians();

    if(!$this->label_adjust_done)
      $this->LabelAdjustment($this->GetMaxValue(), $this->GetLongestKey());

    $this->g_width = $this->width - $this->pad_left - $this->pad_right;
    $this->g_height = $this->height - $this->pad_top - $this->pad_bottom;
    $this->depth_unit = $this->Adjust3DAxes($count, $this->g_width,
      $this->g_height);
    parent::CalcAxes();
  }


  /**
   * Draws the grid behind the bar / line graph
   */
  protected function Grid()
  {
    $this->CalcAxes();
    if(!$this->show_grid)
      return '';

    $this->CalcGrid();
    $x_w = $this->g_width;
    $y_h = $this->g_height;
    $xleft = $this->pad_left;
    $ybottom = $this->height - $this->pad_bottom;
    $h = $this->height - $this->pad_bottom - $this->pad_top;
    $w = $this->width - $this->pad_left - $this->pad_right;

    // move to depth
    $z = $this->depth * $this->depth_unit;
    list($xd,$yd) = $this->Project(0, 0, $z);

    $back = $subpath = $path_h = $path_v = '';
    $back_colour = $this->grid_back_colour;
    if(!empty($back_colour) && $back_colour != 'none') {
      $bpath = array(
        'd' => "M$xleft {$ybottom}v-{$y_h}l{$xd} {$yd}h{$x_w}v{$y_h}l" .
          -$xd . " " . -$yd . "z",
        'fill' => $back_colour
      );
      $back = $this->Element('path', $bpath);
    }
    if($this->show_grid_subdivisions) {
      $subpath_h = $subpath_v = '';
      foreach($this->y_subdivs as $y) 
        $subpath_v .= "M$xleft {$y}l$xd {$yd}l$x_w 0";
      foreach($this->x_subdivs as $x) 
        $subpath_h .= "M$x {$ybottom}l$xd {$yd}l0 " . -$y_h;
      if($subpath_h != '' || $subpath_v != '') {
        $colour_h = $this->GetFirst($this->grid_subdivision_colour_h,
          $this->grid_subdivision_colour, $this->grid_colour_h,
          $this->grid_colour);
        $colour_v = $this->GetFirst($this->grid_subdivision_colour_v,
          $this->grid_subdivision_colour, $this->grid_colour_v,
          $this->grid_colour);
        $dash_h = $this->GetFirst($this->grid_subdivision_dash_h,
          $this->grid_subdivision_dash, $this->grid_dash_h, $this->grid_dash);
        $dash_v = $this->GetFirst($this->grid_subdivision_dash_v,
          $this->grid_subdivision_dash, $this->grid_dash_v, $this->grid_dash);

        if($dash_h == $dash_v && $colour_h == $colour_v) {
          $subpath = $this->GridLines($subpath_h . $subpath_v, $colour_h,
            $dash_h, 'none');
        } else {
          $subpath = $this->GridLines($subpath_h, $colour_h, $dash_h, 'none') .
            $this->GridLines($subpath_v, $colour_v, $dash_v, 'none');
        }
      }
    }

    // start with axis lines
    $path = "M$xleft {$ybottom}l$x_w 0M$xleft {$ybottom}l0 " . -$y_h;
    foreach($this->y_points as $y)
      $path_v .= "M$xleft {$y}l$xd {$yd}l$x_w 0";
    foreach($this->x_points as $x)
      $path_h .= "M$x {$ybottom}l$xd {$yd}l0 " . -$y_h;

    $colour_h = $this->GetFirst($this->grid_colour_h, $this->grid_colour);
    $colour_v = $this->GetFirst($this->grid_colour_v, $this->grid_colour);
    $dash_h = $this->GetFirst($this->grid_dash_h, $this->grid_dash);
    $dash_v = $this->GetFirst($this->grid_dash_v, $this->grid_dash);

    if($dash_h == $dash_v && $colour_h == $colour_v) {
      $path = $this->GridLines($path_v . $path_h, $colour_h, $dash_h, 'none');
    } else {
      $path = $this->GridLines($path_h, $colour_h, $dash_h, 'none') .
        $this->GridLines($path_v, $colour_v, $dash_v, 'none');
    }

    return $back . $subpath . $path;
  }

  /**
   * clamps a value to the grid boundaries
   */
  protected function ClampVertical($val)
  {
    return max($this->height - $this->pad_bottom - $this->g_height,
      min($this->height - $this->pad_bottom, $val));
  }

  protected function ClampHorizontal($val)
  {
    return max($this->width - $this->pad_right - $this->g_width,
      min($this->width - $this->pad_right, $val));
  }

  /**
   * Figure out how many bars there are
   */
  protected function GetHorizontalDivision()
  {
    if(!is_numeric($this->axis_min_h) && !is_numeric($this->axis_max_h))
      return $this->GetHorizontalCount();
    $start = !is_numeric($this->axis_min_h) ? $this->GetMinKey() :
      $this->axis_min_h;
    $end = !is_numeric($this->axis_max_h) ? $this->GetMaxKey() :
      $this->axis_max_h;
    return $end - $start + 1;
  }

  /**
   * Returns the path for a guideline, and sets dimensions of the straight bit
   */
  protected function GuidelinePath($axis, $value, $depth, &$x, &$y, &$w, &$h)
  {
    if($depth == SVGG_GUIDELINE_ABOVE)
      return parent::GuidelinePath($axis, $value, $depth, $x, $y, $w, $h);

    $y_axis_pos = $this->height - $this->pad_bottom - $this->y0;
    $x_axis_pos = $this->pad_left + $this->x0;
    $z = $this->depth * $this->depth_unit;
    list($xd,$yd) = $this->Project(0, 0, $z);

    if($axis == 'x') {
      $x1 = $x_axis_pos + ($value * $this->bar_unit_width);
      $y1 = $y_axis_pos;
      $x = $xd + $x1;
      $y = $this->pad_top;
      $w = 0;
      if($h == 0) {
        $h = $this->g_height;
      } elseif($h < 0) {
        $h = -$h;
        return "M$x {$y}v$h";
      } else {
        $y = $this->height - $this->pad_bottom + $yd - $h;
      }
    } else {
      $x1 = $x_axis_pos;
      $y1 = $y_axis_pos - ($value * $this->bar_unit_height);
      $x = $this->pad_left + $xd;
      $y = $yd + $y1;
      $h = 0;
      if($w == 0) {
        $w = $this->g_width;
      } elseif($w < 0) {
        $w = -$w;
        $x = $this->pad_left + $xd + $this->g_width - $w;
        return "M$x {$y}h$w";
      }
    }
    return "M{$x} {$y}l{$w} {$h}M{$x1} {$y1} l{$xd} {$yd}";
  }

}

