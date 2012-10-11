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

require_once 'SVGGraphMultiGraph.php';
require_once 'SVGGraphHorizontalBarGraph.php';

class HorizontalStackedBarGraph extends HorizontalBarGraph {

  protected $multi_graph;
  protected $legend_reverse = false;

  protected function Draw()
  {
    $body = $this->Grid() . $this->Guidelines(SVGG_GUIDELINE_BELOW);

    $bar_height = ($this->bar_space >= $this->bar_unit_height ? '1' : 
      $this->bar_unit_height - $this->bar_space);
    $bar_style = array();
    $this->SetStroke($bar_style);
    $bar = array('height' => $bar_height);

    $bnum = 0;
    $bspace = $this->bar_space / 2;
    $b_start = $this->height - $this->pad_bottom - ($this->bar_space / 2);
    $ccount = count($this->colours);
    $chunk_count = count($this->values);
    $groups = array_fill(0, $chunk_count, '');

    foreach($this->multi_graph->all_keys as $k) {
      $bar_pos = $this->GridPosition($k, $bnum);
      if(!is_null($bar_pos)) {
        $bar['y'] = $bar_pos - $bspace - $bar_height;

        $xpos = $xneg = $this->pad_left + $this->x0;
        $xpos = $xneg = $xplus = $xminus = 0;
        for($j = 0; $j < $chunk_count; ++$j) {
          $value = $this->multi_graph->GetValue($k, $j);
          $this->Bar($value >= 0 ? $value + $xplus : $value - $xminus, $bar);
          if($value < 0) {
            $bar['width'] -= $xneg;
            $xneg += $bar['width'];
            $xminus -= $value;
          } else {
            $bar['width'] -= $xpos;
            $bar['x'] += $xpos;
            $xpos += $bar['width'];
            $xplus += $value;
          }

          if($bar['width'] > 0) {
            $bar_style['fill'] = $this->GetColour($j % $ccount);

            if($this->show_tooltips)
              $this->SetTooltip($bar, $value, null,
                !$this->compat_events && $this->show_bar_labels);
            if($this->show_bar_labels) {
              $rect = $this->Element('rect', $bar, $bar_style);
              $rect .= $this->BarLabel($value, $bar, $j + 1 < $chunk_count);
              $body .= $this->GetLink($k, $rect);
            } else {
              $rect = $this->Element('rect', $bar);
              $groups[$j] .= $this->GetLink($k, $rect);
            }
            unset($bar['id']); // clear ID for next generated value

            if(!array_key_exists($j, $this->bar_styles))
              $this->bar_styles[$j] = $bar_style;
          }
        }
      }
      ++$bnum;
    }
    if(!$this->show_bar_labels) {
      foreach($groups as $j => $g)
        if(array_key_exists($j, $this->bar_styles))
          $body .= $this->Element('g', NULL, $this->bar_styles[$j], $g);
    }

    $body .= $this->Guidelines(SVGG_GUIDELINE_ABOVE) . $this->Axes();
    return $body;
  }

  /**
   * Overridden to prevent drawing behind higher bars
   * $offset_y should be true for inner bars
   */
  protected function BarLabel($value, &$bar, $offset_x = null)
  {
    list($text_size) = $this->TextSize(strlen($value),
      $this->bar_label_font_size, $this->bar_label_font_adjust);
    $space = $this->bar_label_space;
    if($offset_x) {

      // bar too small, would be above
      if($bar['width'] < $text_size + 2 * $space)
        return parent::BarLabel($value, $bar, ($bar['width'] + $text_size)/2);

      // option set to above
      if($this->bar_label_position == 'above') {
        $this->bar_label_position = 'top';
        $label = parent::BarLabel($value, $bar);
        $this->bar_label_position = 'above';
        return $label;
      }
    }
    return parent::BarLabel($value, $bar);
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
   * Find the longest data set
   */
  protected function GetHorizontalCount()
  {
    return $this->multi_graph->KeyCount();
  }

  /**
   * Returns the maximum (stacked) value
   */
  protected function GetMaxValue()
  {
    $stack = array();
    $chunk_count = count($this->values);

    foreach($this->multi_graph->all_keys as $k) {
      $s = 0;
      for($j = 0; $j < $chunk_count; ++$j) {
        $v = $this->multi_graph->GetValue($k, $j);
        if($v > 0)
          $s += $v;
      }
      $stack[] = $s;
    }
    return max($stack);
  }

  /**
   * Returns the minimum (stacked) value
   */
  protected function GetMinValue()
  {
    $stack = array();
    $chunk_count = count($this->values);

    foreach($this->multi_graph->all_keys as $k) {
      $s = 0;
      for($j = 0; $j < $chunk_count; ++$j) {
        $v = $this->multi_graph->GetValue($k, $j);
        if($v <= 0)
          $s += $v;
      }
      $stack[] = $s;
    }
    return min($stack);
  }

  /**
   * Returns the key from the MultiGraph
   */
  protected function GetKey($index)
  {
    return $this->multi_graph->GetKey($index);
  }

  /**
   * Returns the maximum key from the MultiGraph
   */
  protected function GetMaxKey()
  {
    return $this->multi_graph->GetMaxKey();
  }

  /**
   * Returns the minimum key from the MultiGraph
   */
  protected function GetMinKey()
  {
    return $this->multi_graph->GetMinKey();
  }

  /**
   * Overload to measure keys
   */
  protected function LabelAdjustment($longest_v = 1000, $longest_h = 100)
  {
    GridGraph::LabelAdjustment($longest_h, $longest_v);
  }

  /**
   * Return the longest of all keys
   */
  protected function GetLongestKey()
  {
    return $this->multi_graph->GetLongestKey();
  }
}

