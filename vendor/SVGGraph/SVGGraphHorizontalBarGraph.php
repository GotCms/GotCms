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

require_once 'SVGGraphGridGraph.php';

class HorizontalBarGraph extends GridGraph {

  protected $flip_axes = true;
  protected $label_centre = true;
  protected $legend_reverse = true;
  protected $bar_styles = array();

  protected function Draw()
  {
    $values = $this->GetValues();
    $body = $this->Grid() . $this->Guidelines(SVGG_GUIDELINE_BELOW);

    $bar_height = ($this->bar_space >= $this->bar_unit_height ? '1' : 
      $this->bar_unit_height - $this->bar_space);
    $bar_style = array();
    $this->SetStroke($bar_style);

    $bnum = 0;
    $bspace = $this->bar_space / 2;
    $ccount = count($this->colours);
    foreach($values as $key => $value) {
      $bar = array('height' => $bar_height);
      $bar_pos = $this->GridPosition($key, $bnum);
      if(!is_null($bar_pos)) {
        $bar['y'] = $bar_pos - $bspace - $bar_height;
        $this->Bar($value, $bar);

        if($bar['width'] > 0) {
          $bar_style['fill'] = $this->GetColour($bnum % $ccount);

          if($this->show_tooltips)
            $this->SetTooltip($bar, $value, null,
              !$this->compat_events && $this->show_bar_labels);
          $rect = $this->Element('rect', $bar, $bar_style);
          if($this->show_bar_labels)
            $rect .= $this->BarLabel($value, $bar);
          $body .= $this->GetLink($key, $rect);
          $this->bar_styles[] = $bar_style;
        }
      }
      ++$bnum;
    }

    $body .= $this->Guidelines(SVGG_GUIDELINE_ABOVE) . $this->Axes();
    return $body;
  }

  /**
   * Fills in the x-position and width of a bar
   */
  protected function Bar($value, &$bar)
  {
    $x0 = $this->pad_left + $this->x0;
    $l1 = $this->ClampHorizontal($x0);
    $l2 = $this->ClampHorizontal($x0 + ($value * $this->bar_unit_width));
    $bar['x'] = min($l1, $l2);
    $bar['width'] = abs($l1-$l2);
  }

  /**
   * Text labels in or above the bar
   */
  protected function BarLabel($value, &$bar, $offset_x = null)
  {
    $font_size = $this->bar_label_font_size;
    list($text_size) = $this->TextSize(strlen($value), 
      $this->bar_label_font_size, $this->bar_label_font_adjust);
    $space = $this->bar_label_space;
    $y = $bar['y'] + ($bar['height'] + $font_size) / 2 - $font_size / 8;
    $colour = $this->bar_label_colour;
    $acolour = $this->bar_label_colour_above;
    $anchor = 'end';

    if(!is_null($offset_x)) {
      $x = $bar['x'] + $bar['width'] - $offset_x;
      $anchor = 'start';
    } else {
      // find positions - if $top > $bottom, text will not fit
      $pos = $this->bar_label_position;
      if(empty($pos))
        $pos = 'top';
      $top = $bar['x'] + $bar['width'] - $space;
      $bottom = $bar['x'] + $space;
      if($top - $text_size < $bottom)
        $pos = 'above';

      $swap = ($bar['x'] + $bar['width'] <= $this->pad_left + $this->x0);
      switch($pos) {
      case 'above' :
        $x = $swap ? $bottom - $space * 2 : $top + $space * 2;
        $anchor = $swap ? 'end' : 'start';
        if(!empty($acolour))
          $colour = $acolour;
        break;
      case 'bottom' :
        $x = $swap ? $top : $bottom;
        $anchor = $swap ? 'end' : 'start';
        break;
      case 'centre' :
        $x = $bar['x'] + $bar['width'] / 2;
        $anchor = 'middle';
        break;
      case 'top' :
      default :
        $x = $swap ? $bottom : $top;
        $anchor = $swap ? 'start' : 'end';
        break;
      }
    }

    $text = array(
      'x' => $x,
      'y' => $y,
      'text-anchor' => $anchor,
      'font-family' => $this->bar_label_font,
      'font-size' => $font_size,
      'fill' => $colour,
    );
    if($this->bar_label_font_weight != 'normal')
      $text['font-weight'] = $this->bar_label_font_weight;
    return $this->Element('text', $text, NULL, $value);
  }

  /**
   * Overload to flip axes
   */
  protected function LabelAdjustment($longest_v = 1000, $longest_h = 100)
  {
    parent::LabelAdjustment($longest_h, $longest_v);
  }

  /**
   * Return box for legend
   */
  protected function DrawLegendEntry($set, $x, $y, $w, $h)
  {
    if(!array_key_exists($set, $this->bar_styles))
      return '';

    $bar = array('x' => $x, 'y' => $y, 'width' => $w, 'height' => $h);
    return $this->Element('rect', $bar, $this->bar_styles[$set]);
  }

}

