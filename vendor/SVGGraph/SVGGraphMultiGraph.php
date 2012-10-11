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

class MultiGraph {

  private $values;
  private $force_assoc;
  public $all_keys;

  public function __construct($values, $force_assoc)
  {
    $this->values =& $values;
    $this->force_assoc = $force_assoc;
    $this->all_keys = array();

    // combine all array keys
    $keys = array();
    foreach($this->values as $k => $v)
      foreach($v as $ik => $iv)
        $keys[$ik] = 1;
    $this->all_keys = array_keys($keys);
  }


  /**
   * Counts all the unique data keys
   */
  public function KeyCount()
  {
    return count($this->all_keys);
  }


  /**
   * Returns a value by column and chunk
   */
  public function GetValue($column, $chunk)
  {
    return isset($this->values[$chunk]) &&
      isset($this->values[$chunk][$column]) ?
      $this->values[$chunk][$column] : null;
  }


  /**
   * Returns the maximum value
   */
  public function GetMaxValue()
  {
    $maxima = array();
    $chunk_count = count($this->values);
    for($i = 0; $i < $chunk_count; ++$i)
      if(!empty($this->values[$i]))
        $maxima[] = max($this->values[$i]);

    return max($maxima);
  }


  /**
   * Returns the minimum value
   */
  public function GetMinValue()
  {
    $minima = array();
    $chunk_count = count($this->values);
    for($i = 0; $i < $chunk_count; ++$i)
      if(!empty($this->values[$i]))
        $minima[] = min($this->values[$i]);

    return min($minima);
  }


  /**
   * Returns the key for a given index
   */
  public function GetKey($index)
  {
    if(!$this->force_assoc && is_int($this->all_keys[0]))
      return $index;

    // round to the nearest index
    $i = round($index);
    return isset($this->all_keys[$i]) ? $this->all_keys[$i] : null;
  }

  /**
   * Returns the maximum key value
   */
  public function GetMaxKey()
  {
    return !$this->force_assoc && is_numeric($this->all_keys[0]) ?
      max($this->all_keys) : $this->KeyCount() - 1;
  }

  /**
   * Returns the minimum key value
   */
  public function GetMinKey()
  {
    return !$this->force_assoc && is_numeric($this->all_keys[0]) ?
      min($this->all_keys) : 0;
  }

  /**
   * Returns the longest key
   */
  public function GetLongestKey()
  {
    $longest_key = '';
    $max_len = 0;
    foreach($this->all_keys as $k) {
      $len = strlen($k);
      if($len > $max_len) {
        $max_len = $len;
        $longest_key = $k;
      }
    }
    return $longest_key;
  }

  /**
   * Returns the maximum sum value
   */
  public function GetMaxSumValue()
  {
    $stack = array();
    $chunk_count = count($this->values);

    foreach($this->all_keys as $k) {
      $s = 0;
      for($j = 0; $j < $chunk_count; ++$j) {
        $v = $this->GetValue($k, $j);
        if($v > 0)
          $s += $v;
      }
      $stack[] = $s;
    }
    return max($stack);
  }

  /**
   * Returns the minimum sum value
   */
  public function GetMinSumValue()
  {
    $stack = array();
    $chunk_count = count($this->values);

    foreach($this->all_keys as $k) {
      $s = 0;
      for($j = 0; $j < $chunk_count; ++$j) {
        $v = $this->GetValue($k, $j);
        if($v <= 0)
          $s += $v;
      }
      $stack[] = $s;
    }
    return min($stack);
  }

  /**
   * Returns an option from array, or non-array option
   */
  public function Option($o, $i)
  {
    return is_array($o) ? $o[$i % count($o)] : $o;
  }
}

