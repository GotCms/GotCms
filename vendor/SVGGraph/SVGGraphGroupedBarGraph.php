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
require_once 'SVGGraphBarGraph.php';

class GroupedBarGraph extends BarGraph {

  protected $multi_graph;

  protected function Draw()
  {
    $body = $this->Grid() . $this->Guidelines(SVGG_GUIDELINE_BELOW);

    $chunk_count = count($this->values);
    $gap_count = $chunk_count - 1;
    $bar_width = ($this->bar_space >= $this->bar_unit_width ? '1' : 
      $this->bar_unit_width - $this->bar_space);
    $chunk_gap = $gap_count > 0 ? $this->group_space : 0;
    if($gap_count > 0 && $chunk_gap * $gap_count > $bar_width - $chunk_count)
      $chunk_gap = ($bar_width - $chunk_count) / $gap_count;
    $chunk_width = ($bar_width - ($chunk_gap * ($chunk_count - 1)))
      / $chunk_count;
    $chunk_unit_width = $chunk_width + $chunk_gap;
    $bar_style = array();
    $this->SetStroke($bar_style);
    $bar = array('width' => $chunk_width);

    $b_start = $this->pad_left + ($this->bar_space / 2);
    $bspace = $this->bar_space / 2;
    $bnum = 0;
    $ccount = count($this->colours);
    $groups = array_fill(0, $chunk_count, '');

    foreach($this->multi_graph->all_keys as $k) {
      $bar_pos = $this->GridPosition($k, $bnum);
      if(!is_null($bar_pos)) {
        for($j = 0; $j < $chunk_count; ++$j) {
          $bar['x'] = $bspace + $bar_pos + ($j * $chunk_unit_width);
          $value = $this->multi_graph->GetValue($k, $j);
          $this->Bar($value, $bar);

          if($bar['height'] > 0) {
            $bar_style['fill'] = $this->GetColour($j % $ccount);

            if($this->show_tooltips)
              $this->SetTooltip($bar, $value, null,
                !$this->compat_events && $this->show_bar_labels);
            if($this->show_bar_labels) {
              $rect = $this->Element('rect', $bar, $bar_style);
              $rect .= $this->BarLabel($value, $bar);
              $body .= $this->GetLink($k, $rect);
            } else {
              $rect = $this->Element('rect', $bar);
              $groups[$j] .= $this->GetLink($k, $rect);
            }
            unset($bar['id']); // clear for next generated value

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
   * construct multigraph
   */
  public function Values($values)
  {
    parent::Values($values);
    $this->multi_graph = new MultiGraph($this->values, $this->force_assoc);
  }

  /**
   * Find the full length
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
   * Return box for legend
   */
  protected function DrawLegendEntry($set, $x, $y, $w, $h)
  {
    if(!array_key_exists($set, $this->bar_styles))
      return '';

    $bar = array('x' => $x, 'y' => $y, 'width' => $w, 'height' => $h);
    return $this->Element('rect', $bar, $this->bar_styles[$set]);
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

}

