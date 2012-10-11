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

/**
 * Class for calculating axis measurements
 */
class Axis {

  protected $length;
  protected $max_value;
  protected $min_value;
  protected $unit_size;
  protected $min_unit;
  protected $fit;
  protected $zero;
  protected $uneven;

  public function __construct($length, $max_val, $min_val = 0,
    $min_unit = 0, $fit = false)
  {
    if($max_val <= $min_val && $min_unit == 0)
      throw new Exception('Zero length axis');
    $this->length = $length;
    $this->max_value = $max_val;
    $this->min_value = $min_val;
    $this->min_unit = $min_unit;
    $this->fit = $fit;
    $this->uneven = false;
  }

  /**
   * Returns TRUE if the number $n is 'nice'
   */
  private function nice($n, $m)
  {
    if(is_integer($n) && ($n % 100 == 0 || $n % 10 == 0 || $n % 5 == 0))
      return true;

    if($this->min_unit) {
      $d = $n / $this->min_unit;
      if($d != floor($d))
        return false;
    }
    $s = (string)$n;
    if(preg_match('/^\d(\.\d{1,1})$/', $s))
      return true;
    if(preg_match('/^\d+$/', $s))
      return true;

    return false;
  }


  /**
   * Subdivide when the divisions are too large
   */
  private function sub_division($length, $min, &$count, &$neg_count,
    &$magnitude)
  {
    $m = $magnitude * 0.5;
    $magnitude = $m;
    $count *= 2;
    $neg_count *= 2;
  }

  /**
   * Determine the axis divisions
   */
  private function find_division($length, $min, &$count, &$neg_count,
    &$magnitude)
  {
    if($length / $count >= $min)
      return;

    $c = $count - 1;
    $inc = 0;
    while($c > 1) {
      $m = ($count + $inc) / $c;
      $l = $length / $c;
      $test_below = $neg_count ? $c * $neg_count / $count : 1;
      if($this->nice($m, $count + $inc)) {
        if($l >= $min && $test_below - floor($test_below) == 0) {
          $magnitude *= ($count + $inc) / $c;
          $neg_count *= $c / $count;
          $count = $c;
          return;
        }
        --$c;
        $inc = 0;
      } elseif(!$this->fit && $count % 2 == 1 && $inc == 0) {
        $inc = 1;
      } else {
        --$c;
        $inc = 0;
      }
    }

    // try to balance the +ve and -ve a bit 
    if($neg_count) {
      $c = $count + 1;
      $p_count = $count - $neg_count;
      if($p_count > $neg_count && ($neg_count == 1 || $c % $neg_count))
        ++$neg_count;
      ++$count;
    }
  }

  /**
   * Returns the grid spacing
   */
  public function Grid($min, $round_up = false)
  {
    $this->uneven = false;
    $negative = $this->min_value < 0;
    $min_sub = max($min, $this->length / 200);

    if($round_up || $this->min_value == $this->max_value)
      $this->max_value += $this->min_unit;
    $scale = $this->max_value - $this->min_value;

    // get magnitude from greater of |+ve|, |-ve|
    $abs_min = abs($this->min_value);
    $magnitude = max(pow(10, floor(log10($scale))), $this->min_unit);
    if($this->fit) {
      $count = ceil($scale / $magnitude);
    } else {
      $count = ceil($this->max_value / $magnitude) - 
        floor($this->min_value / $magnitude);
    }

    if($count <= 5 && $magnitude > $this->min_unit) {
      $magnitude *= 0.1;
      $count = ceil($this->max_value / $magnitude) - 
        floor($this->min_value / $magnitude);
    }

    $neg_count = ceil($abs_min / $magnitude);
    $this->find_division($this->length, $min_sub, $count, $neg_count,
      $magnitude);
    $grid = $this->length / $count;

    // guard this loop in case the numbers are too awkward to fit
    $guard = 20;
    while($grid < $min && --$guard) {
      $this->find_division($this->length, $min_sub, $count, $neg_count,
        $magnitude);
      $grid = $this->length / $count;
    }
    if($guard == 0) {
      // could not find a division
      while($grid < $min && $count > 1) {
        $count *= 0.5;
        $magnitude *= 2;
        $grid = $this->length / $count;
        $this->uneven = true;
      }

    } elseif(!$this->fit && $magnitude > $this->min_unit &&
      $grid / $min > 2) {
      // division still seems a bit coarse
      $this->sub_division($this->length, $min_sub, $count, $neg_count,
        $magnitude);
      $grid = $this->length / $count;
    }

    $this->unit_size = $this->length / ($magnitude * $count);
    $this->zero = $negative ? $neg_count * $grid :
      -$this->min_value * $grid / $magnitude;

    return $grid;
  }

  /**
   * Returns the size of a unit in grid space
   */
  public function Unit()
  {
    if(!isset($this->unit_size))
      $this->Grid(1);

    return $this->unit_size;
  }

  /**
   * Returns the distance along the axis where 0 should be
   */
  public function Zero()
  {
    if(!isset($this->zero))
      $this->Grid(1);

    return $this->zero;
  }

  /**
   * Returns TRUE if the grid spacing does not fill the grid
   */
  public function Uneven()
  {
    return $this->uneven;
  }
}

