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
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
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
     * @param $filename
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
     * @param string $file
     * @return \Gc\Media\Image
     */
    public function open($file)
    {
        $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
        $finfo = finfo_open($const); // return mimetype extension
        $mime = finfo_file($finfo, $file);

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
     * @param integer $newwidth
     * @param integer $newheight
     * @param string option can be (auto|crop)
     * @param string $background_color
     * @param integer $source_x
     * @param integer $source_y
     * @return \Gc\Media\Image
     */
    public function resize(
        $newwidth,
        $newheight,
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
            $optimalwidth = $newwidth;
            $optimalheight = $newheight;
            if ($this->width < $newwidth) {
                $optimalwidth = $this->width;
            }

            if ($this->height < $newheight) {
                $optimalheight = $this->height;
            }

            $this->crop($optimalwidth, $optimalheight, $source_x, $source_y);
        } else {
            $optimalheight = $this->getSizeByFixedWidth($newwidth);
            $optimalwidth = $this->getSizeByFixedHeight($newheight);
            if ($optimalheight > $newheight) {
                $optimalheight = $this->getSizeByFixedWidth($optimalwidth);
            } elseif ($optimalwidth > $newwidth) {
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

        $tmpimage = @imagecreatetruecolor($newwidth, $newheight);

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

        $dst_x = (int)(0.5 * ($newwidth - $optimalwidth));
        $dst_y = (int)(0.5 * ($newheight - $optimalheight));
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
     * @param string $hex_string
     * @return array
     */
    public function hex2rgb($hex_string)
    {
        $hex_string = preg_replace('/[^0-9A-Fa-f]/', '', $hex_string); // Gets a proper hex string
        $rgb_array = array();
        if (strlen($hex_string) == 6) {
            $color_val = hexdec($hex_string);
            $rgb_array['red'] = 0xFF & ($color_val >> 0x10);
            $rgb_array['green'] = 0xFF & ($color_val >> 0x8);
            $rgb_array['blue'] = 0xFF & $color_val;
        } elseif (strlen($hex_string) == 3) {
            $rgb_array['red'] = hexdec(str_repeat(substr($hex_string, 0, 1), 2));
            $rgb_array['green'] = hexdec(str_repeat(substr($hex_string, 1, 1), 2));
            $rgb_array['blue'] = hexdec(str_repeat(substr($hex_string, 2, 1), 2));
        } else {
            return false;
        }

        return $rgb_array;
    }

    /**
     * Get fixed height
     *
     * @param integer $newheight
     * @return integer
     */
    protected function getSizeByFixedHeight($newheight)
    {
        $ratio = $this->width / $this->height;
        $newwidth = $newheight * $ratio;

        return floor($newwidth);
    }

    /**
     * Get fixed width
     *
     * @param integer $newwidth
     * @return integer
     */
    protected function getSizeByFixedWidth($newwidth)
    {
        $ratio = $this->height / $this->width;
        $newheight = $newwidth * $ratio;

        return floor($newheight);
    }

    /**
     * Crop image
     *
     * @param integer $newwidth
     * @param integer $newheight
     * @param integer $source_x
     * @param integer $source_y
     * @return \Gc\Media\Image
     */
    protected function crop($newwidth, $newheight, $source_x = 0, $source_y = 0)
    {
        $crop = $this->imageResized;
        $this->imageResized = imagecreatetruecolor($newwidth, $newheight);
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
     * @param string save path
     * @param integer image quality
     * @return boolean
     */
    public function save($save_path, $image_quality = 90)
    {
        if (empty($this->image) or empty($this->imageResized)) {
            return false;
        }

        $extension = strrchr($save_path, '.');
        $extension = strtolower($extension);
        $return = false;
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
