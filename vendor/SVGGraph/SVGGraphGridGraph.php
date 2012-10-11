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

require_once 'SVGGraphAxis.php';
require_once 'SVGGraphAxisFixed.php';

define("SVGG_GUIDELINE_ABOVE", 1);
define("SVGG_GUIDELINE_BELOW", 0);

abstract class GridGraph extends Graph {

  protected $bar_unit_width = 0;
  protected $x0;
  protected $y0;
  protected $y_points;
  protected $x_points;

  /**
   * Set to true for horizontal graphs
   */
  protected $flip_axes = false;

  /**
   *  Set to true for block-based labelling
   */
  protected $label_centre = false;

  protected $g_width = null;
  protected $g_height = null;
  protected $uneven_x = false;
  protected $uneven_y = false;
  protected $label_adjust_done = false;
  protected $axes_calc_done = false;
  protected $sub_x;
  protected $sub_y;
  protected $guidelines = array();
  protected $min_guide = array('x' => null, 'y' => null);
  protected $max_guide = array('x' => null, 'y' => null);

  private $label_left_offset;
  private $label_bottom_offset;

  /**
   * Modifies the graph padding to allow room for labels
   */
  protected function LabelAdjustment($longest_v = 1000, $longest_h = 100)
  {
    // deprecated options need converting
    // NOTE: this works because graph settings become properties, whereas
    // defaults only exist in the $this->settings array
    if(isset($this->show_label_h) && !isset($this->show_axis_text_h))
      $this->show_axis_text_h = $this->show_label_h;
    if(isset($this->show_label_v) && !isset($this->show_axis_text_v))
      $this->show_axis_text_v = $this->show_label_v;

    // if the label_x or label_y are set but not _h and _v, assign them
    $lh = $this->flip_axes ? $this->label_y : $this->label_x;
    $lv = $this->flip_axes ? $this->label_x : $this->label_y;
    if(empty($this->label_h) && !empty($lh))
      $this->label_h = $lh;
    if(empty($this->label_v) && !empty($lv))
      $this->label_v = $lv;

    if(!empty($this->label_v)) {
      // increase padding
      $lines = $this->CountLines($this->label_v);
      $this->label_left_offset = $this->pad_left + $this->label_space +
        $this->label_font_size;
      $this->pad_left += $lines * $this->label_font_size +
        2 * $this->label_space;
    }
    if(!empty($this->label_h)) {
      $lines = $this->CountLines($this->label_h);
      $this->label_bottom_offset = $this->pad_bottom + $this->label_space +
        $this->label_font_size * ($lines - 1);
      $this->pad_bottom += $lines * $this->label_font_size +
        2 * $this->label_space;
    }
    if($this->show_axes) {
      
      // make space for divisions
      $div_size = $this->DivisionOverlap();
      $this->pad_bottom += $div_size['x'];
      $this->pad_left += $div_size['y'];

      if($this->show_axis_text_v || $this->show_axis_text_h) {
        $pos_h = $this->GetFirst($this->axis_text_position_h,
          $this->axis_text_position);
        $pos_v = $this->GetFirst($this->axis_text_position_v,
          $this->axis_text_position);

        if($pos_h != 'inside' || $pos_v != 'inside') {
          $tw = $th = $theight = $twidth = 0;
          $len_h = $len_v = 1;
          $space_x = $this->width - $this->pad_left - $this->pad_right;
          $space_y = $this->height - $this->pad_top - $this->pad_bottom;

          for($i = 0; $i < 3; ++$i) {
            // find the longest axis labels for the grid space, reduced by
            // the current longest label
            list($len_h, $len_v, $tx, $ty) = $this->FindLongestAxisLabel(
              $space_x - $tw, $space_y - $th);

            if($this->show_axis_text_v && $pos_v != 'inside') {
              // modify padding for axis markings
              list($twidth) = $this->TextSize($len_v, $this->axis_font_size,
                $this->axis_font_adjust, $this->axis_text_angle_v);
            }

            if($this->show_axis_text_h && $pos_h != 'inside') {
              // similar to vertical version
              list(, $theight) = $this->TextSize($len_h, $this->axis_font_size,
                $this->axis_font_adjust, $this->axis_text_angle_h);
            }

            if($twidth == $tw && $theight == $th)
              break;

            $tw = $twidth;
            $th = $theight;
          }

          // apply the found spacings
          $this->pad_left += $tw;
          $this->pad_bottom += $th;
        }
      }
    }
    $this->label_adjust_done = true;
  }

  /**
   * Determines the longest axis labels for the given axis lengths
   */
  protected function FindLongestAxisLabel($length_x, $length_y)
  {
    $ends = $this->GetAxisEnds();

    list($x_axis, $y_axis) = $this->GetAxes($ends, $length_x, $length_y);

    $min_space_h = $this->GetFirst($this->minimum_grid_spacing_h,
      $this->minimum_grid_spacing);
    $min_space_v = $this->GetFirst($this->minimum_grid_spacing_v,
      $this->minimum_grid_spacing);
    $bar_h = $bar_v = null;
    if($this->flip_axes)
      $bar_v = $this->label_centre;
    else
      $bar_h = $this->label_centre;
    $h_grid = $x_axis->Grid($min_space_h, $bar_h);
    $v_grid = $y_axis->Grid($min_space_v, $bar_v);

    $y_points = $this->GetGridPoints($length_y, $v_grid, 0, 1, $y_axis->Zero(),
      $y_axis->Unit(), $y_axis->Uneven());
    $x_points = $this->GetGridPoints($length_x, $h_grid, 0, 1, $x_axis->Zero(),
      $x_axis->Unit(), $x_axis->Uneven());

    $longest_x = $longest_y = 0;
    $t_x = $t_y = '';

    foreach($x_points as $key => $val) {
      $text = $this->flip_axes ? $key : $this->GetKey($key);
      $len = strlen($text);
      if($len > $longest_x) {
        $longest_x = $len;
        $t_x = $text;
      }
    }

    foreach($y_points as $key => $val) {
      $text = $this->flip_axes ? $this->GetKey($key) : $key;
      $len = strlen($text);
      if($len > $longest_y) {
        $longest_y = $len;
        $t_y = $text;
      }
    }

    return array($longest_x, $longest_y, $t_x, $t_y);
  }

  /**
   * Returns the amount of overlap the divisions and subdivisions use
   */
  protected function DivisionOverlap()
  {
    if(!$this->show_divisions && !$this->show_subdivisions)
      return array('x' => 0, 'y' => 0);

    $dx = $this->DOverlap(
      $this->GetFirst($this->division_style_h, $this->division_style),
      $this->GetFirst($this->division_size_h, $this->division_size));
    $dy = $this->DOverlap(
      $this->GetFirst($this->division_style_v, $this->division_style),
      $this->GetFirst($this->division_size_v, $this->division_size));
    $sx = $this->DOverlap(
      $this->GetFirst($this->subdivision_style_h, $this->subdivision_style),
      $this->GetFirst($this->subdivision_size_h, $this->subdivision_size));
    $sy = $this->DOverlap(
      $this->GetFirst($this->subdivision_style_v, $this->subdivision_style),
      $this->GetFirst($this->subdivision_size_v, $this->subdivision_size));
    $x = max($dx, $sx);
    $y = max($dy, $sy);

    return array('x' => $x, 'y' => $y);
  }

  /**
   * Calculates the overlap of a division or subdivision
   */
  protected function DOverlap($style, $size)
  {
    $overlap = 0;
    switch($style) {
    case 'in' :
    case 'infull' :
    case 'none' :
      return 0;
    case 'out' :
    case 'over' :
    case 'overfull' :
    default :
      return $size;
    }
  }

  /**
   * Sets up grid width and height to fill padded area
   */
  protected function SetGridDimensions()
  {
    $this->g_height = $this->height - $this->pad_top - $this->pad_bottom;
    $this->g_width = $this->width - $this->pad_left - $this->pad_right;
  }

  /**
   * Returns an array containing the value and key axis min and max
   */
  protected function GetAxisEnds()
  {
    $v_max = $this->GetMaxValue();
    $v_min = $this->GetMinValue();
    $k_max = $this->GetMaxKey();
    $k_min = $this->GetMinKey();

    // check guides
    if(empty($this->guidelines))
      $this->CalcGuidelines();
    if(!is_null($this->max_guide['y']))
      $v_max = max($v_max, $this->max_guide['y']);
    if(!is_null($this->min_guide['y']))
      $v_min = min($v_min, $this->min_guide['y']);
    if(!is_null($this->max_guide['x']))
      $k_max = max($k_max, $this->max_guide['x']);
    if(!is_null($this->min_guide['x']))
      $k_min = min($k_min, $this->min_guide['x']);

    // validate axes
    if((is_numeric($this->axis_max_h) && is_numeric($this->axis_min_h) &&
      $this->axis_max_h <= $this->axis_min_h) ||
      (is_numeric($this->axis_max_v) && is_numeric($this->axis_min_v) &&
      $this->axis_max_v <= $this->axis_min_v))
        throw new Exception('Invalid axes specified');
    if((is_numeric($this->axis_max_h) &&
      ($this->axis_max_h < ($this->flip_axes ? $v_min : $k_min))) ||
      (is_numeric($this->axis_min_h) &&
      ($this->axis_min_h >= ($this->flip_axes ? $v_max : $k_max+1))) ||
      (is_numeric($this->axis_max_v) &&
      ($this->axis_max_v < ($this->flip_axes ? $k_min : $v_min))) ||
      (is_numeric($this->axis_min_v) &&
      ($this->axis_min_v >= ($this->flip_axes ? $k_max+1 : $v_max))))
        throw new Exception('No values in grid range');

    return compact('v_max', 'v_min', 'k_max', 'k_min');
  }

  /**
   * Returns the X and Y axis class instances as a list
   */
  protected function GetAxes($ends, &$x_len, &$y_len)
  {
    $h_by_count = $this->AssociativeKeys();
    $x_max = $h_by_count ? $this->GetHorizontalCount() - 1 : 
      max(0, $ends['k_max']);
    $x_min = $h_by_count ? 0 : min(0, $ends['k_min']);
    $y_max = max(0, $ends['v_max']);
    $y_min = min(0, $ends['v_min']);

    if($this->flip_axes) {
      $max_h = $this->GetFirst($this->axis_max_h, $y_max);
      $min_h = $this->GetFirst($this->axis_min_h, $y_min);
      $max_v = $this->GetFirst($this->axis_max_v, $x_max);
      $min_v = $this->GetFirst($this->axis_min_v, $x_min);
      $x_min_unit = 0;
      $x_fit = false;
      $y_min_unit = 1;
      $y_fit = true;

    } else {
      $max_h = $this->GetFirst($this->axis_max_h, $x_max);
      $min_h = $this->GetFirst($this->axis_min_h, $x_min);
      $max_v = $this->GetFirst($this->axis_max_v, $y_max);
      $min_v = $this->GetFirst($this->axis_min_v, $y_min);
      $x_min_unit = 1;
      $x_fit = true;
      $y_min_unit = 0;
      $y_fit = false;
    }

    // sanitise grid divisions
    if(is_numeric($this->grid_division_v) && $this->grid_division_v <= 0)
      $this->grid_division_v = null;
    if(is_numeric($this->grid_division_h) && $this->grid_division_h <= 0)
      $this->grid_division_h = null;

    // if fixed grid spacing is specified, make the min spacing 1 pixel
    if(is_numeric($this->grid_division_v))
      $this->minimum_grid_spacing_v = 1;
    if(is_numeric($this->grid_division_h))
      $this->minimum_grid_spacing_h = 1;

    if(!is_numeric($this->grid_division_h))
      $x_axis = new Axis($x_len, $max_h, $min_h, $x_min_unit, $x_fit);
    else
      $x_axis = new AxisFixed($x_len, $max_h, $min_h, $this->grid_division_h);

    if(!is_numeric($this->grid_division_v))
      $y_axis = new Axis($y_len, $max_v, $min_v, $y_min_unit, $y_fit);
    else
      $y_axis = new AxisFixed($y_len, $max_v, $min_v, $this->grid_division_v);

    return array($x_axis, $y_axis);
  }

  /**
   * Calculates the effect of axes, applying to padding
   */
  protected function CalcAxes()
  {
    if($this->axes_calc_done)
      return;

    $ends = $this->GetAxisEnds();
    if(!$this->label_adjust_done)
      $this->LabelAdjustment($ends['v_max'], $this->GetLongestKey());
    if(is_null($this->g_height) || is_null($this->g_width))
      $this->SetGridDimensions();

    list($x_axis, $y_axis) = $this->GetAxes($ends, $this->g_width,
      $this->g_height);

    if($this->flip_axes) {
      $bar_h = null;
      $bar_v = $this->label_centre;
      $x_min_unit = 0;
      $y_min_unit = 1;
    } else {
      $bar_h = $this->label_centre;
      $bar_v = null;
      $x_min_unit = 1;
      $y_min_unit = 0;
    }

    $min_space_h = $this->GetFirst($this->minimum_grid_spacing_h,
      $this->minimum_grid_spacing);
    $min_space_v = $this->GetFirst($this->minimum_grid_spacing_v,
      $this->minimum_grid_spacing);
    $this->h_grid = $x_axis->Grid($min_space_h, $bar_h);
    $this->v_grid = $y_axis->Grid($min_space_v, $bar_v);
    $this->x0 = $x_axis->Zero();
    $this->y0 = $y_axis->Zero();
    $this->uneven_x = $x_axis->Uneven();
    $this->uneven_y = $y_axis->Uneven();
    $this->bar_unit_width = $x_axis->Unit();
    $this->bar_unit_height = $y_axis->Unit();

    if($this->show_subdivisions) {
      $this->sub_y = $this->FindSubdiv($this->v_grid, $this->bar_unit_height,
        $this->minimum_subdivision, $y_min_unit, $this->subdivision_v);
      $this->sub_x = $this->FindSubdiv($this->h_grid, $this->bar_unit_width,
        $this->minimum_subdivision, $x_min_unit, $this->subdivision_h);
    }

    $this->axes_calc_done = true;
  }


  /**
   * Find the subdivision size
   */
  protected function FindSubdiv($grid_div, $u, $min, $min_unit, $fixed)
  {
    if(is_numeric($fixed))
      return $u * $fixed;

    $D = $grid_div / $u;  // D = actual division size
    $min = max($min, $min_unit * $u); // use the larger minimum value
    $max_divisions = (int)floor($grid_div / $min);

    // can we subdivide at all?
    if($max_divisions <= 1)
      return null;

    // convert $D to an integer in the 100's range
    $D1 = (int)round(100 * (pow(10,-floor(log10($D)))) * $D);
    for($divisions = $max_divisions; $divisions > 1; --$divisions) {
      // if $D1 / $divisions is not an integer, $divisions is no good
      $dq = $D1 / $divisions;
      if($dq - floor($dq) == 0)
        return $grid_div / $divisions;
    }
    return null;
  }

  /**
   * Returns the grid points as an associative array:
   * array($value => $position)
   */
  protected function GetGridPoints($length, $spacing, $start, $direction,
    $zero, $unit_size, $uneven)
  {
    $c = $pos = 0;
    $d = $spacing * 0.5;
    $points = array();
    while($pos < $length + $d) {
      // converted to string to work as array key
      $point = $this->NumString(($pos - $zero) / $unit_size);
      $points[$point] = $start + ($direction * $pos);
      $pos = ++$c * $spacing;
    }
    // $uneven means the divisions don't fit exactly, so add the last one in
    if($uneven) {
      $pos = $length - $zero;
      $point = $this->NumString($pos / $unit_size);
      $points[$point] = $start + $length;
    }

    return $points;
  }

  /**
   * Returns the grid subdivision points as an array
   */
  protected function GetGridSubdivisions(&$points, $spacing, $direction)
  {
    reset($points);
    list(, $pos1) = each($points);
    $d = $spacing * 0.5;
    $subdivs = array();
    while((list(, $pos2) = each($points)) !== false) {
      $count = (int)round(abs($pos2 - $pos1) / $spacing);
      for($c = 1; $c < $count; ++$c) {
        $subdivs[] = $pos1 + ($direction * $c * $spacing);
      }
      $pos1 = $pos2;
    }
    return $subdivs;
  }

  /**
   * Calculates the position of grid lines
   */
  protected function CalcGrid()
  {
    if(isset($this->y_points))
      return;

    $grid_bottom = $this->height - $this->pad_bottom;
    $grid_left = $this->pad_left;
    $this->y_subdivs = array();
    $this->x_subdivs = array();

    $this->y_points = $this->GetGridPoints($this->g_height, $this->v_grid,
      $grid_bottom, -1, $this->y0, $this->bar_unit_height, $this->uneven_y);
    $this->x_points = $this->GetGridPoints($this->g_width, $this->h_grid,
      $grid_left, 1, $this->x0, $this->bar_unit_width, $this->uneven_x);
    if($this->sub_y)
      $this->y_subdivs = $this->GetGridSubdivisions($this->y_points,
        $this->sub_y, -1);
    if($this->sub_x)
      $this->x_subdivs = $this->GetGridSubdivisions($this->x_points,
        $this->sub_x, 1);
  }

  /**
   * Converts number to string
   */
  protected function NumString($n)
  {
    // subtract number of digits before decimal point from precision
    $d = is_int($n) ? 0 : ($this->precision - floor(log(abs($n))));
    $s = number_format($n, $d);

    if($d && strpos($s, '.') !== false) {
      list($a, $b) = explode('.', $s);
      $b1 = rtrim($b, '0');
      if($b1 != '')
        return "$a.$b1";
      return $a;
    }
    return $s;
  }


  /**
   * Subclasses can override this for non-linear graphs
   */
  protected function GetHorizontalCount()
  {
    $values = $this->GetValues();
    return count($values);
  }

  /**
   * Returns the key that takes up the most space
   */
  protected function GetLongestKey()
  {
    $longest_key = '';
    if($this->show_axis_text_v) {
      $max_len = 0;
      foreach($this->values[0] as $k => $v) {
        if(is_numeric($k))
          $k = $this->NumString($k);
        $len = strlen($k);
        if($len > $max_len) {
          $max_len = $len;
          $longest_key = $k;
        }
      }
    }
    return $longest_key;
  }

  /**
   * Returns the X axis SVG fragment
   */
  protected function XAxis($yoff)
  {
    $x = $this->pad_left - $this->axis_overlap;
    $y = $this->height - $this->pad_bottom - $yoff;
    $len = $this->g_width + 2 * $this->axis_overlap;
    $path = "M$x {$y}h$len";
    return $this->Element('path', array('d' => $path));
  }

  /**
   * Returns the Y axis SVG fragment
   */
  protected function YAxis($xoff)
  {
    $x = $this->pad_left + $xoff;
    $len = $this->g_height + 2 * $this->axis_overlap;
    $y = $this->height - $this->pad_bottom + $this->axis_overlap - $len;
    $path = "M$x {$y}v$len";
    return $this->Element('path', array('d' => $path));
  }

  /**
   * Returns the position and size of divisions
   * @retval array('pos' => $position, 'sz' => $size)
   */
  protected function DivisionsPositions($style, $style_default,
    $size, $size_default, $fullsize, $start, $axis_offset)
  {
    if(empty($style))
      $style = $style_default;
    if(empty($size))
      $size = $size_default;

    $sz = $size;
    $pos = $start + $axis_offset;

    switch($style) {
    case 'none' :
      return null; // no pos or sz
    case 'infull' :
      $pos = $start;
      $sz = $fullsize;
      break;
    case 'over' :
      $pos -= $size;
      $sz = $size * 2;
      break;
    case 'overfull' :
      $pos = $start - $size;
      $sz = $fullsize + $size;
      break;
    case 'in' :
      break; // no change
    case 'out' :
    default :
      $pos -= $size;
      $sz = $size;
    }

    return array('sz' => $sz, 'pos' => $pos);
  }

  /**
   * Returns X-axis divisions as a path
   */
  protected function XAxisDivisions(&$points, $style, $style_default,
    $size, $size_default, $yoff)
  {
    $path = '';
    $pos = $this->DivisionsPositions($style, $style_default, $size,
      $size_default, $this->g_height, $this->pad_bottom, $yoff);
    if(is_null($pos))
      return '';

    $y = $this->height - $pos['pos'];
    $height = -$pos['sz'];
    foreach($points as $x)
      $path .= "M$x {$y}v{$height}";
    return $path;
  }

  /**
   * Returns Y-axis divisions as a path
   */
  protected function YAxisDivisions(&$points, $style, $style_default,
    $size, $size_default, $xoff)
  {
    $path = '';
    $pos = $this->DivisionsPositions($style, $style_default, $size,
      $size_default, $this->g_width, $this->pad_left, $xoff);
    if(is_null($pos))
      return '';

    $x = $pos['pos'];
    $size = $pos['sz'];
    foreach($points as $y)
      $path .= "M$x {$y}h{$size}";
    return $path;
  }

  /**
   * Returns the X-axis text fragment
   */
  protected function XAxisText(&$points, $xoff, $yoff, $angle)
  {
    $labels = '';
    $x_prev = -$this->width;
    $min_space = $this->GetFirst($this->minimum_grid_spacing_h,
      $this->minimum_grid_spacing);
    $count = count($points);
    $label_centre_x = $this->label_centre && !$this->flip_axes;
    $text_centre = $this->axis_font_size * 0.3;

    $inside = ('inside' == $this->GetFirst($this->axis_text_position_h,
      $this->axis_text_position));
    if($inside)
    {
      $y = $this->height - $this->pad_bottom - $yoff - $this->axis_text_space;
      $angle = -$angle;
      $x_rotate_offset = -$text_centre;
    }
    else
    {
      $y = $this->height - $this->pad_bottom + $yoff + $this->axis_font_size +
        $this->axis_text_space - $text_centre;
      $x_rotate_offset = $text_centre;
    }
    if($angle < 0)
      $x_rotate_offset = -$x_rotate_offset;
    $y_rotate_offset = -$text_centre;
    $text = array('y' => $y);
    $p = 0;
    foreach($points as $label => $x) {
      $key = $this->flip_axes ? $label : $this->GetKey($label);

      // don't draw 0 over the axis line
      if($inside && !$label_centre_x && $key == '0')
        $key = '';

      if(strlen($key) > 0 && $x - $x_prev >= $min_space
         &&  (++$p < $count || !$label_centre_x)) {
        $text['x'] = $x + $xoff;
        if($angle != 0) {
          $text['x'] -= $x_rotate_offset;
          $rcx = $text['x'] + $x_rotate_offset;
          $rcy = $text['y'] + $y_rotate_offset;
          $text['transform'] = "rotate($angle,$rcx,$rcy)";
        }
        $labels .= $this->Element('text', $text, NULL, $key);
      }
      $x_prev = $x;
    }
    if($angle == 0) {
      $tgroup = array('text-anchor' => 'middle');
    } else {
      $tgroup = array('text-anchor' => $this->axis_text_angle_h < 0 ?
        'end' : 'start');
    }
    return $this->Element('g', $tgroup, NULL, $labels);
  }

  /**
   * Returns the Y-axis text fragment
   */
  protected function YAxisText(&$points, $xoff, $yoff, $angle)
  {
    $labels = '';
    $y_prev = $this->height;
    $min_space = $this->minimum_grid_spacing_v;
    $text_centre = $this->axis_font_size * 0.3;
    $label_centre_y = $this->label_centre && $this->flip_axes;

    $inside = ('inside' == $this->GetFirst($this->axis_text_position_v,
      $this->axis_text_position));
    $anchor = $inside ? 'start' : 'end';
    $x_rotate_offset = $inside ? $text_centre : -$text_centre;
    $y_rotate_offset = -$text_centre;

    $x = $this->pad_left + ($inside ? $xoff + $this->axis_text_space :
      -$xoff - $this->axis_text_space);
    $text = array('x' => $x);
    $count = count($points);
    $p = 0;
    foreach($points as $label => $y) {
      $key = $this->flip_axes ? $this->GetKey($label) : $label;

      // don't draw 0 over the axis line
      if($inside && !$label_centre_y && $key == '0')
        $key = '';

      if(strlen($key) && $y_prev - $y >= $min_space &&
        (++$p < $count || !$label_centre_y)) {
        $text['y'] = $y + $text_centre + $yoff;
        if($angle != 0) {
          $rcx = $text['x'] + $x_rotate_offset;
          $rcy = $text['y'] + $y_rotate_offset;
          $text['transform'] = "rotate($angle,$rcx,$rcy)";
        }
        $labels .= $this->Element('text', $text, NULL, $key);
      }
      $y_prev = $y;
    }
    return $this->Element('g', array('text-anchor' => $anchor), NULL, $labels);
  }

  /**
   * Returns the horizontal axis label
   */
  protected function HLabel(&$attribs)
  {
    if(empty($this->label_h))
      return '';

    $x = ($this->width - $this->pad_left - $this->pad_right) / 2 +
      $this->pad_left;
    $y = $this->height - $this->label_bottom_offset;
    $pos = array('x' => $x, 'y' => $y);
    return $this->Text($this->label_h, $this->label_font_size,
      array_merge($attribs, $pos));
  }

  /**
   * Returns the vertical axis label
   */
  protected function VLabel(&$attribs)
  {
    if(empty($this->label_v))
      return '';

    $x = $this->label_left_offset;
    $y = ($this->height - $this->pad_bottom - $this->pad_top) / 2 +
      $this->pad_top;
    $pos = array(
      'x' => $x,
      'y' => $y,
      'transform' => "rotate(270,$x,$y)",
    );
    return $this->Text($this->label_v, $this->label_font_size,
      array_merge($attribs, $pos));
  }

  /**
   * Returns the labels grouped with the provided axis division labels
   */
  protected function Labels($axis_text = '')
  {
    $labels = $axis_text;
    if(!empty($this->label_h) || !empty($this->label_v)) {
      $label_text = array('text-anchor' => 'middle');
      if($this->label_font != $this->axis_font)
        $label_text['font-family'] = $this->label_font;
      if($this->label_font_size != $this->axis_font_size)
        $label_text['font-size'] = $this->label_font_size;
      if($this->label_font_weight != 'normal')
        $label_text['font-weight'] = $this->label_font_weight;
      if(!empty($this->label_colour) &&
        $this->label_colour != $this->axis_text_colour)
        $label_text['fill'] = $this->label_colour;

      if(!empty($this->label_h)) {
        $label_text['y'] = $this->height - $this->label_bottom_offset;
        $label_text['x'] = $this->pad_left +
          ($this->width - $this->pad_left - $this->pad_right) / 2;
        $labels .= $this->Text($this->label_h, $this->label_font_size,
          $label_text);
      }

      $labels .= $this->VLabel($label_text);
    }

    if(!empty($labels)) {
      $font = array(
        'font-size' => $this->axis_font_size,
        'font-family' => $this->axis_font,
        'fill' => empty($this->axis_text_colour) ?
          $this->axis_colour : $this->axis_text_colour,
      );
      $labels = $this->Element('g', $font, NULL, $labels);
    }
    return $labels;
  }

  /**
   * Draws bar or line graph axes
   */
  protected function Axes()
  {
    if(!$this->show_axes)
      return $this->Labels();

    $this->CalcGrid();
    $x_axis_visible = $this->y0 >= 0 && $this->y0 < $this->g_height;
    $y_axis_visible = $this->x0 >= 0 && $this->x0 < $this->g_width;
    $yoff = $x_axis_visible ? $this->y0 : 0;
    $xoff = $y_axis_visible ? $this->x0 : 0;

    $axis_group = $axes = $label_group = $divisions = $axis_text = '';
    if($x_axis_visible)
      $axes .= $this->XAxis($yoff);
    if($y_axis_visible)
      $axes .= $this->YAxis($xoff);

    if($axes != '') {
      $line = array(
        'stroke-width' => $this->axis_stroke_width,
        'stroke' => $this->axis_colour
      );
      $axis_group = $this->Element('g', $line, NULL, $axes);
    }

    $x_offset = $y_offset = 0;
    if($this->label_centre) {
      if($this->flip_axes)
        $y_offset = -0.5 * $this->bar_unit_height;
      else
        $x_offset = 0.5 * $this->bar_unit_width;
    }

    arsort($this->y_points);
    asort($this->x_points);
    $text_offset = $this->DivisionOverlap();
    if($this->show_axis_text_v)
      $axis_text .= $this->YAxisText($this->y_points, $text_offset['y'],
        $y_offset, $this->axis_text_angle_v);
    if($this->show_axis_text_h)
      $axis_text .= $this->XAxisText($this->x_points, $x_offset,
        $text_offset['x'], $this->axis_text_angle_h);

    $label_group = $this->Labels($axis_text);

    if($this->show_divisions) {
      // use an array to join paths with same colour
      $div_paths = array();
      $dx_path = $this->XAxisDivisions($this->x_points,
        $this->division_style_h, $this->division_style, 
        $this->division_size_h, $this->division_size, $yoff);
      if(!empty($dx_path)) {
        $dx_colour = $this->GetFirst($this->division_colour_h,
          $this->division_colour, $this->axis_colour);
        @$div_paths[$dx_colour] .= $dx_path;
      }
      $dy_path = $this->YAxisDivisions($this->y_points,
        $this->division_style_v, $this->division_style,
        $this->division_size_v, $this->division_size, $xoff);
      if(!empty($dy_path)) {
        $dy_colour = $this->GetFirst($this->division_colour_v,
          $this->division_colour, $this->axis_colour);
        @$div_paths[$dy_colour] .= $dy_path;
      }

      if($this->show_subdivisions) {
        $sdy_path = $this->YAxisDivisions($this->y_subdivs,
          $this->subdivision_style_v, $this->subdivision_style,
          $this->subdivision_size_v, $this->subdivision_size, $xoff);
        $sdx_path = $this->XAxisDivisions($this->x_subdivs,
          $this->subdivision_style_h, $this->subdivision_style, 
          $this->subdivision_size_h, $this->subdivision_size, $yoff);

        if(!empty($sdx_path)) {
          $sdx_colour = $this->GetFirst($this->subdivision_colour_h,
            $this->subdivision_colour, $this->division_colour_h,
            $this->division_colour, $this->axis_colour);
          @$div_paths[$sdx_colour] .= $sdx_path;
        }
        if(!empty($sdy_path)) {
          $sdy_colour = $this->GetFirst($this->subdivision_colour_v,
            $this->subdivision_colour, $this->division_colour_v,
            $this->division_colour, $this->axis_colour);
          @$div_paths[$sdy_colour] .= $sdy_path;
        }
      }

      foreach($div_paths as $colour => $path) {
        $div = array(
          'd' => $path,
          'stroke-width' => 1,
          'stroke' => $colour
        );
        $divisions .= $this->Element('path', $div);
      }
    }
    return $divisions . $axis_group . $label_group;
  }

  /**
   * Returns a set of gridlines
   */
  protected function GridLines($path, $colour, $dash, $fill = null)
  {
    if($path == '' || $colour == 'none')
      return '';
    $opts = array('d' => $path, 'stroke' => $colour);
    if(!empty($dash))
      $opts['stroke-dasharray'] = $dash;
    if(!empty($fill))
      $opts['fill'] = $fill;
    return $this->Element('path', $opts);
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
    $back = $subpath = $path_h = $path_v = '';
    $back_colour = $this->grid_back_colour;
    if(!empty($back_colour) && $back_colour != 'none') {
      $rect = array(
        'x' => $this->pad_left, 'y' => $this->pad_top,
        'width' => $this->g_width, 'height' => $this->g_height,
        'fill' => $back_colour
      );
      $back = $this->Element('rect', $rect);
    }
    if($this->show_grid_subdivisions) {
      $subpath_h = $subpath_v = '';
      foreach($this->y_subdivs as $y) 
        $subpath_v .= "M{$this->pad_left} {$y}h{$this->g_width}";
      foreach($this->x_subdivs as $x) 
        $subpath_h .= "M$x {$this->pad_top}v{$this->g_height}";

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
            $dash_h);
        } else {
          $subpath = $this->GridLines($subpath_h, $colour_h, $dash_h) .
            $this->GridLines($subpath_v, $colour_v, $dash_v);
        }
      }
    }

    foreach($this->y_points as $y) 
      $path_v .= "M{$this->pad_left} {$y}h{$this->g_width}";
    foreach($this->x_points as $x) 
      $path_h .= "M$x {$this->pad_top}v{$this->g_height}";

    $colour_h = $this->GetFirst($this->grid_colour_h, $this->grid_colour);
    $colour_v = $this->GetFirst($this->grid_colour_v, $this->grid_colour);
    $dash_h = $this->GetFirst($this->grid_dash_h, $this->grid_dash);
    $dash_v = $this->GetFirst($this->grid_dash_v, $this->grid_dash);

    if($dash_h == $dash_v && $colour_h == $colour_v) {
      $path = $this->GridLines($path_v . $path_h, $colour_h, $dash_h);
    } else {
      $path = $this->GridLines($path_h, $colour_h, $dash_h) .
        $this->GridLines($path_v, $colour_v, $dash_v);
    }

    return $back . $subpath . $path;
  }

  /**
   * clamps a value to the grid boundaries
   */
  protected function ClampVertical($val)
  {
    return max($this->pad_top, min($this->height - $this->pad_bottom, $val));
  }

  protected function ClampHorizontal($val)
  {
    return max($this->pad_left, min($this->width - $this->pad_right, $val));
  }

  /**
   * Returns a clipping path for the grid
   */
  protected function ClipGrid(&$attr)
  {
    $rect = array(
      'x' => $this->pad_left, 'y' => $this->pad_top,
      'width' => $this->width - $this->pad_left - $this->pad_right,
      'height' => $this->height - $this->pad_top - $this->pad_bottom
    );
    $clip_id = $this->NewID();
    $this->defs[] = $this->Element('clipPath', array('id' => $clip_id),
      NULL, $this->Element('rect', $rect));
    $attr['clip-path'] = "url(#{$clip_id})";
  }

  /**
   * Returns the grid position for a bar or point, or NULL if not on grid
   * $key  = actual value array index
   * $ikey = integer position in array
   */
  protected function GridPosition($key, $ikey)
  {
    $position = null;
    $gkey = $this->AssociativeKeys() ? $ikey : $key;
    if($this->flip_axes) {
      $top = $this->label_centre ?
        $this->g_height - ($this->bar_unit_height / 2) : $this->g_height;
      $offset = $this->y0 + ($this->bar_unit_height * $gkey);
      if($offset >= 0 && floor($offset) <= $top)
        $position = $this->height - $this->pad_bottom - $offset;
    } else {
      $right_end = $this->label_centre ?
        $this->g_width - ($this->bar_unit_width / 2) : $this->g_width;
      $offset = $this->x0 + ($this->bar_unit_width * $gkey);
      if($offset >= 0 && floor($offset) <= $right_end)
        $position = $this->pad_left + $offset;
    }
    return $position;
  }

  /**
   * Converts guideline options to more useful member variables
   */
  protected function CalcGuidelines($g = null)
  {
    if(is_null($g)) {
      // no guidelines?
      if(empty($this->guideline) && $this->guideline !== 0)
        return;

      if(is_array($this->guideline) && count($this->guideline) > 1 &&
        !is_string($this->guideline[1])) {

        // array of guidelines
        foreach($this->guideline as $gl)
          $this->CalcGuidelines($gl);
        return;
      }

      // single guideline
      $g = $this->guideline;
    }

    if(!is_array($g))
      $g = array($g);

    $value = $g[0];
    $axis = (isset($g[2]) && ($g[2] == 'x' || $g[2] == 'y')) ? $g[2] : 'y';
    $above = isset($g['above']) ? $g['above'] : $this->guideline_above;
    $position = $above ? SVGG_GUIDELINE_ABOVE : SVGG_GUIDELINE_BELOW;
    $guideline = array(
      'value' => $value,
      'depth' => $position,
      'title' => isset($g[1]) ? $g[1] : '',
      'axis' => $axis
    );
    $lopts = $topts = array();
    $line_opts = array(
      'colour' => 'stroke',
      'dash' => 'stroke-dasharray',
      'stroke_width' => 'stroke-width',
      'opacity' => 'opacity',

      // not SVG attributes
      'length' => 'length',
      'length_units' => 'length_units',
    );
    $text_opts = array(
      'colour' => 'fill',
      'opacity' => 'opacity',
      'font' => 'font-family',
      'font_size' => 'font-size',
      'font_weight' => 'font-weight',
      'text_colour' => 'fill', // overrides 'colour' option from line
      'text_opacity' => 'opacity', // overrides line opacity

      // these options do not map to SVG attributes
      'font_adjust' => 'font_adjust',
      'text_position' => 'text_position',
      'text_padding' => 'text_padding',
      'text_angle' => 'text_angle',
      'text_align' => 'text_align',
    );
    foreach($line_opts as $okey => $opt)
      if(isset($g[$okey]))
        $lopts[$opt] = $g[$okey];
    foreach($text_opts as $okey => $opt)
      if(isset($g[$okey]))
        $topts[$opt] = $g[$okey];

    if(count($lopts))
      $guideline['line'] = $lopts;
    if(count($topts))
      $guideline['text'] = $topts;

    // update maxima and minima
    if(is_null($this->max_guide[$axis]) || $value > $this->max_guide[$axis])
      $this->max_guide[$axis] = $value;
    if(is_null($this->min_guide[$axis]) || $value < $this->min_guide[$axis])
      $this->min_guide[$axis] = $value;

    // can flip the axes now the min/max are stored
    if($this->flip_axes)
      $guideline['axis'] = ($guideline['axis'] == 'x' ? 'y' : 'x');

    $this->guidelines[] = $guideline;
  }

  /**
   * Returns the elements to draw the guidelines
   */
  protected function Guidelines($depth)
  {
    if(empty($this->guidelines))
      return '';

    // build all the lines at this depth (above/below) that use
    // global options as one path
    $d = $lines = $text = '';
    $path = array(
      'stroke' => $this->guideline_colour,
      'stroke-width' => $this->guideline_stroke_width,
      'stroke-dasharray' => $this->guideline_dash,
      'fill' => 'none'
    );
    if($this->guideline_opacity != 1)
      $path['opacity'] = $this->guideline_opacity;
    $textopts = array(
      'font-family' => $this->guideline_font,
      'font-size' => $this->guideline_font_size,
      'font-weight' => $this->guideline_font_weight,
      'fill' => $this->GetFirst($this->guideline_text_colour, 
        $this->guideline_colour),
    );
    $text_opacity = $this->GetFirst($this->guideline_text_opacity, 
      $this->guideline_opacity);

    foreach($this->guidelines as $line) {
      if($line['depth'] == $depth) {
        // opacity cannot go in the group because child opacity is multiplied
        // by group opacity
        if($text_opacity != 1 && !isset($line['text']['opacity']))
          $line['text']['opacity'] = $text_opacity;
        $this->BuildGuideline($line, $lines, $text, $path, $d);
      }
    }
    if(!empty($d)) {
      $path['d'] = $d;
      $lines .= $this->Element('path', $path);
    }

    if(!empty($text))
      $text = $this->Element('g', $textopts, null, $text);
    return $lines . $text;
  }

  /**
   * Adds a single guideline and its title to content
   */
  protected function BuildGuideline(&$line, &$lines, &$text, &$path, &$d)
  {
    $length = $this->guideline_length;
    $length_units = $this->guideline_length_units;
    if(isset($line['line'])) {
      $this->UpdateAndUnset($length, $line['line'], 'length');
      $this->UpdateAndUnset($length_units, $line['line'], 'length_units');
    }
    if($length != 0) {
      if($line['axis'] == 'x')
        $h = $length;
      else
        $w = $length;
    } elseif($length_units != 0) {
      if($line['axis'] == 'x')
        $h = $length_units * $this->bar_unit_height;
      else
        $w = $length_units * $this->bar_unit_width;
    }

    $path_data = $this->GuidelinePath($line['axis'], $line['value'],
      $line['depth'], $x, $y, $w, $h);
    if(!isset($line['line'])) {
      // no special options, add to main path
      $d .= $path_data;
    } else {
      $line_path = array_merge($path, $line['line'], array('d' => $path_data));
      $lines .= $this->Element('path', $line_path);
    }
    if(!empty($line['title'])) {
      $text_pos = $this->guideline_text_position;
      $text_pad = $this->guideline_text_padding;
      $text_angle = $this->guideline_text_angle;
      $text_align = $this->guideline_text_align;
      $font_size = $this->guideline_font_size;
      $font_adjust = $this->guideline_font_adjust;
      if(isset($line['text'])) {
        $this->UpdateAndUnset($text_pos, $line['text'], 'text_position');
        $this->UpdateAndUnset($text_pad, $line['text'], 'text_padding');
        $this->UpdateAndUnset($text_angle, $line['text'], 'text_angle');
        $this->UpdateAndUnset($text_align, $line['text'], 'text_align');
        $this->UpdateAndUnset($font_adjust, $line['text'], 'font_adjust');
        if(isset($line['text']['font-size']))
          $font_size = $line['text']['font-size'];
      }
      list($text_w, $text_h) = $this->TextSize($line['title'], 
        $font_size, $font_adjust, $text_angle, $font_size);

      list($x, $y, $text_right) = Graph::RelativePosition(
        $text_pos, $y, $x, $y + $h, $x + $w,
        $text_w, $text_h, $text_pad, true);

      $t = array('x' => $x, 'y' => $y + $font_size);
      if($text_right && empty($text_align))
        $text_align = 'right';
      $align_map = array('right' => 'end', 'centre' => 'middle');
      if(!empty($text_align) && isset($align_map[$text_align]))
        $t['text-anchor'] = $align_map[$text_align];

      if($text_angle != 0) {
        $rx = $x + $text_h/2;
        $ry = $y + $text_h/2;
        $t['transform'] = "rotate($text_angle,$rx,$ry)";
      }

      if(isset($line['text']))
        $t = array_merge($t, $line['text']);
      $text .= $this->Text($line['title'], $font_size, $t);
    }
  }

  /**
   * Creates the path data for a guideline and sets the dimensions
   */
  protected function GuidelinePath($axis, $value, $depth, &$x, &$y, &$w, &$h)
  {
    $y_axis_pos = $this->height - $this->pad_bottom - $this->y0;
    $x_axis_pos = $this->pad_left + $this->x0;

    if($axis == 'x') {
      $x = $x_axis_pos + ($value * $this->bar_unit_width);
      $y = $this->height - $this->pad_bottom - $this->g_height;
      $w = 0;
      if($h == 0) {
        $h = $this->g_height;
      } elseif($h < 0) {
        $h = -$h;
      } else {
        $y = $this->height - $this->pad_bottom - $h;
      }
      return "M$x {$y}v$h";
    } else {
      $x = $this->pad_left;
      $y = $y_axis_pos - ($value * $this->bar_unit_height);
      if($w == 0) {
        $w = $this->g_width;
      } elseif($w < 0) {
        $w = -$w;
        $x = $this->pad_left + $this->g_width - $w;
      }
      $h = 0;
      return "M$x {$y}h$w";
    }
  }

  /**
   * Updates $var with $array[$key] and removes it from array
   */
  protected function UpdateAndUnset(&$var, &$array, $key)
  {
    if(isset($array[$key])) {
      $var = $array[$key];
      unset($array[$key]);
    }
  }
}

