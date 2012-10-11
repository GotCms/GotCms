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

require_once 'SVGGraphGridGraph.php';

/**
 * Abstract base class for graphs which use markers
 */
abstract class PointGraph extends GridGraph {

  private $markers = array();
  private $marker_attrs = array();
  private $marker_ids = array();
  private $marker_link_ids = array();
  private $marker_used = array();
  private $marker_elements = array();
  private $marker_types = array();

  /**
   * Changes to crosshair cursor by overlaying a transparent rectangle
   */
  protected function CrossHairs()
  {
    $rect = array(
      'width' => $this->width, 'height' => $this->height,
      'opacity' => 0.0, 'cursor' => 'crosshair'
    );
    return $this->Element('rect', $rect);
  }


  /**
   * Adds a marker to the list
   */
  protected function AddMarker($x, $y, $key, $value, $extra = NULL, $set = 0)
  {
    $this->markers[$set][] = new Marker($x, $y, $key, $value, $extra);
  }

  /**
   * Adds an attribute common to all markers
   */
  protected function AddMarkerAttr($key, $value, $set = 0)
  {
    $this->marker_attrs[$set][$key] = $value;
  }

  /**
   * Draws (linked) markers on the graph
   */
  protected function DrawMarkers()
  {
    if($this->marker_size == 0 || count($this->markers) == 0)
      return '';

    $this->CreateMarkers();

    $markers = '';
    foreach($this->markers as $set => $data) {
      if($this->marker_ids[$set] && count($data))
        $markers .= $this->DrawMarkerSet($set, $data);
    }
    foreach(array_keys($this->marker_used) as $id) {
      $this->defs[] = $this->marker_elements[$id];
    }
    return $markers;
  }

  /**
   * Draws a single set of markers
   */
  protected function DrawMarkerSet($set, &$marker_data)
  {
    $markers = '';
    foreach($marker_data as $m) {
      $markers .= $this->GetMarker($m, $set);
    }
    return $markers;
  }


  /**
   * Returns a marker element
   */
  private function GetMarker($marker, $set)
  {
    $id = $this->marker_ids[$set];
    $use = array(
      'x' => $marker->x,
      'y' => $marker->y,
      'xlink:href' => '#' . $id
    );

    if(is_array($marker->extra))
      $use = array_merge($marker->extra, $use);
    if($this->show_tooltips)
      $this->SetTooltip($use, $marker->key, $marker->value);

    if($this->GetLinkURL($marker->key)) {
      $id = $this->marker_link_ids[$id];
      $use['xlink:href'] = '#' . $id;
      $element = $this->GetLink($marker->key, $this->Element('use', $use));
    } else {
      $element = $this->Element('use', $use);
    }
    if(!isset($this->marker_used[$id]))
      $this->marker_used[$id] = 1;
    return $element;
  }

  /**
   * Return a centred marker for the given set
   */
  protected function DrawLegendEntry($set, $x, $y, $w, $h)
  {
    if(!array_key_exists($set, $this->marker_ids))
      return '';

    $use = array(
      'x' => $x + $w/2,
      'y' => $y + $h/2,
      'xlink:href' => '#' . $this->marker_ids[$set]
    );
    return $this->Element('use', $use);
  }

  /**
   * Creates the marker types
   */
  private function CreateMarkers()
  {
    foreach(array_keys($this->markers) as $set) {
      $id = $this->NewID();
      $marker = array('id' => $id, 'cursor' => 'crosshair');
      if(isset($this->marker_attrs[$set]))
        $marker = array_merge($this->marker_attrs[$set], $marker);

      $type = is_array($this->marker_type) ?
        $this->marker_type[$set % count($this->marker_type)] :
        $this->marker_type;
      $size = is_array($this->marker_size) ?
        $this->marker_size[$set % count($this->marker_size)] :
        $this->marker_size;

      $stroke_width = '';
      $stroke_colour = $this->marker_stroke_colour;
      if(is_array($stroke_colour))
        $stroke_colour = $stroke_colour[$set % count($stroke_colour)];
      if(!empty($stroke_colour) && $stroke_colour != 'none') {
        $marker['stroke'] = $stroke_colour;

        $stroke_width = $this->marker_stroke_width;
        if(is_array($stroke_width))
          $stroke_width = $stroke_width[$set % count($stroke_width)];
        if(!empty($stroke_width))
          $marker['stroke-width'] = $stroke_width;
      }

      if(isset($this->marker_colour)) {
        $marker['fill'] = is_array($this->marker_colour) ?
          $this->marker_colour[$set % count($this->marker_colour)] :
          $this->marker_colour;
      } else {
        $marker['fill'] = $this->GetColour($set % count($this->colours), true);
      }

      $m_key = "$type:$size:{$marker['fill']}:$stroke_width:$stroke_colour";
      if(isset($this->marker_types[$m_key])) {
        $this->marker_ids[$set] = $this->marker_types[$m_key];
      } else {

        $a = $size; // will be repeated a lot, and 'a' is smaller
        $element = 'path';
        switch($type) {
        case 'triangle' :
          $o = $a * tan(M_PI / 6);
          $h = $a / cos(M_PI / 6);
          $marker['d'] = "M$a,$o L0,-$h L-$a,$o z";
          break;
        case 'diamond' :
          $marker['d'] = "M0 -{$a}L$a 0 0 $a -$a 0z";
          break;
        case 'square' :
          $element = 'rect';
          $marker['x'] = $marker['y'] = -$a;
          $marker['width'] = $marker['height'] = $a * 2;
          break;
        case 'x' :
          $marker['transform'] = 'rotate(45)';
          // no break - 'x' is a cross rotated by 45 degrees
        case 'cross' :
          $t = $a / 4;
          $marker['d'] = "M-$a,-$t L-$a,$t -$t,$t -$t,$a " .
            "$t,$a $t,$t $a,$t " .
            "$a,-$t $t,-$t $t,-$a " .
            "-$t,-$a -$t,-$t z";
          break;
        case 'octagon' :
          $t = $a * sin(M_PI / 8);
          $marker['d'] = "M$t -{$a}L$a -$t $a $t $t $a -$t $a " .
            "-$a $t -$a -$t -$t -{$a}z";
          break;
        case 'star' :
          $t = $a * 0.382;
          $x1 = $t * sin(M_PI * 0.8);
          $y1 = $t * cos(M_PI * 0.8);
          $x2 = $a * sin(M_PI * 0.6);
          $y2 = $a * cos(M_PI * 0.6);
          $x3 = $t * sin(M_PI * 0.4);
          $y3 = $t * cos(M_PI * 0.4);
          $x4 = $a * sin(M_PI * 0.2);
          $y4 = $a * cos(M_PI * 0.2);
          $marker['d'] = "M0 -{$a}L$x1 $y1 $x2 $y2 $x3 $y3 $x4 $y4 0 $t " .
            "-$x4 $y4 -$x3 $y3 -$x2 $y2 -$x1 $y1 z";
          break;
        case 'threestar' :
          $t = $a / 4;
          $t1 = $t * cos(M_PI / 6);
          $t2 = $t * sin(M_PI / 6);
          $a1 = $a * cos(M_PI / 6);
          $a2 = $a * sin(M_PI / 6);
          $marker['d'] = "M0 -{$a}L$t1 -$t2 $a1 $a2 0 $t -$a1 $a2 -$t1 -{$t2}z";
          break;
        case 'fourstar' :
          $t = $a / 4;
          $marker['d'] = "M0 -{$a}L$t -$t $a 0 $t $t " .
            "0 $a -$t $t -$a 0 -$t -{$t}z";
          break;
        case 'eightstar' :
          $t = $a * sin(M_PI / 8);
          $marker['d'] = "M0 -{$t}L$t -$a $t -$t $a -$t $t 0 " .
            "$a $t $t $t $t $a 0 $t -$t $a -$t $t -$a $t -$t 0 " .
            "-$a -$t -$t -$t -$t -{$a}z";
          break;
        case 'asterisk' :
          $t = $a / 3;
          $x1 = $a * sin(M_PI * 0.9);
          $y1 = $a * cos(M_PI * 0.9);
          $x2 = $t * sin(M_PI * 0.8);
          $y2 = $t * cos(M_PI * 0.8);
          $x3 = $a * sin(M_PI * 0.7);
          $y3 = $a * cos(M_PI * 0.7);
          $x4 = $a * sin(M_PI * 0.5);
          $y4 = $a * cos(M_PI * 0.5);
          $x5 = $t * sin(M_PI * 0.4);
          $y5 = $t * cos(M_PI * 0.4);
          $x6 = $a * sin(M_PI * 0.3);
          $y6 = $a * cos(M_PI * 0.3);
          $x7 = $a * sin(M_PI * 0.1);
          $y7 = $a * cos(M_PI * 0.1);
          $marker['d'] = "M$x1 {$y1}L$x2 $y2 $x3 $y3 $x4 $y4 $x5 $y5 " .
            "$x6 $y6 $x7 $y7 0 $t -$x7 $y7 -$x6 $y6 -$x5 $y5 -$x4 $y4 " . 
            "-$x3 $y3 -$x2 $y2 -$x1 ${y1}z";
          break;
        case 'pentagon' :
          $x1 = $a * sin(M_PI * 0.4);
          $y1 = $a * cos(M_PI * 0.4);
          $x2 = $a * sin(M_PI * 0.2);
          $y2 = $a * cos(M_PI * 0.2);
          $marker['d'] = "M0 -{$a}L$x1 -$y1 $x2 $y2 -$x2 $y2 -$x1 -{$y1}z";
          break;
        case 'hexagon' :
          $x = $a * sin(M_PI / 3);
          $y = $a * cos(M_PI / 3);
          $marker['d'] = "M0 -{$a}L$x -$y $x $y 0 $a -$x $y -$x -{$y}z";
          break;
        case 'circle' :
        default :
          $element = 'circle';
          $marker['r'] = $size;
        }

        $this->marker_elements[$marker['id']] = 
          $this->Element('symbol', NULL, NULL, 
            $this->Element($element, $marker, NULL));

        // add link version
        unset($marker['cursor']);
        $this->marker_link_ids[$marker['id']] = $this->NewID();
        $marker['id'] = $this->marker_link_ids[$marker['id']];
        $this->marker_elements[$marker['id']] =
          $this->Element('symbol', NULL, NULL,
            $this->Element($element, $marker, NULL));

        // set the ID for this data set to use
        $this->marker_ids[$set] = $id;

        // save this marker style for reuse
        $this->marker_types[$m_key] = $id;
      }
    }
  }

}

/**
 * These functions are used by scatter graphs to find the maximum and
 * minimum keys and values in scatter_2d data
 */
function pointgraph_vmax($m, $e)
{
  if(is_null($m))
    return $e[1];
  return $e[1] > $m ? $e[1] : $m;
}

function pointgraph_vmin($m, $e)
{
  if(is_null($m))
    return $e[1];
  return $e[1] < $m ? $e[1] : $m;
}

function pointgraph_kmax($m, $e)
{
  if(is_null($m))
    return $e[0];
  return $e[0] > $m ? $e[0] : $m;
}

function pointgraph_kmin($m, $e)
{
  if(is_null($m))
    return $e[0];
  return $e[0] < $m ? $e[0] : $m;
}

class Marker {

  public $x, $y, $key, $value, $extra;

  public function __construct($x, $y, $k, $v, $extra)
  {
    $this->x = $x;
    $this->y = $y;
    $this->key = $k;
    $this->value = $v;
    $this->extra = $extra;
  }
}

