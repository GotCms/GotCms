<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  Media
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Media;

/**
 * Manage Image
 */
class Image
{
    /**
     * @var resource
     */
    protected $_image;

    /**
     * @var integer
     */
    protected $_width;

    /**
     * @var integer
     */
    protected $_height;

    /**
     * @var resource
     */
    protected $_imageResized;

    /**
     * @var array
     */
    protected $_availableOptions = array('auto', 'crop');

    /**
     * Initialize object
     * @param $filename
     * @return void
     */
    public function __construct($filename = NULL)
    {
        if(!empty($filename))
        {
            $this->open($filename);
        }
    }

    /**
     * Open image
     * @param string $file
     * @return Gc\Media\Image
     */
    public function open($file)
    {
        $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
        $finfo = finfo_open($const); // return mimetype extension
        $mime = finfo_file($finfo, $file);

        switch($mime)
        {
            case 'image/jpeg':
                $image = @imagecreatefromjpeg($file);
            break;
            case 'image/gif':
                $image = @imagecreatefromgif($file);
            break;
            case 'image/png':
                $image = @imagecreatefrompng($file);
            break;
            default:
                $image = FALSE;
            break;
        }


        $this->_image = $image;
        if(empty($image))
        {
            $this->_width  = 0;
            $this->_height = 0;
        }
        else
        {
            $this->_width  = imagesx($this->_image);
            $this->_height = imagesy($this->_image);
        }

        return $this;
    }

    /**
     * Resize image
     * @param integer $new_width
     * @param integer $new_heigh
     * @param string $background_color
     * @param string option can be (auto|crop)
     * @param integer $source_x
     * @param integer $source_y
     * @return Gc\Media\Image
     */
    public function resize($new_width, $new_height, $option = 'auto', $background_color = '#000000', $source_x = 0, $source_y = 0)
    {
        if(empty($this->_image))
        {
            return FALSE;
        }

        if(!in_array($option, $this->_availableOptions))
        {
            $option = 'auto';
        }

        if($this->_width > $new_width and $this->_height > $new_height)
        {
            $optimal_width = $new_width;
            $optimal_height = $new_height;
        }
        else
        {
            $optimal_height = $this->_getSizeByFixedWidth($new_width);
            $optimal_width = $this->_getSizeByFixedHeight($new_height);

            if($optimal_height > $new_height)
            {
                $optimal_height = $this->_getSizeByFixedWidth($optimal_width);
            }
            elseif($optimal_width > $new_width)
            {
                $optimal_width = $this->_getSizeByFixedHeight($optimal_height);
            }
        }

        if($option == 'crop')
        {
            $optimal_width = $optimal_width > $this->_width ? $this->_width : $optimal_width;
            $optimal_height = $optimal_height > $this->_width ? $this->_height : $optimal_height;
            $this->_crop($optimal_width, $optimal_height, $source_x, $source_y);
        }
        else
        {
            $this->_imageResized = imagecreatetruecolor($optimal_width, $optimal_height);
            imagecopyresampled($this->_imageResized, $this->_image, 0, 0, 0, 0, $optimal_width, $optimal_height, $this->_width, $this->_height);
        }

        $tmp_image = @imagecreatetruecolor($new_width, $new_height);

        $rgb_array = $this->hex2rbg($background_color);
        if(empty($rgb_array))
        {
            $background_color = imagecolorallocate($tmp_image, 0, 0, 0);
        }
        else
        {
            $background_color = imagecolorallocate($tmp_image, $rgb_array['red'], $rgb_array['green'], $rgb_array['blue']);
        }

        imagefill($tmp_image, 0, 0, $background_color);

        $dst_x = (int)(0.5 * ($new_width - $optimal_width));
        $dst_y = (int)(0.5 * ($new_height - $optimal_height));
        imagecopyresampled($tmp_image, $this->_imageResized, $dst_x, $dst_y, 0, 0, $optimal_width, $optimal_height, $optimal_width, $optimal_height);
        $this->_imageResized = $tmp_image;

        return $this;
    }

    public function hex2rbg($hex_string)
    {
        $hex_string = preg_replace("/[^0-9A-Fa-f]/", '', $hex_string); // Gets a proper hex string
        $rgb_array = array();
        if(strlen($hex_string) == 6)
        {
            $colorVal = hexdec($hex_string);
            $rgb_array['red'] = 0xFF & ($colorVal >> 0x10);
            $rgb_array['green'] = 0xFF & ($colorVal >> 0x8);
            $rgb_array['blue'] = 0xFF & $colorVal;
        }
        elseif(strlen($hex_string) == 3)
        {
            $rgb_array['red'] = hexdec(str_repeat(substr($hex_string, 0, 1), 2));
            $rgb_array['green'] = hexdec(str_repeat(substr($hex_string, 1, 1), 2));
            $rgb_array['blue'] = hexdec(str_repeat(substr($hex_string, 2, 1), 2));
        }
        else
        {
            return false;
        }

        return $rgb_array;
    }

    /**
     * Get fixed height
     * @param integer $new_height
     * @return integer
     */
    protected function _getSizeByFixedHeight($new_height)
    {
        $ratio = $this->_width / $this->_height;
        $new_width = $new_height * $ratio;

        return $new_width;
    }

    /**
     * Get fixed width
     * @param integer $new_width
     * @return integer
     */
    protected function _getSizeByFixedWidth($new_width)
    {
        $ratio = $this->_height / $this->_width;
        $new_height = $new_width * $ratio;

        return $new_height;
    }

    /**
     * Get optimal crop size auto
     * @param integer $new_width
     * @param integer $new_height
     * @return array
     */
    protected function _getSizeByAuto($new_width, $new_height)
    {
        if($this->_height < $this->_width)
        {
            $optimal_width = $new_width;
            $optimal_height= $this->_getSizeByFixedWidth($new_width);
        }
        elseif($this->_height > $this->_width)
        {
            $optimal_width = $this->_getSizeByFixedHeight($new_height);
            $optimal_height= $new_height;
        }
        else
        {
            if($new_height < $new_width)
            {
                $optimal_width = $new_width;
                $optimal_height= $this->_getSizeByFixedWidth($new_width);
            }
            else if($new_height > $new_width)
            {
                $optimal_width = $this->_getSizeByFixedHeight($new_height);
                $optimal_height= $new_height;
            }
            else
            {
                $optimal_width = $new_width;
                $optimal_height= $new_height;
            }
        }

        return array('optimalWidth' => $optimal_width, 'optimalHeight' => $optimal_height);
    }

    /**
     * Get optimal crop size
     * @param integer $new_width
     * @param integer $new_height
     * @return array
     */
    protected function _getOptimalCrop($new_width, $new_height)
    {
        $height_ratio = $this->_height / $new_height;
        $width_ratio  = $this->_width /  $new_width;

        if($height_ratio < $width_ratio)
        {
            $optimal_ratio = $height_ratio;
        }
        else
        {
            $optimal_ratio = $width_ratio;
        }

        $optimal_height = $this->_height / $optimal_ratio;
        $optimal_width  = $this->_width  / $optimal_ratio;

        return array('optimalWidth' => $optimal_width, 'optimalHeight' => $optimal_height);
    }

    /**
     * Crop image
     * @param integer $new_width
     * @param integer $new_height
     * @param integer $source_x
     * @param integer $source_y
     * @return Gc\Media\Image
     */
    protected function _crop($new_width, $new_height, $source_x = 0, $source_y = 0)
    {
        $crop = $this->_imageResized;
        $this->_imageResized = imagecreatetruecolor($new_width , $new_height);
        imagecopyresampled($this->_imageResized, $this->_image, 0, 0, $source_x, $source_y, $this->_width, $this->_height , $this->_width, $this->_height);

        return $this;
    }

    /**
     * Save image
     * @param string save path
     * @param integer image quality
     * @return boolean
     */
    public function save($save_path, $image_quality = 100)
    {
        if(empty($this->_image) or empty($this->_imageResized))
        {
            return FALSE;
        }

        $extension = strrchr($save_path, '.');
        $extension = strtolower($extension);
        $return = FALSE;
        switch($extension)
        {
            case '.jpg':
            case '.jpeg':
                if(imagetypes() & IMG_JPG)
                {
                    $return = imagejpeg($this->_imageResized, $save_path, $image_quality);
                }
            break;

            case '.gif':
                if(imagetypes() & IMG_GIF)
                {
                    $return = imagegif($this->_imageResized, $save_path);
                }
            break;

            case '.png':
                $scale_quality = round(($image_quality/100) * 9);

                $invert_scale_quality = 9 - $scale_quality;

                if(imagetypes() & IMG_PNG)
                {
                     $return = imagepng($this->_imageResized, $save_path, $invert_scale_quality);
                }
            break;

            default:
                //Nothing to do
            break;
        }

        imagedestroy($this->_imageResized);

        return $return;
    }
}
