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

class PieGraph extends Graph {

  // for internal use
  protected $x_centre;
  protected $y_centre;
  protected $radius_x;
  protected $radius_y;
  protected $s_angle;
  protected $calc_done;
  protected $slice_styles = array();

  /**
   * Calculates position of pie
   */
  protected function Calc()
  {
    $bound_x_left = $this->pad_left;
    $bound_y_top = $this->pad_top;
    $bound_x_right = $this->width - $this->pad_right;
    $bound_y_bottom = $this->height - $this->pad_bottom;

    $w = $bound_x_right - $bound_x_left;
    $h = $bound_y_bottom - $bound_y_top;

    if($this->aspect_ratio == 'auto')
      $this->aspect_ratio = $h/$w;
    elseif($this->aspect_ratio <= 0)
      $this->aspect_ratio = 1.0;

    $this->x_centre = (($bound_x_right - $bound_x_left) / 2) + $bound_x_left;
    $this->y_centre = (($bound_y_bottom - $bound_y_top) / 2) + $bound_y_top;
    $this->start_angle %= 360;
    if($this->start_angle < 0)
      $this->start_angle = 360 + $this->start_angle;
    $this->s_angle = deg2rad($this->start_angle);

    if($h/$w > $this->aspect_ratio) {
      $this->radius_x = $w / 2.0;
      $this->radius_y = $this->radius_x * $this->aspect_ratio;
    } else {
      $this->radius_y = $h / 2.0;
      $this->radius_x = $this->radius_y / $this->aspect_ratio;
    }
    $this->calc_done = true;
  }

  /**
   * Draws the pie graph
   */
  public function Draw()
  {
    if(!$this->calc_done)
      $this->Calc();
    $speed_in = $this->show_labels && $this->label_fade_in_speed ?
      $this->label_fade_in_speed / 100.0 : 0;
    $speed_out = $this->show_labels && $this->label_fade_out_speed ?
      $this->label_fade_out_speed / 100.0 : 0;

    // take a copy for sorting
    $values = $this->GetValues();
    $total = array_sum($values);

    $unit_slice = 2.0 * M_PI / $total;
    $ccount = count($this->colours);
    $vcount = count($values);
    $sub_total = 0.0;

    // need to store the original position of each value, because the
    // sorted list must still refer to the relevant legend entries
    $position = 0;
    foreach($values as $key => $value)
      $values[$key] = array($position++, $value);

    if($this->sort) {
      uasort($values, 'PieGraph::svggpsort');
    }
    $body = '';
    $labels = '';

    $slice = 0;
    foreach($values as $key => $value) {

      // get the original array position of the value
      $original_position = $value[0];
      $value = $value[1];
      if(!$value)
        continue;
      ++$slice;

      $angle_start = $sub_total * $unit_slice;
      $angle_end = ($sub_total + $value) * $unit_slice;

      // get the path (or whatever) for a pie slice
      $attr = array('fill' => $this->GetColour(($slice-1) % $ccount, true));
      $style = $attr;
      $this->SetStroke($style);

      // store the current style referenced by the original position
      $this->slice_styles[$original_position] = $style;
      if($this->show_tooltips)
        $this->SetTooltip($attr, $key, $value, !$this->compat_events);
  
      $t_style = NULL;
      if($this->show_labels) {
        $ac = $this->s_angle + ($sub_total + ($value * 0.5)) * $unit_slice;
        $xc = $this->label_position * $this->radius_x * cos($ac);
        $yc = ($this->reverse ? -1 : 1) * $this->label_position *
          $this->radius_y * sin($ac);

        $text['id'] = $this->NewID();
        if($this->label_fade_in_speed && $this->compat_events)
          $text['opacity'] = '0.0';
        $tx = $this->x_centre + $xc;
        $ty = $this->y_centre + $yc + ($this->label_font_size * 0.3);

        // display however many lines of label
        $parts = array($key);
        if($this->show_label_amount)
          $parts[] = $value;
        if($this->show_label_percent)
          $parts[] = ($value / $total) * 100.0 . '%';

        $x_offset = empty($this->label_back_colour) ? $tx : 0;
        $string = $this->TextLines($parts, $x_offset, $this->label_font_size);

        if(!empty($this->label_back_colour)) {
          $labels .= $this->ContrastText($tx, $ty, $string, 
            $this->label_colour, $this->label_back_colour, $text);
        } else {
          $text['x'] = $tx;
          $text['y'] = $ty;
          $text['fill'] = $this->label_colour;
          $labels .= $this->Element('text', $text, NULL, $string);
        }
      }
      if($speed_in || $speed_out)
        $this->SetFader($attr, $speed_in, $speed_out, $text['id'],
          !$this->compat_events);
      $path = $this->GetSlice($angle_start, $angle_end, $attr);
      $body .= $this->GetLink($key, $path);

      $sub_total += $value;
    }

    // group the slices
    $attr = array();
    $this->SetStroke($attr, 'round');
    $body = $this->Element('g', $attr, NULL, $body);

    if($this->show_labels) {
      $label_group = array(
        'text-anchor' => 'middle',
        'font-size' => $this->label_font_size,
        'font-family' => $this->label_font,
        'font-weight' => $this->label_font_weight,
      );
      $labels = $this->Element('g', $label_group, NULL, $labels);
    }
    return $body . $labels;
  }

  /**
   * Returns a single slice of pie
   */
  protected function GetSlice($angle_start, $angle_end, &$attr)
  {
    $x_start = $y_start = $x_end = $y_end = 0;
    $angle_start += $this->s_angle;
    $angle_end += $this->s_angle;
    $this->CalcSlice($angle_start, $angle_end, $x_start, $y_start,
      $x_end, $y_end);
    if((string)$x_start == (string)$x_end &&
      (string)$y_start == (string)$y_end) {
      $attr['cx'] = $this->x_centre;
      $attr['cy'] = $this->y_centre;
      $attr['rx'] = $this->radius_x;
      $attr['ry'] = $this->radius_y;
      return $this->Element('ellipse', $attr);
    } else {
      $outer = ($angle_end - $angle_start > M_PI ? 1 : 0);
      $sweep = ($this->reverse ? 0 : 1);
      $attr['d'] = "M{$this->x_centre},{$this->y_centre} L$x_start,$y_start " .
        "A{$this->radius_x} {$this->radius_y} 0 $outer,$sweep $x_end,$y_end z";
      return $this->Element('path', $attr);
    }
  }

  protected function CalcSlice($angle_start, $angle_end,
    &$x_start, &$y_start, &$x_end, &$y_end)
  {
    $x_start = ($this->radius_x * cos($angle_start));
    $y_start = ($this->reverse ? -1 : 1) *
      ($this->radius_y * sin($angle_start));
    $x_end = ($this->radius_x * cos($angle_end));
    $y_end = ($this->reverse ? -1 : 1) *
      ($this->radius_y * sin($angle_end));

    $x_start += $this->x_centre;
    $y_start += $this->y_centre;
    $x_end += $this->x_centre;
    $y_end += $this->y_centre;
  }

  /**
   * Checks that the data are valid
   */
  protected function CheckValues(&$values)
  {
    parent::CheckValues($values);
    if($this->GetMinValue() < 0)
      throw new Exception('Negative value for pie chart');
    if(array_sum($values[0]) <= 0)
      throw new Exception('Empty pie chart');
  }

  /**
   * Return box for legend
   */
  protected function DrawLegendEntry($set, $x, $y, $w, $h)
  {
    if(!array_key_exists($set, $this->slice_styles))
      return '';

    $bar = array('x' => $x, 'y' => $y, 'width' => $w, 'height' => $h);
    return $this->Element('rect', $bar, $this->slice_styles[$set]);
  }

  /**
   *  Sort callback function reverse-sorts by value
   */
  public static function svggpsort($a, $b)
  {
    return $b[1] - $a[1];
  }

}

