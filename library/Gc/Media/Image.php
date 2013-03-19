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
 * @category   Gc
 * @package    Library
 * @subpackage Media
 * @author     Pierre Rambaud (GoT) http://rambaudpierre.fr
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Media;

/**
 * Manage Image
 *
 * @category   Gc
 * @package    Library
 * @subpackage Media
 */
class Image
{
    /**
     * Original image
     *
     * @var resource
     */
    protected $image;

    /**
     * Original image width
     *
     * @var integer
     */
    protected $width;

    /**
     * Original image height
     *
     * @var integer
     */
    protected $height;

    /**
     * Image resized
     *
     * @var resource
     */
    protected $imageResized;

    /**
     * Available options
     *
     * @var array
     */
    protected $availableOptions = array('auto', 'crop');

    /**
     * Initialize object
     *
     * @param string $filename filename
     *
     * @return void
     */
    public function __construct($filename = null)
    {
        if (!empty($filename)) {
            $this->open($filename);
        }
    }

    /**
     * Open image
     *
     * @param string $file File
     *
     * @return \Gc\Media\Image
     */
    public function open($file)
    {
        $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
        $finfo = finfo_open($const); // return mimetype extension
        $mime  = finfo_file($finfo, $file);

        switch($mime) {
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
                $image = false;
                break;
        }


        $this->image = $image;
        if (empty($image)) {
            $this->width  = 0;
            $this->height = 0;
        } else {
            $this->width  = imagesx($this->image);
            $this->height = imagesy($this->image);
        }

        return $this;
    }

    /**
     * Resize image
     *
     * @param integer $new_width        New width
     * @param integer $new_height       New height
     * @param string  $option           Option can be (auto|crop)
     * @param string  $background_color Background color
     * @param integer $source_x         Source x
     * @param integer $source_y         Source y
     *
     * @return \Gc\Media\Image
     */
    public function resize(
        $new_width,
        $new_height,
        $option = 'auto',
        $background_color = '#000000',
        $source_x = 0,
        $source_y = 0
    ) {
        if (empty($this->image)) {
            return false;
        }

        if (!in_array($option, $this->availableOptions)) {
            $option = 'auto';
        }

        if ($option == 'crop') {
            $optimalwidth  = $new_width;
            $optimalheight = $new_height;
            if ($this->width < $new_width) {
                $optimalwidth = $this->width;
            }

            if ($this->height < $new_height) {
                $optimalheight = $this->height;
            }

            $this->crop($optimalwidth, $optimalheight, $source_x, $source_y);
        } else {
            $optimalheight = $this->getSizeByFixedWidth($new_width);
            $optimalwidth  = $this->getSizeByFixedHeight($new_height);
            if ($optimalheight > $new_height) {
                $optimalheight = $this->getSizeByFixedWidth($optimalwidth);
            } elseif ($optimalwidth > $new_width) {
                $optimalwidth = $this->getSizeByFixedHeight($optimalheight);
            }

            $this->imageResized = imagecreatetruecolor($optimalwidth, $optimalheight);
            imagecopyresampled(
                $this->imageResized,
                $this->image,
                0,
                0,
                0,
                0,
                $optimalwidth,
                $optimalheight,
                $this->width,
                $this->height
            );
        }

        $tmpimage = @imagecreatetruecolor($new_width, $new_height);

        $rgb_array = $this->hex2rgb($background_color);
        if (empty($rgb_array)) {
            $background_color = imagecolorallocate($tmpimage, 0, 0, 0);
        } else {
            $background_color = imagecolorallocate(
                $tmpimage,
                $rgb_array['red'],
                $rgb_array['green'],
                $rgb_array['blue']
            );
        }

        imagefill($tmpimage, 0, 0, $background_color);

        $dst_x = (int) (0.5 * ($new_width - $optimalwidth));
        $dst_y = (int) (0.5 * ($new_height - $optimalheight));
        imagecopyresampled(
            $tmpimage,
            $this->imageResized,
            $dst_x,
            $dst_y,
            0,
            0,
            $optimalwidth,
            $optimalheight,
            $optimalwidth,
            $optimalheight
        );
        $this->imageResized = $tmpimage;

        return $this;
    }

    /**
     * Convert hexa string to rbg
     *
     * @param string $hex_string Hexadecimal string
     *
     * @return array
     */
    public function hex2rgb($hex_string)
    {
        $hex_string = preg_replace('/[^0-9A-Fa-f]/', '', $hex_string); // Gets a proper hex string
        $rgb_array  = array();
        if (strlen($hex_string) == 6) {
            $color_val          = hexdec($hex_string);
            $rgb_array['red']   = 0xFF & ($color_val >> 0x10);
            $rgb_array['green'] = 0xFF & ($color_val >> 0x8);
            $rgb_array['blue']  = 0xFF & $color_val;
        } elseif (strlen($hex_string) == 3) {
            $rgb_array['red']   = hexdec(str_repeat(substr($hex_string, 0, 1), 2));
            $rgb_array['green'] = hexdec(str_repeat(substr($hex_string, 1, 1), 2));
            $rgb_array['blue']  = hexdec(str_repeat(substr($hex_string, 2, 1), 2));
        } else {
            return false;
        }

        return $rgb_array;
    }

    /**
     * Get fixed height
     *
     * @param integer $new_height New height
     *
     * @return integer
     */
    protected function getSizeByFixedHeight($new_height)
    {
        $ratio     = $this->width / $this->height;
        $new_width = $new_height * $ratio;

        return floor($new_width);
    }

    /**
     * Get fixed width
     *
     * @param integer $new_width New width
     *
     * @return integer
     */
    protected function getSizeByFixedWidth($new_width)
    {
        $ratio      = $this->height / $this->width;
        $new_height = $new_width * $ratio;

        return floor($new_height);
    }

    /**
     * Crop image
     *
     * @param integer $new_width  New width
     * @param integer $new_height New height
     * @param integer $source_x   Source x
     * @param integer $source_y   Source y
     *
     * @return \Gc\Media\Image
     */
    protected function crop($new_width, $new_height, $source_x = 0, $source_y = 0)
    {
        $crop               = $this->imageResized;
        $this->imageResized = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled(
            $this->imageResized,
            $this->image,
            0,
            0,
            $source_x,
            $source_y,
            $this->width,
            $this->height,
            $this->width,
            $this->height
        );

        return $this;
    }

    /**
     * Save image
     *
     * @param string  $save_path     Save path
     * @param integer $image_quality Image quality default is 90
     *
     * @return boolean
     */
    public function save($save_path, $image_quality = 90)
    {
        if (empty($this->image) or empty($this->imageResized)) {
            return false;
        }

        $extension = strrchr($save_path, '.');
        $extension = strtolower($extension);
        $return    = false;
        switch($extension) {
            case '.jpg':
            case '.jpeg':
                if (imagetypes() & IMG_JPG) {
                    $return = imagejpeg($this->imageResized, $save_path, $image_quality);
                }
                break;
            case '.gif':
                if (imagetypes() & IMG_GIF) {
                    $return = imagegif($this->imageResized, $save_path);
                }
                break;
            case '.png':
                $scale_quality = round(($image_quality / 100) * 9);

                $invert_scale_quality = 9 - $scale_quality;

                if (imagetypes() & IMG_PNG) {
                     $return = imagepng($this->imageResized, $save_path, $invert_scale_quality);
                }
                break;
            default:
                //Nothing to do
                break;
        }

        imagedestroy($this->imageResized);

        return $return;
    }
}
