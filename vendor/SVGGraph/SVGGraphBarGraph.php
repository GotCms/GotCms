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

class BarGraph extends GridGraph {

  protected $bar_styles = array();
  protected $label_centre = TRUE;

  protected function Draw()
  {
    $values = $this->GetValues();
    $body = $this->Grid() . $this->Guidelines(SVGG_GUIDELINE_BELOW);

    $bar_width = ($this->bar_space >= $this->bar_unit_width ? '1' : 
      $this->bar_unit_width - $this->bar_space);
    $bar_style = array();
    $this->SetStroke($bar_style);

    $bnum = 0;
    $bspace = $this->bar_space / 2;
    $ccount = count($this->colours);
    foreach($values as $key => $value) {
      // assign bar in the loop so it doesn't keep ID
      $bar = array('width' => $bar_width);
      $bar_pos = $this->GridPosition($key, $bnum);
      if(!is_null($bar_pos)) {
        $bar['x'] = $bspace + $bar_pos;
        $this->Bar($value, $bar);

        if($bar['height'] > 0) {
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
   * Fills in the y-position and height of a bar
   */
  protected function Bar($value, &$bar)
  {
    $y = $this->height - $this->pad_bottom - $this->y0;
    $l1 = $this->ClampVertical($y);
    $l2 = $this->ClampVertical($y - ($value * $this->bar_unit_height));
    $bar['y'] = min($l1, $l2);
    $bar['height'] = abs($l1-$l2);
  }

  /**
   * Text labels in or above the bar
   */
  protected function BarLabel($value, &$bar, $offset_y = null)
  {
    $font_size = $this->bar_label_font_size;
    $space = $this->bar_label_space;
    $x = $bar['x'] + ($bar['width'] / 2);
    $colour = $this->bar_label_colour;
    $acolour = $this->bar_label_colour_above;

    if(!is_null($offset_y)) {
      $y = $bar['y'] + $offset_y;
    } else {
      // find positions
      $pos = $this->bar_label_position;
      if(empty($pos))
        $pos = 'top';
      $top = $bar['y'] + $font_size + $space;
      $bottom = $bar['y'] + $bar['height'] - $space;
      if($top > $bottom)
        $pos = 'above';

      $swap = ($bar['y'] >= $this->height - $this->pad_bottom - $this->y0);
      switch($pos) {
      case 'above' :
        $y = $swap ? $bar['y'] + $bar['height'] + $font_size + $space :
          $bar['y'] - $space;
        if(!empty($acolour))
          $colour = $acolour;
        break;
      case 'bottom' :
        $y = $swap ? $top : $bottom;
        break;
      case 'centre' :
        $y = $bar['y'] + ($bar['height'] + $font_size) / 2;
        break;
      case 'top' :
      default :
        $y = $swap ? $bottom : $top;
        break;
      }
    }

    $text = array(
      'x' => $x,
      'y' => $y,
      'text-anchor' => 'middle',
      'font-family' => $this->bar_label_font,
      'font-size' => $font_size,
      'fill' => $colour,
    );
    if($this->bar_label_font_weight != 'normal')
      $text['font-weight'] = $this->bar_label_font_weight;
    return $this->Element('text', $text, NULL, $value);
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

