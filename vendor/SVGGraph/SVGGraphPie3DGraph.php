<?php
/**
 * Copyright (C) 2010-2012 Graham Breach
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

require_once 'SVGGraphPieGraph.php';

class Pie3DGraph extends PieGraph {

  public function Draw()
  {
    // modify pad_bottom to make PieGraph do the hard work
    $pb = $this->pad_bottom;
    $this->pad_bottom += $this->depth;
    $this->Calc();
    $this->pad_bottom = $pb;
    return PieGraph::Draw();
  }

  /**
   * Override the parent to draw 3D slice
   */
  protected function GetSlice($angle_start, $angle_end, &$attr)
  {
    $x_start = $y_start = $x_end = $y_end = 0;
    $angle_start += $this->s_angle;
    $angle_end += $this->s_angle;
    $this->CalcSlice($angle_start, $angle_end, $x_start, $y_start,
      $x_end, $y_end);

    $outer = $angle_end - $angle_start > M_PI ? 1 : 0;
    $sweep = $this->reverse ? 0 : 1;
    $side_start = $this->reverse ? M_PI : M_PI * 2;
    $side_end = $this->reverse ? M_PI * 2 : M_PI;

    $path = '';
    $angle_start_lower = $this->LowerHalf($angle_start);
    $angle_end_lower = $this->LowerHalf($angle_end);
    if($angle_start_lower || $angle_end_lower || $outer) {
      if($angle_start_lower && $angle_end_lower && $outer) {
        // if this is a big slice with both sides at bottom, need 2 edges
        $path .= $this->GetEdge($angle_start, $side_end);
        $path .= $this->GetEdge($side_start, $angle_end);
      } else {
        // if an edge is in the top half, need to truncate to x-radius
        $angle_start_trunc = $angle_start_lower ? $angle_start : $side_start;
        $angle_end_trunc = $angle_end_lower ? $angle_end : $side_end;
        $path .= $this->GetEdge($angle_start_trunc, $angle_end_trunc);
      }
    }
    if((string)$x_start == (string)$x_end &&
      (string)$y_start == (string)$y_end) {
      $attr_path = array('d' => $path);
      $attr_ellipse = array(
        'cx' => $this->x_centre, 'cy' => $this->y_centre,
        'rx' => $this->radius_x, 'ry' => $this->radius_y
      );
      return $this->Element('g', $attr, NULL, 
        $this->Element('path', $attr_path) .
        $this->Element('ellipse', $attr_ellipse));
    } else {
      $outer = ($angle_end - $angle_start > M_PI ? 1 : 0);
      $sweep = ($this->reverse ? 0 : 1);
      $attr['d'] = $path . "M{$this->x_centre},{$this->y_centre} " .
        "L$x_start,$y_start A{$this->radius_x} {$this->radius_y} 0 " .
        "$outer,$sweep $x_end,$y_end z";
      return $this->Element('path', $attr);
    }
  }

  /**
   * Returns the path for an edge
   */
  protected function GetEdge($angle_start, $angle_end)
  {
    $x_start = $y_start = $x_end = $y_end = 0;
    $this->CalcSlice($angle_start, $angle_end, $x_start, $y_start,
      $x_end, $y_end);
    $y_end_depth = $y_end + $this->depth;

    $outer = 0; // edge is never > PI
    $sweep = $this->reverse ? 0 : 1;

    return "M$x_start,$y_start l0,{$this->depth} " .
      "A{$this->radius_x} {$this->radius_y} 0 " .
      "$outer,$sweep $x_end,$y_end_depth l0,-{$this->depth} ";
  }

  /**
   * Returns TRUE if the angle is in the lower half of the pie
   */
  protected function LowerHalf($angle)
  {
    $angle = fmod($angle, M_PI * 2);
    return ($this->reverse && $angle > M_PI && $angle < M_PI * 2) ||
      (!$this->reverse && $angle < M_PI && $angle > 0);
  }

}

